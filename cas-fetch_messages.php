<?php
session_start();

// Database connection to messages database
$servername = "uoa25ublaow4obx5.cbetxkdyhwsb.us-east-1.rds.amazonaws.com";
$username = "lcq4zy2vi4302d1q";
$password = "xswigco0cdxdi5dd";
$dbname = "kup80a8cc3mqs4ao"; // Changed database to `messages`

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sn = "l3855uft9zao23e2.cbetxkdyhwsb.us-east-1.rds.amazonaws.com";
$un = "equ6v8i5llo3uhjm";
$psd = "vkfaxm2are5bjc3q";
$dbname_user_registration = "ylwrjgaks3fw5sdj"; // your database name

$conn_user = new mysqli($sn, $un, $psd, $dbname_user_registration);

// Check connection
if ($conn_user->connect_error) {
    die("Connection failed: " . $conn_user->connect_error);
}


// Fetch messages for the logged-in user from sent_messages
$chatMessages = [];
$fetchSql = "
    SELECT sent_messages.*, 
           IF(sent_messages.sender = ?, 'user', 'other') AS message_type
    FROM sent_messages
    ORDER BY sent_messages.timestamp";

$fetchStmt = $conn->prepare($fetchSql);
$fetchStmt->bind_param("s", $_SESSION['uname']); // Get messages sent to or from the logged-in user
$fetchStmt->execute();
$messageResult = $fetchStmt->get_result();

while ($msgRow = $messageResult->fetch_assoc()) {
    // Fetch the role separately
    $roleSql = "SELECT role FROM colleges WHERE uname = ?";
    $roleStmt = $conn_user->prepare($roleSql);
    $roleStmt->bind_param("s", $msgRow['sender']);
    $roleStmt->execute();
    $roleResult = $roleStmt->get_result();
    $roleRow = $roleResult->fetch_assoc();
    $msgRow['role'] = $roleRow['role'] ?? null;  // If no role found, set to null

    $chatMessages[] = $msgRow;
    
    // Close the role statement
    $roleStmt->close();
}
$fetchStmt->close();

// Close connection
$conn->close();

// Return messages as JSON
header('Content-Type: application/json');
echo json_encode($chatMessages);
?>


