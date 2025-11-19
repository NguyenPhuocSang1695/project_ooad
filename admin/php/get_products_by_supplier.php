<?php
require_once 'connect.php';              // chứa class DatabaseConnection
require_once 'ImportReceiptManager.php'; // chứa class ImportReceiptManager

header('Content-Type: application/json; charset=UTF-8');

try {
    // Lấy supplier_id
    $supplierId = isset($_GET['supplier_id']) ? intval($_GET['supplier_id']) : 0;

    if ($supplierId <= 0) {
        echo json_encode([
            "success" => false,
            "message" => "Thiếu hoặc sai supplier_id"
        ]);
        exit;
    }

    // Kết nối DB
    $db = new DatabaseConnection();
    $db->connect();                       // ⬅️ QUAN TRỌNG
    $conn = $db->getConnection();         // Lấy mysqli connection

    // Khởi tạo Manager
    $manager = new ImportReceiptManager($conn);

    // Gọi hàm lấy sản phẩm theo nhà cung cấp
    $products = $manager->getProductsBySupplier($supplierId);

    echo json_encode([
        "success" => true,
        "data" => $products
    ]);
} catch (Exception $e) {

    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
