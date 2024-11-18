<?php
session_start();

// Database connection for `messages` database
$servername_mess = "uoa25ublaow4obx5.cbetxkdyhwsb.us-east-1.rds.amazonaws.com";
$username_mess = "lcq4zy2vi4302d1q";
$password_mess = "xswigco0cdxdi5dd";
$dbname_mess = "kup80a8cc3mqs4ao"; // messages database

$conn_mess = new mysqli($servername_mess, $username_mess, $password_mess, $dbname_mess);

// Check connection for messages database
if ($conn_mess->connect_error) {
    die("Connection failed: " . $conn_mess->connect_error);
}

// Database connection for `user_registration` database
$servername_user_registration = "l3855uft9zao23e2.cbetxkdyhwsb.us-east-1.rds.amazonaws.com";
$username_user_registration = "equ6v8i5llo3uhjm";
$password_user_registration = "vkfaxm2are5bjc3q";
$dbname_user_registration = "ylwrjgaks3fw5sdj"; // user_registration database

$conn_user_registration = new mysqli($servername_user_registration, $username_user_registration, $password_user_registration, $dbname_user_registration);

// Check connection for user_registration database
if ($conn_user_registration->connect_error) {
    die("Connection failed: " . $conn_user_registration->connect_error);
}

// Fetch all messages from `sent_messages`
$chatMessages = [];
$fetchSql = "SELECT id, sender, message, timestamp FROM sent_messages ORDER BY timestamp";
$fetchStmt = $conn_mess->prepare($fetchSql);
$fetchStmt->execute();
$messageResult = $fetchStmt->get_result();

while ($msgRow = $messageResult->fetch_assoc()) {
    // Fetch the role for each sender
    if ($msgRow['sender'] != $_SESSION['username']) {
        // Fetch role from `colleges` table if the sender is not the logged-in user
        $roleSql = "SELECT role FROM colleges WHERE uname = ?";
        $roleStmt = $conn_user_registration->prepare($roleSql);
        $roleStmt->bind_param("s", $msgRow['sender']);
        $roleStmt->execute();
        $roleResult = $roleStmt->get_result();
        $role = $roleResult->fetch_assoc()['role'] ?? 'default_role'; // fallback to default if not found
        $roleStmt->close();
    } else {
        // Use the logged-in user's role
        $role = 'user'; // Or fetch it from the users table if necessary
    }

    // Add the message along with the role to the chat messages array
    $msgRow['role'] = $role;
    $msgRow['message_type'] = ($msgRow['sender'] === $_SESSION['username']) ? 'user' : 'other';
    $chatMessages[] = $msgRow;
}

$fetchStmt->close();

// Close connections
$conn_mess->close();
$conn_user_registration->close();

// Return messages as JSON
header('Content-Type: application/json');
echo json_encode($chatMessages);
?>
