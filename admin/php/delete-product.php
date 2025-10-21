<?php
require_once '../../php-api/connectdb.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $productId = $data['productId'] ?? null;

    if (!$productId) {
        echo json_encode(['status' => 'error', 'message' => 'Product ID is required']);
        exit;
    }

    $conn = connect_db();

    try {
        // Kiểm tra xem sản phẩm có trong orderdetails không
        $checkQuery = "SELECT COUNT(*) as count FROM orderdetails WHERE ProductID = ?";
        $stmt = $conn->prepare($checkQuery);
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if ($row['count'] > 0) {
            // Nếu sản phẩm có trong đơn hàng, update status thành hidden
            $updateQuery = "UPDATE products SET Status = 'hidden' WHERE ProductID = ?";
            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param("i", $productId);
            
            if ($stmt->execute()) {
                echo json_encode([
                    "status" => "hidden", 
                    "message" => "Sản phẩm đã được ẩn vì đã tồn tại trong đơn hàng"
                ]);
            } else {
                echo json_encode([
                    "status" => "error", 
                    "message" => "Lỗi khi ẩn sản phẩm"
                ]);
            }
        } else {
            // Nếu sản phẩm không có trong đơn hàng, xóa bình thường
            // Lấy thông tin ảnh trước khi xóa
            $imageQuery = "SELECT ImageURL FROM products WHERE ProductID = ?";
            $stmt = $conn->prepare($imageQuery);
            $stmt->bind_param("i", $productId);
            $stmt->execute();
            $imageResult = $stmt->get_result();
            $imageData = $imageResult->fetch_assoc();

            // Xóa sản phẩm
            $deleteQuery = "DELETE FROM products WHERE ProductID = ?";
            $stmt = $conn->prepare($deleteQuery);
            $stmt->bind_param("i", $productId);
            
            if ($stmt->execute()) {
                // Xóa file ảnh nếu tồn tại
                if ($imageData && isset($imageData['ImageURL'])) {
                    $imagePath = $_SERVER['DOCUMENT_ROOT'] . $imageData['ImageURL'];
                    if (file_exists($imagePath)) {
                        unlink($imagePath);
                    }
                }
                echo json_encode([
                    "status" => "deleted", 
                    "message" => "Đã xóa sản phẩm thành công"
                ]);
            } else {
                echo json_encode([
                    "status" => "error", 
                    "message" => "Lỗi khi xóa sản phẩm"
                ]);
            }
        }
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Lỗi: ' . $e->getMessage()
        ]);
    }

    $stmt->close();
    $conn->close();
}
?>