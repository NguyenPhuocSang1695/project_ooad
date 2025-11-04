<?php
require_once __DIR__ . '/connect.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_GET['province_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Province ID is required']);
    exit;
}

try {
    $province_id = (int)$_GET['province_id'];

    $db = new DatabaseConnection();
    $db->connect();

    $sql = "SELECT district_id, name FROM district WHERE province_id = ? ORDER BY name";
    $res = $db->queryPrepared($sql, [$province_id], 'i');

    $data = [];
    if ($res) {
        if (method_exists($res, 'fetch_all')) {
            $data = $res->fetch_all(MYSQLI_ASSOC);
        } else {
            while ($row = $res->fetch_assoc()) {
                $data[] = $row;
            }
        }
    }

    echo json_encode($data);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>