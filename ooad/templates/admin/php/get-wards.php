<?php
header('Content-Type: application/json');
require_once 'connect.php';

try {
    $district_id = filter_input(INPUT_GET, 'district_id', FILTER_VALIDATE_INT);
    
    if (!$district_id) {
        throw new Exception('Invalid district ID');
    }

    $sql = "SELECT wards_id as ward_id, name FROM wards WHERE district_id = ? ORDER BY name";
    $stmt = $myconn->prepare($sql);
    $stmt->bind_param("i", $district_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $wards = [];
    while ($row = $result->fetch_assoc()) {
        $wards[] = $row;
    }

    echo json_encode($wards);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    if (isset($myconn)) {
        $myconn->close();
    }
}
?> 