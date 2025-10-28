<?php
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once 'connect.php';

try {
    $db = new DatabaseConnection();
    $db->connect();

    $result = $db->queryPrepared('SELECT province_id, name FROM province ORDER BY name ASC');
    $data = [];

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = [
                'id' => intval($row['province_id']),
                'name' => trim($row['name'])
            ];
        }
    }

    $db->close();
    echo json_encode(['success' => true, 'data' => $data], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
?>