<?php
header('Content-Type: application/json');
include 'connect.php';

$connectDb = new DatabaseConnection();
$connectDb->connect();
$myconn = $connectDb->getConnection();

//--------------------------------------------------
// 1. KIỂM TRA receipt_id
//--------------------------------------------------
if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Thiếu ID phiếu nhập']);
    exit();
}

$receipt_id = intval($_GET['id']);

//--------------------------------------------------
// 2. LẤY THÔNG TIN PHIẾU NHẬP (JOIN với supplier)
//--------------------------------------------------
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

// Format ngày
$receipt['import_date_raw'] = $receipt['import_date'];
$receipt['import_date'] = date('d/m/Y H:i', strtotime($receipt['import_date']));
//--------------------------------------------------
// 3. LẤY CHI TIẾT SẢN PHẨM
//--------------------------------------------------
$sql_detail = "SELECT ird.*, p.ProductName AS product_name
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

//--------------------------------------------------
// 5. TRẢ JSON VỀ CHO FRONT-END
//--------------------------------------------------
echo json_encode([
    'success' => true,
    'receipt' => $receipt,
    'details' => $details
], JSON_UNESCAPED_UNICODE);

$connectDb->close();
