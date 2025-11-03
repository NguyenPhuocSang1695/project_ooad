<?php
require_once 'connect.php';
header('Content-Type: application/json; charset=utf-8');

/**
 * API: Validate voucher code for customer based on HISTORICAL SPENDING
 * POST /admin/php/validate_voucher_code.php
 * 
 * Input:
 * - voucher_code (string): Mã voucher (name)
 * - customer_phone (string): Số điện thoại khách hàng
 * 
 * Output:
 * {
 *   "success": true/false,
 *   "eligible": true/false,
 *   "message": "...",
 *   "voucher": {...}
 * }
 */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed'], JSON_UNESCAPED_UNICODE);
    exit();
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $voucherCode = $input['voucher_code'] ?? '';
    $customerPhone = $input['customer_phone'] ?? '';
    
    if (!$voucherCode || !$customerPhone) {
        echo json_encode([
            'success' => false,
            'message' => 'Thiếu dữ liệu (voucher_code, customer_phone)',
            'eligible' => false
        ], JSON_UNESCAPED_UNICODE);
        exit();
    }
    
    $db = new DatabaseConnection();
    $db->connect();
    
    // 1. Find voucher by code (name)
    $voucherResult = $db->queryPrepared(
        "SELECT id, name, percen_decrease, conditions, status FROM vouchers WHERE name = ?",
        [$voucherCode]
    );
    
    if (!$voucherResult || $voucherResult->num_rows === 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Mã voucher không tồn tại: ' . htmlspecialchars($voucherCode),
            'eligible' => false
        ], JSON_UNESCAPED_UNICODE);
        exit();
    }
    
    $voucher = $voucherResult->fetch_assoc();
    
    // 2. Check if voucher is active
    if ($voucher['status'] !== 'active') {
        echo json_encode([
            'success' => true,
            'eligible' => false,
            'message' => 'Voucher "' . $voucher['name'] . '" không còn hoạt động',
            'voucher' => $voucher
        ], JSON_UNESCAPED_UNICODE);
        exit();
    }
    
    // 3. Calculate TOTAL HISTORICAL SPENDING (all successful orders)
    $historyResult = $db->queryPrepared(
        "SELECT SUM(TotalAmount) as total_spent, COUNT(*) as order_count 
         FROM orders WHERE Phone = ? AND Status = 'success'",
        [$customerPhone]
    );
    
    $historyData = $historyResult->fetch_assoc();
    $totalHistoricalSpent = $historyData['total_spent'] ?? 0;
    $previousOrderCount = $historyData['order_count'] ?? 0;
    
    // 4. Check if customer has any purchase history
    if ($previousOrderCount === 0) {
        echo json_encode([
            'success' => true,
            'eligible' => false,
            'message' => 'Voucher này chỉ dành cho khách hàng cũ (đã mua hàng trước đó)',
            'voucher' => $voucher,
            'customer_total_spent' => 0
        ], JSON_UNESCAPED_UNICODE);
        exit();
    }
    
    // 5. Check if HISTORICAL total meets condition
    if ($totalHistoricalSpent < $voucher['conditions']) {
        $needed = $voucher['conditions'] - $totalHistoricalSpent;
        echo json_encode([
            'success' => true,
            'eligible' => false,
            'message' => 'Tổng tiền lịch sử (' . number_format($totalHistoricalSpent, 0, ',', '.') . ' VNĐ) chưa đạt yêu cầu ' . number_format($voucher['conditions'], 0, ',', '.') . ' VNĐ',
            'voucher' => $voucher,
            'customer_total_spent' => $totalHistoricalSpent,
            'required_amount' => $voucher['conditions'],
            'needed_amount' => $needed
        ], JSON_UNESCAPED_UNICODE);
        exit();
    }
    
    // 6. All conditions met - customer eligible
    echo json_encode([
        'success' => true,
        'eligible' => true,
        'message' => 'Voucher hợp lệ! Khách hàng đủ điều kiện áp dụng',
        'voucher' => $voucher,
        'customer_total_spent' => $totalHistoricalSpent,
        'previous_order_count' => $previousOrderCount
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi server: ' . $e->getMessage(),
        'eligible' => false
    ], JSON_UNESCAPED_UNICODE);
}
?>