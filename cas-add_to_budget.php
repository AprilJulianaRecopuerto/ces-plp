<?php
session_start();

$servername = "alv4v3hlsipxnujn.cbetxkdyhwsb.us-east-1.rds.amazonaws.com";
$username_db = "ctk6gpo1v7sapq1l";
$password_db = "u1cgfgry8lu5rliz";
$dbname_budget_utilization = "oshzbyiasuos5kn4";

// Establish database connection
$conn = new mysqli($servername, $username_db, $password_db, $dbname_budget_utilization);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and assign POST data to variables
    $projectId = $_POST['projectId'];
    $projTitle = $_POST['projTitle'];
    $leadPerson = $_POST['leadPerson'];
    $semester = $_POST['semester'];
    $expenses = (int)$_POST['expenses']; // Ensure it's treated as an integer

    // Check if the project title already exists in the database
    $checkSql = "SELECT details_id FROM cas_budget WHERE proj_title = ?";
    $stmtCheck = $conn->prepare($checkSql);
    $stmtCheck->bind_param("s", $projTitle);
    $stmtCheck->execute();
    $stmtCheck->store_result();

    if ($stmtCheck->num_rows > 0) {
        // If project already exists, return a message
        echo "exists";
    } else {
        // Fetch the allotted budget for CAS
        $allottedBudgetSql = "SELECT allotted_budget FROM allotted_budget WHERE department_name = 'CAS'";
        $allottedBudgetResult = $conn->query($allottedBudgetSql);
        $allottedBudget = ($allottedBudgetResult && $row = $allottedBudgetResult->fetch_assoc()) ? $row['allotted_budget'] : 0;

        // Calculate the remaining budget manually (optional)
        $remainingBudget = $allottedBudget - $expenses;

        // Insert the new project details into the database (excluding remaining_budget if it's a generated column)
        $insertSql = "INSERT INTO cas_budget (proj_title, lead_person, semester, expenses) VALUES (?, ?, ?, ?)";
        $stmtInsert = $conn->prepare($insertSql);

        if (!$stmtInsert) {
            // Debugging line for prepare failure
            die("Prepare failed: " . $conn->error);
        }

        // Bind parameters and execute the insertion
        $stmtInsert->bind_param("sssi", $projTitle, $leadPerson, $semester, $expenses);

        if ($stmtInsert->execute()) {
            // Return success message
            echo "success";
        } else {
            // Handle insertion errors
            echo "Error: " . $stmtInsert->error;
        }

        // Close the insert statement
        $stmtInsert->close();
    }

    // Close the check statement
    $stmtCheck->close();
}

// Close the database connection
$conn->close();
?>
