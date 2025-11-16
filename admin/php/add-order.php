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

    error_log("[ADD_ORDER] Incoming data: " . json_encode($data));

    // Validate required fields
    if (empty($data['payment_method'])) {
        throw new Exception('Missing payment_method');
    }
    if (empty($data['products']) || !is_array($data['products']) || count($data['products']) == 0) {
        throw new Exception('No products in order');
    }

    // Format customer phone - ensure it starts with 0
    $customerPhone = $data['customer_phone'] ?? '';
    if (!empty($customerPhone) && $customerPhone[0] !== '0') {
        $customerPhone = '0' . $customerPhone;
    }
    
    // Set customer name and phone
    $customerName = $data['customer_name'] ?? '';
    $customerPhone = !empty($customerPhone) ? $customerPhone : '';

    // Connect to database
    $db = new DatabaseConnection();
    $db->connect();

    // Get username from session (for logging/auditing purposes)
    $username = isset($_SESSION['Username']) ? $_SESSION['Username'] : null;
    
    if (!$username) {
        throw new Exception('User not authenticated - Username not found in session');
    }

    // Check if customer phone exists in users table - if yes, get user_id and name if not provided
    $userId = null;
    $myconn = $db->getConnection();
    $phoneStmt = $myconn->prepare("SELECT user_id, FullName FROM users WHERE Phone = ?");
    $phoneStmt->bind_param("s", $customerPhone);
    $phoneStmt->execute();
    $phoneResult = $phoneStmt->get_result();
    
    if ($phoneResult->num_rows > 0) {
        // Customer already exists
        $phoneData = $phoneResult->fetch_assoc();
        $userId = $phoneData['user_id'];
        
        // If customer name is not provided, use name from users table
        if (empty($customerName) || $customerName === 'Không có') {
            $customerName = $phoneData['FullName'] ?? 'Không có';
            error_log("[ADD_ORDER] Customer found: Phone=" . $customerPhone . ", user_id=" . $userId . ", auto-filled name: " . $customerName);
        } else {
            error_log("[ADD_ORDER] Customer found: Phone=" . $customerPhone . ", user_id=" . $userId . ", provided name: " . $customerName);
        }
    } else {
        // New customer - set user_id to null
        $userId = null;
        error_log("[ADD_ORDER] New customer: Phone=" . $customerPhone . ", user_id=null");
    }
    $phoneStmt->close();

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
    $orderService = new OrderManager($db);
    $voucherId = $data['voucher_id'] ?? null;
    
    error_log("[ADD_ORDER] Creating order with voucher_id: " . ($voucherId ?? 'null') . ", user_id: " . ($userId ?? 'null'));
    
    $orderId = $orderService->createOrder(
        $userId,
        $customerName,
        $customerPhone,
        $data['payment_method'],
        $data['products'],
        $addressId,
        $voucherId
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
