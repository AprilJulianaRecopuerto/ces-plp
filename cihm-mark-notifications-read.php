<?php
// Connect to the database
$servername = "d6ybckq58s9ru745.cbetxkdyhwsb.us-east-1.rds.amazonaws.com";
$username = "t9riamok80kmok3h";
$password = "lzh13ihy0axfny6d";
$dbname = "g8ri1hhtsfx77ptb";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Update notifications to 'read' for the current user or session
$query = "UPDATE cihm_notifications SET cihm_status = 'read' WHERE cihm_status = 'unread'";
$stmt = $conn->prepare($query);
$stmt->execute();

// Return a response
echo json_encode(['status' => 'success']);

// Close the database connection
$stmt->close();
$conn->close();
?>