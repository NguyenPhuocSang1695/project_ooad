<?php
header('Content-Type: application/json');
require_once 'connect.php';

try {
    $db = new DatabaseConnection();
    $db->connect();

    $result = $db->queryPrepared('SELECT province_id, name FROM province ORDER BY name ASC');
    $data = [];

    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'id' => $row['province_id'],
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