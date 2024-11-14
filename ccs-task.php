<?php
session_start(); // Start the session

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader
require 'vendor/autoload.php';

// Check if the user is logged in
if (!isset($_SESSION['uname'])) {
    // Redirect to login page if the session variable is not set
    header("Location: collegelogin.php");
    exit;
}

// Database credentials for the general connection
$servername = "localhost";
$username_db = "root";
$password_db = "";
$dbname = "task_management"; // General database name (used for both 'mov' and 'user_registration')

// Create connection to the main database (task_management)
$conn = new mysqli($servername, $username_db, $password_db, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Database credentials for 'mov' (notifications)
$dbname_mov = "mov"; // For notifications
$conn_mov = new mysqli($servername, $username_db, $password_db, $dbname_mov);

// Check connection for 'mov'
if ($conn_mov->connect_error) {
    die("Connection to 'mov' database failed: " . $conn_mov->connect_error);
}

// Handle deletion if project ID is provided
if (isset($_POST['delete_id'])) {
    $project_id = intval($_POST['delete_id']);

    // Fetch the project name before deleting
    $sql_fetch = "SELECT project_name FROM ccs WHERE id = ?";
    $stmt_fetch = $conn->prepare($sql_fetch);
    $stmt_fetch->bind_param("i", $project_id);
    $stmt_fetch->execute();
    $stmt_fetch->bind_result($project_name);
    $stmt_fetch->fetch();
    $stmt_fetch->close();

    // Proceed with deletion
    $sql = "DELETE FROM ccs WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $project_id);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Data deleted successfully";

        // Insert the notification into the 'mov' database
        $notification_message = "$project_name has been deleted";

        // Prepare and execute the notification insert for 'mov' database
        $notification_sql = "INSERT INTO notifications (project_name, notification_message) VALUES (?, ?)";
        $stmt_notification = $conn_mov->prepare($notification_sql);
        $stmt_notification->bind_param("ss", $project_name, $notification_message);
        $stmt_notification->execute();
        $stmt_notification->close();

        // Now send the email notification to the admin
        // Database connection to fetch admin email (user_registration database)
        $user_dbname = "user_registration"; // For user data
        $conn_users = new mysqli($servername, $username_db, $password_db, $user_dbname);

        if ($conn_users->connect_error) {
            die("Connection to 'user_registration' database failed: " . $conn_users->connect_error);
        }

        // Fetch the admin email
        $user_sql = "SELECT email FROM users WHERE roles = 'Head Coordinator' LIMIT 1";
        $result = $conn_users->query($user_sql);

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $recipientEmail = $row['email'];

            // Send email using PHPMailer
            $mail = new PHPMailer(true);
            try {
                // Server settings
                $mail->isSMTP();                                            // Send using SMTP
                $mail->Host       = 'smtp.gmail.com';                         // Set the SMTP server to send through
                $mail->SMTPAuth   = true;                                     // Enable SMTP authentication
                $mail->Username   = 'communityextensionservices1@gmail.com'; // SMTP username
                $mail->Password   = 'ctpy rvsc tsiv fwix';                    // SMTP password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;          // Enable TLS encryption
                $mail->Port       = 587;                                     // TCP port to connect to

                // Recipients
                $mail->setFrom('communityextensionservices1@gmail.com', 'PLP CES');
                $mail->addAddress($recipientEmail); // Add the admin email fetched from the database

                // Content
                $mail->isHTML(true);                                   // Set email format to HTML
                $mail->Subject = 'Task Deletion Notification';
                $mail->Body    = "The project <strong>$project_name</strong> has been deleted.<br><br>Best regards,<br>PLP CES";

                $mail->send();
                $mail->send();
            } catch (Exception $e) {
                $_SESSION['error'] = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                header("Location: ccs-addtask.php");
                exit;
            }
        } else {
            $_SESSION['error'] = "No admin user found for email notification.";
            header("Location: ccs-addtask.php");
            exit;
        }
    } else {
        $_SESSION['message'] = "Error deleting data";
    }

    $stmt->close();
    header("Location: ccs-task.php"); // Redirect back to project list page
    exit();
}

// Handle the download request
if (isset($_GET['download']) && isset($_GET['name'])) {
    $filePath = $_GET['download'];
    $newFileName = $_GET['name'];

    // Validate file path and existence
    if (file_exists($filePath)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . basename($newFileName) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    } else {
        echo "File not found.";
        exit;
    }
}


// Fetch projects
$sql = "SELECT * FROM ccs";
$result = $conn->query($sql);

