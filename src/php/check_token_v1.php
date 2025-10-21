<?php
// Kiểm tra xem cookie 'token' có tồn tại và không rỗng
if (!empty($_COOKIE['token'])) {
    echo "<script>
        window.location.replace('../index.php');
    </script>";
    exit;  // Dừng thực thi mã còn lại sau khi chuyển hướng
}
?>
