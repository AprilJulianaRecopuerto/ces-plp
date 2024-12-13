<?php
session_start();

// Database connection to `messages` database
$servername = "uoa25ublaow4obx5.cbetxkdyhwsb.us-east-1.rds.amazonaws.com";
$username = "lcq4zy2vi4302d1q";
$password = "xswigco0cdxdi5dd";
$dbname = "kup80a8cc3mqs4ao"; // Changed database to `messages`

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check for unread messages
$sql = "SELECT COUNT(*) as unread_count FROM sent_messages WHERE coed_read = 0";
$result = $conn->query($sql);

$response = ['status' => 'success', 'unread_count' => 0];
if ($result) {
    $row = $result->fetch_assoc();
    $response['unread_count'] = $row['unread_count'];
}

echo json_encode($response);
?>