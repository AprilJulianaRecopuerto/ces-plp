<?php
// Database credentials
$servername_resource = "mwgmw3rs78pvwk4e.cbetxkdyhwsb.us-east-1.rds.amazonaws.com";
$username_resource = "dnr20srzjycb99tw";
$password_resource = "ndfnpz4j74v8t0p7";
$dbname_resource = "x8uwt594q5jy7a7o";

// Create connection
$conn = new mysqli($servername_resource, $username_resource, $password_resource, $dbname_resource);

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
    $countSql = "SELECT COUNT(*) as itemCount FROM cba_items WHERE requisition_id = ?";
    $stmt = $conn->prepare($countSql);
    $stmt->bind_param('i', $requisitionId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $response['count'] = $result['itemCount'];

    if ($response['count'] === 1) {
        // Only one item, delete from cba_items and then from cba_requisition
        $deleteSql = "DELETE FROM cba_items WHERE requisition_id = ?";
        $stmt = $conn->prepare($deleteSql);
        $stmt->bind_param('i', $requisitionId);
        $response['success'] = $stmt->execute();

        // If successful, delete from cba_requisition
        if ($response['success']) {
            $deleteRequisitionSql = "DELETE FROM cba_requisition WHERE requisition_id = ?";
            $stmt = $conn->prepare($deleteRequisitionSql);
            $stmt->bind_param('i', $requisitionId);
            $stmt->execute();
        }
    }
} else {
    // Step 2: Delete items based on user choice ('all' or specific item)
    if ($itemName === 'all') {
        // Delete all items with the requisition_id
        $deleteSql = "DELETE FROM cba_items WHERE requisition_id = ?";
        $stmt = $conn->prepare($deleteSql);
        $stmt->bind_param('i', $requisitionId);
        $response['success'] = $stmt->execute();

        if ($response['success']) {
            $deleteRequisitionSql = "DELETE FROM cba_requisition WHERE requisition_id = ?";
            $stmt = $conn->prepare($deleteRequisitionSql);
            $stmt->bind_param('i', $requisitionId);
            $stmt->execute();
        }
    } else {
        // Delete a specific item by name
        $deleteSql = "DELETE FROM cba_items WHERE requisition_id = ? AND item_name = ?";
        $stmt = $conn->prepare($deleteSql);
        $stmt->bind_param('is', $requisitionId, $itemName);
        $response['success'] = $stmt->execute();

        // Check if there are remaining items for this requisition_id
        $remainingItemsSql = "SELECT COUNT(*) as remainingCount FROM cba_items WHERE requisition_id = ?";
        $stmt = $conn->prepare($remainingItemsSql);
        $stmt->bind_param('i', $requisitionId);
        $stmt->execute();
        $remainingResult = $stmt->get_result()->fetch_assoc();

        // If no items remain, delete from cba_requisition
        if ($remainingResult['remainingCount'] === 0) {
            $deleteRequisitionSql = "DELETE FROM cba_requisition WHERE requisition_id = ?";
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
