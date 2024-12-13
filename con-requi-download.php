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
    $requisitionSql = "SELECT * FROM con_requisition WHERE requisition_id = $id";
    $resultRequisition = $conn->query($requisitionSql);

    // Fetch items for the selected requisition ID
    $itemsSql = "SELECT * FROM con_items WHERE requisition_id = $id";
    $resultItems = $conn->query($itemsSql);

    if ($resultRequisition && $resultRequisition->num_rows > 0) {
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
                /* Removed margin-left settings */
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

        <h3>Requisition Form Details</h3>";

        // Output requisition details
        while ($row = $resultRequisition->fetch_assoc()) {
            $html .= "<div class='table-container'>
                <table class='crud-table'>
                    <tr><th>Date</th><td>" . $row["date"] . "</td></tr>
                    <tr><th>Name</th><td>" . $row["name"] . "</td></tr>
                    <tr><th>Position</th><td>" . $row["position"] . "</td></tr>
                    <tr><th>College Name</th><td>" . $row["college_name"] . "</td></tr>
                </table>
            </div>";
        }

        // Add items details
        $html .= "<h3>Items for Requisition</h3>
        <div class='table-container'>
            <table class='crud-table'>
                <thead>
                    <tr>
                        <th>Requisition ID</th>
                        <th>Item Name</th>
                        <th>Total Items Requested</th>
                        <th>Total Usage</th>
                        <th>Utilization %</th>
                    </tr>
                </thead>
                <tbody>";

        if ($resultItems && $resultItems->num_rows > 0) {
            // Group items by requisition_id
            $itemsData = [];
            while ($item = $resultItems->fetch_assoc()) {
                $itemsData[$item['requisition_id']][] = $item;
            }

            // Generate table rows with grouped data
            foreach ($itemsData as $requisitionId => $items) {
                $firstRow = true;
                foreach ($items as $item) {
                    $html .= "<tr>";
                    if ($firstRow) {
                        $html .= "<td rowspan='" . count($items) . "'>" . $requisitionId . "</td>";
                        $firstRow = false;
                    }
                    $html .= "<td>" . htmlspecialchars($item["item_name"]) . "</td>
                            <td>" . htmlspecialchars($item["total_items"]) . "</td>
                            <td>" . htmlspecialchars($item["total_usage"]) . "</td>
                            <td>" . htmlspecialchars($item["utilization_percentage"]) . "%</td>
                        </tr>";
                }
            }
        } else {
            $html .= "<tr><td colspan='5'>No items found for this requisition.</td></tr>";
        }

        $html .= "</tbody></table></div>";

        // Generate the PDF
        ob_end_clean();

        $options = new Options();
        $options->set('defaultFont', 'Poppins');
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