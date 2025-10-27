<?php
require_once '../php/connect.php';

header('Content-Type: application/json');

try {
    $stmt = $myconn->prepare("SELECT id, name FROM provinces ORDER BY name");
    $stmt->execute();
    $provinces = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($provinces);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>