<?php
    session_start();
    require_once('../src/php/connect.php');

    // Lấy dữ liệu từ fetch
    $product_id = isset($_POST['product_id']) ? (int) $_POST['product_id'] : 0;
    $quantity = isset($_POST['quantity']) ? (int) $_POST['quantity'] : 1;

    $response = ['success' => false];

    if ($product_id > 0) {
        $sql = "SELECT * FROM products WHERE ProductID = $product_id";
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            $product = $result->fetch_assoc();

            $item = [
                'ProductID' => $product['ProductID'],
                'ProductName' => $product['ProductName'],
                'Price' => $product['Price'],
                'ImageURL' => $product['ImageURL'],
                'Quantity' => $quantity
            ];

            // Nếu đã có giỏ hàng
            if (!isset($_SESSION['cart'])) {
                $_SESSION['cart'] = [];
            }

            $found = false;

            foreach ($_SESSION['cart'] as &$cart_item) {
                if ($cart_item['ProductID'] == $product_id) {
                    $cart_item['Quantity'] += $quantity;
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $_SESSION['cart'][] = $item;
            }

            // Đếm số lượng sản phẩm khác nhau trong giỏ hàng
            $total_items = count($_SESSION['cart']);
            
            // Tính tổng tiền
            $total_price = 0;
            foreach ($_SESSION['cart'] as $ci) {
                $total_price += $ci['Price'] * $ci['Quantity'];
            }

            $response['success'] = true;
            $response['totalQuantity'] = $total_items; // Thay đổi từ tổng số lượng thành số lượng sản phẩm khác nhau
            $response['total_price'] = $total_price;
            $response['cart_items'] = $_SESSION['cart'];
        } else {
            $response['message'] = "Không tìm thấy sản phẩm.";
        }
    } else {
        $response['message'] = "Thiếu thông tin sản phẩm.";
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
