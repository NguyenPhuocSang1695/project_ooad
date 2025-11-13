<?php
require_once './connect.php';

header('Content-Type: application/json');

if (!isset($_GET['supplier_id'])) {
    echo json_encode(['error' => 'Missing supplier_id']);
    exit;
}

$supplierId = intval($_GET['supplier_id']);

$connectDb = new DatabaseConnection();
$connectDb->connect();
$myconn = $connectDb->getConnection();

// Lấy danh sách sản phẩm và tổng giá trị
$sql = "SELECT 
            p.ProductName,
            SUM(ird.quantity) AS Quantity,
            AVG(ird.import_price) AS UnitPrice,
            SUM(ird.subtotal) AS TotalValue
        FROM import_receipt_detail ird
        JOIN import_receipt ir ON ird.receipt_id = ir.receipt_id
        JOIN Products p ON ird.product_id = p.ProductID
        WHERE ir.supplier_id = ?
        GROUP BY p.ProductName
        ORDER BY p.ProductName DESC;
";


$stmt = $myconn->prepare($sql);
$stmt->bind_param("i", $supplierId);
$stmt->execute();
$result = $stmt->get_result();

$products = [];
$totalAmount = 0;

while ($row = $result->fetch_assoc()) {
    $products[] = $row;
    $totalAmount += $row['TotalValue'];
}

$connectDb->close();

echo json_encode([
    'products' => $products,
    'totalAmount' => $totalAmount
]);
