<?php
/**
 * Grant Consent API
 * Creates a new consent record with digital signature or worker attestation
 */

require_once '../../config/database.php';
require_once '../../includes/Auth.php';

header('Content-Type: application/json');

// Check authentication
$auth = new Auth($conn);
if (!$auth->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

// Validate required fields
$clientId = $data['client_id'] ?? null;
$category = $data['category'] ?? null;
$duration = $data['duration'] ?? null;
$signatureType = $data['signature_type'] ?? 'digital';

if (!$clientId || !$category || !$duration) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

// Validate category
$validCategories = ['housing', 'health', 'mental_health', 'legal', 'financial', 'general'];
if (!in_array($category, $validCategories)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid consent category']);
    exit;
}

try {
    // Calculate expiry date
    $expiryDate = null;
    if ($duration === '6months') {
        $expiryDate = date('Y-m-d H:i:s', strtotime('+6 months'));
    } elseif ($duration === '1year') {
        $expiryDate = date('Y-m-d H:i:s', strtotime('+1 year'));
    }
    // 'ongoing' leaves expiry_date as null

    // Get signature data or worker attestation
    $signatureData = $data['signature_data'] ?? null;
    $workerAttestation = null;
    
    if ($signatureType === 'verbal') {
        $workerAttestation = json_encode($data['worker_attestation'] ?? []);
    }

    // Check if there's an active consent for this category
    $checkStmt = $conn->prepare("
        SELECT consent_id, status 
        FROM consents 
        WHERE client_id = ? AND category = ? AND status = 'active'
        ORDER BY granted_date DESC 
        LIMIT 1
    ");
    $checkStmt->bind_param("ss", $clientId, $category);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        // Revoke old consent first
        $oldConsent = $checkResult->fetch_assoc();
        $revokeStmt = $conn->prepare("
            UPDATE consents 
            SET status = 'revoked', 
                revoked_date = NOW(), 
                revoked_by = ?,
                revoke_reason = 'Replaced by new consent'
            WHERE consent_id = ?
        ");
        $userId = $_SESSION['user_id'];
        $revokeStmt->bind_param("si", $userId, $oldConsent['consent_id']);
        $revokeStmt->execute();
    }

    // Insert new consent
    $stmt = $conn->prepare("
        INSERT INTO consents (
            client_id, 
            category, 
            status, 
            granted_date, 
            expiry_date, 
            signature_type, 
            signature_data, 
            worker_attestation,
            granted_by
        ) VALUES (?, ?, 'active', NOW(), ?, ?, ?, ?, ?)
    ");
    
    $grantedBy = $_SESSION['user_id'];
    $stmt->bind_param(
        "sssssss",
        $clientId,
        $category,
        $expiryDate,
        $signatureType,
        $signatureData,
        $workerAttestation,
        $grantedBy
    );

    if ($stmt->execute()) {
        $consentId = $conn->insert_id;

        // Log audit trail
        $auditStmt = $conn->prepare("
            INSERT INTO audit_log (
                user_id,
                action,
                entity_type,
                entity_id,
                details,
                created_at
            ) VALUES (?, 'consent_granted', 'consent', ?, ?, NOW())
        ");
        $details = json_encode([
            'category' => $category,
            'duration' => $duration,
            'signature_type' => $signatureType,
            'expiry_date' => $expiryDate
        ]);
        $auditStmt->bind_param("sis", $grantedBy, $consentId, $details);
        $auditStmt->execute();

        // Schedule expiry notification if applicable
        if ($expiryDate) {
            $notificationDate = date('Y-m-d H:i:s', strtotime($expiryDate . ' -7 days'));
            $notifStmt = $conn->prepare("
                INSERT INTO notifications (
                    user_id,
                    type,
                    title,
                    message,
                    scheduled_for,
                    status
                ) VALUES (?, 'consent_expiry', 'Consent Expiring Soon', ?, ?, 'pending')
            ");
            $message = "Consent for {$category} will expire in 7 days. Please review with the client.";
            $notifStmt->bind_param("sss", $clientId, $message, $notificationDate);
            $notifStmt->execute();
        }

        echo json_encode([
            'success' => true,
            'message' => 'Consent granted successfully',
            'consent_id' => $consentId
        ]);
    } else {
        throw new Exception('Failed to create consent record');
    }

} catch (Exception $e) {
    error_log("Error in grant consent API: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error'
    ]);
}
?>
