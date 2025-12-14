<?php
/**
 * OUTSINC - Logout API Endpoint
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../../config/config.php';

$auth = new Auth();
$result = $auth->logout();

echo json_encode($result);
?>
