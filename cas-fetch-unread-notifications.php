<?php
// Database credentials
$servername = "localhost";
$username_db = "root";
$password_db = "";
$dbname_proj_list = "admin_todo_list";

// Create connection to proj_list database
$conn = new mysqli($servername, $username_db, $password_db, $dbname_proj_list);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query to count unread notifications
$query = "SELECT COUNT(*) as count FROM cas_notifications WHERE cas_status = 'unread'";
$result = $conn->query($query);

$response = ['unreadCount' => 0];
if ($result && $row = $result->fetch_assoc()) {
    $response['unreadCount'] = (int)$row['count'];
}

// Close connection
$conn->close();

// Return the count as JSON
header('Content-Type: application/json');
echo json_encode($response);
?>
