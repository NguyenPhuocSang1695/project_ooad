<?php
header("Content-Type: application/json");

include 'connect.php';

if ($myconn->connect_error) {
    die(json_encode(['error' => 'Connection failed: ' . $myconn->connect_error]));
}

$query = isset($_GET['query']) ? $myconn->real_escape_string($_GET['query']) : '';

$sql = "SELECT DISTINCT Address 
        FROM users 
        WHERE Address LIKE '%$query%' 
        LIMIT 10";
$result = $myconn->query($sql);

$addresses = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $addresses[] = $row['Address'];
    }
} else {
    $addresses = ['error' => 'Query failed: ' . $myconn->error];
}

echo json_encode($addresses);

$myconn->close();
?>