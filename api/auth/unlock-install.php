<?php
/**
 * OUTSINC - Deployment Unlock API
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$passcode = $input['passcode'] ?? null;

if (!$passcode) {
    echo json_encode(['success' => false, 'message' => 'Passcode is required']);
    exit();
}

if ($passcode === DEPLOYMENT_PASSCODE) {
    $_SESSION['wizard_unlocked'] = true;
    echo json_encode(['success' => true]);
    exit();
}

http_response_code(401);
echo json_encode(['success' => false, 'message' => 'Invalid passcode']);
?>
