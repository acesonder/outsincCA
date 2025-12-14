<?php
/**
 * Save intake step progress (auto-save)
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

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

$sessionId = $input['session_id'] ?? null;
$currentStep = intval($input['current_step'] ?? 1);
$formData = json_encode($input['form_data'] ?? []);
$clientId = $input['client_id'] ?? null;

try {
    if ($sessionId) {
        // Update existing session
        $stmt = $conn->prepare("
            UPDATE intake_sessions
            SET current_step = ?, form_data = ?, updated_at = NOW()
            WHERE session_id = ? AND (user_id = ? OR client_id = ?)
        ");
        $stmt->bind_param("issis", $currentStep, $formData, $sessionId, $userId, $userId);
        $stmt->execute();
        
        echo json_encode([
            'success' => true,
            'session_id' => $sessionId,
            'message' => 'Progress saved'
        ]);
        
    } else {
        // Create new session
        $sessionId = uniqid('intake_', true);
        
        $stmt = $conn->prepare("
            INSERT INTO intake_sessions 
            (session_id, user_id, client_id, current_step, form_data, status, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, 'in_progress', NOW(), NOW())
        ");
        $stmt->bind_param("ssiss", $sessionId, $userId, $clientId, $currentStep, $formData);
        $stmt->execute();
        
        echo json_encode([
            'success' => true,
            'session_id' => $sessionId,
            'message' => 'Session created'
        ]);
    }
    
    // Log audit trail
    $stmt = $conn->prepare("
        INSERT INTO audit_log (user_id, action, entity_type, entity_id, details, created_at)
        VALUES (?, 'intake_progress_saved', 'intake_session', ?, ?, NOW())
    ");
    $details = json_encode(['step' => $currentStep]);
    $stmt->bind_param("sss", $userId, $sessionId, $details);
    $stmt->execute();
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
