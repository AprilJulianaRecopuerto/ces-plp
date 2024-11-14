<?php
// admin-done_task_fetch_notifications.php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "admin_todo_list";

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
