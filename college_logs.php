<?php
date_default_timezone_set('Asia/Manila'); // Change to your timezone
session_start(); // Make sure the session is started to access $_SESSION

function logUserActivity($username, $action) {
    // Database connection (update with your own connection details)
    $servername = "l3855uft9zao23e2.cbetxkdyhwsb.us-east-1.rds.amazonaws.com";
    $usernameDB = "equ6v8i5llo3uhjm";
    $passwordDB = "vkfaxm2are5bjc3q";
    $dbname = "ylwrjgaks3fw5sdj";

    // Create connection
    $conn = new mysqli($servername, $usernameDB, $passwordDB, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO colleges_actlogs (uname, action) VALUES (?, ?)");
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("ss", $username, $action);

    // Execute the statement
    if ($stmt->execute()) {
        // Optionally, you can log a success message or return a response
    } else {
        echo "Error logging activity: " . $stmt->error;
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();
}

// Check if user is logged in and action is received
if (isset($_SESSION['uname']) && isset($_POST['action'])) {
    $username = $_SESSION['uname'];
    $action = $_POST['action']; // Get the action description from the POST request

    // Call the function to log activity
    logUserActivity($username, $action);
} else {
    echo "User not logged in or action not set.";
}
?>
