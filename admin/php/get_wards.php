<?php
require_once __DIR__ . '/connect.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_GET['district_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'District ID is required']);
    exit;
}

try {
    $district_id = (int)$_GET['district_id'];

    $db = new DatabaseConnection();
    $db->connect();

    $sql = "SELECT ward_id, name FROM ward WHERE district_id = ? ORDER BY name";
    $res = $db->queryPrepared($sql, [$district_id], 'i');

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