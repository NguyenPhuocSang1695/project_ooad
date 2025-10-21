<?php
header('Content-Type: application/json');
include 'connect.php';

if ($myconn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Connection failed: ' . $myconn->connect_error]);
    exit;
}
$myconn->set_charset("utf8");

if (isset($_POST['province_id'])) {
    $province_id = $_POST['province_id'];
    
    $sql = "SELECT * FROM district WHERE province_id = ?";
    $stmt = $myconn->prepare($sql);
    $stmt->bind_param("i", $province_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $districts = array();
    while ($row = $result->fetch_assoc()) {
        $districts[] = array(
            'district_id' => $row['district_id'],
            'name' => $row['name']
        );
    }
    
    echo json_encode(['success' => true, 'districts' => $districts]);
} else {
    echo json_encode(['success' => false, 'message' => 'Không có province_id']);
}

$myconn->close();
?> 