cas-update_expenses.php
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
    $detailsId = (int) $_POST['detailsId'];
    $newExpense = (int) $_POST['newExpense'];

    // Fetch the existing expenses and remaining budget for this entry
    $fetchSql = "SELECT expenses, remaining_budget FROM cas_budget WHERE details_id = ?";
    $stmtFetch = $conn->prepare($fetchSql);
    $stmtFetch->bind_param("i", $detailsId);
    $stmtFetch->execute();
    $stmtFetch->bind_result($oldExpense, $oldRemainingBudget);
    $stmtFetch->fetch();
    $stmtFetch->close();

    // Update only the expenses, do not change the remaining budget
    $updateSql = "UPDATE cas_budget SET expenses = ? WHERE details_id = ?";
    $stmtUpdate = $conn->prepare($updateSql);
    $stmtUpdate->bind_param("ii", $newExpense, $detailsId);

    if ($stmtUpdate->execute()) {
        echo "success";
    } else {
        echo "Error: " . $stmtUpdate->error;
    }
    $stmtUpdate->close();
}
$conn->close();
?>