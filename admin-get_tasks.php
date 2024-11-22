<?php
// get_tasks.php
$servername = "d6ybckq58s9ru745.cbetxkdyhwsb.us-east-1.rds.amazonaws.com";
$username = "t9riamok80kmok3h";
$password = "lzh13ihy0axfny6d";
$dbname = "g8ri1hhtsfx77ptb";

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
