<?php
/**
 * Consent History API
 * Returns complete history of consent changes for a category
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

// Get parameters
$clientId = $_GET['client_id'] ?? null;
$category = $_GET['category'] ?? null;

if (!$clientId || !$category) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Client ID and category required']);
    exit;
}

try {
    // Get all consents for this client and category
    $stmt = $conn->prepare("
        SELECT 
            c.consent_id,
            c.status,
            c.granted_date,
            c.expiry_date,
            c.revoked_date,
            c.revoke_reason,
            c.signature_type,
            u1.first_name as granted_by_fname,
            u1.last_name as granted_by_lname,
            u2.first_name as revoked_by_fname,
            u2.last_name as revoked_by_lname
        FROM consents c
        LEFT JOIN users u1 ON c.granted_by = u1.user_id
        LEFT JOIN users u2 ON c.revoked_by = u2.user_id
        WHERE c.client_id = ? AND c.category = ?
        ORDER BY c.granted_date DESC
    ");
    
    $stmt->bind_param("ss", $clientId, $category);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $history = [];
    
    while ($row = $result->fetch_assoc()) {
        // Create history entries for each action
        
        // Grant action
        $grantedBy = ($row['granted_by_fname'] && $row['granted_by_lname']) 
            ? $row['granted_by_fname'] . ' ' . $row['granted_by_lname'] 
            : 'Unknown';
            
        $history[] = [
            'action_date' => $row['granted_date'],
            'action' => 'Consent Granted',
            'details' => "Granted by {$grantedBy} using {$row['signature_type']} signature" .
                        ($row['expiry_date'] ? " (Expires: " . date('Y-m-d', strtotime($row['expiry_date'])) . ")" : " (No expiry)")
        ];
        
        // Revocation action if applicable
        if ($row['status'] === 'revoked' && $row['revoked_date']) {
            $revokedBy = ($row['revoked_by_fname'] && $row['revoked_by_lname']) 
                ? $row['revoked_by_fname'] . ' ' . $row['revoked_by_lname'] 
                : 'Unknown';
                
            $history[] = [
                'action_date' => $row['revoked_date'],
                'action' => 'Consent Revoked',
                'details' => "Revoked by {$revokedBy}. Reason: {$row['revoke_reason']}"
            ];
        }
        
        // Expiry status if applicable
        if ($row['expiry_date'] && strtotime($row['expiry_date']) < time() && $row['status'] !== 'revoked') {
            $history[] = [
                'action_date' => $row['expiry_date'],
                'action' => 'Consent Expired',
                'details' => 'Consent automatically expired'
            ];
        }
    }
    
    // Sort by date descending
    usort($history, function($a, $b) {
        return strtotime($b['action_date']) - strtotime($a['action_date']);
    });

    echo json_encode([
        'success' => true,
        'history' => $history,
        'category' => $category
    ]);

} catch (Exception $e) {
    error_log("Error in consent history API: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error'
    ]);
}
?>
