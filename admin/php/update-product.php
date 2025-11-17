<?php
require_once './ProductManager.php'; // hoặc file chứa updateProduct
header('Content-Type: application/json');

$productManager = new ProductManager();

$productId   = $_POST['productId'] ?? 0;
$productName = $_POST['productName'] ?? '';
$categoryID  = $_POST['categoryID'] ?? 0;
$price       = $_POST['price'] ?? 0;
$description = $_POST['description'] ?? '';
$status      = $_POST['status'] ?? 'appear';
$quantity    = $_POST['quantity'] ?? 0;
$supplierID  = $_POST['supplierID'] ?? 0;
$fileImage   = $_FILES['imageURL'] ?? null;

$result = $productManager->updateProduct($productId, $productName, $categoryID, $price, $description, $status, $quantity, $supplierID, $fileImage);
echo json_encode($result);
