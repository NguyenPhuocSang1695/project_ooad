<?php
require_once './connect.php';

// Tạo kết nối
$db = new DatabaseConnection();
$db->connect();
$conn = $db->getConnection();

// Lấy ID voucher cần xóa
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = intval($_GET['id']);

    // Kiểm tra voucher có tồn tại không
    $check_sql = "SELECT * FROM vouchers WHERE id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows === 0) {
        echo "<script>alert('Voucher không tồn tại hoặc đã bị xóa!'); history.back();</script>";
        exit;
    }

    // Xóa voucher
    $sql = "DELETE FROM vouchers WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo "<script>alert('Xóa voucher thành công!'); window.location.href='../index/voucherManage.php';</script>";
    } else {
        echo "<script>alert('Xóa voucher thất bại!'); history.back();</script>";
    }

    $stmt->close();
    $check_stmt->close();
} else {
    echo "<script>alert('Thiếu ID voucher hợp lệ!'); history.back();</script>";
}

$db->close();
