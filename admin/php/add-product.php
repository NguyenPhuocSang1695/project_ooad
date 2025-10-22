<?php
require_once '../../php-api/connectdb.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn = connect_db();

    // Lấy dữ liệu từ form
    $productName = $_POST['productName'];
    $categoryID = $_POST['categoryID'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    $suppliers_id = $_POST['SupplierAddProduct'];
    $quantity_in_stock = $_POST['priceAddProduct'];

    // Kiểm tra sản phẩm đã tồn tại chưa
    $checkSql = "SELECT COUNT(*) as count FROM products WHERE ProductName = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("s", $productName);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    $row = $result->fetch_assoc();

    if ($row['count'] > 0) {
        echo json_encode(["success" => false, "message" => "Sản phẩm đã tồn tại trong hệ thống"]);
        exit;
    }

    // Mặc định
    $status = 'appear';

    // Xử lý ảnh (chỉ 1 ảnh, vì input không phải multiple)
    $imageRelativeURL = "";

    if (isset($_FILES['imageURL']) && $_FILES['imageURL']['error'] === UPLOAD_ERR_OK) {
        $targetDir = "../../assets/images/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $originalName = basename($_FILES["imageURL"]["name"]);
        $fileExtension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        // Tạo tên file mới
        $newFileName = uniqid("product_") . "." . $fileExtension;
        $targetFilePath = $targetDir . $newFileName;

        // Di chuyển ảnh vào thư mục đích
        if (move_uploaded_file($_FILES["imageURL"]["tmp_name"], $targetFilePath)) {
            $imageRelativeURL = "/assets/images/" . $newFileName;
        } else {
            echo json_encode(["success" => false, "message" => "Không thể lưu ảnh."]);
            exit;
        }
    } else {
        echo json_encode(["success" => false, "message" => "Ảnh không hợp lệ hoặc chưa được chọn."]);
        exit;
    }

    // Thêm vào cơ sở dữ liệu
    $sql = "INSERT INTO products (ProductName, CategoryID, Price, Description, ImageURL, Status, Supplier_id, quantity_in_stock)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("siisssii", $productName, $categoryID, $price, $description, $imageRelativeURL, $status, $suppliers_id, $quantity_in_stock);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Thêm sản phẩm thành công"]);
    } else {
        echo json_encode(["success" => false, "message" => "Lỗi khi thêm sản phẩm: " . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
}
