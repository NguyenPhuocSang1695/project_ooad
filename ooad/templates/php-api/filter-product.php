<?php
require_once './connectdb.php'; // Kết nối database
$conn = connect_db();
if (isset($_GET['category_id'])) {
    $category_id = intval($_GET['category_id']);

    $sql = "SELECT * FROM products WHERE CategoryID = ? AND Status = 'appear'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }

    echo json_encode($products);
} else {
    echo json_encode(["error" => "Không tìm thấy sản phẩm!"]);
}
