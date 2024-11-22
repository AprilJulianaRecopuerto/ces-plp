<?php

// Database credentials for proj_list
$servername = "d6ybckq58s9ru745.cbetxkdyhwsb.us-east-1.rds.amazonaws.com";
$username_db = "t9riamok80kmok3h";
$password_db = "lzh13ihy0axfny6d";
$dbname_proj_list = "g8ri1hhtsfx77ptb";

// Create connection to proj_list database
$conn = new mysqli($servername, $username_db, $password_db, $dbname_proj_list);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tasks']) && isset($_POST['colleges'])) {
    $taskIds = json_decode($_POST['tasks']);
    $colleges = json_decode($_POST['colleges']);

    if (!empty($taskIds) && !empty($colleges)) {
        // Define department tables for task assignments
        $department_tables = [
            'cas' => 'cas_tasks',
            'cba' => 'cba_tasks',
            'con' => 'con_tasks',
            'coe' => 'coe_tasks',
            'coed' => 'coed_tasks',
            'ccs' => 'ccs_tasks',
            'cihm' => 'cihm_tasks'
        ];

        // Loop through each selected task ID
        foreach ($taskIds as $taskId) {
            // Fetch task details from the tasks table
            $stmt = $conn->prepare("SELECT task_description, due_date FROM tasks WHERE id = ?");
            $stmt->bind_param("i", $taskId);
            $stmt->execute();
            $result = $stmt->get_result();
            $task = $result->fetch_assoc();

            // Check if task exists
            if ($task) {
                // Insert task into selected department tables
                foreach ($colleges as $college) {
                    if (array_key_exists(strtolower($college), $department_tables)) {
                        $table = $department_tables[strtolower($college)];

                        // Insert task into department-specific table
                        $insertQuery = "INSERT INTO $table (task_id, task_description, due_date) 
                                        VALUES (?, ?, ?)";
                        $insertStmt = $conn->prepare($insertQuery);
                        $insertStmt->bind_param("iss", $taskId, $task['task_description'], $task['due_date']);
                        $insertStmt->execute();

                        // Add a notification for the task in the specific department's notification table
                        $notificationQuery = "INSERT INTO {$college}_notifications (task_description, {$college}_status, created_at) 
                                               VALUES (?, 'unread', NOW())";
                        $notificationStmt = $conn->prepare($notificationQuery);
                        $notificationStmt->bind_param("s", $task['task_description']);
                        $notificationStmt->execute();
                    }
                }
            }
        }

        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No tasks selected or no colleges specified']);
    }
}
?>
