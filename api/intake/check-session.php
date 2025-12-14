<?php
/**
 * Check for existing incomplete intake session
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
$userRole = $_SESSION['role'];

try {
    // Check for incomplete intake session
    $stmt = $conn->prepare("
        SELECT session_id, client_id, current_step, form_data, created_at, updated_at
        FROM intake_sessions
        WHERE (user_id = ? OR client_id = ?)
        AND status = 'in_progress'
        AND updated_at > DATE_SUB(NOW(), INTERVAL 30 DAY)
        ORDER BY updated_at DESC
        LIMIT 1
    ");
    
    $stmt->bind_param("si", $userId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $session = $result->fetch_assoc();
        echo json_encode([
            'success' => true,
            'session' => $session
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'session' => null
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
