<?php
require_once './ProductManager.php';
header('Content-Type: application/json');

$productManager = new ProductManager();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Yêu cầu không hợp lệ"]);
    exit;
}

$productName       = $_POST['productName'] ?? '';
$categoryID        = $_POST['categoryID'] ?? 0;
$price             = $_POST['price'] ?? 0;
$description       = $_POST['description'] ?? '';
$supplierID        = $_POST['SupplierAddProduct'] ?? 0;
$quantity_in_stock = $_POST['priceAddProduct'] ?? 0;
$fileImage         = $_FILES['imageURL'] ?? null;

$result = $productManager->addProduct($productName, $categoryID, $price, $description, $supplierID, $quantity_in_stock, $fileImage);
echo json_encode($result);
