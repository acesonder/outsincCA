<?php
/**
 * Revoke Consent API
 * Revokes an active consent with audit trail
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

// Get PUT data
$data = json_decode(file_get_contents('php://input'), true);

// Validate required fields
$consentId = $data['consent_id'] ?? null;
$reason = $data['reason'] ?? null;

if (!$consentId || !$reason) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Consent ID and reason required']);
    exit;
}

try {
    // Check if consent exists and is active
    $checkStmt = $conn->prepare("
        SELECT consent_id, client_id, category, status 
        FROM consents 
        WHERE consent_id = ?
    ");
    $checkStmt->bind_param("i", $consentId);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Consent not found']);
        exit;
    }
    
    $consent = $result->fetch_assoc();
    
    if ($consent['status'] !== 'active') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Consent is not active']);
        exit;
    }

    // Revoke the consent
    $stmt = $conn->prepare("
        UPDATE consents 
        SET status = 'revoked',
            revoked_date = NOW(),
            revoked_by = ?,
            revoke_reason = ?
        WHERE consent_id = ?
    ");
    
    $userId = $_SESSION['user_id'];
    $stmt->bind_param("ssi", $userId, $reason, $consentId);

    if ($stmt->execute()) {
        // Log audit trail
        $auditStmt = $conn->prepare("
            INSERT INTO audit_log (
                user_id,
                action,
                entity_type,
                entity_id,
                details,
                created_at
            ) VALUES (?, 'consent_revoked', 'consent', ?, ?, NOW())
        ");
        $details = json_encode([
            'category' => $consent['category'],
            'reason' => $reason,
            'revoked_by' => $userId
        ]);
        $auditStmt->bind_param("sis", $userId, $consentId, $details);
        $auditStmt->execute();

        // Notify affected agencies/workers
        $notifStmt = $conn->prepare("
            INSERT INTO notifications (
                user_id,
                type,
                title,
                message,
                status
            ) VALUES (?, 'consent_revoked', 'Consent Revoked', ?, 'pending')
        ");
        $message = "Client has revoked consent for {$consent['category']} services. Please update your records immediately.";
        
        // Get all workers assigned to this client
        $workerStmt = $conn->prepare("
            SELECT DISTINCT assigned_worker_id 
            FROM case_notes 
            WHERE client_id = ? AND assigned_worker_id IS NOT NULL
        ");
        $workerStmt->bind_param("s", $consent['client_id']);
        $workerStmt->execute();
        $workers = $workerStmt->get_result();
        
        while ($worker = $workers->fetch_assoc()) {
            $notifStmt->bind_param("ss", $worker['assigned_worker_id'], $message);
            $notifStmt->execute();
        }

        echo json_encode([
            'success' => true,
            'message' => 'Consent revoked successfully'
        ]);
    } else {
        throw new Exception('Failed to revoke consent');
    }

} catch (Exception $e) {
    error_log("Error in revoke consent API: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error'
    ]);
}
?>
