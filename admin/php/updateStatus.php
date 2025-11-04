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

    // Read and validate input
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (!$data || !isset($data['orderId']) || !isset($data['status'])) {
        throw new Exception('Invalid input data');
    }

    $orderId = intval($data['orderId']);
    $newStatus = trim($data['status']);

    // Use OOP Service to update status (includes validation via canTransitionTo)
    $orderService = new OrderService($db);
    $orderService->updateStatus($orderId, $newStatus);

    echo json_encode(['success' => true], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
?>