<?php
// Database connection details
$servername = "d6ybckq58s9ru745.cbetxkdyhwsb.us-east-1.rds.amazonaws.com";
$username = "t9riamok80kmok3h";
$password = "lzh13ihy0axfny6d";
$dbname = "g8ri1hhtsfx77ptb";

// Check if task_id is provided
if (isset($_POST['task_id'])) {
    $taskId = intval($_POST['task_id']);

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Delete task from cas_tasks table
    $sql = "DELETE FROM cas_tasks WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $taskId);
    
    if ($stmt->execute()) {
        // Add a notification for the admin
        $notification_message = "A task was marked as done by a CAS user.";
        $project_name = "CAS Task Completion"; // Adjust as needed
        
        // Insert notification into notifications table
        $sql = "INSERT INTO cas_notifications (project_name, notification_message, created_at) VALUES (?, ?, NOW())";
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
