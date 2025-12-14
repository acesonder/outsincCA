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
$clientId = $data['client_id'] ?? null;

if (!$clientId) {
    echo json_encode(['success' => false, 'message' => 'Client ID required']);
    exit;
}

try {
    $db = getDBConnection();
    
    // Check for existing incomplete assessment
    $stmt = $db->prepare("
        SELECT id, created_at, answers, scores 
        FROM needs_assessments 
        WHERE client_id = ? AND status = 'in_progress' 
        ORDER BY created_at DESC 
        LIMIT 1
    ");
    $stmt->execute([$clientId]);
    $existingAssessment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existingAssessment) {
        // Resume existing assessment
        $assessmentId = $existingAssessment['id'];
        $existingAnswers = json_decode($existingAssessment['answers'], true) ?? [];
    } else {
        // Create new assessment
        $stmt = $db->prepare("
            INSERT INTO needs_assessments (client_id, worker_id, status, created_at)
            VALUES (?, ?, 'in_progress', NOW())
        ");
        $stmt->execute([$clientId, $user['user_id']]);
        $assessmentId = $db->lastInsertId();
        $existingAnswers = [];
    }
    
    // Get baseline assessment (first completed assessment)
    $stmt = $db->prepare("
        SELECT scores, completed_at 
        FROM needs_assessments 
        WHERE client_id = ? AND status = 'completed' 
        ORDER BY completed_at ASC 
        LIMIT 1
    ");
    $stmt->execute([$clientId]);
    $baselineAssessment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($baselineAssessment) {
        $baselineAssessment['scores'] = json_decode($baselineAssessment['scores'], true);
    }
    
    echo json_encode([
        'success' => true,
        'assessment_id' => $assessmentId,
        'existing_answers' => $existingAnswers,
        'baseline_assessment' => $baselineAssessment
    ]);
    
} catch (Exception $e) {
    error_log("Assessment start error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>
