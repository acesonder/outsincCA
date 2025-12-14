<?php
/**
 * Complete intake and create client record
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
$formData = $input['form_data'] ?? [];
$clientId = $input['client_id'] ?? null;

try {
    $conn->begin_transaction();
    
    // Create or update client record
    if (!$clientId) {
        // Extract client info from form data
        $firstName = $formData['first_name'] ?? '';
        $lastName = $formData['last_name'] ?? '';
        $preferredName = $formData['preferred_name'] ?? '';
        $dob = $formData['date_of_birth'] ?? null;
        $phone = $formData['phone'] ?? '';
        $email = $formData['email'] ?? '';
        $housingStatus = $formData['housing_status'] ?? '';
        $emergencyContact = $formData['emergency_contact'] ?? '';
        $emergencyPhone = $formData['emergency_phone'] ?? '';
        
        $stmt = $conn->prepare("
            INSERT INTO clients 
            (first_name, last_name, preferred_name, date_of_birth, phone, email, 
             housing_status, emergency_contact_name, emergency_contact_phone, 
             intake_completed_at, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), NOW())
        ");
        $stmt->bind_param("sssssssss", 
            $firstName, $lastName, $preferredName, $dob, $phone, $email,
            $housingStatus, $emergencyContact, $emergencyPhone
        );
        $stmt->execute();
        $clientId = $conn->insert_id;
    }
    
    // Store full intake data in needs_assessments table
    $assessmentData = json_encode($formData);
    $stmt = $conn->prepare("
        INSERT INTO needs_assessments 
        (client_id, assessment_type, assessment_data, completed_by, completed_at, created_at)
        VALUES (?, 'full_intake', ?, ?, NOW(), NOW())
    ");
    $stmt->bind_param("iss", $clientId, $assessmentData, $userId);
    $stmt->execute();
    $assessmentId = $conn->insert_id;
    
    // Update intake session status
    if ($sessionId) {
        $stmt = $conn->prepare("
            UPDATE intake_sessions
            SET status = 'completed', client_id = ?, completed_at = NOW(), updated_at = NOW()
            WHERE session_id = ?
        ");
        $stmt->bind_param("is", $clientId, $sessionId);
        $stmt->execute();
    }
    
    // Analyze intake for resource suggestions
    $suggestedResources = getSuggestedResources($formData, $conn);
    
    // Generate PDF summary
    $pdfUrl = generateIntakePDF($clientId, $formData);
    
    // Create baseline QOL assessment if data provided
    if (isset($formData['qol_stress']) || isset($formData['qol_mood'])) {
        createBaselineQOL($clientId, $formData, $conn);
    }
    
    // Schedule 6-month follow-up assessment
    scheduleFollowUpAssessment($clientId, $conn);
    
    // Log audit trail
    $stmt = $conn->prepare("
        INSERT INTO audit_log (user_id, action, entity_type, entity_id, details, created_at)
        VALUES (?, 'intake_completed', 'client', ?, ?, NOW())
    ");
    $details = json_encode(['assessment_id' => $assessmentId]);
    $stmt->bind_param("sis", $userId, $clientId, $details);
    $stmt->execute();
    
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Intake completed successfully',
        'client_id' => $clientId,
        'assessment_id' => $assessmentId,
        'suggested_resources' => $suggestedResources,
        'pdf_url' => $pdfUrl
    ]);
    
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode([
        'success' => false,
        'message' => 'Error completing intake: ' . $e->getMessage()
    ]);
}

function getSuggestedResources($formData, $conn) {
    $suggestions = [];
    
    // Housing resources
    if (isset($formData['housing_status']) && in_array($formData['housing_status'], ['homeless', 'unstable', 'couch_surfing'])) {
        $stmt = $conn->prepare("
            SELECT resource_id, name, description, contact_info
            FROM resources
            WHERE category = 'housing' AND status = 'active'
            LIMIT 3
        ");
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $suggestions[] = [
                'name' => $row['name'],
                'description' => $row['description'],
                'contact' => $row['contact_info']
            ];
        }
    }
    
    // Mental health resources
    if (isset($formData['mental_health_support']) && $formData['mental_health_support'] === 'yes') {
        $stmt = $conn->prepare("
            SELECT resource_id, name, description, contact_info
            FROM resources
            WHERE category = 'mental_health' AND status = 'active'
            LIMIT 2
        ");
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $suggestions[] = [
                'name' => $row['name'],
                'description' => $row['description'],
                'contact' => $row['contact_info']
            ];
        }
    }
    
    return $suggestions;
}

function generateIntakePDF($clientId, $formData) {
    // In production, this would use a PDF library like TCPDF or FPDF
    // For now, return a placeholder URL
    return "/api/intake/generate-pdf.php?client_id={$clientId}";
}

function createBaselineQOL($clientId, $formData, $conn) {
    $stress = $formData['qol_stress'] ?? null;
    $mood = $formData['qol_mood'] ?? null;
    $safety = $formData['qol_safety'] ?? null;
    $hope = $formData['qol_hope'] ?? null;
    $connection = $formData['qol_connection'] ?? null;
    
    $stmt = $conn->prepare("
        INSERT INTO qol_assessments
        (client_id, assessment_type, stress_level, mood_level, safety_level, 
         hope_level, connection_level, assessment_date, created_at)
        VALUES (?, 'baseline', ?, ?, ?, ?, ?, NOW(), NOW())
    ");
    $stmt->bind_param("iiiiii", $clientId, $stress, $mood, $safety, $hope, $connection);
    $stmt->execute();
}

function scheduleFollowUpAssessment($clientId, $conn) {
    // Schedule 6-month follow-up
    $stmt = $conn->prepare("
        INSERT INTO scheduled_tasks
        (task_type, entity_type, entity_id, scheduled_for, status, created_at)
        VALUES ('assessment_followup', 'client', ?, DATE_ADD(NOW(), INTERVAL 6 MONTH), 'pending', NOW())
    ");
    $stmt->bind_param("i", $clientId);
    $stmt->execute();
}
