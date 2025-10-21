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
    // Chuẩn bị truy vấn
    $stmt = $myconn->prepare("SELECT Status FROM users WHERE Username = ? AND Role = 'admin'");
    $stmt->bind_param("s", $_SESSION['Username']);
    $stmt->execute();
    $result = $stmt->get_result();

    // Kiểm tra trạng thái tài khoản
    if ($result->num_rows > 0) {
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
    $stmt->close();
} else {
    // Nếu chưa đăng nhập → chuyển về trang đăng nhập
    header("Location: ../index.php");
    exit();
}
