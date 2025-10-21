<?php
require_once './connectdb.php'; // Kết nối database
$conn = connect_db(); // Kết nối bằng MySQLi

$search = isset($_GET['q']) ? trim($_GET['q']) : ''; // Đổi 'search' thành 'q'
$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$minPrice = isset($_GET['minPrice']) ? floatval($_GET['minPrice']) : 0;
$maxPrice = isset($_GET['maxPrice']) ? floatval($_GET['maxPrice']) : 0;

// Nếu category là tên danh mục, tìm CategoryID từ CategoryName
if (!empty($category) && !ctype_digit($category)) { // Chỉ tìm nếu không phải số
    $category = mb_strtolower($category, 'UTF-8');

    $sql = "SELECT CategoryID FROM categories WHERE LOWER(CategoryName) = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $category);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $category = $row['CategoryID'] ?? 0;
    $stmt->close();
} else {
    $category = intval($category);
}

// Tạo câu truy vấn cơ bản
$sql = "SELECT ProductID, ProductName, Price, ImageURL FROM products WHERE Status = 'appear'";

$params = [];
$types = "";

// Thêm điều kiện tìm kiếm nếu có
if (!empty($search)) {
    $sql .= " AND ProductName LIKE ?";
    $params[] = "%$search%";
    $types .= "s";
}

if ($category > 0) {
    $sql .= " AND CategoryID = ?";
    $params[] = $category;
    $types .= "i";
}

if ($minPrice >= 0) {
    $sql .= " AND Price >= ?";
    $params[] = $minPrice;
    $types .= "d";
}

if ($maxPrice > 0) {
    $sql .= " AND Price <= ?";
    $params[] = $maxPrice;
    $types .= "d";
}

// Thêm ORDER BY để sắp xếp theo ProductID giảm dần
$sql .= " ORDER BY ProductID DESC";

// Chuẩn bị và thực thi truy vấn
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

if (!$stmt->execute()) {
    die(json_encode(["error" => "Lỗi truy vấn: " . $stmt->error]));
}

$result = $stmt->get_result();
$products = [];

while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

// Trả về kết quả
if (empty($products)) {
    echo json_encode(["message" => "Không tìm thấy sản phẩm nào"], JSON_UNESCAPED_UNICODE);
} else {
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode($products, JSON_UNESCAPED_UNICODE);
}

$stmt->close();
$conn->close();
