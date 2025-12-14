<?php
/**
 * OUTSINC - Get Security Question API Endpoint
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['first_name']) || !isset($input['last_name']) || !isset($input['date_of_birth'])) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit();
}

$auth = new Auth();
$result = $auth->getSecurityQuestion($input['first_name'], $input['last_name'], $input['date_of_birth']);

echo json_encode($result);
?>
