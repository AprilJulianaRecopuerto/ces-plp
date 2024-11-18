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

// Fetch messages for the logged-in user from sent_messages
$chatMessages = [];
$fetchSql = "
    SELECT sent_messages.*, 
           IF(users.roles IS NOT NULL, users.roles, colleges.role) AS role,
           IF(sent_messages.sender = ?, 'user', 'other') AS message_type
    FROM sent_messages
    LEFT JOIN user_registration.colleges ON sent_messages.sender = colleges.uname
    LEFT JOIN user_registration.users ON sent_messages.sender = users.username
    ORDER BY sent_messages.timestamp";

$fetchStmt = $conn->prepare($fetchSql);
$fetchStmt->bind_param("s", $_SESSION['username']); // Get messages sent to or from the logged-in user
$fetchStmt->execute();
$messageResult = $fetchStmt->get_result();

while ($msgRow = $messageResult->fetch_assoc()) {
    $chatMessages[] = $msgRow;
}
$fetchStmt->close();

// Close connection
$conn->close();

// Return messages as JSON
header('Content-Type: application/json');
echo json_encode($chatMessages);
?>

