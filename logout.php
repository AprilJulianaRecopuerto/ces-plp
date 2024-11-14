<?php
session_start();

// Set the default timezone
date_default_timezone_set('Asia/Manila'); // Change to your timezone


$servername = "localhost";
$username = "root"; // Replace with your database username
$password = ""; // Replace with your database password
$dbname = "user_registration"; // Replace with your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// This code should be triggered by a logout request
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    if (isset($_SESSION['username'])) {
        $username = $_SESSION['username'];
        $logoutTime = date('Y-m-d H:i:s');

        // Prepare the SQL statement to update the logout timestamp
        $updateLogoutSql = "UPDATE adhistory SET logout_ts = ? WHERE username = ? AND logout_ts IS NULL";
        $logoutStmt = $conn->prepare($updateLogoutSql);

        if ($logoutStmt === false) {
            die("MySQL prepare failed: " . $conn->error);
        }

        // Bind parameters: first is the logout timestamp, second is the username
        if (!$logoutStmt->bind_param("ss", $logoutTime, $username)) {
            die("Bind param failed: " . $logoutStmt->error);
        }

        // Execute the update
        if (!$logoutStmt->execute()) {
            die("Execute failed: " . $logoutStmt->error);
        } else {
            echo "Logout timestamp updated successfully.";
        }

        // Close the logout statement
        $logoutStmt->close();

        // Destroy the session and redirect
        session_destroy();
        header("Location: your_login_page.php");
        exit;
    } else {
        http_response_code(400);
        echo json_encode(['message' => 'No active session found']);
    }
}
?>