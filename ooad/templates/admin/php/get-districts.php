<?php
header('Content-Type: application/json');
require_once 'connect.php';

try {
    $province_id = filter_input(INPUT_GET, 'province_id', FILTER_VALIDATE_INT);
    
    if (!$province_id) {
        throw new Exception('Invalid province ID');
    }

    $sql = "SELECT district_id, name FROM district WHERE province_id = ? ORDER BY name";
    $stmt = $myconn->prepare($sql);
    $stmt->bind_param("i", $province_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $districts = [];
    while ($row = $result->fetch_assoc()) {
        $districts[] = $row;
    }

    echo json_encode($districts);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    if (isset($myconn)) {
        $myconn->close();
    }
}
?> 