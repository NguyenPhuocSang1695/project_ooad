<?php
session_name('admin_session');
session_start();
header('Content-Type: application/json; charset=utf-8');

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once 'connect.php';
require_once 'Services/OrderService.php';

try {
    // Validate method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }


    $rawInput = file_get_contents('php://input');
    $data = json_decode($rawInput, true);
    
    if (!$data) {
        throw new Exception('Invalid JSON data');
    }


    if (empty($data['customer_name'])) {
        throw new Exception('Missing customer_name');
    }
    if (empty($data['customer_phone'])) {
        throw new Exception('Missing customer_phone');
    }
    if (empty($data['payment_method'])) {
        throw new Exception('Missing payment_method');
    }
    if (empty($data['products']) || !is_array($data['products']) || count($data['products']) == 0) {
        throw new Exception('No products in order');
    }

    // Connect to database
    $db = new DatabaseConnection();
    $db->connect();

    // Get username from session
    $username = isset($_SESSION['Username']) ? $_SESSION['Username'] : null;

    // Handle address if provided
    $addressId = null;
    if (!empty($data['address']['ward_id']) && !empty($data['address']['address_detail'])) {
        error_log("[ADD_ORDER] Creating address: " . json_encode($data['address']));
        
        $wardId = intval($data['address']['ward_id']);
        $addressDetail = $data['address']['address_detail'];
        
        $myconn = $db->getConnection();
        $stmt = $myconn->prepare("INSERT INTO address (ward_id, address_detail) VALUES (?, ?)");
        $stmt->bind_param("is", $wardId, $addressDetail);
        $stmt->execute();
        
        if ($stmt->error) {
            error_log("[ADD_ORDER] Address creation error: " . $stmt->error);
        } else {
            $addressId = $stmt->insert_id;
            error_log("[ADD_ORDER] Address created with ID: " . $addressId);
        }
        $stmt->close();
    }

    // Use OrderService to create order
    $orderService = new OrderService($db);
    
    $status = $data['status'] ?? 'execute';
    error_log("[ADD_ORDER] Creating order with status: " . $status);
    
    $orderId = $orderService->createOrder(
        $username,
        $data['customer_name'],
        $data['customer_phone'],
        $data['payment_method'],
        $data['products'],
        $addressId,
        $status
    );
    
    error_log("[ADD_ORDER] SUCCESS - Order #" . $orderId . " created");

    // Get total amount from created order
    $order = $orderService->getOrder($orderId);
    $totalAmount = $order->getTotalAmount();

    // Send success response
    echo json_encode([
        'success' => true,
        'message' => 'Order created successfully',
        'order_id' => $orderId,
        'total_amount' => $totalAmount
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    error_log("[ADD_ORDER] ERROR: " . $e->getMessage());
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
