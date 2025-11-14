<?php
require_once 'VoucherManager.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $manager = new VoucherManager();

    $name = $_POST['name'] ?? '';
    $percen_decrease = $_POST['percen_decrease'] ?? '';
    $condition = $_POST['condition'] ?? '';
    $status = $_POST['status'] ?? '';

    $result = $manager->addVoucher($name, $percen_decrease, $condition, $status);

    echo $result['message'];

    // Nếu muốn return JSON:
    // header('Content-Type: application/json');
    // echo json_encode($result);
} else {
    echo "Phương thức không hợp lệ!";
}
