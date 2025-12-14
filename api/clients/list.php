<?php
/**
 * OUTSINC - List Clients API
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../../config/config.php';

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Check permissions
if (!$auth->hasAnyRole([ROLE_WORKER, ROLE_ADMIN])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit();
}

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    $query = "SELECT u.id, u.user_id, u.first_name, u.last_name, u.email, u.phone, 
                     c.preferred_name, c.alias, c.housing_status
              FROM users u
              LEFT JOIN clients c ON u.id = c.user_id
              WHERE u.role = :role AND u.status = 'active'
              ORDER BY u.last_name, u.first_name";
    
    $stmt = $conn->prepare($query);
    $role = ROLE_CLIENT;
    $stmt->bindParam(':role', $role);
    $stmt->execute();
    
    $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'clients' => $clients
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching clients: ' . $e->getMessage()
    ]);
}
?>
