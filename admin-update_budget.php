<?php
$servername = "alv4v3hlsipxnujn.cbetxkdyhwsb.us-east-1.rds.amazonaws.com";
$username = "ctk6gpo1v7sapq1l";
$password = "u1cgfgry8lu5rliz";
$dbname = "oshzbyiasuos5kn4"; // Database name


// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['college']) && isset($_POST['updatedBudget'])) {
    $college = $_POST['college'];
    $updatedBudget = $_POST['updatedBudget'];

    // Update the allotted budget in the database for the selected college
    $updateSql = "UPDATE allotted_budget SET allotted_budget = ? WHERE department_name = ?";
    $stmt = $conn->prepare($updateSql);
    $stmt->bind_param('ds', $updatedBudget, strtoupper($college)); // 'd' for double, 's' for string
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo "Budget updated successfully!";
    } else {
        echo "Error updating budget!";
    }

    $stmt->close();
}

$conn->close();
?>