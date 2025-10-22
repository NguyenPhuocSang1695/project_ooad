<?php
require_once './connect.php';
header('Content-Type: application/json');

try {
    $db = new DatabaseConnection();
    $db->connect();
    $conn = $db->getConnection();

    // Validate POST data
    $requiredFields = ['productId', 'productName', 'categoryID', 'price', 'description'];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    $productId   = (int)$_POST['productId'];
    $productName = trim($_POST['productName']);
    $categoryID  = (int)$_POST['categoryID'];
    $price       = (float)$_POST['price'];
    $description = trim($_POST['description']);
    $status      = $_POST['status'] ?? 'appear';
    $quantity    = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;
    $supplierID  = isset($_POST['supplierID']) ? (int)$_POST['supplierID'] : 0;

    if ($price <= 0) throw new Exception("Giá sản phẩm phải lớn hơn 0");
    if (!in_array($status, ['appear', 'hidden'])) throw new Exception("Trạng thái không hợp lệ");

    // Lấy ảnh hiện tại
    $currentImage = '';
    $result = $db->queryPrepared("SELECT ImageURL FROM products WHERE ProductID = ?", [$productId], "i");
    $row = $result->fetch_assoc();
    if (!$row) throw new Exception("Sản phẩm không tồn tại");
    $currentImage = $row['ImageURL'];

    $newImageURL = $currentImage;

    // Xử lý ảnh mới nếu có upload
    if (isset($_FILES['imageURL']) && $_FILES['imageURL']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['imageURL'];
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        $maxSize = 2 * 1024 * 1024;

        if (!in_array($file['type'], $allowedTypes)) throw new Exception("Định dạng file không hợp lệ");
        if ($file['size'] > $maxSize) throw new Exception("Kích thước file quá lớn (max 2MB)");

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'product_' . uniqid() . '.' . $ext;
        $uploadDir = '../../assets/images/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $uploadPath = $uploadDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
            throw new Exception("Không thể tải lên hình ảnh");
        }

        $newImageURL = '/assets/images/' . $filename;

        // Xóa ảnh cũ nếu khác
        if ($currentImage && $currentImage !== $newImageURL) {
            $oldImagePath = '../../' . $currentImage;
            if (file_exists($oldImagePath)) unlink($oldImagePath);
        }
    }

    // Cập nhật sản phẩm
    $sql = "UPDATE products SET 
                ProductName = ?, 
                CategoryID = ?, 
                Price = ?, 
                Description = ?, 
                Status = ?, 
                Supplier_id = ?, 
                quantity_in_stock = ?, 
                ImageURL = ?
            WHERE ProductID = ?";

    $params = [$productName, $categoryID, $price, $description, $status, $supplierID, $quantity, $newImageURL, $productId];
    $types  = "sidssissi";

    $success = $db->queryPrepared($sql, $params, $types);

    if ($success) {
        echo json_encode([
            'success' => true,
            'message' => 'Cập nhật sản phẩm thành công',
            'productId' => $productId
        ]);
    } else {
        throw new Exception("Không có thay đổi hoặc lỗi khi cập nhật sản phẩm");
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    $db->close();
}
