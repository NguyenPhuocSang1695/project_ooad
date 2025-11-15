<?php
require_once 'connect.php';
header('Content-Type: application/json; charset=utf-8');

/**

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
 *   "customer_name": "Tên khách hàng (nếu tìm thấy)",
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
            'order_count' => 0,
            'customer_name' => null
        ], JSON_UNESCAPED_UNICODE);
        exit();
    }
    
    $db = new DatabaseConnection();
    $db->connect();
    
    // Tìm FullName từ bảng users
    $userResult = $db->queryPrepared(
        "SELECT FullName FROM users WHERE Phone = ? LIMIT 1",
        [$customerPhone]
    );
    
    $customerName = null;
    $isInUserTable = false;
    
    if ($userResult && $userResult->num_rows > 0) {
        $userRow = $userResult->fetch_assoc();
        $customerName = $userRow['FullName'];
        $isInUserTable = true;  // Số điện thoại tồn tại trong bảng users
    }
    
    // Lấy lịch sử mua từ bảng orders để hiển thị thông tin
    $result = $db->queryPrepared(
        "SELECT OrderID, TotalAmount, DateGeneration FROM orders 
         WHERE Phone = ? 
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
    
    // hasPurchased dựa trên việc có tồn tại trong users table hay không
    // Nếu không trong users thì dù có order cũng là khách hàng mới
    $hasPurchased = $isInUserTable;
    
    echo json_encode([
        'success' => true,
        'has_purchased' => $hasPurchased,
        'total_spent' => $totalSpent,
        'order_count' => count($orders),
        'customer_name' => $customerName,
        'orders' => $orders
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi server: ' . $e->getMessage(),
        'has_purchased' => false,
        'total_spent' => 0,
        'order_count' => 0,
        'customer_name' => null
    ], JSON_UNESCAPED_UNICODE);
}
?>
