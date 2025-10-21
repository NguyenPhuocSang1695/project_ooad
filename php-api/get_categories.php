<?php
require_once './connectdb.php'; // Đảm bảo đường dẫn đúng đến tệp connectdb.php của bạn

$conn = connect_db();  // Kết nối cơ sở dữ liệu

$sql = "SELECT CategoryID, CategoryName FROM categories ORDER BY CategoryID ASC";
$result = $conn->query($sql);

$categories = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row; // Thêm các danh mục vào mảng
    }
}

// Trả về danh mục dưới dạng JSON
echo json_encode($categories);

$conn->close();
