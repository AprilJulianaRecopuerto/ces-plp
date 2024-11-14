<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "resource_utilization"; // Change to your actual database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get requisition_id and item_name from the AJAX request
$requisitionId = $_GET['requisition_id'];
$itemName = isset($_GET['item_name']) ? $_GET['item_name'] : null;
$response = ['success' => false, 'count' => 0];

if (!$itemName) {
    // Step 1: Check the count of items for the given requisition_id
    $countSql = "SELECT COUNT(*) as itemCount FROM coe_items WHERE requisition_id = ?";
    $stmt = $conn->prepare($countSql);
    $stmt->bind_param('i', $requisitionId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $response['count'] = $result['itemCount'];

    if ($response['count'] === 1) {
        // Only one item, delete from coe_items and then from coe_requisition
        $deleteSql = "DELETE FROM coe_items WHERE requisition_id = ?";
        $stmt = $conn->prepare($deleteSql);
        $stmt->bind_param('i', $requisitionId);
        $response['success'] = $stmt->execute();

        // If successful, delete from coe_requisition
        if ($response['success']) {
            $deleteRequisitionSql = "DELETE FROM coe_requisition WHERE requisition_id = ?";
            $stmt = $conn->prepare($deleteRequisitionSql);
            $stmt->bind_param('i', $requisitionId);
            $stmt->execute();
        }
    }
} else {
    // Step 2: Delete items based on user choice ('all' or specific item)
    if ($itemName === 'all') {
        // Delete all items with the requisition_id
        $deleteSql = "DELETE FROM coe_items WHERE requisition_id = ?";
        $stmt = $conn->prepare($deleteSql);
        $stmt->bind_param('i', $requisitionId);
        $response['success'] = $stmt->execute();

        if ($response['success']) {
            $deleteRequisitionSql = "DELETE FROM coe_requisition WHERE requisition_id = ?";
            $stmt = $conn->prepare($deleteRequisitionSql);
            $stmt->bind_param('i', $requisitionId);
            $stmt->execute();
        }
    } else {
        // Delete a specific item by name
        $deleteSql = "DELETE FROM coe_items WHERE requisition_id = ? AND item_name = ?";
        $stmt = $conn->prepare($deleteSql);
        $stmt->bind_param('is', $requisitionId, $itemName);
        $response['success'] = $stmt->execute();

        // Check if there are remaining items for this requisition_id
        $remainingItemsSql = "SELECT COUNT(*) as remainingCount FROM coe_items WHERE requisition_id = ?";
        $stmt = $conn->prepare($remainingItemsSql);
        $stmt->bind_param('i', $requisitionId);
        $stmt->execute();
        $remainingResult = $stmt->get_result()->fetch_assoc();

        // If no items remain, delete from coe_requisition
        if ($remainingResult['remainingCount'] === 0) {
            $deleteRequisitionSql = "DELETE FROM coe_requisition WHERE requisition_id = ?";
            $stmt = $conn->prepare($deleteRequisitionSql);
            $stmt->bind_param('i', $requisitionId);
            $stmt->execute();
        }
    }
}

// Return JSON response
echo json_encode($response);

// Close the statement and connection
$stmt->close();
$conn->close();
?>
