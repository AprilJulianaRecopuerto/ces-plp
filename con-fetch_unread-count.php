<?php
// Database credentials
$servername = "d6ybckq58s9ru745.cbetxkdyhwsb.us-east-1.rds.amazonaws.com";
$username = "t9riamok80kmok3h";
$password = "lzh13ihy0axfny6d";
$dbname = "g8ri1hhtsfx77ptb";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Initialize unread count
$unreadCount = 0;

// Query to count unread notifications
$query = "SELECT COUNT(*) as count FROM con_notifications WHERE con_status = 'unread'";
$result = $conn->query($query);

if ($result && $row = $result->fetch_assoc()) {
    $unreadCount = $row['count'];
}

echo json_encode(['unreadCount' => $unreadCount]);

$conn->close();
?>
