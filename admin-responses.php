<?php
session_start();

$showForm = isset($_SESSION['show_event_form']) && $_SESSION['show_event_form'];
require 'vendor/autoload.php';
use Dompdf\Dompdf;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "certificate";
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch submissions from the database
$sql = "SELECT name, email, event, rate, department FROM submissions";
$result = $conn->query($sql);

// Handle form submission for sending certificates
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_certificates'])) {
    $all_sent = true;
    
    while ($row = $result->fetch_assoc()) {
        $name = $row['name'];
        $email = $row['email'];

        // Generate PDF for each participant
        $date = date("F j, Y");
        $html = "
        <html>
        <head>
            <style>
                body { 
                    text-align: center; 
                    font-family: Arial, sans-serif; 
                    margin: 0; 
                    padding: 0; 
                }
                .certificate {
                    border: 10px solid green; 
                    padding: 20px; 
                    width: 120%; 
                    max-width: 800px; 
                    height: 85%; 
                    box-sizing: border-box; 
                    margin: auto; 
                }
                h1 { 
                    font-size: 48px; 
                    margin-bottom: 0; 
                }
                p { 
                    font-size: 24px; 
                }
                .name { 
                    font-size: 32px; 
                    font-weight: bold; 
                }
            </style>
        </head>
        <body>
            <div class='certificate'>
                <h1>Certificate of Participation</h1>
                <p>This certificate is awarded to</p>
                <p class='name'>" . htmlspecialchars($name) . "</p>
                <p>for their participation in our event.</p>
                <p>Date: $date</p>
            </div>
        </body>
        </html>
        ";

        // Generate PDF
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $pdfFilePath = "certificates/certificate_$name.pdf";
        file_put_contents($pdfFilePath, $dompdf->output());

        // Send Email with the certificate attached
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; 
            $mail->SMTPAuth = true;
            $mail->Username = 'communityextensionservices1@gmail.com'; 
            $mail->Password = 'ctpy rvsc tsiv fwix'; 
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('communityextensionservices1@gmail.com', 'Community Extension Services');
            $mail->addAddress($email);
            $mail->Subject = 'Your Certificate of Participation';
            $mail->Body = 'Attached is your certificate of participation.';
            $mail->addAttachment($pdfFilePath);

            $mail->send();
        } catch (Exception $e) {
            $all_sent = false;
            break; // Stop if an error occurs
        }

        // Remove the PDF file after sending
        unlink($pdfFilePath);
    }

    // Send a response back to the front end
    if ($all_sent) {
        echo 'success';
    } else {
        echo 'error';
    }
    exit;
}

$conn->close();
?>



