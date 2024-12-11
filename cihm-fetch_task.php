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


// Get the last fetch timestamp or set it to a default (e.g., 24 hours ago) if this is the first fetch
$last_fetch_time = isset($_GET['last_fetch_time']) ? $_GET['last_fetch_time'] : date('Y-m-d H:i:s', strtotime('-24 hours'));

// Query to fetch new notifications after the last fetch time
$query = "SELECT * FROM cihm_notifications WHERE created_at > ? ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $last_fetch_time);
$stmt->execute();
$result = $stmt->get_result();

// Fetch all notifications
$notifications = [];
while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}

// Return notifications as a JSON response
echo json_encode(['notifications' => $notifications]);

// Close the database connection
$stmt->close();
$conn->close();
?>