<?php
/**
 * OUTSINC - Registration API Endpoint
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

// Validate required fields
$required = ['first_name', 'last_name', 'date_of_birth', 'security_question', 'security_answer', 'password', 'role'];
foreach ($required as $field) {
    if (!isset($input[$field]) || empty($input[$field])) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        exit();
    }
}

// Validate password length
if (strlen($input['password']) < PASSWORD_MIN_LENGTH) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters']);
    exit();
}

// Validate role
$validRoles = [ROLE_CLIENT, ROLE_WORKER, ROLE_PROVIDER, ROLE_ADMIN];
if (!in_array($input['role'], $validRoles)) {
    $input['role'] = ROLE_CLIENT; // Default to client
}

$auth = new Auth();
$result = $auth->register($input);

echo json_encode($result);
?>
