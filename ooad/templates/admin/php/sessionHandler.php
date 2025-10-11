<?php
session_name('admin_session');
session_start();

if (isset($_SESSION['Username']) && isset($_SESSION['FullName']) && isset($_SESSION['Role'])) {
    $defaultAvatar = '../../assets/images/admin.jpg';
    if ($_SESSION['Role'] === 'admin') {
        $defaultAvatar = '../../assets/images/sang.jpg';
    }
    
    echo json_encode([
        'status' => 'success',
        'username' => $_SESSION['Username'],
        'fullname' => $_SESSION['FullName'],
        'role' => $_SESSION['Role'],
        'avatar' => $defaultAvatar
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Không tìm thấy thông tin đăng nhập'
    ]);
}
?>