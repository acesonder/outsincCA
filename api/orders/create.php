<?php
/**
 * OUTSINC - Create Order API
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../../config/config.php';

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Check permissions
if (!$auth->hasAnyRole([ROLE_WORKER, ROLE_ADMIN])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

// Validate required fields
if (!isset($input['client_id']) || !isset($input['products']) || !isset($input['delivery_method'])) {
    echo json_encode(['success' => false, 'message' => 'Client, products, and delivery method are required']);
    exit();
}

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Start transaction
    $conn->beginTransaction();
    
    // Generate order number
    $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    
    // Determine delivery details
    $pickupDropoff = $input['delivery_method'];
    $location = $pickupDropoff === 'pickup' 
        ? ($input['pickup_location'] ?? null) 
        : ($input['dropoff_address'] ?? null);
    
    $scheduledTime = null;
    if ($pickupDropoff === 'pickup' && isset($input['pickup_time'])) {
        // Convert pickup time to datetime (today + time)
        $timeValue = strtotime($input['pickup_time']);
        if ($timeValue !== false) {
            $scheduledTime = date('Y-m-d') . ' ' . date('H:i:s', $timeValue);
        }
    } elseif ($pickupDropoff === 'dropoff' && isset($input['dropoff_time'])) {
        // Validate datetime format
        $timeValue = strtotime($input['dropoff_time']);
        if ($timeValue !== false) {
            $scheduledTime = date('Y-m-d H:i:s', $timeValue);
        }
    }
    
    // Insert order
    $orderQuery = "INSERT INTO orders 
                   (order_number, client_id, order_date, status, pickup_dropoff, 
                    scheduled_time, location, instructions, created_by)
                   VALUES
                   (:order_number, :client_id, NOW(), 'pending', :pickup_dropoff,
                    :scheduled_time, :location, :instructions, :created_by)";
    
    $orderStmt = $conn->prepare($orderQuery);
    $orderStmt->bindParam(':order_number', $orderNumber);
    $orderStmt->bindParam(':client_id', $input['client_id']);
    $orderStmt->bindParam(':pickup_dropoff', $pickupDropoff);
    $orderStmt->bindParam(':scheduled_time', $scheduledTime);
    $orderStmt->bindParam(':location', $location);
    $instructions = $input['instructions'] ?? null;
    $orderStmt->bindParam(':instructions', $instructions);
    $orderStmt->bindParam(':created_by', $_SESSION['user_id']);
    $orderStmt->execute();
    
    $orderId = $conn->lastInsertId();
    
    // Insert order items
    $itemQuery = "INSERT INTO order_items (order_id, product_id, quantity)
                  VALUES (:order_id, :product_id, :quantity)";
    $itemStmt = $conn->prepare($itemQuery);
    
    foreach ($input['products'] as $product) {
        $itemStmt->bindParam(':order_id', $orderId);
        $itemStmt->bindParam(':product_id', $product['id']);
        $itemStmt->bindParam(':quantity', $product['quantity']);
        $itemStmt->execute();
    }
    
    // Add case note if provided
    if (!empty($input['case_notes'])) {
        $noteQuery = "INSERT INTO case_notes 
                      (client_id, note_date, note_type, note_text, created_by)
                      VALUES
                      (:client_id, NOW(), 'harm_reduction', :note_text, :created_by)";
        $noteStmt = $conn->prepare($noteQuery);
        $noteStmt->bindParam(':client_id', $input['client_id']);
        $noteStmt->bindParam(':note_text', $input['case_notes']);
        $noteStmt->bindParam(':created_by', $_SESSION['user_id']);
        $noteStmt->execute();
    }
    
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Order created successfully',
        'order_id' => $orderId,
        'order_number' => $orderNumber
    ]);
} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollBack();
    }
    echo json_encode([
        'success' => false,
        'message' => 'Error creating order: ' . $e->getMessage()
    ]);
}
?>
