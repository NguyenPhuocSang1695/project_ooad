<?php
require_once './ProductManager.php'; // hoặc file chứa deleteOrHideProduct
header('Content-Type: application/json');

$productManager = new ProductManager();

$data = json_decode(file_get_contents('php://input'), true);
$productId = $data['productId'] ?? null;

$result = $productManager->deleteOrHideProduct($productId);
echo json_encode($result);
