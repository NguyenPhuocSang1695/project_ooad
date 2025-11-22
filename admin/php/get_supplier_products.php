<?php
require_once './connect.php';
require_once './SupplierManager.php';

header('Content-Type: application/json');

if (!isset($_GET['supplier_id'])) {
    echo json_encode(['error' => 'Missing supplier_id']);
    exit;
}

$supplierId = intval($_GET['supplier_id']);

$connectDb = new DatabaseConnection();
$connectDb->connect();
$myconn = $connectDb->getConnection();

$supplierService = new SupplierManager($myconn);

// GỌI HÀM!!!
$data = $supplierService->getSupplierProducts($supplierId);

$connectDb->close();

echo json_encode($data);
