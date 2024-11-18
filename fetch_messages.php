<?php
session_start();

// Database connection to `messages` database
$servername_messages = "uoa25ublaow4obx5.cbetxkdyhwsb.us-east-1.rds.amazonaws.com";
$username_messages = "lcq4zy2vi4302d1q";
$password_messages = "xswigco0cdxdi5dd";
$dbname_messages = "kup80a8cc3mqs4ao"; // your messages database name

$conn_messages = new mysqli($servername_messages, $username_messages, $password_messages, $dbname_messages);

// Check connection to messages database
if ($conn_messages->connect_error) {
    die("Connection failed: " . $conn_messages->connect_error);
}

// Database connection to `user_registration` database
$servername_user = "l3855uft9zao23e2.cbetxkdyhwsb.us-east-1.rds.amazonaws.com";
$username_user = "equ6v8i5llo3uhjm";
$password_user = "vkfaxm2are5bjc3q";
$dbname_user_registration = "ylwrjgaks3fw5sdj"; // your user_registration database name

$conn_user = new mysqli($servername_user, $username_user, $password_user, $dbname_user_registration);

// Check connection to user_registration database
if ($conn_user->connect_error) {
    die("Connection failed: " . $conn_user->connect_error);
}

// Fetch messages for the logged-in user from sent_messages (messages database)
$chatMessages = [];
$fetchSql = "SELECT * FROM sent_messages WHERE sender = ? OR receiver = ? ORDER BY timestamp";
$fetchStmt = $conn_messages->prepare($fetchSql);
$fetchStmt->bind_param("ss", $_SESSION['username'], $_SESSION['username']); // Get messages sent to or from the logged-in user
$fetchStmt->execute();
$messageResult = $fetchStmt->get_result();

while ($msgRow = $messageResult->fetch_assoc()) {
    // Fetch role from the user_registration.users table based on the sender (user_registration database)
    $roleSql = "SELECT role FROM users WHERE username = ?";
    $roleStmt = $conn_user->prepare($roleSql);
    $roleStmt->bind_param("s", $msgRow['sender']);
    $roleStmt->execute();
    $roleResult = $roleStmt->get_result();
    
    // If a role is found, add it to the message
    $role = '';
    if ($roleRow = $roleResult->fetch_assoc()) {
        $role = $roleRow['role'];
    } else {
        // If no role is found in users table, try colleges table (user_registration database)
        $roleSql = "SELECT role FROM colleges WHERE uname = ?";
        $roleStmt = $conn_user->prepare($roleSql);
        $roleStmt->bind_param("s", $msgRow['sender']);
        $roleStmt->execute();
        $roleResult = $roleStmt->get_result();
        
        if ($roleRow = $roleResult->fetch_assoc()) {
            $role = $roleRow['role'];
        }
    }

    // Add the role and message type ('user' or 'other') to the message
    $msgRow['role'] = $role;
    $msgRow['message_type'] = ($msgRow['sender'] == $_SESSION['username']) ? 'user' : 'other';
    
    $chatMessages[] = $msgRow;
}

$fetchStmt->close();

// Close both database connections
$conn_messages->close();
$conn_user->close();

// Return messages as JSON
header('Content-Type: application/json');
echo json_encode($chatMessages);
?>
