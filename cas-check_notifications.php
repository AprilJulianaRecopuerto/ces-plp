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

// Check for unread messages
$sql = "SELECT COUNT(*) as unread_count FROM sent_messages WHERE cas_read = 0";
$result = $conn->query($sql);

$response = ['status' => 'success', 'unread_count' => 0];
if ($result) {
    $row = $result->fetch_assoc();
    $response['unread_count'] = $row['unread_count'];
}

echo json_encode($response);
?>