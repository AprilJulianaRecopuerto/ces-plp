<?php
require 'vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Check if an ID is passed via the URL
if (isset($_GET['id'])) {
    $id = (int)$_GET['id']; // Sanitize input

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
    $eventFormSql = "SELECT * FROM cba_tor WHERE id = $id";
    $resultEventForm = $conn->query($eventFormSql);

    // Fetch event details for the selected ID
    $eventDetailsSql = "SELECT * FROM cba_food WHERE cba_tor_id = $id ORDER BY event_date";
    $resultEventDetails = $conn->query($eventDetailsSql);

    if ($resultEventDetails && $resultEventDetails->num_rows > 0) {
        // Organize the fetched data
        $eventData = [];
        while ($row = $resultEventDetails->fetch_assoc()) {
            $eventData[$row['cba_tor_id']][$row['event_date']][] = $row;
        }

        // HTML content for the PDF
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
                /* Removed margin-left settings */
            }

            .crud-table1 {
                width: 50%;              /* Full width of the container */
                border-collapse: collapse; /* Collapse borders for a cleaner look */
                font-family: 'Poppins', sans-serif;
                background-color: #ffffff;
                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
                border-radius: 10px;
            }

            .crud-table {
                width: 50%;              /* Full width of the container */
                border-collapse: collapse; /* Collapse borders for a cleaner look */
                font-family: 'Poppins', sans-serif;
                background-color: #ffffff;
                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
                border-radius: 10px;
            }

            .crud-table th, .crud-table td {
                text-align: center;
                border: 1px solid #ddd;
                padding: 10px;
                text-align: left;
                white-space: nowrap; /* Prevent text from wrapping */
            }

            .crud-table th {
                text-align: center;
                background-color: #4CAF50;
                color: white;
                height: 30px;
            }

            .crud-table td {
                text-align: center;
                height: 50px;
                background-color: #fafafa;
            }

            .crud-table tr:hover {
                background-color: #f1f1f1;
            }
        </style>

        <h3>Event Form Details</h3>
        ";

        // Output Event Form details for the selected ID
        if ($resultEventForm && $resultEventForm->num_rows > 0) {
            while ($row = $resultEventForm->fetch_assoc()) {
                $html .= "<div class='table-container'>
                <table class='crud-table1'>
                    <tr><th>Department</th><td>" . $row["college_name"] . "</td></tr>
                    <tr><th>Procurement Title</th><td>" . $row["procurement_title"] . "</td></tr>
                    <tr><th>Agency</th><td>" . $row["agency"] . "</td></tr>
                    <tr><th>Date of Delivery</th><td>" . $row["date_of_delivery"] . "</td></tr>
                </table>
            </div>";
            }
        }

        $html .= "<h3>Event Details</h3>
        <div class='table-container'>
            <table class='crud-table'>
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
                        $html .= "<td rowspan='" . array_sum(array_map("count", $dates)) . "'>" . $eventFormId . "</td>";
                        $firstEventRow = false;
                    }

                    // Display Event Date only once per unique event_date
                    if ($firstDateRow) {
                        $html .= "<td rowspan='" . $dateRowCount . "'>" . $eventDate . "</td>";
                        $firstDateRow = false;
                    }

                    // Output remaining columns
                    $html .= "<td>" . $row['event_title'] . "</td>
                            <td>" . $row['meal_type'] . "</td>
                            <td>" . $row['menu'] . "</td>
                            <td>" . $row['total_meals'] . "</td>
                            <td>" . $row['total_usage'] . "</td>
                            <td>" . $row['utilization_percentage'] . '%' . "</td>
                        </tr>";
                }
            }
        }

        $html .= "</tbody></table>";

        // Generate the PDF
        ob_end_clean(); // Clean output buffer to prevent any unwanted output

        $options = new Options();
        $options->set('defaultFont', 'Poppins');
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isImageEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Stream the PDF to the browser with forced download
        $dompdf->stream("TOR_$id.pdf", array("Attachment" => 1));

    } else {
        echo "No event details found for this ID.";
    }

    $conn->close();
} else {
    echo "No ID provided.";
}
?>