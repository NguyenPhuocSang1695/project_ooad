<?php
// Kết nối database
require_once 'connect.php'; // file connect db
require_once 'token.php';   // file bạn vừa gửi, để lấy $loggedInUsername

// Truy vấn kiểm tra Status
$sql = "SELECT Status FROM users WHERE Username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $loggedInUsername);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    if ($user['Status'] == 'Block') {
        // Xóa token
        setcookie('token', '', time() - 3600, '/');

        // Hiện alert + redirect
        echo "<script>
                alert('Tài khoản đã bị khóa.');
                window.location.href='../index.php';
              </script>";
        exit();
    }
    // Nếu Active thì cho dùng bình thường
} else {
    // Không tìm thấy user => xóa token + báo lỗi
    setcookie('token', '', time() - 3600, '/');
    echo "<script>
            alert('Không tìm thấy người dùng.');
            window.location.href='../index.php';
          </script>";
    exit();
}
?>
