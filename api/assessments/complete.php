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
$clientId = $data['client_id'] ?? null;
$answers = $data['answers'] ?? [];
$scores = $data['scores'] ?? [];

if (!$assessmentId || !$clientId) {
    echo json_encode(['success' => false, 'message' => 'Assessment ID and Client ID required']);
    exit;
}

try {
    $db = getDBConnection();
    
    // Complete the assessment
    $stmt = $db->prepare("
        UPDATE needs_assessments 
        SET answers = ?, scores = ?, status = 'completed', completed_at = NOW(), updated_at = NOW()
        WHERE id = ?
    ");
    $stmt->execute([
        json_encode($answers),
        json_encode($scores),
        $assessmentId
    ]);
    
    // Schedule 6-month follow-up assessment
    $stmt = $db->prepare("
        INSERT INTO scheduled_tasks (client_id, task_type, scheduled_date, status, created_at)
        VALUES (?, 'assessment_followup', DATE_ADD(NOW(), INTERVAL 6 MONTH), 'pending', NOW())
    ");
    $stmt->execute([$clientId]);
    
    // Create notification for completion
    $stmt = $db->prepare("
        INSERT INTO notifications (user_id, type, message, created_at, status)
        SELECT worker_id, 'assessment_completed', 
               CONCAT('Needs assessment completed for client ID: ', ?),
               NOW(), 'unread'
        FROM case_assignments 
        WHERE client_id = ?
    ");
    $stmt->execute([$clientId, $clientId]);
    
    // Log to audit trail
    $stmt = $db->prepare("
        INSERT INTO audit_log (user_id, action, entity_type, entity_id, details, created_at)
        VALUES (?, 'complete_assessment', 'assessment', ?, ?, NOW())
    ");
    $stmt->execute([
        $user['user_id'],
        $assessmentId,
        json_encode(['scores' => $scores, 'client_id' => $clientId])
    ]);
    
    // Generate PDF (placeholder - would use a PDF library like TCPDF or mPDF)
    $pdfUrl = "/api/assessments/export-pdf.php?assessment_id=" . $assessmentId;
    
    echo json_encode([
        'success' => true,
        'message' => 'Assessment completed successfully',
        'pdf_url' => $pdfUrl
    ]);
    
} catch (Exception $e) {
    error_log("Assessment completion error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>
