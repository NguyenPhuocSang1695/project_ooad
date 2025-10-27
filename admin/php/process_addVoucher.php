<?php
require_once 'connect.php';
$db = new DatabaseConnection();
$db->connect();
$conn = $db->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $percen_decrease = $_POST['percen_decrease'];
    $condition = $_POST['condition'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("INSERT INTO vouchers (name, percen_decrease, conditions, status) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sdss", $name, $percen_decrease, $condition, $status);

    if ($stmt->execute()) {
        echo "<script>alert('Thêm voucher thành công!'); window.location.href='../index/voucherManage.php';</script>";
    } else {
        echo "<script>alert('Lỗi khi thêm voucher'); window.history.back();</script>";
    }

    $stmt->close();
    $conn->close();
}
