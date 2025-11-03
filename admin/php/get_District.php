<?php
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once 'connect.php';

try {
    $db = new DatabaseConnection();
    $db->connect();

    $province_id = 0;
    if (isset($_GET['province_id']) && is_numeric($_GET['province_id'])) {
        $province_id = intval($_GET['province_id']);
    }

    if ($province_id <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid province ID: ' . $province_id], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Debug: log the query
    $sql = 'SELECT district_id, name FROM district WHERE province_id = ? ORDER BY name ASC';
    
    $result = $db->queryPrepared($sql, [$province_id]);
    
    $data = [];
    if ($result) {
        $row_count = $result->num_rows;
        while ($row = $result->fetch_assoc()) {
            $data[] = [
                'id' => intval($row['district_id']),
                'name' => trim($row['name'])
            ];
        }
    }
    
    $db->close();
    
    if (count($data) > 0) {
        echo json_encode(['success' => true, 'data' => $data, 'debug' => ['province_id' => $province_id, 'count' => count($data)]], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(['success' => true, 'data' => [], 'message' => 'No districts found for province ' . $province_id, 'debug' => ['province_id' => $province_id]], JSON_UNESCAPED_UNICODE);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString()], JSON_UNESCAPED_UNICODE);
}
?>