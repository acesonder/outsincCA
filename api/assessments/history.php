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

$clientId = $_GET['client_id'] ?? null;

if (!$clientId) {
    echo json_encode(['success' => false, 'message' => 'Client ID required']);
    exit;
}

try {
    $db = getDBConnection();
    
    // Get all completed assessments for this client
    $stmt = $db->prepare("
        SELECT id, scores, completed_at, created_at
        FROM needs_assessments 
        WHERE client_id = ? AND status = 'completed'
        ORDER BY completed_at DESC
    ");
    $stmt->execute([$clientId]);
    $assessments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Decode scores
    foreach ($assessments as &$assessment) {
        $assessment['scores'] = json_decode($assessment['scores'], true) ?? [];
    }
    
    echo json_encode([
        'success' => true,
        'assessments' => $assessments
    ]);
    
} catch (Exception $e) {
    error_log("Assessment history error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>
