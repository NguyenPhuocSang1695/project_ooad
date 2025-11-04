<?php
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once 'connect.php';

try {
    $db = new DatabaseConnection();
    $db->connect();
    $myconn = $db->getConnection();
    
    // Get all products with status 'appear'
    $query = "SELECT ProductID, ProductName, Price, Status FROM products WHERE Status = 'appear' ORDER BY ProductName ASC";
    $result = $myconn->query($query);
    
    if (!$result) {
        throw new Exception("Query failed: " . $myconn->error);
    }
    
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = [
            'id' => $row['ProductID'],
            'name' => $row['ProductName'],
            'price' => $row['Price'],
            'status' => $row['Status']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'products' => $products
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>