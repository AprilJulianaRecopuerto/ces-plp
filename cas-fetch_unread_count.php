<?php
// Database credentials
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "admin_todo_list";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Initialize unread count
$unreadCount = 0;

// Query to count unread notifications
$query = "SELECT COUNT(*) as count FROM cas_notifications WHERE cas_status = 'unread'";
$result = $conn->query($query);

if ($result && $row = $result->fetch_assoc()) {
    $unreadCount = $row['count'];
}

echo json_encode(['unreadCount' => $unreadCount]);

$conn->close();
?>