<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>CES PLP</title>

        <link rel="icon" href="images/icoon.png">

        <!-- SweetAlert CSS and JavaScript -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>  <!-- Include Chart.js -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css">
        

        <style>
            @import url('https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,400;0,600;1,500&display=swap');
            @import url('https://fonts.cdnfonts.com/css/glacial-indifference-2');
            @import url('https://fonts.googleapis.com/css2?family=Saira+Condensed:wght@500&display=swap');

            body {
                margin: 0;
                background-color: #F6F5F5; /* Light gray background color */
            }

            .navbar {
                background-color: #E7F0DC; /* Dirty white color */
                color: black;
                padding: 10px;
                display: flex;
                justify-content: space-between; /* Space between heading and profile */
                align-items: center;
                position: fixed;
                width: calc(96.2% - 270px); /* Adjusted width considering the sidebar */
                height: 80px;
                margin-left: 320px; /* Align with the sidebar */
                border-radius: 10px;
                z-index: 5;
                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2); /* Added box shadow */
            }

            .navbar h2 {
                font-family: "Glacial Indifference", sans-serif;
                margin: 0; /* Remove default margin */
                font-size: 32px; /* Adjust font size if needed */
                color: black; /* Set text color */
                margin-left: 20px;
            }

            .profile {
                position: relative;
                display: flex;
                align-items: center;
                cursor: pointer;
                margin-right: 20px; /* Space from the right edge */
            }

            .profile img, .profile-placeholder {
                width: 50px;
                height: 50px;
                border-radius: 50%;
                margin-right: 10px;
            }

            .profile-placeholder {
                font-family: "Poppins", sans-serif;
                width: 50px; /* Adjust as needed */
                height: 50px;
                border-radius: 50%;
                background-color: #ccc; /* Placeholder background color */
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 24px; /* Adjust text size */
                color: green;
                font-weight: bold;
                margin-right: 20px; /* Space between profile picture and name */
            }

            span {
                font-family: "Poppins", sans-serif;
                font-size: 17px;
                color: black; /* Set text color */
                white-space: nowrap; /* Prevent line breaks */
                overflow: hidden; /* Hide overflow */
                text-overflow: ellipsis; /* Show ellipsis if the text overflows */
                flex-grow: 1; /* Allow the username to take available space */
            }

            .dropdown-icon {
                width:22px !important; /* Adjust size of the down-arrow icon */
                height: 15px !important;
                margin-left: 10px; /* Space between name and icon */
            }

            .dropdown-menu {
                font-family: "Poppins", sans-serif;
                display: none; /* Hidden by default */
                position: absolute;
                width: 198px;
                top: 60px; /* Adjust based on the profile's height */
                right: 0;
                background-color: white;
                border: 1px solid #ccc;
                border-radius: 10px;
                padding: 10px;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                z-index: 1;
            }

            .dropdown-menu a {
                text-decoration: none;
                border-radius: 10px;
                color: black;
                display: block;
                padding: 10px;
            }

            .dropdown-menu a:hover {
                background-color: #218838;
                color: white;
            }

            .sidebar {
                position: fixed;
                top: 0;
                left: 0;
                height: 100%;
                width: 278px;
                background-color: #FFF8A5; /* Light yellow */
                color: black;
                padding: 20px;
                z-index: 1000;
                box-shadow: 2px 0 5px rgba(0, 0, 0, 0.2); /* Added box shadow */
            }

            .logo {
                display: flex;
                align-items: center;
                margin-bottom: 25px; /* Increased margin bottom */
            }

            .logo img {
                height: 80px; /* Increased logo size */
                margin-left: 25px; /* Adjusted margin */
            }

            .logo span {
                font-size: 30px; /* Increased font size */
                margin-left: -15px;
                font-family: 'Glacial Indifference', sans-serif;
                font-weight: bold;
            }

            .menu {
                list-style: none;
                padding: 0;
                margin: 0;
            }

            .menu li {
                margin: 6px; /* Increased margin for spacing between items */
                display: flex;
                align-items: center;
            }

            .menu a {
                color: black;
                text-decoration: none;
                display: flex;
                align-items: center;
                padding: 10.5px; /* Increased padding for better click area */
                border-radius: 5px; /* Increased border-radius for rounded corners */
                width: 94%;
                font-size: 17px; /* Increased font size */
                font-family: 'Poppins', sans-serif;
            }

            .menu a:hover {
                background-color: #22901C;
                transition: 0.3s;
                color: white; /* Ensure the text color is white when hovered */
            }

            /* Style the sidenav links and the dropdown button */
            .menu .dropdown-btn {
                list-style: none;
                padding: 0;
                margin: 0;
                text-decoration: none !important;
                display: flex;
                align-items: center;
                padding: 10.5px; /* Increased padding for better click area */
                border-radius: 5px; /* Increased border-radius for rounded corners */
                width: 100%;
                font-size: 17px; /* Increased font size */
                font-family: 'Poppins', sans-serif;
                background-color: transparent; /* Set background to transparent */
                border: none; /* Remove border */
                cursor: pointer; /* Change cursor to pointer */
            }

            /* On mouse-over */
            .menu .dropdown-btn:hover {
                background-color: #22901C;
                transition: 0.3s;
                color: white;
            }

            .dropdown-btn img {
                height: 30px; /* Increased icon size */
                margin-left: 6px; /* Adjusted space between icon and text */
            }

            /* Dropdown container (hidden by default). Optional: add a lighter background color and some left padding to change the design of the dropdown content */
            .dropdown-container {
                display: none;
                padding-left: 8px;
                margin-top: -2px;
                width: 85%;
                margin-left: 25px;
                margin-bottom: -5px;
            }

            /* Optional: Style the caret down icon */
            .fa-chevron-down {
                float: right;
                margin-left: 15px;
            }
            .menu img {
                height: 30px; /* Increased icon size */
                margin-right: 15px; /* Adjusted space between icon and text */
            }

            .menu li a.active {
                background-color: green; /* Change background color */
                color: white; /* Change text color */
            }

            .content {
                margin-left: 340px;
                padding: 20px;
            }

        .content h2 {
            font-family: 'Poppins', sans-serif;
            font-size: 28px; /* Adjust the font size as needed */
            margin-bottom: 20px; /* Space below the heading */
            color: black; /* Adjust text color */
            margin-top: 110px;
        }   
            .custom-swal-popup {
                font-family: "Poppins", sans-serif !important;
                width: 400px;
            }

            .custom-swal-confirm {
                font-family: "Poppins", sans-serif !important;
            }

            .custom-swal-cancel {
                font-family: "Poppins", sans-serif !important;
            }

            canvas {
                font-family: 'Poppins', sans-serif !important;
               max-height: 330px;
                width: 500px;
                margin: auto;
                display: block;
            }

            .button-container {
                display: flex;
                justify-content: flex-end; /* Align buttons to the right */
                margin-bottom: 20px; /* Space below the buttons */
                margin-right: 20px;
            }

            .button-container button {
                background-color: #4CAF50;
                border: none;
                color: white;
                padding: 10px 20px;
                margin-left: 10px;
                border-radius: 5px;
                font-size: 16px;
                cursor: pointer;
                transition: background-color 0.3s;
                font-family: 'Poppins', sans-serif;
            }

            .button-container button:hover {
                background-color: #45a049; /* Darker green on hover */
            }

            
            .notify-button {
                background-color: #4CAF50;
                border: none;
                color: white;
                padding: 10px 20px;
                margin-left: 10px;
                border-radius: 5px;
                font-size: 14px;
                cursor: pointer;
                transition: background-color 0.3s;
                font-family: 'Poppins', sans-serif;
            }

            .notify-button:hover {
                background-color: #45a049; /* Darker green on hover */  
            }

            .notification { position: relative; }
            .notification-count {
                position: absolute;
                top: -10px;
                right: -10px;
                background: red;
                color: white;
                border-radius: 50%;
                padding: 5px 10px;
                font-size: 12px;
            }

            .notification-container { 
                display: none; 
            }
            
            .notification-container {
                display: none; /* Initially hide the container */
                border: 1px solid #ccc;
                border-radius: 8px; /* Slightly more rounded corners */
                background-color: #fff; /* White background for better contrast */
                position: absolute; /* Position it below the icon */
                top: 50px; /* Adjust this based on your icon size */
                right: 0; /* Align it to the right of the icon */
                padding: 20px; /* Increased padding for a more spacious feel */
                width: 300px; /* Increased width */
                max-height: 300px; /* Set a max height to allow for scrolling */
                overflow-y: auto; /* Enable vertical scrolling */
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2); /* More pronounced shadow for depth */
                z-index: 1000; /* Ensure it's above other elements */
            }

            .notification-icon {
                position: absolute;
                font-size: 18px;
                cursor: pointer;
                color: #333; /* Adjust color as needed */
               
            }

            .notification-container ul {
                list-style-type: none; /* Removes bullets */
                padding: 0; /* Removes padding */
                margin: 0; /* Removes margin */
            }

            .trash-icon {
                margin-left: 90px; /* Align trash icon to the right */
                cursor: pointer; /* Pointer cursor for trash icon */
                color: red; /* Red color for trash icon */
            }
            .table-container {
                width: 100%;
                margin-left: -12px;
                overflow-x: auto;
                margin-top: 50px; /* Space above the table */
            }

            .crud-table {
                width: 100%;
                border-collapse: collapse;
                font-family: 'Poppins', sans-serif;
                background-color: #ffffff;
                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
                border-radius: 10px;
                overflow: hidden;
            }

            .crud-table th, .crud-table td {
                border: 1px solid #ddd;
                padding: 10px;
                text-align: left;
                white-space: nowrap; /* Prevent text from wrapping */
            }

            .crud-table th {
                background-color: #4CAF50;
                color: white;
                height: 40px;
            }

            .crud-table td {
                height: 50px;
                background-color: #fafafa;
            }

            .crud-table tr:hover {
                background-color: #f1f1f1;
            }

            /* Custom styling for SweetAlert popups */
            .popup-success, .popup-error, .popup-info, .popup-open {
                font-family: 'Arial', sans-serif; /* Custom font */
                border-radius: 15px; /* Rounded corners */
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Soft shadow */
            }

            /* Style for confirm button */
            .swal2-confirm {
                padding: 10px 25px; /* Adjust padding */
                font-size: 16px;
                
            }

            /* Custom style for buttons based on type */
            .swal2-confirm:focus {
                outline: none; /* Remove focus outline */
            }

            /* Optional: Customize background colors for different types */
            .popup-success {
                background-color: #d4edda !important;
            }

            .popup-error {
                background-color: #f8d7da !important;
            }

            .popup-info {
                background-color: #d1ecf1 !important;
            }

            .popup-open {
                background-color: #d1ecf1 !important;
            }

        .swal2-popup {
            font-family: Arial, sans-serif;
            border-radius: 8px;
        }

        .swal2-success .swal2-title, .swal2-error .swal2-title {
            font-weight: bold;
        }

        .swal2-success {
            background-color: #f4f7f6;
            color: #4CAF50;
        }

        .swal2-error {
            background-color: #f8d7da;
            color: #721c24;
        }

        #loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            text-align: center;
            color: white;
            font-size: 24px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        #loading-overlay.show {
            display: flex;
        }

      /* Custom Popup */
      .swal-popup {
            font-family: "Poppins", sans-serif !important;
            width: 400px;
        }

        /* SweetAlert confirm button */
        .swal-confirm {
            font-family: "Poppins", sans-serif !important;
        }

        /* SweetAlert cancel button */
        .swal-cancel {
            font-family: "Poppins", sans-serif !important;
        }

        /* Chat styles */
        .navbar .profile-container {
                display: flex;
                align-items: center;
            }

            .chat-icon {
                font-size: 20px;
                color: #333;
                text-decoration: none;
                position: relative; /* To position the badge correctly */
                margin-right: 30px;
                margin-top: 8px;
                margin-left: -37px;
            }

            .notification-badge {
                display: inline-block;
                background-color: red; /* Change this to your preferred color */
                color: white;
                border-radius: 50%;
                width: 20px; /* Width of the badge */
                height: 20px; /* Height of the badge */
                text-align: center;
                font-weight: bold;
                position: absolute; /* Position it relative to the chat icon */
                top: -5px; /* Adjust as needed */
                right: -10px; /* Adjust as needed */
                font-size: 14px; /* Size of the exclamation point */
            }

            .pagination-info {
                font-family: 'Poppins', sans-serif;
                display: flex; 
                justify-content: space-between; 
                align-items: center; 
                margin-top: 10px;"
            }

            .pagination-link {
                font-family: 'Poppins', sans-serif;
                background-color: #4CAF50;
                border: none;
                color: white;
                padding: 10px 20px;
                margin-right:15px !important;
                border-radius: 5px;
                font-size: 14px;
                cursor: pointer;
                transition: background-color 0.3s;
                text-decoration: none;
            }

            .pagination-link:hover {
                background-color: #45a049; /* Darker green on hover */
            }

            .pagination-text {
                margin-right: 18px !important;
            }
        </style>
    </head>

    <body>
        <nav class="navbar">
            <h2>Responses</h2> 

            <div class="profile-container">
                <!-- Chat Icon with Notification Badge -->
                <a href="chat.php" class="chat-icon" onclick="resetNotifications()">
                    <i class="fa fa-comments"></i>
                    <span class="notification-badge" id="chatNotification" style="display: none;">!</span>
                </a>
            <div>

            <div class="profile" id="profileDropdown">
                <?php
                    // Check if a profile picture is set in the session
                    if (!empty($_SESSION['pictures'])) {
                        // Show the profile picture
                        echo '<img src="' . htmlspecialchars($_SESSION['pictures']) . '" alt="Profile Picture">';
                    } else {
                        // Get the first letter of the username for the placeholder
                        $firstLetter = strtoupper(substr($_SESSION['username'], 0, 1));
                        echo '<div class="profile-placeholder">' . htmlspecialchars($firstLetter) . '</div>';
                    }
                ?>

                <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>

                <i class="fa fa-chevron-down dropdown-icon"></i>
                <div class="dropdown-menu">
                    <a href="admin-your-profile.php">Profile</a>
                    <a class="signout" href="roleaccount.php" onclick="confirmLogout(event)">Sign out</a>
                </div>
            </div>
        </nav>
        
        <div class="sidebar">
            <div class="logo">
                <img src="images/logo.png" alt="Logo">
            </div>

            <ul class="menu">
                <li><a href="admin-dash.php"><img src="images/home.png">Dashboard</a></li>
                <li><a href="admin-projlist.php"><img src="images/project-list.png">Project List</a></li>
                <li><a href="admin-calendar.php"><img src="images/calendar.png">Event Calendar</a></li>

                <!-- Dropdown for Resource Utilization -->
                <button class="dropdown-btn">
                    <img src="images/resource.png"> Resource Utilization
                    <i class="fas fa-chevron-down"></i> <!-- Dropdown icon -->
                </button>
                <div class="dropdown-container">
                    <a href="admin-tor.php">Term of Reference</a>
                    <a href="admin-requi.php">Requisition</a>
                    <a href="admin-venue.php">Venue</a>
                </div>

                <li><a href="admin-budget-utilization.php"><img src="images/budget.png">Budget Allocation</a></li>

                <!-- Dropdown for Task Management -->
                <button class="dropdown-btn">
                    <img src="images/task.png">Task Management
                    <i class="fas fa-chevron-down"></i> <!-- Dropdown icon -->
                </button>
                <div class="dropdown-container">
                    <a href="admin-task.php">Upload Files</a>
                    <a href="admin-mov.php">Mode of Verification</a>
                </div>

                <li><a href="admin-responses.php" class="active"><img src="images/feedback.png">Responses</a></li>

                <!-- Dropdown for Audit Trails -->
                <button class="dropdown-btn">
                    <img src="images/logs.png"> Audit Trails
                    <i class="fas fa-chevron-down"></i> <!-- Dropdown icon -->
                </button>
                <div class="dropdown-container">
                    <a href="admin-history.php">Log In History</a>
                    <a href="admin-logs.php">Activity Logs</a>
                </div>
            </ul>
        </div>
    
        <div class="content-projectlist">
            <div class="content">
                <div class="button-container">
                    <form method="POST" action = "">
                        <button type="submit" name="send_certificates" id="sendCertificatesButton">Send Certificates to All Participants</button>
                    </form>

                    <button id="toggle-button" onclick="toggleForm()">Open Event Form</button>
                </div>

            <div class="table-container">
                <table class="crud-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Event</th>
                            <th>Department</th>
                            <th>Rating</th>
                        </tr>
                    </thead>
                    <tbody id="table-body">
                        <?php
                        // Establish a connection to the database
                        $conn = new mysqli("localhost", "root", "", "certificate"); // Replace with actual DB name

                        // Check the connection
                        if ($conn->connect_error) {
                            die("Connection failed: " . $conn->connect_error);
                        }

                        // Pagination variables
                        $limit = 5; // Number of records per page
                        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Current page
                        $offset = ($page - 1) * $limit; // Offset for SQL query

                        // Count total records
                        $countSql = "SELECT COUNT(*) as total FROM submissions"; // Your table name
                        $countResult = $conn->query($countSql);

                        if ($countResult && $countResult->num_rows > 0) {
                            $totalRecords = $countResult->fetch_assoc()['total'];
                        } else {
                            $totalRecords = 0; // Default value to avoid warnings
                        }

                        // Calculate total pages
                        $totalPages = $totalRecords > 0 ? ceil($totalRecords / $limit) : 1; // Ensure at least 1 page

                        // Ensure $page doesn't exceed $totalPages
                        if ($page > $totalPages) {
                            $page = $totalPages;
                        }

                        // Ensure $page isn't less than 1
                        if ($page < 1) {
                            $page = 1;
                        }

                        // Fetch the data for the current page with DESC order
                        $sql = "SELECT name, email, event, department, rate FROM submissions ORDER BY id DESC LIMIT $limit OFFSET $offset"; // Your table name and column to order by
                        $result = $conn->query($sql);

                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>
                                        <td>" . htmlspecialchars($row["name"]) . "</td>
                                        <td>" . htmlspecialchars($row["email"]) . "</td>
                                        <td>" . htmlspecialchars($row["event"]) . "</td>
                                        <td>" . htmlspecialchars($row["department"]) . "</td>
                                        <td>" . htmlspecialchars($row["rate"]) . "</td>
                                    </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6'>No records found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Section: Move it under the table -->
            <div class="pagination-info">
                <div>
                    <p><?php echo "$totalRecords RECORDS FOUND"; ?></p>
                </div>

                <div class="page">
                    <p>
                        <?php if ($page > 1): ?>
                            <a class="pagination-link" href="?page=<?php echo $page - 1; ?>">PREV</a>
                        <?php endif; ?>

                        <span class="pagination-text">Page <?php echo $page; ?> of <?php echo $totalPages; ?></span>

                        <?php if ($page < $totalPages): ?>
                            <a class="pagination-link" href="?page=<?php echo $page + 1; ?>">NEXT</a>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>

            <script>         
            // Display loading SweetAlert
            $(document).ready(function() {
                $('form').submit(function (e) {
                    e.preventDefault(); // Prevent the form from submitting normally

                    // Show loading SweetAlert
                    Swal.fire({
                        title: 'Sending Certificates...',
                        text: 'Please wait while certificates are being sent.',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading(),
                        width: '500px'
                    });

                    // Send AJAX request to trigger PHP functionality
                    $.ajax({
                        url: '', // The same page, so leave the URL empty
                        type: 'POST',
                        data: { send_certificates: true },
                        success: function(response) {
                            if (response === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success!',
                                    text: 'Certificates sent successfully.',
                                    confirmButtonColor: '#28a745',
                                    width: '500px'
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error!',
                                    text: 'Something went wrong while sending certificates.',
                                    confirmButtonColor: '#dc3545',
                                    width: '500px'
                                });
                            }
                        },
                        error: function(xhr, status, error) {
                            // Error: show error alert
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: 'Something went wrong while sending certificates.',
                                confirmButtonColor: '#dc3545',
                                width: '500px'
                            });
                        }
                    });
                });
            });


            function confirmLogout(event) {
                event.preventDefault();
                Swal.fire({
                    title: 'Are you sure?',
                    text: "Do you really want to log out?",
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6', // Green confirm button
                    cancelButtonColor: '#dc3545', // Red cancel button
                    confirmButtonText: 'Yes, log me out',
                    cancelButtonText: 'Cancel',
                    customClass: {
                        popup: 'swal-popup',
                        confirmButton: 'swal-confirm',
                        cancelButton: 'swal-cancel'
                    },
                    // Additional custom styles via CSS can be added here
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Pass action in the fetch call
                        fetch('logout.php?action=logout')
                            .then(response => response.text())
                            .then(data => {
                                console.log(data); // Log response for debugging
                                window.location.href = 'roleaccount.php';
                            })
                            .catch(error => console.error('Error:', error));
                    }
                });
            }

            document.getElementById('profileDropdown').addEventListener('click', function() {
            var dropdownMenu = document.querySelector('.dropdown-menu');
            dropdownMenu.style.display = dropdownMenu.style.display === 'block' ? 'none' : 'block';
            });

            // Optional: Close the dropdown if clicking outside the profile area
            window.onclick = function(event) {
                if (!event.target.closest('#profileDropdown')) {
                    var dropdownMenu = document.querySelector('.dropdown-menu');
                    if (dropdownMenu.style.display === 'block') {
                        dropdownMenu.style.display = 'none';
                    }
                }
            };

            var dropdowns = document.getElementsByClassName("dropdown-btn");

            for (let i = 0; i < dropdowns.length; i++) {
                dropdowns[i].addEventListener("click", function () {
                    // Close all dropdowns first
                    let dropdownContents = document.getElementsByClassName("dropdown-container");
                    for (let j = 0; j < dropdownContents.length; j++) {
                        dropdownContents[j].style.display = "none";
                    }

                    // Toggle the clicked dropdown's visibility
                    let dropdownContent = this.nextElementSibling;
                    if (dropdownContent.style.display === "block") {
                        dropdownContent.style.display = "none";
                    } else {
                        dropdownContent.style.display = "block";
                    }
                });
            }

            document.addEventListener('DOMContentLoaded', function () {
                const username = "<?php echo $_SESSION['username']; ?>"; // Get the username from PHP session

                // Function to log activity
                function logActivity(buttonName) {
                    const timestamp = new Date().toISOString(); // Get current timestamp

                    fetch('log_activity.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            buttonFunction: buttonName // Updated to match the PHP variable
                        }),
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'error') {
                            console.error('Error logging activity:', data.message);
                        } else {
                            console.log('Activity logged successfully:', data);
                        }
                    })
                    .catch(error => {
                        console.error('Error logging activity:', error);
                    });
                }

                // Add event listeners specifically to buttons and links
                const trackableElements = document.querySelectorAll('button, a'); // Select all buttons and links
                trackableElements.forEach(element => {
                    element.addEventListener('click', function (event) {
                        const buttonName = this.tagName === 'BUTTON' ? this.innerText.trim() || "Unnamed Button" : this.textContent.trim() || "Unnamed Link";
                        logActivity(buttonName); // Log the button/link activity
                    });
                });
            });

            document.addEventListener('DOMContentLoaded', function() {
            var toggleButton = document.getElementById("toggle-button");

            // Preserve the button text based on initial state from PHP
            var isFormOpen = <?php echo json_encode($showForm); ?>;
            toggleButton.innerText = isFormOpen ? "Close Event Form" : "Open Event Form";

            toggleButton.addEventListener("click", function() {
                // Send AJAX request to update session variable
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "toggle-form-session.php", true); // Send to PHP script
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        // Toggle the button text based on current state
                        isFormOpen = !isFormOpen; // Update the local state
                        toggleButton.innerText = isFormOpen ? "Close Event Form" : "Open Event Form";

                        // Show SweetAlert feedback to the user
                        Swal.fire({
                            icon: 'success',
                            title: 'Form Visibility Toggled',
                            text: 'The event form visibility has been successfully toggled.',
                            confirmButtonColor: '#3085d6'
                        });
                    }
                };

                xhr.send("toggleForm=true"); // Data to send
            });
        });
      </script>
    </body>
</html>
