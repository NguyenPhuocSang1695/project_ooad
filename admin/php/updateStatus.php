<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'connect.php';

if ($myconn->connect_error) {
    die(json_encode(['success' => false, 'error' => 'Connection failed: ' . $myconn->connect_error]));
}

$myconn->set_charset("utf8");

// Đọc và kiểm tra input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data || !isset($data['orderId']) || !isset($data['status'])) {
    die(json_encode(['success' => false, 'error' => 'Invalid input data']));
}

$orderId = $myconn->real_escape_string($data['orderId']);
$newStatus = $myconn->real_escape_string($data['status']);

// Kiểm tra trạng thái hợp lệ
$validStatuses = ['execute','confirmed','ship', 'success', 'fail'];
if (!in_array($newStatus, $validStatuses)) {
    die(json_encode(['success' => false, 'error' => 'Invalid status value']));
}

// Cập nhật trạng thái
$sql = "UPDATE orders SET Status = ? WHERE OrderID = ?";
$stmt = $myconn->prepare($sql);
if (!$stmt) {
    die(json_encode(['success' => false, 'error' => 'Prepare failed: ' . $myconn->error]));
}

$stmt->bind_param('ss', $newStatus, $orderId);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'No order found with ID: ' . $orderId]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Update failed: ' . $stmt->error]);
}

$stmt->close();
$myconn->close();
?>