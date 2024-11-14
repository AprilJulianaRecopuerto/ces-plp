<?php
// Connect to the database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "admin_todo_list";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Update notifications to 'read' for the current user or session
$query = "UPDATE cas_notifications SET cas_status = 'read' WHERE cas_status = 'unread'";
$stmt = $conn->prepare($query);
$stmt->execute();

// Return a response
echo json_encode(['status' => 'success']);

// Close the database connection
$stmt->close();
$conn->close();
?>