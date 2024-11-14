<?php
// get_tasks.php
$servername = "localhost";
$username = "root"; // replace with your database username
$password = ""; // replace with your database password
$dbname = "admin_todo_list"; // replace with your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT id, task_description, due_date FROM tasks ORDER BY created_at DESC";
$result = $conn->query($sql);

$tasks = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $tasks[] = $row;
    }
}

echo json_encode($tasks);
$conn->close();
?>
