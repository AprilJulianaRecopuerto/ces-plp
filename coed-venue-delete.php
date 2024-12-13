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

// Check if reservation_id is provided for count check
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['reservation_id'])) {
    $reservation_id = intval($_GET['reservation_id']);

    // Query to count venues and additional requests
    $countQuery = "
        SELECT 
            (SELECT COUNT(*) FROM coed_venue_request WHERE reservation_id = ?) AS venue_count,
            (SELECT COUNT(*) FROM coed_addedrequest WHERE reservation_id = ?) AS request_count
    ";

    $stmt = $conn->prepare($countQuery);
    $stmt->bind_param('ii', $reservation_id, $reservation_id);
    $stmt->execute();
    $countResult = $stmt->get_result();
    $counts = $countResult->fetch_assoc();

    header('Content-Type: application/json');
    echo json_encode($counts);
    exit;
}

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reservation_id'])) {
    $reservation_id = intval($_POST['reservation_id']);
    $venue_name = isset($_POST['venue_name']) ? trim($_POST['venue_name']) : null;
    $request_name = isset($_POST['additional_request']) ? trim($_POST['additional_request']) : null;

    $success = true; // Track success of deletions

    // Case 1: Delete all records when both venue_name and request_name are 'all'
    if ($venue_name === 'all' && $request_name === 'all') {
        // Delete all venues
        $deleteAllVenuesQuery = "DELETE FROM coed_venue_request WHERE reservation_id = ?";
        $stmt = $conn->prepare($deleteAllVenuesQuery);
        $stmt->bind_param('i', $reservation_id);
        if (!$stmt->execute()) {
            error_log("Error deleting venues: " . $stmt->error);
            $success = false;
        }

        // Delete all additional requests
        $deleteAllRequestsQuery = "DELETE FROM coed_addedrequest WHERE reservation_id = ?";
        $stmt = $conn->prepare($deleteAllRequestsQuery);
        $stmt->bind_param('i', $reservation_id);
        if (!$stmt->execute()) {
            error_log("Error deleting additional requests: " . $stmt->error);
            $success = false;
        }

        // Delete reservation record if all related records are deleted
        if ($success) {
            $deleteReservationQuery = "DELETE FROM coed_reservation WHERE id = ?";
            $stmt = $conn->prepare($deleteReservationQuery);
            $stmt->bind_param('i', $reservation_id);
            $stmt->execute();
        }
    } else if ($venue_name === 'single' && $request_name === 'single') {
        // Case 2: Delete single records when exactly 1 venue and 1 request exist
        $deleteSingleVenueQuery = "DELETE FROM coed_venue_request WHERE reservation_id = ? LIMIT 1";
        $stmt = $conn->prepare($deleteSingleVenueQuery);
        $stmt->bind_param('i', $reservation_id);
        if (!$stmt->execute()) {
            error_log("Error deleting the single venue: " . $stmt->error);
            $success = false;
        }

        $deleteSingleRequestQuery = "DELETE FROM coed_addedrequest WHERE reservation_id = ? LIMIT 1";
        $stmt = $conn->prepare($deleteSingleRequestQuery);
        $stmt->bind_param('i', $reservation_id);
        if (!$stmt->execute()) {
            error_log("Error deleting the single request: " . $stmt->error);
            $success = false;
        }

        // Delete reservation record if both single records were deleted
        if ($success) {
            $deleteReservationQuery = "DELETE FROM coed_reservation WHERE id = ?";
            $stmt = $conn->prepare($deleteReservationQuery);
            $stmt->bind_param('i', $reservation_id);
            $stmt->execute();
        }
    } else {
        // Case 3: Delete specific venue or request
        // If a specific venue name is provided
        if ($venue_name) {
            $deleteVenueQuery = "DELETE FROM coed_venue_request WHERE reservation_id = ? AND venue_name = ?";
            $stmt = $conn->prepare($deleteVenueQuery);
            $stmt->bind_param('is', $reservation_id, $venue_name);
            $stmt->execute();

            // Check if any rows were affected
            if ($stmt->affected_rows === 0) {
                $success = false; // Venue not found for deletion
                error_log("No matching venue found for deletion: $venue_name");
            }
        }

        // If a specific request name is provided
        if ($request_name) {
            $deleteRequestQuery = "DELETE FROM coed_addedrequest WHERE reservation_id = ? AND additional_request = ?";
            $stmt = $conn->prepare($deleteRequestQuery);
            $stmt->bind_param('is', $reservation_id, $request_name);
            $stmt->execute();

            // Check if any rows were affected
            if ($stmt->affected_rows === 0) {
                $success = false; // Request not found for deletion
                error_log("No matching request found for deletion: $request_name");
            }
        }
    }

    if ($success) {
        echo json_encode(["success" => true, "message" => "Records deleted successfully."]);
    } else {
        echo json_encode(["success" => false, "message" => "No matching records found for deletion."]);
    }
}

$conn->close();
?>
