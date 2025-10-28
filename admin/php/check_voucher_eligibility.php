<?php
require_once 'connect.php';
header('Content-Type: application/json; charset=utf-8');

/**
 * API: Check if customer is eligible for voucher based on HISTORICAL SPENDING
 * POST /admin/php/check_voucher_eligibility.php
 * 
 * Input:
 * - customer_phone (string): Số điện thoại khách hàng
 * - voucher_id (int): ID của voucher
 * - current_order_total (float, optional): Tổng tiền đơn hàng hiện tại (cho hiển thị)
 * 
 * Output:
 * {
 *   "success": true/false,
 *   "eligible": true/false,
 *   "message": "...",
 *   "voucher": {...},
 *   "customer_total_spent": X,
 *   "estimated_discount": Y
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
    $voucherId = $input['voucher_id'] ?? null;
    $currentOrderTotal = $input['current_order_total'] ?? 0;
    
    if (!$customerPhone || !$voucherId) {
        echo json_encode([
            'success' => false,
            'message' => 'Thiếu dữ liệu (customer_phone, voucher_id)',
            'eligible' => false
        ], JSON_UNESCAPED_UNICODE);
        exit();
    }
    
    $db = new DatabaseConnection();
    $db->connect();
    
    // 1. Get voucher info
    $voucherResult = $db->queryPrepared(
        "SELECT id, name, percen_decrease, conditions, status FROM vouchers WHERE id = ?",
        [$voucherId]
    );
    
    if (!$voucherResult || $voucherResult->num_rows === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Voucher không tồn tại',
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
            'message' => 'Voucher này không còn hoạt động',
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
            'customer_total_spent' => 0,
            'required_amount' => $voucher['conditions']
        ], JSON_UNESCAPED_UNICODE);
        exit();
    }
    
    // 5. Check if HISTORICAL total meets condition
    if ($totalHistoricalSpent < $voucher['conditions']) {
        $needed = $voucher['conditions'] - $totalHistoricalSpent;
        echo json_encode([
            'success' => true,
            'eligible' => false,
            'message' => 'Tổng tiền lịch sử mua hàng của khách (' . number_format($totalHistoricalSpent, 0, ',', '.') . ' VNĐ) chưa đạt yêu cầu. Cần thêm ' . number_format($needed, 0, ',', '.') . ' VNĐ',
            'voucher' => $voucher,
            'customer_total_spent' => $totalHistoricalSpent,
            'required_amount' => $voucher['conditions'],
            'needed_amount' => $needed
        ], JSON_UNESCAPED_UNICODE);
        exit();
    }
    
    // 6. All conditions met - customer eligible
    $estimatedDiscount = ($currentOrderTotal * $voucher['percen_decrease']) / 100;
    
    echo json_encode([
        'success' => true,
        'eligible' => true,
        'message' => 'Khách hàng đủ điều kiện áp dụng voucher (tổng tiền lịch sử: ' . number_format($totalHistoricalSpent, 0, ',', '.') . ' VNĐ)',
        'voucher' => $voucher,
        'customer_total_spent' => $totalHistoricalSpent,
        'previous_order_count' => $previousOrderCount,
        'estimated_discount' => $estimatedDiscount
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