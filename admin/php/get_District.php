<?php
header('Content-Type: application/json');
require_once 'connect.php';

try {
    $db = new DatabaseConnection();
    $db->connect();

    $province_id = 0;
    if (isset($_GET['province_id']) && is_numeric($_GET['province_id'])) {
        $province_id = intval($_GET['province_id']);
    }

    if ($province_id > 0) {
        $result = $db->queryPrepared(
            'SELECT district_id, name FROM district WHERE province_id = ? ORDER BY name ASC',
            [$province_id],
            'i'
        );
        
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = [
                'id' => $row['district_id'],
                'name' => $row['name']
            ];
        }
        
        $db->close();
        echo json_encode(['success' => true, 'data' => $data]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid province ID']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>