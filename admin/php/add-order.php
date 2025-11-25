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

    // Check stock for all products and categorize warnings
    $myconn = $db->getConnection();
    $outOfStockProducts = [];      // Trường hợp 1: Sản phẩm hết hàng (quantity = 0)
    $insufficientStockProducts = []; // Trường hợp 2: Số lượng mua vượt quá tồn kho
    
    foreach ($data['products'] as $product) {
        $productId = intval($product['product_id']);
        $quantity = intval($product['quantity']);
        
        $stockStmt = $myconn->prepare("SELECT quantity_in_stock, ProductName FROM products WHERE ProductID = ?");
        $stockStmt->bind_param("i", $productId);
        $stockStmt->execute();
        $stockResult = $stockStmt->get_result();
        
        if ($stockResult->num_rows === 0) {
            throw new Exception("Sản phẩm ID #" . $productId . " không tồn tại");
        }
        
        $productData = $stockResult->fetch_assoc();
        $availableStock = intval($productData['quantity_in_stock']);
        $productName = $productData['ProductName'];
        
        // Trường hợp 1: Sản phẩm hết hàng (quantity_in_stock = 0)
        if ($availableStock == 0) {
            $outOfStockProducts[] = "Sản phẩm: " . $productName. " đã hết hàng";
        }
        // Trường hợp 2: Số lượng mua vượt quá tồn kho nhưng còn hàng
        else if ($quantity > $availableStock) {
            $insufficientStockProducts[] = $productName . " - Chỉ còn " . $availableStock . " sản phẩm trong kho";
        }
        
        $stockStmt->close();
    }
    
    // Trường hợp 1: Nếu có sản phẩm hết hàng
    if (!empty($outOfStockProducts)) {
        http_response_code(200);
        echo json_encode([
            'success' => false,
            'warning' => true,
            'type' => 'out_of_stock',
            'message' => 'Thông báo: ',
            'details' => $outOfStockProducts
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Trường hợp 2: Nếu số lượng mua vượt quá tồn kho
    if (!empty($insufficientStockProducts)) {
        http_response_code(200);
        echo json_encode([
            'success' => false,
            'warning' => true,
            'type' => 'insufficient_stock',
            'message' => 'Thông báo: ',
            'details' => $insufficientStockProducts
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Trường hợp 3: Tất cả sản phẩm có đủ hàng - cứ tạo đơn bình thường

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
