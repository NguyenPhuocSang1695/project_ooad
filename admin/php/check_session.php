<?php
require_once __DIR__ . '/connect.php'; // luôn dùng đường dẫn tuyệt đối

session_name('admin_session');
session_start();

// Tạo kết nối OOP
$db = new DatabaseConnection();
$db->connect();
$myconn = $db->getConnection();

// Kiểm tra session đăng nhập
if (isset($_SESSION['Username'])) {
    $username = $_SESSION['Username'];

    $result = $db->queryPrepared(
        "SELECT Status FROM users WHERE Username = ? AND Role = 'admin'",
        [$username]
    );

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if ($user['Status'] === 'Block') {
            session_unset();
            echo "<script>
                alert('Tài khoản của bạn đã bị khóa 🔒');
                window.location.href = '../../index.php';
            </script>";
            exit();
        }
    }
}
