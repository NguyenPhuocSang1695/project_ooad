<?php
require_once './VoucherManager.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $manager = new VoucherManager();

    $id = $_POST['id'] ?? '';

    $result = $manager->deleteVoucher($id);

    echo $result['message'];

    // Nếu muốn return JSON:
    // header('Content-Type: application/json');
    // echo json_encode($result);
} else {
    echo "Phương thức không hợp lệ!";
}
