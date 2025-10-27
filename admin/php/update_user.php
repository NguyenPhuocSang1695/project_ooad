<?php
// JSON endpoint: update user information
ob_start();
header('Content-Type: application/json; charset=utf-8');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ']);
        exit;
    }

    require_once __DIR__ . '/connect.php';
    require_once __DIR__ . '/UserManager.php';

    // Collect payload safely
    $payload = [
        'username' => isset($_POST['username']) ? trim($_POST['username']) : '',
        'fullname' => isset($_POST['fullname']) ? trim($_POST['fullname']) : '',
        'email'    => isset($_POST['email']) ? trim($_POST['email']) : '',
        'phone'    => isset($_POST['phone']) ? trim($_POST['phone']) : '',
        'role'     => isset($_POST['role']) ? trim($_POST['role']) : 'customer',
        'status'   => isset($_POST['status']) ? trim($_POST['status']) : 'Active',
        'province' => isset($_POST['province']) && $_POST['province'] !== '' ? (int)$_POST['province'] : null,
        'district' => isset($_POST['district']) && $_POST['district'] !== '' ? (int)$_POST['district'] : null,
        'ward'     => isset($_POST['ward']) && $_POST['ward'] !== '' ? (int)$_POST['ward'] : null,
        'address'  => isset($_POST['address']) ? trim($_POST['address']) : '',
    ];

    $userManager = new UserManager($myconn ?? null);
    $result = $userManager->updateUser($payload);

    // Ensure clean JSON output
    ob_clean();
    echo json_encode($result);
    exit;
} catch (Throwable $e) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Lỗi máy chủ: ' . $e->getMessage()]);
    exit;
}

?>
