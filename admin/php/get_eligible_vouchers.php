<?php
require_once 'connect.php';
header('Content-Type: application/json; charset=utf-8');

/**
 * API: Get eligible vouchers for customer
 * POST /admin/php/get_eligible_vouchers.php
 * 
 * Input:
 * - customer_phone (string): Số điện thoại khách hàng
 * 
 * Output:
 * {
 *   "success": true/false,
 *   "eligible_vouchers": [
 *     {
 *       "id": 6,
 *       "name": "Khách hàng Vip 1",
 *       "percen_decrease": 18,
 *       "conditions": 200000,
 *       "total_spent": 500000
 *     }
 *   ]
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
            'eligible_vouchers' => []
        ], JSON_UNESCAPED_UNICODE);
        exit();
    }
    
    $db = new DatabaseConnection();
    $db->connect();
    
    // Get all active vouchers
    $voucherResult = $db->queryPrepared(
        "SELECT id, name, percen_decrease, conditions, status FROM vouchers WHERE status = 'active' ORDER BY percen_decrease DESC",
        []
    );
    
    if (!$voucherResult) {
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi truy vấn vouchers',
            'eligible_vouchers' => []
        ], JSON_UNESCAPED_UNICODE);
        exit();
    }
    
    // Get customer's total spending from all orders (any status)
    $historyResult = $db->queryPrepared(
        "SELECT SUM(TotalAmount) as total_spent, COUNT(*) as order_count FROM orders WHERE Phone = ?",
        [$customerPhone]
    );
    
    $historyData = $historyResult->fetch_assoc();
    $totalHistoricalSpent = $historyData['total_spent'] ?? 0;
    $previousOrderCount = $historyData['order_count'] ?? 0;
    
    // Filter eligible vouchers
    $eligibleVouchers = [];
    
    // Show vouchers to repeat customers if their total spending meets condition
    if ($previousOrderCount > 0) {
        while ($voucher = $voucherResult->fetch_assoc()) {
            // Check if customer total spending meets voucher condition
            if ($totalHistoricalSpent >= $voucher['conditions']) {
                $eligibleVouchers[] = [
                    'id' => (int)$voucher['id'],
                    'name' => $voucher['name'],
                    'percen_decrease' => (int)$voucher['percen_decrease'],
                    'conditions' => (int)$voucher['conditions'],
                    'total_spent' => (int)$totalHistoricalSpent
                ];
            }
        }
    }
    
    echo json_encode([
        'success' => true,
        'eligible_vouchers' => $eligibleVouchers,
        'is_repeat_customer' => $previousOrderCount > 0,
        'total_spent' => (int)$totalHistoricalSpent,
        'message' => $previousOrderCount === 0 
            ? 'Khách hàng mới - Chưa có voucher phù hợp'
            : ($eligibleVouchers ? 'Có ' . count($eligibleVouchers) . ' voucher thỏa điều kiện' : 'Không có voucher thỏa điều kiện (tổng tiền chưa đủ)')
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi server: ' . $e->getMessage(),
        'eligible_vouchers' => []
    ], JSON_UNESCAPED_UNICODE);
}
?>