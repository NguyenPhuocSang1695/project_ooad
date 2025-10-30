<?php
require_once 'connect.php';

$db = new DatabaseConnection();
$db->connect();
$conn = $db->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $percen_decrease = $_POST['percen_decrease'] ?? '';
    $condition = $_POST['condition'] ?? '';
    $status = $_POST['status'] ?? '';

    // Kiểm tra dữ liệu
    if (empty($name) || $percen_decrease === '' || $condition === '' || empty($status)) {
        echo "Vui lòng nhập đầy đủ thông tin!";
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO vouchers (name, percen_decrease, conditions, status) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sdss", $name, $percen_decrease, $condition, $status);

    if ($stmt->execute()) {
        echo "Thêm voucher thành công!";
    } else {
        echo "Lỗi khi thêm voucher!";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Phương thức không hợp lệ!";
}
