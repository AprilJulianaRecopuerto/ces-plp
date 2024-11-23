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

    // Fetch requisition form details for the selected ID
    $requisitionSql = "SELECT * FROM cas_requisition WHERE requisition_id = $id";
    $resultRequisition = $conn->query($requisitionSql);

    // Fetch items for the selected requisition ID
    $itemsSql = "SELECT * FROM cas_items WHERE requisition_id = $id";
    $resultItems = $conn->query($itemsSql);

    if ($resultRequisition && $resultRequisition->num_rows > 0) {
        // Start building the PDF HTML content
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
        </style>

        <h3>Requisition Form Details</h3>";

        // Output requisition details
        while ($row = $resultRequisition->fetch_assoc()) {
            $html .= "<table>
                <tr><th>ID</th><td>" . $row["requisition_id"] . "</td></tr>
                <tr><th>Date</th><td>" . $row["date"] . "</td></tr>
                <tr><th>Name</th><td>" . $row["name"] . "</td></tr>
                <tr><th>Position</th><td>" . $row["position"] . "</td></tr>
                <tr><th>College Name</th><td>" . $row["college_name"] . "</td></tr>
            </table>";
        }

        // Add items details
        $html .= "<h3>Items for Requisition</h3>
        <table>
            <thead>
                <tr>
                    <th>Item Name</th>
                    <th>Total Items Requested</th>
                    <th>Total Usage</th>
                    <th>Utilization %</th>
                </tr>
            </thead>
            <tbody>";

        if ($resultItems && $resultItems->num_rows > 0) {
            while ($item = $resultItems->fetch_assoc()) {
                $html .= "<tr>
                    <td>" . htmlspecialchars($item["item_name"]) . "</td>
                    <td>" . htmlspecialchars($item["total_items"]) . "</td>
                    <td>" . htmlspecialchars($item["total_usage"]) . "</td>
                    <td>" . htmlspecialchars($item["utilization_percentage"]) . "%</td>
                </tr>";
            }
        } else {
            $html .= "<tr><td colspan='4'>No items found for this requisition.</td></tr>";
        }

        $html .= "</tbody></table>";

        // Generate the PDF
        ob_end_clean();

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isImageEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Stream the PDF to the browser with forced download
        $dompdf->stream("Requisition_$id.pdf", array("Attachment" => 1));
    } else {
        echo "No requisition details found for this ID.";
    }

    $conn->close();
} else {
    echo "No ID provided.";
}
?>
