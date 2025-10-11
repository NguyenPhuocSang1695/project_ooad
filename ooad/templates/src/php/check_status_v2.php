<?php
require_once 'connect.php';
require_once 'token.php'; // file giải mã token

if (isset($_COOKIE['token'])) {
    // Có token => kiểm tra status
    $sql = "SELECT Status FROM users WHERE Username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $loggedInUsername);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if ($user['Status'] == 'Block') {
            // User bị block => xóa token + báo lỗi
            setcookie('token', '', time() - 3600, '/');
            echo "<script>
                    alert('Tài khoản đã bị khóa.');
                    window.location.href='../index.php';
                  </script>";
            exit();
        }
    } else {
        // Không tìm thấy user => xóa token
        setcookie('token', '', time() - 3600, '/');
    }
}
// Nếu không có token thì mặc kệ, vẫn cho truy cập bình thường
?>
