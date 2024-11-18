<?php
session_start();

// Database connection to `messages` database
$servername = "uoa25ublaow4obx5.cbetxkdyhwsb.us-east-1.rds.amazonaws.com";
$username = "lcq4zy2vi4302d1q";
$password = "xswigco0cdxdi5dd";
$dbname = "kup80a8cc3mqs4ao"; // Messages database

$conn_mess = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn_mess->connect_error) {
    die("Connection failed: " . $conn_mess->connect_error);
}

// Database connection to `user_registration` database
$sn = "l3855uft9zao23e2.cbetxkdyhwsb.us-east-1.rds.amazonaws.com";
$un = "equ6v8i5llo3uhjm";
$psd = "vkfaxm2are5bjc3q";
$dbname_user_registration = "ylwrjgaks3fw5sdj";

$conn_users = new mysqli($sn, $un, $psd, $dbname_user_registration);

// Check connection to user_registration database
if ($conn_users->connect_error) {
    die("Connection failed: " . $conn_users->connect_error);
}

// Fetch messages for the logged-in user from sent_messages
$chatMessages = [];
$fetchSql = "
    SELECT sent_messages.* 
    FROM sent_messages
    WHERE sent_messages.sender = ? OR sent_messages.receiver = ?
    ORDER BY sent_messages.timestamp";

$fetchStmt = $conn_mess->prepare($fetchSql);
$fetchStmt->bind_param("ss", $_SESSION['username'], $_SESSION['username']); // Get messages for the logged-in user
$fetchStmt->execute();
$messageResult = $fetchStmt->get_result();

while ($msgRow = $messageResult->fetch_assoc()) {
    // Fetch the role of the sender from the users or colleges table
    $role = '';
    $sender = $msgRow['sender'];

    // Check if sender is in users table
    $roleSql = "SELECT roles FROM users WHERE username = ?";
    $roleStmt = $conn_users->prepare($roleSql);
    $roleStmt->bind_param("s", $sender);
    $roleStmt->execute();
    $roleResult = $roleStmt->get_result();
    
    if ($roleRow = $roleResult->fetch_assoc()) {
        $role = $roleRow['roles'];
    } else {
        // If not found in users, check colleges table
        $roleSql = "SELECT role FROM colleges WHERE uname = ?";
        $roleStmt = $conn_users->prepare($roleSql);
        $roleStmt->bind_param("s", $sender);
        $roleStmt->execute();
        $roleResult = $roleStmt->get_result();

        if ($roleRow = $roleResult->fetch_assoc()) {
            $role = $roleRow['role'];
        }
    }

    // Add role to the message data
    $msgRow['role'] = $role;

    // Determine message type (user or other)
    $msgRow['message_type'] = ($msgRow['sender'] == $_SESSION['username']) ? 'user' : 'other';

    // Add message to the chat messages array
    $chatMessages[] = $msgRow;
}

$fetchStmt->close();
$roleStmt->close();

// Close connections
$conn_mess->close();
$conn_users->close();

// Return messages as JSON
header('Content-Type: application/json');
echo json_encode($chatMessages);
?>
