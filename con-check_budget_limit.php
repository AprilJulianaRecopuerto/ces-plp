<?php
// Include your database connection
$servername = "alv4v3hlsipxnujn.cbetxkdyhwsb.us-east-1.rds.amazonaws.com";
$username_db = "ctk6gpo1v7sapq1l";
$password_db = "u1cgfgry8lu5rliz";
$dbname_budget_utilization = "oshzbyiasuos5kn4";

// Establish database connection
$conn = new mysqli($servername, $username_db, $password_db, $dbname_budget_utilization);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newExpense = isset($_POST['newExpense']) ? floatval($_POST['newExpense']) : 0;

    // Fetch the allotted budget for CON
    $queryAllottedBudget = "
        SELECT allotted_budget 
        FROM allotted_budget 
        WHERE department_name = 'CON'";
    $resultAllottedBudget = $conn->query($queryAllottedBudget);

    if ($resultAllottedBudget && $resultAllottedBudget->num_rows > 0) {
        $row = $resultAllottedBudget->fetch_assoc();
        $allottedBudget = floatval($row['allotted_budget']);
    } else {
        echo "error|Allotted budget not found.";
        exit;
    }

    // Calculate the total expenses in CON
    $queryTotalExpenses = "
        SELECT COALESCE(SUM(expenses), 0) AS total_expenses 
        FROM con_budget";
    $resultTotalExpenses = $conn->query($queryTotalExpenses);

    if ($resultTotalExpenses && $resultTotalExpenses->num_rows > 0) {
        $row = $resultTotalExpenses->fetch_assoc();
        $currentExpenses = floatval($row['total_expenses']);
    } else {
        echo "error|Failed to fetch total expenses.";
        exit;
    }

    // Check if the new expense will exceed the allotted budget
    $remainingBudget = $allottedBudget - $currentExpenses;

    if ($newExpense > $remainingBudget) {
        echo "error|Budget exceeded! Remaining budget: " . number_format($remainingBudget, 2);
    } else {
        echo "success|Sufficient budget available. Remaining budget: " . number_format($remainingBudget, 2);
    }
}
?>