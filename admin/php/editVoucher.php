<?php
require_once './connect.php';

// Tạo kết nối
$db = new DatabaseConnection();
$db->connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';

    if (empty($id)) {
        die("Thiếu ID voucher.");
    }

    // Lấy dữ liệu hiện tại để giữ nguyên giá trị cũ
    $oldData = $db->queryPrepared("SELECT * FROM vouchers WHERE id = ?", [$id], "i")->fetch_assoc();
    if (!$oldData) die("Voucher không tồn tại.");

    // Lấy giá trị mới, nếu không nhập thì dùng giá trị cũ
    $name = trim($_POST['name'] ?? $oldData['name']);
    $percen_decrease = $_POST['percen_decrease'] !== '' ? $_POST['percen_decrease'] : $oldData['percen_decrease'];
    $conditions = $_POST['condition'] !== '' ? $_POST['condition'] : $oldData['condition'];
    $status = $_POST['status'] ?? $oldData['status'];

    // Cập nhật
    $sql = "UPDATE vouchers 
            SET name = ?, 
                percen_decrease = ?, 
                conditions = ?, 
                status = ? 
            WHERE id = ?";
    $params = [$name, $percen_decrease, $conditions, $status, $id];
    $types = "sdiss";

    $result = $db->queryPrepared($sql, $params, $types);

    if ($result) {
        echo "Cập nhật voucher thành công!";
    } else {
        echo "Không có thay đổi nào hoặc lỗi khi cập nhật.";
    }
}
