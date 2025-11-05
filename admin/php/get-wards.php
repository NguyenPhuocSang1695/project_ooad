<?php
header('Content-Type: application/json');
require_once 'connect.php';

try {
    $db = new DatabaseConnection();
    $db->connect();
    
    $district_id = filter_input(INPUT_GET, 'district_id', FILTER_VALIDATE_INT);
    
    if (!$district_id) {
        throw new Exception('Invalid district ID');
    }

    $sql = "SELECT ward_id, name FROM ward WHERE district_id = ? ORDER BY name";
    $result = $db->queryPrepared($sql, [$district_id], 'i');

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'id' => $row['ward_id'],
            'name' => $row['name']
        ];
    }
 
    $db->close();
    echo json_encode(['success' => true, 'data' => $data]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?> 