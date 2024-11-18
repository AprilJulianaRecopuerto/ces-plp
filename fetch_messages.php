<?php
session_start();

// Database connection to messages database
$servername = "uoa25ublaow4obx5.cbetxkdyhwsb.us-east-1.rds.amazonaws.com";
$username = "lcq4zy2vi4302d1q";
$password = "xswigco0cdxdi5dd";
$dbname = "kup80a8cc3mqs4ao"; // Changed database to messages

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set timezone for the session to UTC+8
$conn->query("SET time_zone = '+08:00'");

// User database connection
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
           IF(sent_messages.sender = ?, 'user', 'other') AS message_type,
           sent_messages.timestamp AS timestamp_utc8
    FROM sent_messages
    ORDER BY sent_messages.timestamp";

$fetchStmt = $conn->prepare($fetchSql);
$fetchStmt->bind_param("s", $_SESSION['username']); // Get messages sent to or from the logged-in user
$fetchStmt->execute();
$messageResult = $fetchStmt->get_result();

while ($msgRow = $messageResult->fetch_assoc()) {
    // Fetch the role for the sender from the users table (only if it's accessible)
    $roleSql = "SELECT roles FROM users WHERE username = ?";
    $roleStmt = $conn_user->prepare($roleSql);
    $roleStmt->bind_param("s", $msgRow['sender']);
    $roleStmt->execute();
    $roleResult = $roleStmt->get_result();
    $role = $roleResult->fetch_assoc()['roles'] ?? null;

    // Fetch the role from the colleges table (only if it's accessible)
    $collegeSql = "SELECT role FROM colleges WHERE uname = ?";
    $collegeStmt = $conn_user->prepare($collegeSql);
    $collegeStmt->bind_param("s", $msgRow['sender']);
    $collegeStmt->execute();
    $collegeResult = $collegeStmt->get_result();
    $collegeRole = $collegeResult->fetch_assoc()['role'] ?? null;

    // Add the message with roles to the chat array
    $msgRow['role'] = $role ?? $collegeRole;

    // Convert timestamp from UTC to UTC+8
    $timestamp = new DateTime($msgRow['timestamp_utc8'], new DateTimeZone('UTC'));
    $timestamp->setTimezone(new DateTimeZone('Asia/Manila'));
    $msgRow['timestamp_utc8'] = $timestamp->format('F j, Y || h:i A');

    $chatMessages[] = $msgRow;

    $roleStmt->close();
    $collegeStmt->close();
}
$fetchStmt->close();

// Close connection
$conn->close();

// Return messages as JSON
header('Content-Type: application/json');
echo json_encode($chatMessages);

?>
