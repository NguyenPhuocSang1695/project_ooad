<?php 
header('Content-Type: application/json');
    $conn = new mysqli("localhost", "root", "", "c01db");
    if ($conn->connect_error) {
      die("Kết nối thất bại: " . $conn->connect_error);
    }
    
    $province_id = $_GET['province_id'];
    
    $sql = "SELECT * FROM `district` WHERE `province_id` = {$province_id}";
    $result = mysqli_query($conn, $sql);

    // $data[0] = [
    //     'id' => null,
    //     'name' => 'Chọn một Quận/huyện'
    // ];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = [
            'id' => $row['district_id'],
            'name'=> $row['name']
        ];
    }
    echo json_encode($data);
?>