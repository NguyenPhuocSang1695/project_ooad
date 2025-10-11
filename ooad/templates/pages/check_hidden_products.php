<?php
require_once '../php-api/connectdb.php';

header('Content-Type: application/json');

try {
    $conn = connect_db();

    // Truy vấn các sản phẩm bị ẩn
    $sql = "SELECT ProductID FROM products WHERE Status = 'hidden';";
    $result = $conn->query($sql);

    $hidden_products = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $hidden_products[] = (int)$row['ProductID'];
        }
    }

    echo json_encode(['hidden_products' => $hidden_products]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Lỗi khi lấy danh sách sản phẩm bị ẩn']);
}
?>