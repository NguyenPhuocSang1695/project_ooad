<?php
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once 'connect.php';
require_once 'Services/OrderService.php';

try {
    // Initialize connection
    $db = new DatabaseConnection();
    $db->connect();
    
    $orderId = isset($_GET['orderId']) ? intval($_GET['orderId']) : 0;

    if (!$orderId) {
        throw new Exception('Order ID is required');
    }

    $orderService = new OrderService($db);
    $orderData = $orderService->getOrderWithDetails($orderId);
    
    /** @var Order $order */
    $order = $orderData['order'];
    
    /** @var OrderDetail[] $details */
    $details = $orderData['details'];

    // Transform products array
    $products = [];
    foreach ($details as $detail) {
        $products[] = [
            'productId' => $detail->getProductId(),
            'productName' => $detail->getProductName() ?: 'Unknown',
            'quantity' => $detail->getQuantity(),
            'unitPrice' => floatval($detail->getUnitPrice()),
            'totalPrice' => floatval($detail->getTotalPrice())
        ];
    }

    // Prepare response
    $response = [
        'success' => true,
        'order' => [
            'orderId' => intval($order->getOrderId()),
            'username' => $order->getUsername(),
            'orderDate' => $order->getDateGeneration(),
            'status' => $order->getStatus(),
            'customerName' => $order->getCustomerName(),
            'customerPhone' => $order->getPhone(),
            'address' => $order->getFullAddress(),
            'paymentMethod' => $order->getPaymentMethod(),
            'totalAmount' => floatval($order->getTotalAmount()),
            'productCount' => count($products),
            'products' => $products
        ]
    ];

    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>