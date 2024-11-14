<?php
session_start();
if (!isset($_SESSION['username'])) {
    exit(json_encode(["status" => "error", "message" => "Unauthorized access"]));
}
date_default_timezone_set('Asia/Manila');

// Database connection
$log_conn = new mysqli('localhost', 'root', '', 'user_registration');
if ($log_conn->connect_error) {
    exit(json_encode(["status" => "error", "message" => "Connection failed: " . $log_conn->connect_error]));
}

// Read the JSON input
$data = json_decode(file_get_contents("php://input"), true);
if (isset($data['buttonFunction'])) {
    $functionName = $log_conn->real_escape_string($data['buttonFunction']);
    $username = $log_conn->real_escape_string($_SESSION['username']);
    $timestamp = date('Y-m-d H:i:s');

    // Insert log into the database
    $sql = "INSERT INTO activity_logs (button_name, username, timestamp) VALUES ('$functionName', '$username', '$timestamp')";
    if ($log_conn->query($sql) === TRUE) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error: " . $log_conn->error]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "No button function specified"]);
}

$log_conn->close();
?>