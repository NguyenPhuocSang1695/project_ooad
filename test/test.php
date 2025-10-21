<?php
header('Content-Type: application/json');

$server = "localhost";
$user = "root";
$password = "";
$database = "webdb"; // Database của bạn

$myconn = new mysqli($server, $user, $password, $database);

// Kiểm tra kết nối
if ($myconn->connect_error) {
    die(json_encode(["error" => "Kết nối thất bại: " . $myconn->connect_error]));
}

$sql = "SELECT ImageURL FROM products";
$result = mysqli_query($myconn, $sql);

$images = [];

// Lấy dữ liệu từ bảng products
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $images[] = $row['ImageURL'];
    }
}

// Đóng kết nối
mysqli_close($myconn);

// Trả về JSON
echo json_encode(["images" => $images], JSON_PRETTY_PRINT);
?>
