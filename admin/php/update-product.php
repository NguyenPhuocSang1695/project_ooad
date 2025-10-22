<?php
// Ensure no errors are output in the response
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

try {
    // Database connection
    $conn = new mysqli("localhost", "root", "", "c01db");

    if ($conn->connect_error) {
        throw new Exception('Connection failed: ' . $conn->connect_error);
    }

    // Get and validate form data
    if (
        !isset($_POST['productId']) || !isset($_POST['productName']) || !isset($_POST['categoryID']) ||
        !isset($_POST['price']) || !isset($_POST['description'])
    ) {
        throw new Exception('Missing required fields');
    }

    $productId = (int)$_POST['productId'];
    $productName = trim($_POST['productName']);
    $categoryId = (int)$_POST['categoryID'];
    $price = (float)$_POST['price'];
    $description = trim($_POST['description']);
    $status = $_POST['status'] ?? 'appear';
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : null;
    $Supplier_id = isset($_POST['supplierID']) ? (int)$_POST['supplierID'] : null;


    if (empty($productName)) {
        throw new Exception('Tên sản phẩm không được để trống');
    }

    if ($price <= 0) {
        throw new Exception('Giá phải lớn hơn 0');
    }

    if (!in_array($status, ['hidden', 'appear'])) {
        throw new Exception('Trạng thái không hợp lệ');
    }

    // First, get the current image URL
    $currentImageQuery = "SELECT ImageURL FROM products WHERE ProductID = ?";
    $stmt = $conn->prepare($currentImageQuery);
    if (!$stmt) {
        throw new Exception('Không thể xử lý truy vấn hình ảnh hiện tại: ' . $conn->error);
    }

    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    $currentImageData = $result->fetch_assoc();

    if (!$currentImageData) {
        throw new Exception('Product not found');
    }

    $currentImageURL = $currentImageData['ImageURL'];
    $stmt->close();

    $newImageURL = $currentImageURL; // Default to current image if no new one is uploaded

    // Handle new image upload if provided
    if (isset($_FILES['imageURL']) && $_FILES['imageURL']['error'] === 0) {
        $file = $_FILES['imageURL'];

        // Validate file
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        $maxSize = 2 * 1024 * 1024; // 2MB

        if (!in_array($file['type'], $allowedTypes)) {
            throw new Exception('Định dạng file không hợp lệ. Chỉ chấp nhận JPG, JPEG và PNG.');
        }

        if ($file['size'] > $maxSize) {
            throw new Exception('Kích thước file quá lớn. Tối đa 2MB.');
        }

        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'product_' . uniqid() . '.' . $extension;
        $uploadPath = '../../assets/images/' . $filename;

        // Make sure the directory exists
        $uploadDir = dirname($uploadPath);
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0777, true)) {
                throw new Exception('Failed to create upload directory');
            }
        }

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
            throw new Exception('Không thể tải lên hình ảnh');
        }

        $newImageURL = '/assets/images/' . $filename;

        // Delete old image if it exists and is different
        if ($currentImageURL && $currentImageURL !== $newImageURL) {
            $oldImagePath = '../../' . $currentImageURL;
            if (file_exists($oldImagePath)) {
                unlink($oldImagePath);
            }
        }
    }

    // Update product in database
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

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Không thể chuẩn bị truy vấn cập nhật: ' . $conn->error);
    }

    $stmt->bind_param(
        "sidssissi",
        $productName,       // s
        $categoryId,        // i
        $price,             // d
        $description,       // s
        $status,            // s
        $Supplier_id,        // i
        $quantity,          // s (nếu là int thì đổi thành i)
        $newImageURL,       // s
        $productId          // i
    );

    file_put_contents('debug.log', print_r($_POST, true));

    if (!$stmt->execute()) {
        throw new Exception('Không thể cập nhật sản phẩm: ' . $stmt->error);
    }

    if ($stmt->affected_rows === 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Không có thay đổi nào được thực hiện',
            'productId' => $productId
        ]);
        exit;
    }

    echo json_encode([
        'success' => true,
        'message' => 'Cập nhật sản phẩm thành công',
        'productId' => $productId
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($conn)) {
        $conn->close();
    }
}
