<?php
header('Content-Type: application/json');
require_once 'connect.php';

// Validate and sanitize input
function validateInput($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

try {
    // Get and validate user data from POST request
    $username = validateInput($_POST['username'] ?? '');
    $fullname = validateInput($_POST['fullname'] ?? '');
    $email = validateInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $phone = validateInput($_POST['phone'] ?? '');
    $address = validateInput($_POST['address'] ?? '');
    $province = validateInput($_POST['province'] ?? '');
    $district = validateInput($_POST['district'] ?? '');
    $ward = validateInput($_POST['ward'] ?? '');
    $status = validateInput($_POST['status'] ?? 'Active');
    $role = validateInput($_POST['role'] ?? 'customer');

    // Validate required fields
    if (empty($username) || empty($fullname) || empty($password) || empty($phone)) {
        throw new Exception('Vui lòng điền đầy đủ thông tin bắt buộc');
    }

    // Validate username format (only letters, numbers, and underscores)
    if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
        throw new Exception('Tên tài khoản chỉ được chứa chữ cái, số và dấu gạch dưới, độ dài từ 3-20 ký tự');
    }

    // Validate email if provided
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Email không hợp lệ');
    }

    // Validate phone number (10 digits)
    if (!preg_match('/^[0-9]{10}$/', $phone)) {
        throw new Exception('Số điện thoại phải có 10 chữ số');
    }

    // Validate password strength
    if (strlen($password) < 8) {
        throw new Exception('Mật khẩu phải có ít nhất 8 ký tự');
    }
    if (!preg_match('/[A-Z]/', $password)) {
        throw new Exception('Mật khẩu phải chứa ít nhất một chữ hoa');
    }
    // if (!preg_match('/[a-z]/', $password)) {
    //     throw new Exception('Mật khẩu phải chứa ít nhất một chữ thường');
    // }
    if (!preg_match('/[0-9]/', $password)) {
        throw new Exception('Mật khẩu phải chứa ít nhất một số');
    }
    if (!preg_match('/[^A-Za-z0-9]/', $password)) {
        throw new Exception('Mật khẩu phải chứa ít nhất một ký tự đặc biệt');
    }

    // Validate province, district and ward
    $province_id = validateInput($_POST['province_id'] ?? '');
    $district_id = validateInput($_POST['district_id'] ?? '');
    $ward_id = validateInput($_POST['ward_id'] ?? '');

    if (empty($province_id) || empty($district_id) || empty($ward_id)) {
        throw new Exception('Vui lòng chọn đầy đủ thông tin địa chỉ');
    }

    // Validate role
    if (!in_array($role, ['admin', 'customer'])) {
        throw new Exception('Vai trò không hợp lệ');
    }

    // Verify province exists
    $check_province = "SELECT province_id FROM province WHERE province_id = ?";
    $stmt = $myconn->prepare($check_province);
    $stmt->bind_param("i", $province_id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows === 0) {
        throw new Exception('Tỉnh/Thành phố không hợp lệ');
    }

    // Verify district exists and belongs to province
    $check_district = "SELECT district_id FROM district WHERE district_id = ? AND province_id = ?";
    $stmt = $myconn->prepare($check_district);
    $stmt->bind_param("ii", $district_id, $province_id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows === 0) {
        throw new Exception('Quận/Huyện không hợp lệ');
    }

    // Verify ward exists and belongs to district
    $check_ward = "SELECT wards_id FROM wards WHERE wards_id = ? AND district_id = ?";
    $stmt = $myconn->prepare($check_ward);
    $stmt->bind_param("ii", $ward_id, $district_id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows === 0) {
        throw new Exception('Phường/Xã không hợp lệ');
    }

    // Check if username already exists
    $check_sql = "SELECT Username FROM users WHERE Username = ?";
    $check_stmt = $myconn->prepare($check_sql);
    $check_stmt->bind_param("s", $username);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Tên tài khoản đã tồn tại. Vui lòng chọn tên khác.'
        ]);
        exit();
    }
    $check_stmt->close();

    // Check if email already exists (if provided)
    if (!empty($email)) {
        $check_sql = "SELECT Email FROM users WHERE Email = ? AND Email IS NOT NULL";
        $check_stmt = $myconn->prepare($check_sql);
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $result = $check_stmt->get_result();

        if ($result->num_rows > 0) {
            throw new Exception('Email đã được sử dụng');
        }
        $check_stmt->close();
    }

    // Hash password using PHP's built-in password hashing function
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Prepare insert statement
    $sql = "INSERT INTO users (Username, FullName, Email, PasswordHash, Phone, Address, Province, District, Ward, Status, Role) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $myconn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Lỗi chuẩn bị câu lệnh SQL: ' . $myconn->error);
    }

    $stmt->bind_param(
        "ssssssiiiss",
        $username,
        $fullname,
        $email,
        $hashed_password,
        $phone,
        $address,
        $province_id,
        $district_id,
        $ward_id,
        $status,
        $role
    );

    if (!$stmt->execute()) {
        throw new Exception('Lỗi thực thi câu lệnh SQL: ' . $stmt->error);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Thêm người dùng thành công',
        'user' => [
            'username' => $username,
            'fullname' => $fullname,
            'email' => $email,
            'phone' => $phone,
            'status' => $status,
            'role' => $role
        ]
    ]);

    $stmt->close();
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($myconn)) {
        $myconn->close();
    }
}
