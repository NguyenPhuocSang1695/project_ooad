<?php
require_once('connect.php');
header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'Product ID is required']);
    exit;
}

$productId = intval($_GET['id']);

try {
    $sql = "SELECT p.*, c.Description as CategoryName 
            FROM products p 
            LEFT JOIN categories c ON p.CategoryID = c.CategoryID 
            WHERE p.ProductID = ?";
            
    $stmt = $myconn->prepare($sql);
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($product = $result->fetch_assoc()) {
        echo json_encode($product);
    } else {
        echo json_encode(['error' => 'Product not found']);
    }
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

$stmt->close();
$myconn->close();
?>