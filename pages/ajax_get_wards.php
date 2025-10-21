<?php 
    header('Content-Type: application/json');
    $conn = new mysqli("localhost", "root", "", "c01db");
    if ($conn->connect_error) {
        die("Kết nối thất bại: " . $conn->connect_error);
    }

    $district_id = isset($_GET['district_id']) ? intval($_GET['district_id']) : 0;

    if ($district_id === 0) {
        echo json_encode([]);
        exit;
    }

    $sql = "SELECT * FROM `wards` WHERE `district_id` = {$district_id}";
    $result = mysqli_query($conn, $sql);

    $data = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = [
            'id' => $row['wards_id'],
            'name'=> $row['name']
        ];
    }

    echo json_encode($data);
?>
