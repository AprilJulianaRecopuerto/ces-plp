<?php
// Database credentials
$servername = "d6ybckq58s9ru745.cbetxkdyhwsb.us-east-1.rds.amazonaws.com";
$username = "t9riamok80kmok3h";
$password = "lzh13ihy0axfny6d";
$dbname = "g8ri1hhtsfx77ptb";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query to count unread notifications
$query = "SELECT COUNT(*) as count FROM cba_notifications WHERE cba_status = 'unread'";
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
