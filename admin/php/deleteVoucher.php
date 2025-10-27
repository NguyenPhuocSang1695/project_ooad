<?php
require_once './connect.php';

// Kết nối database
$db = new DatabaseConnection();
$db->connect();
$conn = $db->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';

    if (empty($id) || !is_numeric($id)) {
        echo "ID voucher không hợp lệ!";
        exit;
    }

    // Kiểm tra voucher có tồn tại
    $check_sql = "SELECT * FROM vouchers WHERE id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows === 0) {
        echo "Voucher không tồn tại hoặc đã bị xóa!";
        exit;
    }

    // Tiến hành xóa
    $delete_sql = "DELETE FROM vouchers WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $id);

    if ($delete_stmt->execute()) {
        echo "Xóa voucher thành công!";
    } else {
        echo "Xóa voucher thất bại!";
    }

    $delete_stmt->close();
    $check_stmt->close();
} else {
    echo "Phương thức không hợp lệ!";
}

$db->close();
