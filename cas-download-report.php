<?php
// Ensure the composer autoload is included for dependencies (Dompdf)
require 'vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Check if an ID is passed via the URL
if (isset($_GET['id'])) {
    $id = (int)$_GET['id']; // Sanitize input to prevent SQL injection

    // Database connection
    $servername = "mwgmw3rs78pvwk4e.cbetxkdyhwsb.us-east-1.rds.amazonaws.com";
    $username = "dnr20srzjycb99tw";
    $password = "ndfnpz4j74v8t0p7";
    $dbname = "x8uwt594q5jy7a7o";

    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Fetch the event form details for the selected ID
    $eventFormSql = "SELECT * FROM cas_tor WHERE id = $id";
    $resultEventForm = $conn->query($eventFormSql);

    // Fetch event details for the selected ID
    $eventDetailsSql = "SELECT * FROM cas_food WHERE cas_tor_id = $id ORDER BY event_date";
    $resultEventDetails = $conn->query($eventDetailsSql);

    if ($resultEventDetails && $resultEventDetails->num_rows > 0) {
        // Organize the fetched data
        $eventData = [];
        while ($row = $resultEventDetails->fetch_assoc()) {
            $eventData[$row['cas_tor_id']][$row['event_date']][] = $row;
        }

        // Convert image to base64
        $imagePath = __DIR__ . "/images/pasiglogo.png"; // Update path as needed
        if (file_exists($imagePath)) {
            $imageData = file_get_contents($imagePath);
            $base64Image = base64_encode($imageData);
            $base64ImageSrc = 'data:image/png;base64,' . $base64Image;
        } else {
            $base64ImageSrc = ''; // Handle missing image gracefully
        }

        // HTML content for the PDF
        $html = "
        <style>
            body {
                font-family: Arial, sans-serif;
                font-size: 12px;
            }
            h3 {
                text-align: center;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin: 20px 0;
            }
            table, th, td {
                border: 1px solid #ddd;
            }
            th, td {
                padding: 8px;
                text-align: left;
            }
            th {
                background-color: #f2f2f2;
            }
            .logo {
                text-align: center;
                margin-bottom: 20px;
            }
            .logo img {
                width: 150px; /* Adjust width as needed */
            }
        </style>

        <div class='logo'>
            <img src='$base64ImageSrc' alt='Logo'>
        </div>

        <h3>Event Form Details</h3>
        ";

        // Output Event Form details for the selected ID
        if ($resultEventForm && $resultEventForm->num_rows > 0) {
            while ($row = $resultEventForm->fetch_assoc()) {
                $html .= "<table>
                    <tr><th>Department</th><td>" . htmlspecialchars($row["college_name"]) . "</td></tr>
                    <tr><th>Procurement Title</th><td>" . htmlspecialchars($row["procurement_title"]) . "</td></tr>
                    <tr><th>Agency</th><td>" . htmlspecialchars($row["agency"]) . "</td></tr>
                    <tr><th>Date of Delivery</th><td>" . htmlspecialchars($row["date_of_delivery"]) . "</td></tr>
                </table>";
            }
        }

        $html .= "<h3>Event Details</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Event Date</th>
                    <th>Event Title</th>
                    <th>Food Category</th>
                    <th>Menu</th>
                    <th>Total Meal Requested</th>
                    <th>Total Usage of Meal</th>
                    <th>Utilization %</th>
                </tr>
            </thead>
            <tbody>";

        // Loop through the organized event data to output rows with merged cells for the ID and Event Date
        foreach ($eventData as $eventFormId => $dates) {
            $firstEventRow = true; // Track the first row for event_form_id

            foreach ($dates as $eventDate => $rows) {
                $dateRowCount = count($rows); // Number of rows for this event_form_id and event_date
                $firstDateRow = true; // Track the first row for event_date

                foreach ($rows as $row) {
                    $html .= "<tr>";

                    // Display Event Form ID only once per unique event_form_id
                    if ($firstEventRow) {
                        $html .= "<td rowspan='" . $dateRowCount . "'>" . htmlspecialchars($eventFormId) . "</td>";
                        $firstEventRow = false;
                    }

                    // Display Event Date only once per unique event_date
                    if ($firstDateRow) {
                        $html .= "<td rowspan='" . $dateRowCount . "'>" . htmlspecialchars($eventDate) . "</td>";
                        $firstDateRow = false;
                    }

                    // Output remaining columns
                    $html .= "<td>" . htmlspecialchars($row['event_title']) . "</td>
                            <td>" . htmlspecialchars($row['meal_type']) . "</td>
                            <td>" . htmlspecialchars($row['menu']) . "</td>
                            <td>" . htmlspecialchars($row['total_meals']) . "</td>
                            <td>" . htmlspecialchars($row['total_usage']) . "</td>
                            <td>" . htmlspecialchars($row['utilization_percentage']) . '%' . "</td>
                        </tr>";
                }
            }
        }

        $html .= "</tbody></table>";

        // Generate the PDF
        try {
            $options = new Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isPhpEnabled', true);
            $options->set('isImageEnabled', true);

            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            // Stream the PDF to the browser
            $dompdf->stream("TOR_$id.pdf", ["Attachment" => 1]);
        } catch (Exception $e) {
            echo "Error generating PDF: " . $e->getMessage();
        }

    } else {
        echo "No event details found for this ID.";
    }

    $conn->close();
} else {
    echo "No ID provided.";
}
?>
