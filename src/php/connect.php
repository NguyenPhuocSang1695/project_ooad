<?php
$servername = "localhost";
$username1 = "root";
$password = "";
$dbname = "c01db";
$conn = new mysqli($servername, $username1, $password, $dbname);
if ($conn->connect_error) {
  die("Kết nối thất bại: " . $conn->connect_error);
}
