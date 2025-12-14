<?php
/**
 * List Consents API
 * Returns all consents for a specific client
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

// Get client ID
$clientId = $_GET['client_id'] ?? null;

if (!$clientId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Client ID required']);
    exit;
}

try {
    // Get all consent categories
    $categories = ['housing', 'health', 'mental_health', 'legal', 'financial', 'general'];
    $consents = [];

    foreach ($categories as $category) {
        // Get most recent consent for each category
        $stmt = $conn->prepare("
            SELECT 
                c.consent_id,
                c.client_id,
                c.category,
                c.status,
                c.granted_date,
                c.expiry_date,
                c.signature_type,
                c.signature_data,
                c.worker_attestation
            FROM consents c
            WHERE c.client_id = ? AND c.category = ?
            ORDER BY c.granted_date DESC
            LIMIT 1
        ");
        
        $stmt->bind_param("ss", $clientId, $category);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            // Check if expired
            if ($row['expiry_date'] && strtotime($row['expiry_date']) < time()) {
                $row['status'] = 'expired';
            }
            
            // Don't send signature data in list view
            unset($row['signature_data']);
            
            $consents[] = $row;
        } else {
            // No consent exists for this category
            $consents[] = [
                'client_id' => $clientId,
                'category' => $category,
                'status' => 'none',
                'consent_id' => null,
                'granted_date' => null,
                'expiry_date' => null,
                'signature_type' => null,
                'worker_attestation' => null
            ];
        }
    }

    echo json_encode([
        'success' => true,
        'consents' => $consents
    ]);

} catch (Exception $e) {
    error_log("Error in list consents API: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error'
    ]);
}
?>
