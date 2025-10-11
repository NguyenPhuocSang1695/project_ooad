<?php
// src/php/logout.php

// Bắt đầu session
session_start();

// // Chỉ xóa session_user
// if (isset($_SESSION['session_user'])) {
//     unset($_SESSION['session_user']);
// }

session_unset();
session_destroy();


// Xóa cookie 'token'
setcookie("token", "", time() - 3600, "/");

// Chuyển hướng về trang index.php
header("Location: ../../index.php");
exit();

// Gia Thiệu 
// <?php
// // src/php/logout.php

// // Bắt đầu session
// session_start();

// // Xóa thông tin người dùng trong session (nếu có)
// session_unset();
// session_destroy();

// // Xóa cookie 'token'
// setcookie("token", "", time() - 3600, "/");

// // Chuyển hướng về trang index.php
// header("Location: ../../index.php");
// exit();
