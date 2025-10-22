<?php
require_once './connect.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Yêu cầu không hợp lệ"]);
    exit;
}

$db = new DatabaseConnection();
$db->connect();
$conn = $db->getConnection();

// Lấy dữ liệu từ form
$productName       = $_POST['productName'] ?? '';
$categoryID        = $_POST['categoryID'] ?? 0;
$price             = $_POST['price'] ?? 0;
$description       = $_POST['description'] ?? '';
$supplierID        = $_POST['SupplierAddProduct'] ?? 0;
$quantity_in_stock = $_POST['priceAddProduct'] ?? 0;

// Validate cơ bản
if (!$productName || !$categoryID || !$price || !$supplierID) {
    echo json_encode(["success" => false, "message" => "Vui lòng điền đầy đủ thông tin bắt buộc"]);
    exit;
}

// Kiểm tra sản phẩm đã tồn tại
$checkSql = "SELECT COUNT(*) as count FROM products WHERE ProductName = ?";
$result = $db->queryPrepared($checkSql, [$productName], "s");
$row = $result->fetch_assoc();

if ($row['count'] > 0) {
    echo json_encode(["success" => false, "message" => "Sản phẩm đã tồn tại trong hệ thống"]);
    exit;
}

// Xử lý ảnh
$imageRelativeURL = '';
if (isset($_FILES['imageURL']) && $_FILES['imageURL']['error'] === UPLOAD_ERR_OK) {
    $targetDir = "../../assets/images/";
    if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

    $ext = strtolower(pathinfo($_FILES['imageURL']['name'], PATHINFO_EXTENSION));
    $newFileName = uniqid("product_") . "." . $ext;
    $targetFilePath = $targetDir . $newFileName;

    if (!move_uploaded_file($_FILES['imageURL']['tmp_name'], $targetFilePath)) {
        echo json_encode(["success" => false, "message" => "Không thể lưu ảnh."]);
        exit;
    }

    $imageRelativeURL = "/assets/images/" . $newFileName;
} else {
    echo json_encode(["success" => false, "message" => "Ảnh không hợp lệ hoặc chưa được chọn."]);
    exit;
}

// Thêm sản phẩm vào DB
$insertSql = "INSERT INTO products 
    (ProductName, CategoryID, Price, Description, ImageURL, Status, Supplier_id, quantity_in_stock)
    VALUES (?, ?, ?, ?, ?, 'appear', ?, ?)";

$params = [$productName, $categoryID, $price, $description, $imageRelativeURL, $supplierID, $quantity_in_stock];
$types  = "siissii";

if ($db->queryPrepared($insertSql, $params, $types)) {
    echo json_encode(["success" => true, "message" => "Thêm sản phẩm thành công"]);
} else {
    echo json_encode(["success" => false, "message" => "Lỗi khi thêm sản phẩm"]);
}

$db->close();
