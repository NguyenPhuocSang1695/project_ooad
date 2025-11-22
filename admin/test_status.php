<?php
// Test file to check Status field in database
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'php/connect.php';

$db = new DatabaseConnection();
$db->connect();

$sql = "SELECT user_id, Username, FullName, Status, Role FROM users LIMIT 5";
$result = $db->query($sql);

echo "<h2>Test Status Field</h2>";
echo "<table border='1' cellpadding='10'>";
echo "<tr><th>user_id</th><th>Username</th><th>FullName</th><th>Status (DB)</th><th>Role</th></tr>";

while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['user_id']) . "</td>";
    echo "<td>" . htmlspecialchars($row['Username'] ?? 'NULL') . "</td>";
    echo "<td>" . htmlspecialchars($row['FullName']) . "</td>";
    echo "<td><strong>" . htmlspecialchars($row['Status']) . "</strong></td>";
    echo "<td>" . htmlspecialchars($row['Role']) . "</td>";
    echo "</tr>";
}

echo "</table>";

// Test User class
require_once 'php/User.php';

echo "<h2>Test User Class</h2>";
$result = $db->query($sql);
while ($row = $result->fetch_assoc()) {
    $user = new User($row);
    echo "<div style='padding: 10px; margin: 10px 0; border: 1px solid #ccc;'>";
    echo "<strong>FullName:</strong> " . htmlspecialchars($user->getFullname()) . "<br>";
    echo "<strong>Status (getStatus):</strong> " . htmlspecialchars($user->getStatus()) . "<br>";
    echo "<strong>isActive():</strong> " . ($user->isActive() ? 'TRUE' : 'FALSE') . "<br>";
    echo "<strong>getStatusText():</strong> " . htmlspecialchars($user->getStatusText()) . "<br>";
    echo "</div>";
}

$db->close();
?>
