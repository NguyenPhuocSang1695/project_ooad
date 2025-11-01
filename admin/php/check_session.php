<?php
require_once __DIR__ . '/connect.php'; // luôn dùng đường dẫn tuyệt đối
session_name('admin_session');
session_start();
// Tạo kết nối OOP
$db = new DatabaseConnection();
$db->connect();
$myconn = $db->getConnection();

// Kiểm tra session đăng nhập

if (isset($_SESSION['Phone'])) {
    $Phone = $_SESSION['Phone'];

    $result = $db->queryPrepared(
        "SELECT Status FROM users WHERE Phone = ? AND Role = 'admin'",
        [$Phone]
    );

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if ($user['Status'] === 'Block') {
            session_unset();
            echo "<script>
                alert('Tài khoản của bạn đã bị khóa 🔒');
                window.location.href = '../index.php';
            </script>";
            exit();
        }
    }
}

$username = $_SESSION['Username'];

$result = $db->queryPrepared(
    "SELECT Status, FullName, Role FROM users WHERE Username = ? AND Role = 'admin'",
    [$username]
);

if (!$result || $result->num_rows === 0) {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'message' => 'Không tìm thấy người dùng'
    ], JSON_UNESCAPED_UNICODE);
    exit();
}

$user = $result->fetch_assoc();

if ($user['Status'] === 'Block') {
    session_unset();
    http_response_code(403);
    echo json_encode([
        'status' => 'error',
        'message' => 'Tài khoản của bạn đã bị khóa 🔒'
    ], JSON_UNESCAPED_UNICODE);
    exit();
}

// Trả về thông tin người dùng
http_response_code(200);
echo json_encode([
    'status' => 'success',
    'username' => $username,
    'fullname' => $user['FullName'],
    'role' => $user['Role']
], JSON_UNESCAPED_UNICODE);
exit();
