<?php
header('Content-Type: application/json');
require_once 'connect.php';

try {
    $username = $_GET['username'] ?? '';
    if (empty($username)) {
        throw new Exception('Username is required');
    }

    // Get user details with location names
    $sql = "SELECT u.*, 
            p.name as province_name, 
            d.name as district_name, 
            w.name as ward_name,
            p.province_id,
            d.district_id,
            w.wards_id
            FROM users u
            LEFT JOIN province p ON u.Province = p.province_id
            LEFT JOIN district d ON u.District = d.district_id
            LEFT JOIN wards w ON u.Ward = w.wards_id
            WHERE u.Username = ?";

    $stmt = $myconn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('User not found');
    }

    $user = $result->fetch_assoc();

    echo json_encode([
        'success' => true,
        'user' => [
            'username' => $user['Username'],
            'fullname' => $user['FullName'],
            'email' => $user['Email'],
            'phone' => $user['Phone'],
            'address' => $user['Address'],
            'province_id' => $user['Province'],
            'district_id' => $user['District'],
            'ward_id' => $user['Ward'],
            'province_name' => $user['province_name'],
            'district_name' => $user['district_name'],
            'ward_name' => $user['ward_name'],
            'status' => $user['Status']
        ]
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($myconn)) {
        $myconn->close();
    }
}
