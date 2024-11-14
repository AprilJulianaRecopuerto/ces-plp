<?php
// Database connection details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "admin_todo_list";  // Database for tasks

// Check if task_id is provided
if (isset($_POST['task_id'])) {
    $taskId = intval($_POST['task_id']);

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Delete task from cba_tasks table
    $sql = "DELETE FROM cba_tasks WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $taskId);
    
    if ($stmt->execute()) {
        // Add a notification for the admin
        $notification_message = "A task was marked as done by a CAS user.";
        $project_name = "CAS Task Completion"; // Adjust as needed
        
        // Insert notification into notifications table
        $sql = "INSERT INTO cba_notifications (project_name, notification_message, created_at) VALUES (?, ?, NOW())";
        $stmt2 = $conn->prepare($sql);
        $stmt2->bind_param("ss", $project_name, $notification_message);
        $stmt2->execute();

        echo "success";
    } else {
        echo "error";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "No task ID provided";
}
?>
