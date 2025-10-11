<?php
session_start();

$response = ['success' => false];

if (isset($_POST['product_id'], $_POST['quantity'])) {
    $product_id = (int) $_POST['product_id'];
    $quantity = (int) $_POST['quantity'];

    if ($product_id > 0 && $quantity > 0 && isset($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['ProductID'] == $product_id) {
                $item['Quantity'] = $quantity;
                $response['success'] = true;
                break;
            }
        }
    } else {
        $response['message'] = "Dữ liệu không hợp lệ hoặc chưa có giỏ hàng.";
    }
} else {
    $response['message'] = "Thiếu dữ liệu.";
}

header('Content-Type: application/json');
echo json_encode($response);
exit;
