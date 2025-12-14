<?php
/**
 * OUTSINC - Reset Password API Endpoint
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['user_id']) || !isset($input['security_answer']) || !isset($input['new_password'])) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit();
}

// Validate password length
if (strlen($input['new_password']) < PASSWORD_MIN_LENGTH) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters']);
    exit();
}

$auth = new Auth();
$result = $auth->resetPassword($input['user_id'], $input['security_answer'], $input['new_password']);

echo json_encode($result);
?>
