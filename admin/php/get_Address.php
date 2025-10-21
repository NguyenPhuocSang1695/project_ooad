<?php
header('Content-Type: application/json');
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

include 'connect.php';

if ($myconn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Connection failed: ' . $myconn->connect_error]);
    exit;
}

$myconn->set_charset("utf8");

$type = isset($_GET['type']) ? $myconn->real_escape_string($_GET['type']) : 'district';
$query = isset($_GET['query']) ? $myconn->real_escape_string($_GET['query']) : '';

if ($type === 'city') { 
    $sql = "SELECT DISTINCT Province 
            FROM users 
            WHERE Province IS NOT NULL";
    if ($query) {
        $sql .= " AND Province LIKE '%$query%'";
    }
    $sql .= " ORDER BY Province LIMIT 10";
    
    $result = $myconn->query($sql);
    
    if ($result) {
        $provinces = [];
        while ($row = $result->fetch_assoc()) {
            $provinces[] = $row['Province'];
        }
        echo json_encode(['success' => true, 'data' => $provinces]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Query failed: ' . $myconn->error]);
    }
} else {
    $sql = "SELECT DISTINCT District 
            FROM users 
            WHERE District IS NOT NULL";
    if ($query) {
        $sql .= " AND District LIKE '%$query%'";
    }
    $sql .= " ORDER BY District LIMIT 10";
    
    $result = $myconn->query($sql);
    
    if ($result) {
        $districts = [];
        while ($row = $result->fetch_assoc()) {
            $districts[] = $row['District'];
        }
        echo json_encode(['success' => true, 'data' => $districts]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Query failed: ' . $myconn->error]);
    }
}

$myconn->close();
?>