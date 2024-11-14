<?php
session_start();
// Database connection to `messages` database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "messages"; // Changed database to `messages`

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Update all unread messages to mark them as read
$sql = "UPDATE sent_messages SET admin_read = 1 WHERE admin_read = 0";
if ($conn->query($sql) === TRUE) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => $conn->error]);
}
?>