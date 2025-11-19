<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'ProductManager.php';

$productId = $_GET['id'] ?? 0;

$manager = new ProductManager();
$response = $manager->getProductDetails($productId);

echo json_encode($response, JSON_UNESCAPED_UNICODE);