$message = isset($_SESSION['message']) ? $_SESSION['message'] : '';
unset($_SESSION['message']); // Clear the message after displaying it

// Database credentials for user_registration (separate connection)
$dbname_user_registration = "user_registration"; 

// Create connection to user_registration database
$conn_user_registration = new mysqli($servername, $username_db, $password_db, $dbname_user_registration);

// Check connection
if ($conn_user_registration->connect_error) {
    die("Connection failed: " . $conn_user_registration->connect_error);
}

// Fetch the department name from user_registration
$username = $_SESSION['uname'];
$sql = "SELECT department FROM colleges WHERE uname = ?";
$stmt = $conn_user_registration->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->bind_result($departmentName);
$stmt->fetch();
$stmt->close();
$conn_user_registration->close();

$departmentName = htmlspecialchars($departmentName);

$conn->close(); // Close the task_management connection
$conn_mov->close(); // Close the 'mov' database connection
?>



<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>CES PLP</title>

        <link rel="icon" href="images/logoicon.png">

        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

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

            .content-task {
                margin-left: 340px; /* Align with the sidebar */
                padding: 20px;
            }

            .content-task h2 {
                font-family: 'Poppins', sans-serif;
                font-size: 28px; /* Adjust the font size as needed */
                margin-bottom: 20px; /* Space below the heading */
                color: black; /* Adjust text color */
                margin-top: 110px;
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

            .table-container {
                width: 100%;
                margin-left: -12px;
                overflow-x: auto;
                margin-top: 20px; /* Space above the table */
            }

            .crud-table {
                width: 100%;
                border-collapse: collapse;
                font-family: 'Poppins', sans-serif;
                background-color: #ffffff;
                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
                overflow: hidden;
            }

            .crud-table th, .crud-table td {
                border: 1px solid #ddd;
                padding: 10px;
                text-align: left;
                white-space: nowrap; /* Prevent text from wrapping */
            }

            .crud-table th {
                text-align: center; 
                background-color: #4CAF50;
                color: white;
                height: 40px;
                width: 14px; /* Set a fixed width for table headers */
            }

            .crud-table td {
                height: 50px;
                background-color: #fafafa;
            }

            .crud-table tr:hover {
                background-color: #f1f1f1;
            }

            .custom-swal-popup {
                font-family: 'Poppins', sans-serif;
                width: 400px !important; /* Set a larger width */
            }

            .custom-swal-title {
                font-family: 'Poppins', sans-serif;
                color: #3085d6; /* Custom title color */
            }

            .custom-swal-confirm {
                font-family: 'Poppins', sans-serif;
                font-size: 17px;
                background-color: #089451;
                border: 0.5px #089451 !important;
                color: #fff;
                border-radius: 10px;
                cursor: pointer;
                outline: none !important; /* Remove default focus outline */
            }

            .custom-swal-cancel {
                font-family: 'Poppins', sans-serif;
                font-size: 17px;
                background-color: #e74c3c;
                color: #fff;
                border-radius: 10px;
                cursor: pointer;
                outline: none; /* Remove default focus outline */
            }

            /* Custom styles for SweetAlert error popup */
            .custom-error-popup {
                font-family: 'Poppins', sans-serif;
                width: 400px !important; /* Set a larger width */
            }

            .custom-error-title {
                font-family: 'Poppins', sans-serif;
                color: #e74c3c; /* Custom title color for error */
            }

            .custom-error-confirm {
                font-family: 'Poppins', sans-serif;
                font-family: 'Glacial Indifference', sans-serif;
                font-size: 17px;
                background-color: #e74c3c;
                color: #fff;
                border-radius: 10px;
                cursor: pointer;
                outline: none; /* Remove default focus outline */
            }

            .edit a {
                font-family: 'Poppins', sans-serif;
                background-color: #4CAF50;
                border: none;
                color: white;
                padding: 10px 20px;
                margin-left: 10px;
                border-radius: 5px;
                font-size: 16px;
                cursor: pointer;
                text-align: center;
                transition: background-color 0.3s;
                text-decoration: none;
            }

            .edit a:hover {
                background-color: #45a049; /* Darker green on hover */
            }

            .delete-project-button {
                font-family: 'Poppins', sans-serif;
                background-color: #e74c3c;
                border: none;
                color: white;
                padding: 10px 20px;
                margin-left: 10px;
                border-radius: 5px;
                font-size: 16px;
                cursor: pointer;
                transition: background-color 0.3s;
            }

            .delete-project-button:hover {
                background-color: #ef233c; /* Darker green on hover */
            }

            .pagination-info {
                font-family: 'Poppins', sans-serif;
                display: flex; 
                justify-content: space-between; 
                margin-top: 10px;"
            }

            .pagination-link {
                font-family: 'Poppins', sans-serif;
                background-color: #4CAF50;
                border: none;
                color: white;
                padding: 10px 20px;
                margin-left: 10px;
                border-radius: 5px;
                font-size: 14px;
                cursor: pointer;
                transition: background-color 0.3s;
                text-decoration: none;
            }

            .pagination-link:hover {
                background-color: #45a049; /* Darker green on hover */
            }

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

        </style>
    </head>

    <body>
    <nav class="navbar">
            <h2>Task - Upload Files </h2> 

            <div class="profile-container">
                <!-- Chat Icon with Notification Badge -->
                <a href="ccs-chat.php" class="chat-icon" onclick="resetNotifications()">
                    <i class="fa fa-comments"></i>
                    <span class="notification-badge" id="chatNotification" style="display: none;">!</span>
                </a>

                <div class="profile" id="profileDropdown">
                    <?php
                        // Check if a profile picture is set in the session
                        if (!empty($_SESSION['picture'])) {
                            // Show the profile picture
                            echo '<img src="' . htmlspecialchars($_SESSION['picture']) . '" alt="Profile Picture">';
                        } else {
                            // Get the first letter of the username for the placeholder
                            $firstLetter = strtoupper(substr($_SESSION['uname'], 0, 1));
                            echo '<div class="profile-placeholder">' . htmlspecialchars($firstLetter) . '</div>';
                        }
                    ?>

                    <span><?php echo htmlspecialchars($_SESSION['uname']); ?></span>

                    <i class="fa fa-chevron-down dropdown-icon"></i>
                    <div class="dropdown-menu">
                        <a href="ccs-your-profile.php">Profile</a>
                        <a class="signout" href="roleaccount.php" onclick="confirmLogout(event)">Sign out</a>
                    </div>
                </div>
            </div>
        </nav>
        
        <div class="sidebar">
            <div class="logo">
                <img src="images/logo.png" alt="Logo">
            </div>

            <ul class="menu">
                <li><a href="ccs-dash.php"><img src="images/home.png">Dashboard</a></li>
                <li><a href="ccs-projlist.php"><img src="images/project-list.png">Project List</a></li>
                <li><a href="ccs-calendar.php"><img src="images/calendar.png">Event Calendar</a></li>

                <!-- Dropdown for Resource Utilization -->
                <button class="dropdown-btn">
                    <img src="images/resource.png"> Resource Utilization
                    <i class="fas fa-chevron-down"></i> <!-- Dropdown icon -->
                </button>
                <div class="dropdown-container">
                    <a href="ccs-tor.php">Term of Reference</a>
                    <a href="ccs-requi.php">Requisition</a>
                    <a href="ccs-venue.php">Venue</a>
                </div>

                <li><a href="ccs-budget-utilization.php"><img src="images/budget.png">Budget Allocation</a></li>

                <!-- Dropdown for Task Management -->
                <button class="dropdown-btn">
                    <img src="images/task.png">Task Management
                    <i class="fas fa-chevron-down"></i> <!-- Dropdown icon -->
                </button>
                <div class="dropdown-container">
                    <a href="ccs-task.php">Upload Files</a>
                    <a href="ccs-mov.php">Mode of Verification</a>
                </div>

                <li><a href="ccs-responses.php"><img src="images/feedback.png">Responses</a></li>

                <!-- Dropdown for Audit Trails -->
                <button class="dropdown-btn">
                    <img src="images/logs.png"> Audit Trails
                    <i class="fas fa-chevron-down"></i> <!-- Dropdown icon -->
                </button>
                <div class="dropdown-container">
                    <a href="ccs-history.php">Log In History</a>
                    <a href="ccs-activitylogs.php">Activity Logs</a>
                </div>
            </ul>
        </div>
        
        <div class="content-task">
            <h2>
                <span style="color: purple;  font-size: 28px;"><?php echo htmlspecialchars($departmentName); ?></span> Task
            </h2>

            <div class="button-container">
                <button onclick="window.location.href='ccs-addtask.php'">Add</button>
            </div>

            <div class="table-container">
                <table class="crud-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Project Name</th>
                            <th>Department</th>
                            <th>Date of Implementation</th>
                            <th>Files</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        // Database credentials
                        $servername = "localhost";
                        $username = "root";
                        $password = "";
                        $dbname = "task_management";

                        // Create connection
                        $conn = new mysqli($servername, $username, $password, $dbname);

                        // Check connection
                        if ($conn->connect_error) {
                            die("Connection failed: " . $conn->connect_error);
                        }

                        // Pagination variables
                        $limit = 5; // Number of records per page
                        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Current page
                        $offset = ($page - 1) * $limit; // Offset for SQL query

                        // Count total records
                        $countSql = "SELECT COUNT(*) as total FROM ccs";
                        $countResult = $conn->query($countSql);
                        $totalRecords = $countResult->fetch_assoc()['total'];
                        $totalPages = ceil($totalRecords / $limit); // Calculate total pages

                        // Fetch projects
                        $sql = "SELECT * FROM ccs";
                        $result = $conn->query($sql);

                        if ($result->num_rows > 0) {
                            // Output data of each row
                            while ($row = $result->fetch_assoc()) {
                                // Fetch multiple files; assuming 'files' column has multiple file names separated by commas
                                $fileNames = [
                                    'Letter Request:' => htmlspecialchars($row['letter_request']),
                                    'Activity Design:' => htmlspecialchars($row['act_plan']),
                                    'Terms of Reference:' => htmlspecialchars($row['termof_ref']),
                                    'Requisition Form:' => htmlspecialchars($row['requi_form']),
                                    'Venue Reservation:' => htmlspecialchars($row['venue_reserve']),
                                    'Budget Expenditure Plan:' => htmlspecialchars($row['budget_plan'])
                                ];
                                
                                // Create links for each file with indication
                                $fileLinks = '';
                                foreach ($fileNames as $label => $fileName) {
                                    if (!empty($fileName)) { // Ensure the file name is not empty
                                        // Get the file extension
                                        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                                        
                                        // Add label and link with space
                                        $fileLinks .= "<div style='margin-bottom: 5px;'><strong>{$label}</strong></div>"; // Display the label
                                        if ($fileExtension === 'pdf') {
                                            // For PDFs, open in a new tab
                                            $fileLinks .= "<div style='margin-bottom: 10px;'><a href='ccs-task-fileviewer.php?file=" . urlencode($fileName) . "' target='_blank'>" . basename($fileName) . "</a></div>";
                                        } else {
                                            // For other file types, use JavaScript to handle downloads
                                            $fileLinks .= "<div style='margin-bottom: 10px;'><a href='ccs-task-fileviewer.php?file=" . urlencode($fileName) . "' class='download-file' data-filename='" . htmlspecialchars($fileName) . "'>" . basename($fileName) . "</a></div>";
                                        }
                                    }
                                }

                                echo "<tr>
                                        <td>" . htmlspecialchars($row["id"]) . "</td>
                                        <td>" . htmlspecialchars($row["project_name"]) . "</td>
                                        <td>" . htmlspecialchars($row["department"]) . "</td>
                                        <td>" . htmlspecialchars($row["due_date"]) . "</td>
                                        <td>
                                            {$fileLinks}
                                        </td>

                                        <td class='edit'>
                                    <a href='ccs-edittask.php?id=" . htmlspecialchars($row["id"]) . "'>EDIT</a>
                                    <button class='delete-project-button' data-id='" . htmlspecialchars($row["id"]) . "'>DELETE</button>
                                </td>
                            </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6'>No records found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

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

        <form id="delete-form" method="post" style="display:none;">
            <input type="hidden" name="delete_id" id="delete_id">
        </form>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                <?php if ($message): ?>
                    Swal.fire({
                        title: 'Success',
                        text: "<?php echo $message; ?>",
                        icon: 'success',
                        confirmButtonColor: '#089451',
                        confirmButtonText: 'OK',
                        customClass: {
                            popup: 'custom-swal-popup'
                        }
                    });
                <?php endif; ?>

            // Attach click event listener to all delete buttons
            document.querySelectorAll('.delete-project-button').forEach(button => {
                button.addEventListener('click', function() {
                    const projectId = this.getAttribute('data-id');
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "You won't be able to restore this!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#e74c3c',
                        cancelButtonColor: '#089451',
                        confirmButtonText: 'Yes, delete it!',
                        customClass: {
                            popup: 'custom-swal-popup',
                            confirmButton: 'custom-swal-confirm',
                            cancelButton: 'custom-swal-cancel'
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            document.getElementById('delete_id').value = projectId;
                            document.getElementById('delete-form').submit();
                        }
                    });
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
                fetch('college-logout.php?action=logout')
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
        </script>
    </body>
</html>