<?php
header('Content-Type: application/json');
include 'connect.php';

$connectDb = new DatabaseConnection();
$connectDb->connect();
$myconn = $connectDb->getConnection();

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Thiếu ID phiếu nhập']);
    exit();
}

$receipt_id = intval($_GET['id']);

// Lấy thông tin phiếu nhập
$sql = "SELECT ir.*, s.supplier_name 
        FROM import_receipt ir
        LEFT JOIN suppliers s ON ir.supplier_id = s.supplier_id
        WHERE ir.receipt_id = ?";
$stmt = $myconn->prepare($sql);
$stmt->bind_param("i", $receipt_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo json_encode(['success' => false, 'message' => 'Không tìm thấy phiếu nhập']);
    exit();
}

$receipt = $result->fetch_assoc();
// Thêm import_date_raw để dùng cho form edit
$receipt['import_date_raw'] = $receipt['import_date']; // Format: YYYY-MM-DD HH:MM:SS
$receipt['import_date'] = date('d/m/Y H:i', strtotime($receipt['import_date'])); // Format hiển thị

// Lấy chi tiết sản phẩm
$sql_detail = "SELECT ird.*, p.ProductName as product_name
               FROM import_receipt_detail ird
               LEFT JOIN products p ON ird.product_id = p.ProductID
               WHERE ird.receipt_id = ?
               ORDER BY ird.product_id";
$stmt_detail = $myconn->prepare($sql_detail);
$stmt_detail->bind_param("i", $receipt_id);
$stmt_detail->execute();
$result_detail = $stmt_detail->get_result();

$details = [];
while ($row = $result_detail->fetch_assoc()) {
    $details[] = $row;
}

echo json_encode([
    'success' => true,
    'receipt' => $receipt,
    'details' => $details
]);

$connectDb->close();
