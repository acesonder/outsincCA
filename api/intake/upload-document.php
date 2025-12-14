<?php
/**
 * Upload documents during intake process
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
$sessionId = $_POST['session_id'] ?? null;

if (!$sessionId) {
    echo json_encode(['success' => false, 'message' => 'Session ID required']);
    exit;
}

// Create upload directory if it doesn't exist
$uploadDir = '../../uploads/intake/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

try {
    $uploadedFiles = [];
    
    foreach ($_FILES as $key => $file) {
        if ($file['error'] === UPLOAD_ERR_OK) {
            // Validate file type
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
            if (!in_array($file['type'], $allowedTypes)) {
                continue;
            }
            
            // Validate file size (max 5MB)
            if ($file['size'] > 5 * 1024 * 1024) {
                continue;
            }
            
            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = $sessionId . '_' . uniqid() . '.' . $extension;
            $filepath = $uploadDir . $filename;
            
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                // Store in database
                $stmt = $conn->prepare("
                    INSERT INTO document_uploads
                    (session_id, user_id, filename, original_filename, file_path, file_type, file_size, uploaded_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
                ");
                $stmt->bind_param("ssssssi", 
                    $sessionId, $userId, $filename, $file['name'], $filepath, $file['type'], $file['size']
                );
                $stmt->execute();
                
                $uploadedFiles[] = [
                    'filename' => $filename,
                    'original_name' => $file['name'],
                    'size' => $file['size']
                ];
            }
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => count($uploadedFiles) . ' file(s) uploaded',
        'files' => $uploadedFiles
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Upload error: ' . $e->getMessage()
    ]);
}
