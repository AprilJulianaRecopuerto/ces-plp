<?php
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader
require 'vendor/autoload.php';

// Check if the user is logged in
if (!isset($_SESSION['uname'])) {
    header("Location: collegelogin.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form inputs
    $project_name = $_POST['project_name'];
    $department = $_POST['department'];
    $due_date = $_POST['due_date'];

    // Directory for uploads
    $target_dir = "taskuploadfiles/";

    // Allowed file types (docx, pdf, xls, xlsx)
    $allowed_types = array("docx", "pdf", "xls", "xlsx");

    // Handling file uploads for each field
    $files = ['letter_request', 'act_plan', 'termof_ref', 'requi_form', 'venue_reserve', 'budget_plan'];
    $file_paths = [];

    foreach ($files as $file) {
        if (isset($_FILES[$file]) && $_FILES[$file]["error"] == 0) {
            $file_full_path = $target_dir . basename($_FILES[$file]["name"]);
            $file_type = strtolower(pathinfo($file_full_path, PATHINFO_EXTENSION));

            // Check file type
            if (in_array($file_type, $allowed_types)) {
                if (move_uploaded_file($_FILES[$file]["tmp_name"], $file_full_path)) {
                    $file_paths[$file] = basename($_FILES[$file]["name"]); // Save only the file name
                } else {
                    $_SESSION['error'] = "There was an error uploading the $file file.";
                    header("Location: cba-addtask.php");
                    exit;
                }
            } else {
                $_SESSION['error'] = "Invalid file type for $file. Only DOCX, PDF, XLS, and XLSX files are allowed.";
                header("Location: cba-addtask.php");
                exit;
            }
        } else {
            $_SESSION['error'] = "No $file file uploaded.";
            header("Location: cba-addtask.php");
            exit;
        }
    }

    // Database connection
    $db_host = "localhost";
    $db_user = "root";
    $db_pass = "";
    $task_dbname = "task_management"; // For task data
    $conn_task = new mysqli($db_host, $db_user, $db_pass, $task_dbname);

    if ($conn_task->connect_error) {
        die("Connection failed: " . $conn_task->connect_error);
    }

    // Insert the form and file data into the task management database
    $sql = "INSERT INTO cba (project_name, department, due_date, letter_request, act_plan, termof_ref, requi_form, venue_reserve, budget_plan) 
            VALUES ('$project_name', '$department', '$due_date', 
            '{$file_paths['letter_request']}', '{$file_paths['act_plan']}', '{$file_paths['termof_ref']}', '{$file_paths['requi_form']}', '{$file_paths['venue_reserve']}', '{$file_paths['budget_plan']}')";

    if ($conn_task->query($sql) === TRUE) {
        $_SESSION['success'] = "New task successfully added!";

        // Now insert the notification after successfully inserting the task
        // Database connection for notifications (mov database)
        $dbname_mov = "mov"; // For notifications
        $conn_mov = new mysqli($db_host, $db_user, $db_pass, $dbname_mov);

        if ($conn_mov->connect_error) {
            die("Connection to 'mov' database failed: " . $conn_mov->connect_error);
        }

        // Prepare the notification message
        $notification_message = "A new task has been submitted by $department for project: $project_name.";

        // Insert notification into the notifications table in 'mov' database
        $notification_sql = "INSERT INTO notifications (project_name, department, notification_message) 
                             VALUES ('$project_name', '$department',  '$notification_message')";

        if ($conn_mov->query($notification_sql) === TRUE) {
            // Now send an email notification to the admin (Head Coordinator)

            // Database connection to fetch admin email (user_registration database)
            $user_dbname = "user_registration"; // For user data
            $conn_users = new mysqli($db_host, $db_user, $db_pass, $user_dbname);

            if ($conn_users->connect_error) {
                die("Connection to 'user_registration' database failed: " . $conn_users->connect_error);
            }

            // Fetch the admin email
            $user_sql = "SELECT email FROM users WHERE roles = 'Head Coordinator' LIMIT 1";
            $result = $conn_users->query($user_sql);

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $recipientEmail = $row['email'];

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
                    $mail->Subject = 'New Task Notification';
                    $mail->Body    = "A new task has been submitted by <strong>$department</strong>.<br>Project Name: $project_name<br>Date of Implementation: $due_date<br><br>Best regards,<br>PLP CES";

                    $mail->send();
                } catch (Exception $e) {
                    $_SESSION['error'] = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                    header("Location: cba-addtask.php");
                    exit;
                }
            } else {
                $_SESSION['error'] = "No admin user found for email notification.";
                header("Location: cba-addtask.php");
                exit;
            }

            // Close the user database connection
            $conn_users->close();
        } else {
            $_SESSION['error'] = "Error inserting notification: " . $conn_mov->error;
            header("Location: cba-addtask.php");
            exit;
        }

        // Close the notifications connection
        $conn_mov->close();

    } else {
        $_SESSION['error'] = "Error saving your task data: " . $conn_task->error;
    }

    $conn_task->close();

    header("Location: cba-addtask.php");
    exit;
}
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
                font-family: 'Poppins', sans-serif;
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
                margin-left: 320px; /* Align with the sidebar */
                padding: 20px;
            }

            .content-task h2 {
                font-family: 'Poppins', sans-serif;
                font-size: 28px; /* Adjust the font size as needed */
                margin-bottom: 20px; /* Space below the heading */
                color: black; /* Adjust text color */
                margin-top: 110px;
            }
            
            .form-container {
                margin-top:110px;
                background-color: #ffffff;
                padding: 20px;
                border-radius: 10px;
                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            }

            .form-container h3 {
                margin-top: 0;
                font-family: 'Poppins', sans-serif;
                font-size: 24px;
                color: black;
            }

            .form-group {
                margin-bottom: 15px;
            }

            .form-group label {
                display: block;
                font-weight: bold;
                margin-bottom: 5px;
            }

            .form-group select, .form-group input[type="text"], .form-group input[type="date"], .form-group input[type="time"] {
                width: 100%;
                padding: 8px;
                border: 1px solid #ddd;
                border-radius: 5px;
                font-size: 16px;
                box-sizing: border-box;
                font-family: 'Poppins', sans-serif;
            }

            .form-group input::placeholder {
                font-family: 'Poppins', sans-serif;
                color: #999;
                font-style: italic;
            }

            .form-group select {
                background: #f9f9f9;
            }

            .button-container {
                display: flex;
                justify-content: flex-end; /* Align buttons to the right */
                margin-top: 20px; /* Space above the buttons */
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

            .custom-swal-popup {
                font-family: 'Poppins', sans-serif;
                width: 400px !important; /* Set a larger width */
            }

            .custom-swal-title {
                font-family: 'Poppins', sans-serif;
                color: #3085d6; /* Custom title color */
            }

            .cutsom-swal-confirm {
                font-family: 'Poppins', sans-serif;
                font-size: 17px;
                background-color: #089451;
                border: 0.5px #089451 !important;
                color: #fff;
                border-radius: 10px;
                cursor: pointer;
                outline: none !important; /* Remove default focus outline */
            }

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
                font-size: 17px;
                background-color: #e74c3c;
                color: #fff;
                border-radius: 10px;
                cursor: pointer;
                outline: none; /* Remove default focus outline */
            }

            .form-text.text-muted {
                font-size: 0.875rem; /* Adjust the font size */
                color: #6c757d; /* Bootstrap's muted color */
                margin-top: 0.25rem; /* Space above the text */
            }

            input[type="file"] {
                font-family: 'Poppins', sans-serif;
                display: block;
                width: 100%;
                height: 38px;
                margin-top: 5px;
                padding: 0;
                border: 1px solid #ced4da;
                border-radius: 4px;
                font-size: 16px;
                color: #495057;
                background-color: #fff;
                background-clip: padding-box;
                transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
            }

            input[type="file"]::file-selector-button {
                font-family: 'Poppins', sans-serif;
                width: 120px;
                padding: 6px 12px;
                margin-right: 10px;
                background-color: #3085d6; /* Custom background color */
                color: white;
                border: 1px solid #3085d6;;
                border-radius: 4px;
                cursor: pointer;
            }

            input[type="file"]::file-selector-button:hover {
                background-color: #2579a8;
            }

            .form-group {
                margin-bottom: 15px;
            }

            .form-text {
                margin-top: 5px;
                font-size: 0.875em;
                color: #6c757d;
            }
            /* Custom style for swal5 */
            .swal5 .swal2-title {
                color: #4CAF50; /* Green title */
                font-family: 'Arial', sans-serif;
                font-size: 20px;
                text-align: center;
            }

            .swal5 .swal2-text {
                color: #555; /* Text color */
                font-family: 'Arial', sans-serif;
                font-size: 16px;
                text-align: center;
            }

            .swal5 .swal2-confirm {
                background-color: #4CAF50; /* Green confirm button */
                border: 2px solid #4CAF50;
                color: white;
                font-weight: bold;
            }

            .swal5 .swal2-cancel {
                background-color: #F44336; /* Red cancel button */
                border: 2px solid #F44336;
                color: white;
                font-weight: bold;
            }

            .swal5 .swal2-popup {
                border-radius: 12px; /* Rounded corners */
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
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
            <h2>Add Task</h2> 
            <div class="profile-container">
                <!-- Chat Icon with Notification Badge -->
                <a href="cba-chat.php" class="chat-icon" onclick="resetNotifications()">
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
                        <a href="cba-your-profile.php">Profile</a>
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
                <li><a href="cba-dash.php" class="active"><img src="images/home.png">Dashboard</a></li>
                <li><a href="cba-projlist.php"><img src="images/project-list.png">Project List</a></li>
                <li><a href="cba-calendar.php"><img src="images/calendar.png">Event Calendar</a></li>

                <!-- Dropdown for Resource Utilization -->
                <button class="dropdown-btn">
                    <img src="images/resource.png"> Resource Utilization
                    <i class="fas fa-chevron-down"></i> <!-- Dropdown icon -->
                </button>
                <div class="dropdown-container">
                    <a href="cba-tor.php">Term of Reference</a>
                    <a href="cba-requi.php">Requisition</a>
                    <a href="cba-venue.php">Venue</a>
                </div>

                <li><a href="cba-budget-utilization.php"><img src="images/budget.png">Budget Allocation</a></li>

                <!-- Dropdown for Task Management -->
                <button class="dropdown-btn">
                    <img src="images/task.png">Task Management
                    <i class="fas fa-chevron-down"></i> <!-- Dropdown icon -->
                </button>
                <div class="dropdown-container">
                    <a href="cba-task.php">Upload Files</a>
                    <a href="cba-mov.php">Mode of Verification</a>
                </div>

                <li><a href="cba-responses.php"><img src="images/feedback.png">Responses</a></li>

                <!-- Dropdown for Audit Trails -->
                <button class="dropdown-btn">
                    <img src="images/logs.png"> Audit Trails
                    <i class="fas fa-chevron-down"></i> <!-- Dropdown icon -->
                </button>
                <div class="dropdown-container">
                    <a href="cba-history.php">Log In History</a>
                    <a href="cba-activitylogs.php">Activity Logs</a>
                </div>
            </ul>
        </div>
    
        <div class="content-task">
            <div class="form-container">
                <h3>Task Details</h3>

                <form action="" method="post" enctype="multipart/form-data"> 
                    <div class="form-group">
                        <label for="project_name">Project Name:</label>
                        <input type="text" id="project_name" name="project_name" placeholder="Enter Project Name" required>
                    </div>

                    <div class="form-group">
                        <label for="department">Department:</label>
                        <input type="text" id="department" name="department" value="College of Business Administration" readonly>
                    </div>

                    <div class="form-group">
                        <label for="due_date">Date of Implemention:</label>
                        <input type="date" id="due_date" name="due_date" placeholder="Enter Due Date" required>
                    </div>

                    <div class="form-group">
                        <label for="letter_request">Request Letter:</label>
                        <input type="file" id="letter_request" name="letter_request" class="file-input" required>
                        <small class="form-text text-muted">File must be a DOCX, PDF, XLS, or XLSX.</small>
                    </div>

                    <div class="form-group">
                        <label for="act_plan">Activity Design:</label>
                        <input type="file" id="act_plan" name="act_plan" class="file-input" required>
                        <small class="form-text text-muted">File must be a DOCX, PDF, XLS, or XLSX.</small>
                    </div>

                    <div class="form-group">
                        <label for="termof_ref">Terms of Reference:</label>
                        <input type="file" id="termof_ref" name="termof_ref" class="file-input" required>
                        <small class="form-text text-muted">File must be a DOCX, PDF, XLS, or XLSX.</small>
                    </div>

                    <div class="form-group">
                        <label for="requi_form">Requistion Form:</label>
                        <input type="file" id="requi_form" name="requi_form" class="file-input" required>
                        <small class="form-text text-muted">File must be a DOCX, PDF, XLS, or XLSX.</small>
                    </div>

                    <div class="form-group">
                        <label for="venue_reserve">Venue Reservation:</label>
                        <input type="file" id="venue_reserve" name="venue_reserve" class="file-input" required>
                        <small class="form-text text-muted">File must be a DOCX, PDF, XLS, or XLSX.</small>
                    </div>

                    <div class="form-group">
                        <label for="budget_plan">Budget Expenditure Plan:</label>
                        <input type="file" id="budget_plan" name="budget_plan" class="file-input" required>
                        <small class="form-text text-muted">File must be a DOCX, PDF, XLS, or XLSX.</small>
                    </div>

                    <div class="button-container">
                        <button type="submit" id="submitButton" onclick="submitForm(event)">Submit</button>
                        <button type="reset">Reset</button>
                    </div>
                </form>
            </div>
        </div>

        <script>

function submitForm(event) {
    event.preventDefault();
    // Show SweetAlert confirmation
    Swal.fire({
        title: 'Are you sure?',
        text: "Do you want to submit the form?",
        icon: 'question',  // You can use 'question' for the question mark icon
        showCancelButton: true,
        confirmButtonColor: '#4CAF50',
        cancelButtonColor: '#F44336',
        confirmButtonText: 'Yes, submit it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Proceed with form submission
            document.getElementById("submitButton").form.submit();  // Submit the form
        }
    });
}



            // Check if there is a success or error message
            <?php if (isset($_SESSION['success'])): ?>
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: '<?php echo $_SESSION['success']; ?>',
                    confirmButtonText: 'OK',
                    customClass: {
                        popup: 'custom-swal-popup',
                        title: 'custom-swal-title',
                        confirmButton: 'custom-swal-confirm'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'cba-task.php';
                    }
                });
            <?php unset($_SESSION['success']); endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: '<?php echo $_SESSION['error']; ?>',
                    confirmButtonText: 'Try Again',
                    customClass: {
                        popup: 'custom-error-popup',
                        title: 'custom-error-title',
                        confirmButton: 'custom-error-confirm'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'cba-addtask.php';
                    }
                });
            <?php unset($_SESSION['error']); endif; ?>

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

            document.addEventListener('DOMContentLoaded', () => {
                // Add change event listeners to all file inputs
                document.getElementById('letter_request').addEventListener('change', validateFile);
                document.getElementById('act_plan').addEventListener('change', validateFile);
                document.getElementById('termof_ref').addEventListener('change', validateFile);
                document.getElementById('requi_form').addEventListener('change', validateFile);
                document.getElementById('venue_reserve').addEventListener('change', validateFile);
                document.getElementById('budget_plan').addEventListener('change', validateFile);
            });

            function validateFile() {
                const file = this.files[0];
                const allowedTypes = /(\.docx|\.pdf|\.xls|\.xlsx)$/i; // Allowed file types

                // Complete names for display in error messages
                const fileNames = {
                    'letter_request': 'LETTER REQUEST',
                    'act_plan': 'ACTIVITY PLAN',
                    'termof_ref': 'TERM OF REFERENCE',
                    'requi_form' : 'REQUISITION FORM',
                    'venue_reserve': 'VENUE RESERVATION',
                    'budget_plan': 'BUDGET EXPENDITURE PLAN'
                };

                if (file) {
                    if (!allowedTypes.test(file.name)) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Invalid File Type',
                            text: `${fileNames[this.id]} must be a DOCX, PDF, XLS, or XLSX file.`,
                            confirmButtonText: 'OK',
                            customClass: {
                                popup: 'custom-error-popup',
                                title: 'custom-error-title',
                                text: 'custom-error-text',
                                confirmButton: 'custom-error-confirm'
                            }
                        });
                        this.value = ''; // Clear the input
                    }
                }
            }

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