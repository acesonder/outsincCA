<?php
require_once '../../config/database.php';
require_once '../../includes/Auth.php';

header('Content-Type: application/json');

$auth = new Auth();
$user = $auth->getUser();

if (!$user) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$assessmentId = $data['assessment_id'] ?? null;
$answers = $data['answers'] ?? [];
$scores = $data['scores'] ?? [];

if (!$assessmentId) {
    echo json_encode(['success' => false, 'message' => 'Assessment ID required']);
    exit;
}

try {
    $db = getDBConnection();
    
    // Update assessment with current answers and scores
    $stmt = $db->prepare("
        UPDATE needs_assessments 
        SET answers = ?, scores = ?, updated_at = NOW() 
        WHERE id = ?
    ");
    $stmt->execute([
        json_encode($answers),
        json_encode($scores),
        $assessmentId
    ]);
    
    // Check for high-risk scores and create alerts
    foreach ($scores as $domain => $score) {
        if ($score >= 7) {
            // Insert notification for assigned workers
            $stmt = $db->prepare("
                INSERT INTO notifications (user_id, type, message, created_at, status)
                SELECT worker_id, 'high_risk_alert', 
                       CONCAT('High risk detected in ', ?, ' assessment (Score: ', ?, '/10) for client ID: ', ?),
                       NOW(), 'unread'
                FROM case_assignments 
                WHERE client_id = (SELECT client_id FROM needs_assessments WHERE id = ?)
            ");
            $stmt->execute([$domain, $score, $data['client_id'] ?? 'Unknown', $assessmentId]);
        }
    }
    
    // Log to audit trail
    $stmt = $db->prepare("
        INSERT INTO audit_log (user_id, action, entity_type, entity_id, details, created_at)
        VALUES (?, 'save_assessment_progress', 'assessment', ?, ?, NOW())
    ");
    $stmt->execute([
        $user['user_id'],
        $assessmentId,
        json_encode(['scores' => $scores])
    ]);
    
    echo json_encode(['success' => true, 'message' => 'Progress saved']);
    
} catch (Exception $e) {
    error_log("Assessment save error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>
