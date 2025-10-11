<?php
header('Content-Type: application/json');
include 'connect.php';

if ($myconn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Connection failed: ' . $myconn->connect_error]);
    exit;
}
$myconn->set_charset("utf8");

$province_id = 0;
if (isset($_GET['province_id']) && is_numeric($_GET['province_id'])) {
    $province_id = intval($_GET['province_id']);
}

if ($province_id > 0) {
    $stmt = $myconn->prepare('SELECT district_id, name FROM district WHERE province_id = ? ORDER BY name ASC');
    $stmt->bind_param('i', $province_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'id' => $row['district_id'],
            'name' => $row['name']
        ];
    }
    $stmt->close();
    echo json_encode(['success' => true, 'data' => $data]);
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid province ID']);
}
?>