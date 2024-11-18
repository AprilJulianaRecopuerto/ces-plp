<?php
session_start();

$servername = "alv4v3hlsipxnujn.cbetxkdyhwsb.us-east-1.rds.amazonaws.com";
$username_db = "ctk6gpo1v7sapq1l";
$password_db = "u1cgfgry8lu5rliz";
$dbname_budget_utilization = "oshzbyiasuos5kn4";

$conn = new mysqli($servername, $username_db, $password_db, $dbname_budget_utilization);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $projectId = $_POST['projectId'];
    $projTitle = $_POST['projTitle'];
    $leadPerson = $_POST['leadPerson'];
    $semester = $_POST['semester'];
    $expenses = (int) $_POST['expenses']; // Ensure it's treated as an integer

    // Check if project already exists
    $checkSql = "SELECT details_id FROM cas_budget WHERE proj_title = ?";
    $stmtCheck = $conn->prepare($checkSql);
    $stmtCheck->bind_param("s", $projTitle);
    $stmtCheck->execute();
    $stmtCheck->store_result();

    if ($stmtCheck->num_rows > 0) {
        echo "exists";
    } else {
        // Fetch the allotted budget for CAS
        $allottedBudgetSql = "SELECT allotted_budget FROM allotted_budget WHERE department_name = 'CAS'";
        $allottedBudgetResult = $conn->query($allottedBudgetSql);
        $allottedBudget = ($allottedBudgetResult && $row = $allottedBudgetResult->fetch_assoc()) ? $row['allotted_budget'] : 0;

        // Calculate the remaining budget for this project
        $remainingBudget = $allottedBudget - $expenses;  // Calculate based on the project's expenses

        // Insert the new project with its remaining budget
        $insertSql = "INSERT INTO cas_budget (proj_title, lead_person, semester, expenses, remaining_budget) VALUES (?, ?, ?, ?, ?)";
        $stmtInsert = $conn->prepare($insertSql);
        $stmtInsert->bind_param("sssii", $projTitle, $leadPerson, $semester, $expenses, $remainingBudget);

        if ($stmtInsert->execute()) {
            echo "success";
        } else {
            echo "Error: " . $stmtInsert->error;
        }
        $stmtInsert->close();
    }
    $stmtCheck->close();
}
$conn->close();
?>