<?php
/**
 * OUTSINC - Login API Endpoint
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['username']) || !isset($input['password'])) {
    echo json_encode(['success' => false, 'message' => 'Username and password are required']);
    exit();
}

$auth = new Auth();
$result = $auth->login($input['username'], $input['password']);

echo json_encode($result);
?>
