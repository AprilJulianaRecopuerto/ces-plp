<?php
require 'vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Check if an ID is passed via the URL
if (isset($_GET['id'])) {
    $id = (int)$_GET['id']; // Sanitize input

    // Database credentials
    $servername = "mwgmw3rs78pvwk4e.cbetxkdyhwsb.us-east-1.rds.amazonaws.com";
    $username = "dnr20srzjycb99tw";
    $password = "ndfnpz4j74v8t0p7";
    $dbname = "x8uwt594q5jy7a7o";

    // Database connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Fetch reservation details
    $reservationSql = "SELECT * FROM cas_reservation WHERE id = $id";
    $resultReservation = $conn->query($reservationSql);

    // Fetch venue requests for the reservation ID
    $venueSql = "SELECT reservation_id, venue_name FROM cas_venue_request ORDER BY reservation_id DESC";
    $venueResult = $conn->query($venueSql);

    // Fetch added requests for the reservation ID
    $addedRequestSql = "SELECT reservation_id, additional_request, quantity FROM cas_addedrequest ORDER BY reservation_id DESC";
    $addedRequestResult = $conn->query($addedRequestSql);

    if ($resultReservation && $resultReservation->num_rows > 0) {
        // Start building the PDF HTML content
        $html = "
        <link href='https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,400;0,600;1,500&display=swap' rel='stylesheet'>

        <style>
            body {
                font-family: 'Poppins', sans-serif;
                font-size: 14px;
            }

            h3 {
                text-align: left;
            }

            .table-container {
                text-align: center;
                width: 100%;               /* Full width of the parent */
                overflow-x: auto;         /* Allow horizontal scrolling if needed */
                margin: 20px auto;        /* Center the container with space above */
            }

            .crud-table {
                width: 100%;              /* Full width of the container */
                border-collapse: collapse; /* Collapse borders for a cleaner look */
                font-family: 'Poppins', sans-serif;
                background-color: #ffffff;
                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
                border-radius: 10px;
            }

            .crud-table th, .crud-table td {
                border: 1px solid #ddd;
                padding: 10px;
                text-align: left;
            }

            .crud-table th {
                background-color: #4CAF50;
                color: white;
            }

            .crud-table tr:hover {
                background-color: #f1f1f1;
            }
        </style>

        <h3>Reservation Details</h3>";

        // Output reservation details
        while ($row = $resultReservation->fetch_assoc()) {
            $html .= "<div class='table-container'>
                <table class='crud-table'>
                    <tr><th>Name</th><td>" . $row["name"] . "</td></tr>
                    <tr><th>Date of Request</th><td>" . $row["date_of_request"] . "</td></tr>
                    <tr><th>Event Activity</th><td>" . $row["event_activity"] . "</td></tr>
                    <tr><th>Event Date</th><td>" . $row["event_date"] . "</td></tr>
                    <tr><th>Time of Event</th><td>" . $row["time_of_event"] . "</td></tr>
                </table>
            </div>";
        }

        // Process and display merged venue requests
        if ($venueResult && $venueResult->num_rows > 0) {
            $venueData = []; // Array to hold venue data grouped by reservation ID

            // Group venue data by reservation ID
            while ($row = $venueResult->fetch_assoc()) {
                $venueData[$row['reservation_id']][] = $row['venue_name'];
            }

            $html .= "<h3>Venue Requests</h3>
            <div class='table-container'>
                <table class='crud-table'>
                    <thead>
                        <tr>
                            <th>Reservation ID</th>
                            <th>Venue Name</th>
                        </tr>
                    </thead>
                    <tbody>";

            // Display grouped venue requests
            foreach ($venueData as $reservationId => $venues) {
                $html .= "<tr>";
                $html .= "<td rowspan='" . count($venues) . "'>" . htmlspecialchars($reservationId) . "</td>"; // Display Reservation ID once
                
                // Display the first venue request
                $html .= "<td>" . htmlspecialchars($venues[0]) . "</td>";
                $html .= "</tr>";

                // Display remaining venue requests
                for ($i = 1; $i < count($venues); $i++) {
                    $html .= "<tr><td>" . htmlspecialchars($venues[$i]) . "</td></tr>";
                }
            }

            $html .= "</tbody></table></div>";
        } else {
            $html .= "<p>No venue requests found.</p>";
        }

        // Process and display merged additional requests
        if ($addedRequestResult && $addedRequestResult->num_rows > 0) {
            $addedRequestData = []; // Array to hold added request data grouped by reservation ID

            // Group added request data by reservation ID
            while ($row = $addedRequestResult->fetch_assoc()) {
                $addedRequestData[$row['reservation_id']][] = ['additional_request' => $row['additional_request'], 'quantity' => $row['quantity']];
            }

            $html .= "<h3>Additional Requests</h3>
            <div class='table-container'>
                <table class='crud-table'>
                    <thead>
                        <tr>
                            <th>Reservation ID</th>
                            <th>Request Name</th>
                            <th>Quantity</th>
                        </tr>
                    </thead>
                    <tbody>";

            // Display grouped additional requests
            foreach ($addedRequestData as $reservationId => $requests) {
                $html .= "<tr>";
                $html .= "<td rowspan='" . count($requests) . "'>" . htmlspecialchars($reservationId) . "</td>"; // Display Reservation ID once
                
                // Display the first additional request
                $html .= "<td>" . htmlspecialchars($requests[0]['additional_request']) . "</td>";
                $html .= "<td>" . htmlspecialchars($requests[0]['quantity']) . "</td>";
                $html .= "</tr>";

                // Display remaining additional requests
                for ($i = 1; $i < count($requests); $i++) {
                    $html .= "<tr><td>" . htmlspecialchars($requests[$i]['additional_request']) . "</td><td>" . htmlspecialchars($requests[$i]['quantity']) . "</td></tr>";
                }
            }

            $html .= "</tbody></table></div>";
        } else {
            $html .= "<p>No additional requests found.</p>";
        }

        // Generate the PDF
        ob_end_clean();

        $options = new Options();
        $options->set('defaultFont', 'Poppins');
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Stream the PDF to the browser with forced download
        $dompdf->stream("Reservation_$id.pdf", array("Attachment" => 1));
    } else {
        echo "No reservation details found for this ID.";
    }

    $conn->close();
} else {
    echo "No ID provided.";
}
?>
