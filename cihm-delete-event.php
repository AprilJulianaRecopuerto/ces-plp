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

// Get event_form_id and date from the AJAX request
$eventFormId = $_GET['cihm_tor_id'];
$date = isset($_GET['event_date']) ? $_GET['event_date'] : null;
$response = ['success' => false, 'eventCount' => 0];

if (!$date) {
    // Step 1: Check for multiple events
    $countSql = "SELECT COUNT(*) as eventCount FROM cihm_food WHERE cihm_tor_id = ?";
    $stmt = $conn->prepare($countSql);
    $stmt->bind_param('i', $eventFormId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $response['eventCount'] = $result['eventCount'];

    // If there's only one event, prepare to delete it
    if ($response['eventCount'] === 1) {
        // Prepare to delete from cihm_event_details
        $deleteSql = "DELETE FROM cihm_food WHERE cihm_tor_id = ?";
        $stmt = $conn->prepare($deleteSql);
        $stmt->bind_param('i', $eventFormId);
        
        // Execute deletion from cihm_event_details
        $response['success'] = $stmt->execute(); // Check if the deletion was successful
    
        // If successful, delete from cihm_event_form
        if ($response['success']) {
            $deleteFormSql = "DELETE FROM cihm_tor WHERE id = ?";
            $stmt = $conn->prepare($deleteFormSql);
            $stmt->bind_param('i', $eventFormId);
            $response['success'] = $stmt->execute(); // Check if the form deletion was successful
        }
    }    
} else {
    // Step 2: Delete events based on user's choice
    if ($date === 'all') {
        // Delete all events with the same event_form_id
        $deleteSql = "DELETE FROM cihm_food WHERE cihm_tor_id = ?";
        $stmt = $conn->prepare($deleteSql);
        $stmt->bind_param('i', $eventFormId);
    } elseif ($date === 'single') {
        // Delete a single event
        $deleteSql = "DELETE FROM cihm_food WHERE cihm_tor_id = ? LIMIT 1";
        $stmt = $conn->prepare($deleteSql);
        $stmt->bind_param('i', $eventFormId);
    } else {
        // Delete events for a specific date
        $deleteSql = "DELETE FROM cihm_food WHERE cihm_tor_id = ? AND event_date = ?";
        $stmt = $conn->prepare($deleteSql);
        $stmt->bind_param('is', $eventFormId, $date);
    }

    // Execute the deletion of events
    $response['success'] = $stmt->execute();

    // If all events are deleted, delete the corresponding cihm_event_form record
    if ($response['success'] && ($date === 'all' || $date === 'single')) {
        $deleteFormSql = "DELETE FROM cihm_tor WHERE id = ?";
        $stmt = $conn->prepare($deleteFormSql);
        $stmt->bind_param('i', $eventFormId);
        $stmt->execute();
    }
}

echo json_encode($response);
?>
