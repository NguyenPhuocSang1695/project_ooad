<?php
require_once './connect.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Yêu cầu không hợp lệ']);
    exit;
}

// Lấy dữ liệu từ JSON body
$data = json_decode(file_get_contents('php://input'), true);
$productId = $data['productId'] ?? null;

if (!$productId) {
    echo json_encode(['status' => 'error', 'message' => 'Product ID is required']);
    exit;
}

$db = new DatabaseConnection();
$db->connect();
$conn = $db->getConnection();

try {
    // 1️⃣ Kiểm tra xem sản phẩm có trong orderdetails không
    $checkQuery = "SELECT COUNT(*) AS count FROM orderdetails WHERE ProductID = ?";
    $result = $db->queryPrepared($checkQuery, [$productId], "i");
    $row = $result->fetch_assoc();

    if ($row['count'] > 0) {
        // 2️⃣ Nếu có trong đơn hàng, update status thành hidden
        $updateQuery = "UPDATE products SET Status = 'hidden' WHERE ProductID = ?";
        if ($db->queryPrepared($updateQuery, [$productId], "i")) {
            echo json_encode([
                "status" => "hidden",
                "message" => "Sản phẩm đã được ẩn vì đã tồn tại trong đơn hàng"
            ]);
        } else {
            echo json_encode(["status" => "error", "message" => "Lỗi khi ẩn sản phẩm"]);
        }
    } else {
        // 3️⃣ Nếu không có trong đơn hàng, xóa bình thường
        $imageQuery = "SELECT ImageURL FROM products WHERE ProductID = ?";
        $imageResult = $db->queryPrepared($imageQuery, [$productId], "i");
        $imageData = $imageResult->fetch_assoc();

        $deleteQuery = "DELETE FROM products WHERE ProductID = ?";
        if ($db->queryPrepared($deleteQuery, [$productId], "i")) {
            // Xóa file ảnh nếu tồn tại
            if ($imageData && !empty($imageData['ImageURL'])) {
                $imagePath = $_SERVER['DOCUMENT_ROOT'] . $imageData['ImageURL'];
                if (file_exists($imagePath)) unlink($imagePath);
            }
            echo json_encode([
                "status" => "deleted",
                "message" => "Đã xóa sản phẩm thành công"
            ]);
        } else {
            echo json_encode(["status" => "error", "message" => "Lỗi khi xóa sản phẩm"]);
        }
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Lỗi: ' . $e->getMessage()]);
}

$db->close();
