<?php
header('Content-Type: application/json; charset=utf-8');

try {
    require_once __DIR__ . '/connect.php';
    
    $db = new DatabaseConnection();
    $db->connect();
    $conn = $db->getConnection();
    
    if (!$conn) {
        throw new Exception("Database connection failed");
    }
    
    $query = "SELECT id, name, percen_decrease, conditions, status FROM vouchers ORDER BY name ASC";
    $result = $conn->query($query);
    
    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }
    
    $vouchers = [];
    while ($row = $result->fetch_assoc()) {
        $vouchers[] = [
            'id' => intval($row['id']),
            'name' => $row['name'],
            'percen_decrease' => floatval($row['percen_decrease']),
            'conditions' => intval($row['conditions']),
            'status' => $row['status']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'data' => $vouchers,
        'count' => count($vouchers)
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
