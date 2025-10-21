<?php
header('Content-Type: application/json');
require_once 'connect.php';

// Validate và sanitize input
function validateInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

try {
    // Lấy dữ liệu từ POST request
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        throw new Exception("Dữ liệu không hợp lệ");
    }

    // Validate required fields
    $required_fields = ['username', 'fullname', 'phone', 'address', 'province', 'district', 'ward'];
    foreach ($required_fields as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            throw new Exception("Vui lòng điền đầy đủ thông tin bắt buộc");
        }
    }

    // Validate và sanitize input
    $username = validateInput($data['username']);
    $fullname = validateInput($data['fullname']);
    $email = isset($data['email']) ? validateInput($data['email']) : null;
    $phone = validateInput($data['phone']);
    $address = validateInput($data['address']);
    $province = validateInput($data['province']);
    $district = validateInput($data['district']);
    $ward = validateInput($data['ward']);
    $status = isset($data['status']) ? validateInput($data['status']) : 'Active';

    // Kiểm tra nếu đang cố gắng thay đổi trạng thái của tài khoản admin đang đăng nhập
    session_name('admin_session');
    session_start();
    
    if (isset($_SESSION['Username']) && $username === $_SESSION['Username']) {
        // Nếu là tài khoản của chính mình, giữ nguyên trạng thái hiện tại
        $stmt = $myconn->prepare("SELECT Status FROM users WHERE Username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $status = $row['Status']; // Giữ nguyên trạng thái cũ
        }
        $stmt->close();
    }

    // Validate email format if provided
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Email không hợp lệ");
    }

    // Validate phone format
    if (!preg_match("/^[0-9]{10}$/", $phone)) {
        throw new Exception("Số điện thoại phải có 10 chữ số");
    }

    // Kiểm tra email đã tồn tại chưa (nếu có email)
    if (!empty($email)) {
        $stmt = $myconn->prepare("SELECT Email FROM users WHERE Email = ? AND Username != ?");
        $stmt->bind_param("ss", $email, $username);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            throw new Exception("Email đã tồn tại");
        }
    }

    // Cập nhật thông tin người dùng
    $stmt = $myconn->prepare("UPDATE users SET 
        FullName = ?, 
        Email = ?, 
        Phone = ?, 
        Address = ?, 
        Province = ?, 
        District = ?, 
        Ward = ?, 
        Status = ? 
        WHERE Username = ?");

    $stmt->bind_param("sssssssss", $fullname, $email, $phone, $address, $province, $district, $ward, $status, $username);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Cập nhật thông tin thành công']);
    } else {
        throw new Exception("Lỗi khi cập nhật thông tin: " . $stmt->error);
    }

    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$myconn->close();
?>