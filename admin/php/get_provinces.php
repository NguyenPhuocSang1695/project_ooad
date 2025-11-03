<?php
require_once __DIR__ . '/connect.php';

header('Content-Type: application/json; charset=utf-8');

try {
    // Use existing mysqli-based DatabaseConnection
    $db = new DatabaseConnection();
    $db->connect();

    $sql = "SELECT province_id, name FROM province ORDER BY name";
    $result = $db->query($sql);

    $data = [];
    if ($result) {
        // fetch_all available on mysqli_result in mysqlnd
        if (method_exists($result, 'fetch_all')) {
            $data = $result->fetch_all(MYSQLI_ASSOC);
        } else {
            while ($row = $result->fetch_assoc()) {
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