<?php
header('Content-Type: application/json');
include 'connect.php';

if ($myconn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Connection failed: ' . $myconn->connect_error]);
    exit;
}
$myconn->set_charset("utf8");

$stmt = $myconn->prepare('SELECT province_id, name FROM province ORDER BY name ASC');
$stmt->execute();
$result = $stmt->get_result();
$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = [
        'id' => $row['province_id'],
        'name' => $row['name']
    ];
}

$stmt->close();
echo json_encode(['success' => true, 'data' => $data]);
?>