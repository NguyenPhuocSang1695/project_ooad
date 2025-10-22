<?php
session_name('admin_session');
session_start();
header('Content-Type: application/json');
require_once './Account.php';

if (!isset($_SESSION['Username'])) {
    echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
    exit;
}

$account = new Account($_SESSION['Username']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'fullname' => $_POST['fullname'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'email' => $_POST['email'] ?? '',
        'address_detail' => $_POST['address_detail'] ?? '',
        'ward_id' => $_POST['ward_id'] ?? ''
    ];

    $result = $account->updateAccount($data);
    echo json_encode($result);
    exit;
}

// Nếu GET hoặc các request khác
echo json_encode(['success' => false, 'message' => 'Yêu cầu không hợp lệ']);
