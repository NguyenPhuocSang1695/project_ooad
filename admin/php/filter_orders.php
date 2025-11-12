<?php
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 0);
require_once 'connect.php';
require_once 'Services/OrderService.php';

try {
    $db = new DatabaseConnection();
    $db->connect();
    $orderService = new OrderService($db);
    
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $limit = 5;
    
    // Build filters array
    $filters = [];
    if (!empty($_GET['search'])) $filters['search'] = $_GET['search'];
    if (!empty($_GET['date_from'])) $filters['date_from'] = $_GET['date_from'];
    if (!empty($_GET['date_to'])) $filters['date_to'] = $_GET['date_to'];
    if (!empty($_GET['order_status']) && $_GET['order_status'] !== 'all') $filters['order_status'] = $_GET['order_status'];
    if (!empty($_GET['price_min'])) $filters['price_min'] = floatval($_GET['price_min']);
    if (!empty($_GET['price_max'])) $filters['price_max'] = floatval($_GET['price_max']);
    if (!empty($_GET['voucher_filter'])) $filters['voucher_filter'] = $_GET['voucher_filter'];
    if (!empty($_GET['specific_voucher'])) $filters['specific_voucher'] = intval($_GET['specific_voucher']);
    
    // Get orders using OOP service
    $result = $orderService->listOrders($filters, $page, $limit);
    
    echo json_encode([
        'success' => true,
        'orders' => $result['orders'],
        'total_pages' => $result['total_pages'],
        'current_page' => $result['current_page']
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>