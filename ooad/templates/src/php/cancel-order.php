<?php
require_once('connect.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderID = isset($_POST['orderID']) ? intval($_POST['orderID']) : 0;

    if ($orderID > 0) {
        // Check order status first
        $checkStatus = "SELECT Status FROM orders WHERE OrderID = ?";
        $stmt = $conn->prepare($checkStatus);
        $stmt->bind_param("i", $orderID);
        $stmt->execute();
        $result = $stmt->get_result();
        $orderStatus = $result->fetch_assoc()['Status'];

        // Check for both ship and fail status
        if ($orderStatus == 'ship') {
            echo json_encode(['success' => false, 'error' => 'Cannot cancel orders that are being shipped']);
            exit;
        }

        if ($orderStatus == 'fail') {
            echo json_encode(['success' => false, 'error' => 'Order is already cancelled']);
            exit;
        }

        if ($orderStatus == 'success') {
            echo json_encode(['success' => false, 'error' => 'Order is dilivered']);
            exit;
        }

        // Proceed with cancellation if not shipped or failed
        $sql = "UPDATE orders SET Status = 'fail' WHERE OrderID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $orderID);

        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Database error']);
        }

        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid order ID']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}

$conn->close();
