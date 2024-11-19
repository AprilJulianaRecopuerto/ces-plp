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

if (isset($_GET['college'])) {
    $college = $_GET['college'];
    $collegeTable = $college . '_budget'; // Table name based on college

    // Get the total expenses for the college
    $expensesSql = "SELECT SUM(expenses) AS total_expenses FROM $collegeTable";
    $expensesResult = $conn->query($expensesSql);

    $remainingBudget = 0;
    if ($expensesResult && $expensesResult->num_rows > 0) {
        $expenseRow = $expensesResult->fetch_assoc();
        $totalExpenses = $expenseRow['total_expenses'] ? $expenseRow['total_expenses'] : 0;

        // Get the allotted budget for the college
        $allottedBudgetSql = "SELECT allotted_budget FROM allotted_budget WHERE department_name = '" . strtoupper($college) . "'";
        $allottedResult = $conn->query($allottedBudgetSql);

        if ($allottedResult && $allottedResult->num_rows > 0) {
            $allottedRow = $allottedResult->fetch_assoc();
            $allottedBudget = $allottedRow['allotted_budget'] ? $allottedRow['allotted_budget'] : 0;

            // Calculate the remaining budget
            $remainingBudget = $allottedBudget - $totalExpenses;
        }
    }

    // Return the remaining budget as a formatted number
    echo number_format($remainingBudget, 2);
}

$conn->close();
?>
