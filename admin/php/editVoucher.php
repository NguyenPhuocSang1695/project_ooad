<?php
require_once './VoucherManager.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $manager = new VoucherManager();

    $id = $_POST['id'] ?? '';
    $name = $_POST['name'] ?? null;
    $percen_decrease = isset($_POST['percen_decrease']) && $_POST['percen_decrease'] !== ''
        ? $_POST['percen_decrease']
        : null;
    $condition = isset($_POST['condition']) && $_POST['condition'] !== ''
        ? $_POST['condition']
        : null;
    $status = $_POST['status'] ?? null;

    $result = $manager->editVoucher($id, $name, $percen_decrease, $condition, $status);

    echo $result['message'];

    // Nếu muốn return JSON:
    // header('Content-Type: application/json');
    // echo json_encode($result);
} else {
    echo "Phương thức không hợp lệ!";
}
