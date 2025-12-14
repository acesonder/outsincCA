<?php
/**
 * OUTSINC - Add Client API
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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

// Validate required fields
if (!isset($input['first_name']) || !isset($input['last_name'])) {
    echo json_encode(['success' => false, 'message' => 'First name and last name are required']);
    exit();
}

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Start transaction
    $conn->beginTransaction();
    
    // Generate a simple user ID for quick client intake (worker doesn't know DOB yet)
    $userId = strtoupper(substr($input['first_name'], 0, 3) . substr($input['last_name'], 0, 3) . rand(1000, 9999));
    
    // Check if user_id exists, regenerate if needed
    $checkQuery = "SELECT id FROM users WHERE user_id = :user_id";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bindParam(':user_id', $userId);
    $checkStmt->execute();
    while ($checkStmt->rowCount() > 0) {
        $userId = strtoupper(substr($input['first_name'], 0, 3) . substr($input['last_name'], 0, 3) . rand(1000, 9999));
        $checkStmt->bindParam(':user_id', $userId);
        $checkStmt->execute();
    }
    
    // Create temporary password (client can set later)
    $tempPassword = password_hash('temporary123', PASSWORD_DEFAULT);
    
    // Insert user
    $userQuery = "INSERT INTO users 
                  (user_id, username, password_hash, role, first_name, last_name, phone, status)
                  VALUES
                  (:user_id, :username, :password_hash, :role, :first_name, :last_name, :phone, 'active')";
    
    $userStmt = $conn->prepare($userQuery);
    $role = ROLE_CLIENT;
    $userStmt->bindParam(':user_id', $userId);
    $userStmt->bindParam(':username', $userId);
    $userStmt->bindParam(':password_hash', $tempPassword);
    $userStmt->bindParam(':role', $role);
    $userStmt->bindParam(':first_name', $input['first_name']);
    $userStmt->bindParam(':last_name', $input['last_name']);
    $phone = $input['phone'] ?? null;
    $userStmt->bindParam(':phone', $phone);
    $userStmt->execute();
    
    $newUserId = $conn->lastInsertId();
    
    // Create client profile
    $clientQuery = "INSERT INTO clients (user_id, preferred_name, alias)
                   VALUES (:user_id, :preferred_name, :alias)";
    $clientStmt = $conn->prepare($clientQuery);
    $clientStmt->bindParam(':user_id', $newUserId);
    $preferredName = $input['preferred_name'] ?? null;
    $alias = $input['alias'] ?? null;
    $clientStmt->bindParam(':preferred_name', $preferredName);
    $clientStmt->bindParam(':alias', $alias);
    $clientStmt->execute();
    
    // Create user preferences
    $prefQuery = "INSERT INTO user_preferences (user_id) VALUES (:user_id)";
    $prefStmt = $conn->prepare($prefQuery);
    $prefStmt->bindParam(':user_id', $newUserId);
    $prefStmt->execute();
    
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Client added successfully',
        'client' => [
            'id' => $newUserId,
            'user_id' => $userId,
            'first_name' => $input['first_name'],
            'last_name' => $input['last_name']
        ]
    ]);
} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollBack();
    }
    echo json_encode([
        'success' => false,
        'message' => 'Error adding client: ' . $e->getMessage()
    ]);
}
?>
