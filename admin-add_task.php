<?php
$host = 'd6ybckq58s9ru745.cbetxkdyhwsb.us-east-1.rds.amazonaws.com';
$user = 't9riamok80kmok3h';
$pass = 'lzh13ihy0axfny6d'; // Use your database password
$db = 'g8ri1hhtsfx77ptb';

// Create connection
$conn = new mysqli($host, $user, $pass, $db);


// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if task and due date are set
if (isset($_POST['task']) && isset($_POST['due_date'])) {
    $task = $conn->real_escape_string($_POST['task']);
    $due_date = $conn->real_escape_string($_POST['due_date']);

    $sql = "INSERT INTO tasks (task_description, due_date) VALUES ('$task', '$due_date')";

    if ($conn->query($sql) === TRUE) {
        echo json_encode(["status" => "success", "message" => "Task added successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error: " . $sql . "<br>" . $conn->error]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid input"]);
}

$conn->close();
?>