<?php
session_start();

// Database credentials for proj_list
$servername = "localhost";
$username_db = "root";
$password_db = "";
$dbname_proj_list = "admin_todo_list";

// Create connection to proj_list database
$conn = new mysqli($servername, $username_db, $password_db, $dbname_proj_list);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if data is provided
if (isset($_POST['id'], $_POST['description'], $_POST['due_date'])) {
    $id = intval($_POST['id']);
    $description = $_POST['description'];
    $due_date = $_POST['due_date'];

    // Prepare the SQL update statement
    $sql = "UPDATE tasks SET task_description = ?, due_date = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $description, $due_date, $id);

    // Execute the query
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update task']);
    }
    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
}

$conn->close();
?>
