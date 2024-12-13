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

// Get the notification ID from the POST request
$data = json_decode(file_get_contents("php://input"), true);
$notificationId = $data['id'];

// Validate the notification ID
if (!empty($notificationId)) {
    // Delete the notification from the database
    $sql = "DELETE FROM coed_notifications WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $notificationId);

    if ($stmt->execute()) {
        $response = ['status' => 'success'];
    } else {
        $response = ['status' => 'error', 'message' => 'Failed to delete notification.'];
    }

    $stmt->close();
} else {
    $response = ['status' => 'error', 'message' => 'Invalid notification ID.'];
}

$conn->close();

// Return a response as JSON
header('Content-Type: application/json');
echo json_encode($response);
?>
