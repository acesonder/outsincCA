<?php
/**
 * Schedule reminder notification for incomplete intake
 */
session_start();
require_once '../../config/database.php';
require_once '../../includes/Auth.php';

header('Content-Type: application/json');

$auth = new Auth($conn);

if (!$auth->isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$userId = $_SESSION['user_id'];

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$sessionId = $input['session_id'] ?? null;

if (!$sessionId) {
    echo json_encode(['success' => false, 'message' => 'Session ID required']);
    exit;
}

try {
    // Schedule notification for 3 days from now
    $stmt = $conn->prepare("
        INSERT INTO notifications
        (user_id, type, title, message, scheduled_for, status, created_at)
        VALUES (?, 'intake_reminder', 'Complete Your Intake', 
                'You have an incomplete intake session. Complete it to access all services.', 
                DATE_ADD(NOW(), INTERVAL 3 DAY), 'pending', NOW())
    ");
    $stmt->bind_param("s", $userId);
    $stmt->execute();
    
    echo json_encode([
        'success' => true,
        'message' => 'Reminder scheduled'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error scheduling reminder: ' . $e->getMessage()
    ]);
}
