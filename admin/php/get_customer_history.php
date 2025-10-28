<?php
require_once 'connect.php';
header('Content-Type: application/json; charset=utf-8');

/**
 * API: Get customer purchase history
 * POST /admin/php/get_customer_history.php
 * 
 * Input:
 * - customer_phone (string): Số điện thoại khách hàng
 * 
 * Output:
 * {
 *   "success": true/false,
 *   "has_purchased": true/false,
 *   "total_spent": 0,
 *   "order_count": 0,
 *   "orders": [...]
 * }
 */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed'], JSON_UNESCAPED_UNICODE);
    exit();
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $customerPhone = $input['customer_phone'] ?? '';
    
    if (!$customerPhone) {
        echo json_encode([
            'success' => false,
            'message' => 'Thiếu customer_phone',
            'has_purchased' => false,
            'total_spent' => 0,
            'order_count' => 0
        ], JSON_UNESCAPED_UNICODE);
        exit();
    }
    
    $db = new DatabaseConnection();
    $db->connect();
    
    // Get all orders (successful only) for this phone number
    $result = $db->queryPrepared(
        "SELECT OrderID, TotalAmount, DateGeneration, Status FROM orders 
         WHERE Phone = ? AND Status = 'success' 
         ORDER BY DateGeneration DESC",
        [$customerPhone]
    );
    
    $orders = [];
    $totalSpent = 0;
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $totalSpent += $row['TotalAmount'];
            $orders[] = $row;
        }
    }
    
    $hasPurchased = count($orders) > 0;
    
    echo json_encode([
        'success' => true,
        'has_purchased' => $hasPurchased,
        'total_spent' => $totalSpent,
        'order_count' => count($orders),
        'orders' => $orders
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi server: ' . $e->getMessage(),
        'has_purchased' => false,
        'total_spent' => 0,
        'order_count' => 0
    ], JSON_UNESCAPED_UNICODE);
}
?>