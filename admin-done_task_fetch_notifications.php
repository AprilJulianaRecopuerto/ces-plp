<?php
// admin-done_task_fetch_notifications.php
session_start();

$servername = "d6ybckq58s9ru745.cbetxkdyhwsb.us-east-1.rds.amazonaws.com";
$username = "t9riamok80kmok3h";
$password = "lzh13ihy0axfny6d";
$dbname = "g8ri1hhtsfx77ptb";

$conn = new mysqli($servername, $username, $password, $dbname);

$notifications = [];

// Fetch unread notifications
$query = "SELECT * FROM admin_notifications WHERE status = 'unread' ORDER BY created_at DESC";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
}

echo json_encode($notifications);
$conn->close();
?>