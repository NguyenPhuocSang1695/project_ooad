<?php
error_log('=== START get_eligible_vouchers.php ===');
require_once 'connect.php';
header('Content-Type: application/json; charset=utf-8');

/**
 * POST /admin/php/get_eligible_vouchers.php
 * 
 * Input:
 * - customer_phone (string): Số điện thoại khách hàng
 * 
 * Output:
 * {
 *   "success": true/false,
 *   "eligible_vouchers": [...]
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
    
    // Fetch all vouchers into array first
    $allVouchers = [];
    while ($voucher = $voucherResult->fetch_assoc()) {
        $allVouchers[] = $voucher;
    }
    
    // Kiểm tra xem số điện thoại có tồn tại trong bảng users không
    $userCheckResult = $db->queryPrepared(
        "SELECT user_id FROM users WHERE Phone = ? LIMIT 1",
        [$customerPhone]
    );
    
    $isExistingCustomer = $userCheckResult && $userCheckResult->num_rows > 0;
    $previousOrderCount = 0;
    $totalHistoricalSpent = 0;
    
    // Filter eligible vouchers
    $eligibleVouchers = [];
    
    // Chỉ show vouchers nếu khách hàng tồn tại trong bảng users
    if ($isExistingCustomer) {
        try {
            // Get customer's total spending from all orders (any status)
            $historyResult = $db->queryPrepared(
                "SELECT SUM(TotalAmount) as total_spent, COUNT(*) as order_count FROM orders WHERE Phone = ?",
                [$customerPhone]
            );
            
            if ($historyResult) {
                $historyData = $historyResult->fetch_assoc();
                if ($historyData) {
                    $totalHistoricalSpent = (float)($historyData['total_spent'] ?? 0);
                    $previousOrderCount = (int)($historyData['order_count'] ?? 0);
                }
            }
        } catch (Exception $innerEx) {
            error_log('Lỗi lấy history: ' . $innerEx->getMessage());
        }
        
        // Show vouchers to repeat customers if their total spending meets condition
        if ($previousOrderCount > 0) {
            // Kiểm tra từng voucher
            foreach ($allVouchers as $voucher) {
                // Check if customer total spending meets voucher condition
                if ($totalHistoricalSpent >= (float)$voucher['conditions']) {
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
    }
    
    echo json_encode([
        'success' => true,
        'eligible_vouchers' => $eligibleVouchers,
        'is_existing_customer' => $isExistingCustomer,
        'message' => !$isExistingCustomer 
            ? 'Khách hàng mới'
            : ($previousOrderCount === 0 
                ? 'Khách hàng nhưng chưa có đơn hàng - Chưa có voucher phù hợp'
                : ($eligibleVouchers ? 'Có ' . count($eligibleVouchers) . ' voucher thỏa điều kiện' : 'Không có voucher thỏa điều kiện (tổng tiền chưa đủ)'))
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    error_log('Lỗi get_eligible_vouchers.php: ' . $e->getMessage());
    error_log('Trace: ' . $e->getTraceAsString());
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi server: ' . $e->getMessage(),
        'eligible_vouchers' => [],
        'debug' => [
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    ], JSON_UNESCAPED_UNICODE);
}
error_log('=== END get_eligible_vouchers.php ===');
?>