<?php
session_start(); // Add this at the beginning

// Check if the user is logged in
if (!isset($_SESSION['uname'])) {
    // Redirect to login page if the session variable is not set
    header("Location: loginpage.php");
    exit;
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader
require 'vendor/autoload.php';
// Database credentials
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "proj_list";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$dbname_mov = "mov"; // For notifications
$conn_mov = new mysqli($servername, $username, $password, $dbname_mov);

// Check connection for mov database
if ($conn_mov->connect_error) {
    die("Connection to 'mov' database failed: " . $conn_mov->connect_error);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the submitted data
    $projectId = intval($_POST['id']);
    
    // Retrieve form data
    $date_of_sub = $_POST['date_of_sub'];
    $semester = $_POST['semester'];
    $lead_person = $_POST['lead_person'];
    $dept = $_POST['dept'];
    $implementor = $_POST['implementor'];
    $proj_title = $_POST['proj_title'];
    $classification = $_POST['classification'];
    $specific_activity = $_POST['specific_activity'];
    $dateof_imple = $_POST['dateof_imple'];
    $time_from = $_POST['time_from'];
    $time_to = $_POST['time_to'];
    $district = $_POST['district'];
    $barangay = $_POST['barangay'];
    $beneficiary = $_POST['beneficiary'];
    $duration = $_POST['duration'];
    $status = $_POST['status'];

    // Prepare and bind to retrieve the current project data
    $stmt = $conn->prepare("SELECT * FROM ccs WHERE id = ?");
    $stmt->bind_param("i", $projectId);
    $stmt->execute();
    $result = $stmt->get_result();
    $currentProject = $result->fetch_assoc();

    // Check for changes
    if ($currentProject && (
        $currentProject['date_of_sub'] === $date_of_sub &&
        $currentProject['semester'] === $semester &&
        $currentProject['lead_person'] === $lead_person &&
        $currentProject['dept'] === $dept &&
        $currentProject['implementor'] === $implementor &&
        $currentProject['proj_title'] === $proj_title &&
        $currentProject['classification'] === $classification &&
        $currentProject['specific_activity'] === $specific_activity &&
        $currentProject['dateof_imple'] === $dateof_imple &&
        $currentProject['time_from'] === $time_from &&
        $currentProject['time_to'] === $time_to &&
        $currentProject['district'] === $district &&
        $currentProject['barangay'] === $barangay &&
        $currentProject['beneficiary'] === $beneficiary &&
        $currentProject['duration'] === $duration &&
        $currentProject['status'] === $status
    )) {
        // No changes made
        $_SESSION['warning'] = 'No changes were made in this form.';
        header("Location: ccs-editproj.php?id=" . $projectId); // Redirect back with project ID
        exit();
    }

    // Prepare and bind the update statement
    $stmt = $conn->prepare("UPDATE ccs SET 
        date_of_sub=?, 
        semester=?, 
        lead_person=?, 
        dept=?, 
        implementor=?, 
        proj_title=?, 
        classification=?, 
        specific_activity=?, 
        dateof_imple=?, 
        time_from=?, 
        time_to=?, 
        district=?, 
        barangay=?, 
        beneficiary=?, 
        duration=?, 
        status=? 
    WHERE id=?");

    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }

    // Adjust the type definition string to include all parameters
    $stmt->bind_param('ssssssssssssssssi', $date_of_sub, $semester, $lead_person, $dept, $implementor, $proj_title, $classification, $specific_activity, $dateof_imple, $time_from, $time_to, $district, $barangay, $beneficiary, $duration, $status, $projectId);

    if ($stmt->execute()) {
        $_SESSION['success'] = 'Project updated successfully.';

        // Prepare the notification message
        $notification_message = "Project updated: ";
        if ($date_of_sub !== $currentProject['date_of_sub']) {
            $notification_message .= "Date of Submission changed from '{$currentProject['date_of_sub']}' to '$date_of_sub'. ";
        }
        
        if ($semester !== $currentProject['semester']) {
            $notification_message .= "Semester changed from '{$currentProject['semester']}' to '$semester'. ";
        }
        
        if ($lead_person !== $currentProject['lead_person']) {
            $notification_message .= "Lead Person changed from '{$currentProject['lead_person']}' to '$lead_person'. ";
        }
        
        if ($dept !== $currentProject['dept']) {
            $notification_message .= "Department changed from '{$currentProject['dept']}' to '$dept'. ";
        }
        
        if ($implementor !== $currentProject['implementor']) {
            $notification_message .= "Implementor changed from '{$currentProject['implementor']}' to '$implementor'. ";
        }
        
        if ($proj_title !== $currentProject['proj_title']) {
            $notification_message .= "Project Title changed from '{$currentProject['proj_title']}' to '$proj_title'. ";
        }
        
        if ($classification !== $currentProject['classification']) {
            $notification_message .= "Classification changed from '{$currentProject['classification']}' to '$classification'. ";
        }
        
        if ($specific_activity !== $currentProject['specific_activity']) {
            $notification_message .= "Specific Activity changed from '{$currentProject['specific_activity']}' to '$specific_activity'. ";
        }
        
        if ($dateof_imple !== $currentProject['dateof_imple']) {
            $notification_message .= "Date of Implementation changed from '{$currentProject['dateof_imple']}' to '$dateof_imple'. ";
        }
        
        if ($time_from !== $currentProject['time_from']) {
            $notification_message .= "Time From changed from '{$currentProject['time_from']}' to '$time_from'. ";
        }
        
        if ($time_to !== $currentProject['time_to']) {
            $notification_message .= "Time To changed from '{$currentProject['time_to']}' to '$time_to'. ";
        }
        
        if ($district !== $currentProject['district']) {
            $notification_message .= "District changed from '{$currentProject['district']}' to '$district'. ";
        }
        
        if ($barangay !== $currentProject['barangay']) {
            $notification_message .= "Barangay changed from '{$currentProject['barangay']}' to '$barangay'. ";
        }
        
        if ($beneficiary !== $currentProject['beneficiary']) {
            $notification_message .= "Beneficiary changed from '{$currentProject['beneficiary']}' to '$beneficiary'. ";
        }
        
        if ($duration !== $currentProject['duration']) {
            $notification_message .= "Duration changed from '{$currentProject['duration']}' to '$duration'. ";
        }
        
        if ($status !== $currentProject['status']) {
            $notification_message .= "Status changed from '{$currentProject['status']}' to '$status'. ";
        }
        

        // Insert notification into 'notifications' table
        $stmt_notification = $conn_mov->prepare("INSERT INTO notifications (project_name, department, notification_message) VALUES (?, ?, ?)");
        $stmt_notification->bind_param('sss', $proj_title, $dept, $notification_message);

        if ($stmt_notification === false) {
            die("Error preparing statement: " . $conn_mov->error);
        }

        // Execute the notification insertion
        if ($stmt_notification->execute()) {
            $_SESSION['success'] .= ' Notification added.';
        } else {
            $_SESSION['error'] = 'Failed to add notification: ' . $stmt_notification->error;
        }
        
        // Database connection to fetch admin email (user_registration database)
        $user_dbname = "user_registration"; // For user data
        $conn_users = new mysqli($servername, $username, $password, $user_dbname);

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
                $mail->Subject = 'New Task Notification';
                $mail->Body    = "A Project has been edited by <strong>$dept</strong>.<br>Project Name: $proj_title<br>Date of Submission: $date_of_sub<br><br>Best regards,<br>PLP CES";

                $mail->send();
            } catch (Exception $e) {
                $_SESSION['error'] = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                header("Location: ccs-addproj.php");
                exit;
            }
        } else {
            $_SESSION['error'] = "No admin user found for email notification.";
            header("Location: ccs-addproj.php");
            exit;
        }

        // Redirect with project ID
        header("Location: ccs-editproj.php?id=" . $projectId);
        exit();
    } else {
        $_SESSION['error'] = 'Failed to update project: ' . $stmt->error;
        header("Location: ccs-editproj.php?id=" . $projectId); // Redirect with project ID
        exit();
    }

    $stmt->close();
}

// Get project ID from URL
if (isset($_GET['id'])) {
    $projectId = intval($_GET['id']);

    // Prepare and bind
    $stmt = $conn->prepare("SELECT * FROM ccs WHERE id = ?");

    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("i", $projectId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $project = $result->fetch_assoc();
    } else {
        $_SESSION['error'] = 'Project not found.';
        header("Location: ccs-projlist.php"); // Redirect to project list if no ID is found
        exit();
    }
} else {
    $_SESSION['error'] = 'No project ID specified.';
    header("Location: ccs-projlist.php"); // Redirect to project list if no ID is specified
    exit();
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>CES PLP</title>

        <link rel="icon" href="images/logoicon.png">

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
        
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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

            .content-editor{
                margin-left: 320px; /* Align with the sidebar */
                padding: 20px;
            }

            .content-editor h2 {
                font-family: 'Poppins', sans-serif;
                font-size: 28px; /* Adjust the font size as needed */
                margin-bottom: 20px; /* Space below the heading */
                color: black; /* Adjust text color */
                margin-top: 110px;
            }

            .form-container {
                font-family: 'Glacial Indifference', sans-serif;
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

            .button-container {
                display: flex;
                justify-content: flex-end;
                margin-top: 20px;
            }

            .button-container button {
                font-family: 'Poppins', sans-serif;
                background-color: #22901C;
                color: white;
                border: none;
                padding: 10px 20px;
                border-radius: 5px;
                font-size: 16px;
                cursor: pointer;
                transition: background-color 0.3s;
            }

            .button-container button:hover {
                background-color: #1b7a0f;
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
                font-size: 17px;
                background-color: #e74c3c;
                color: #fff;
                border-radius: 10px;
                cursor: pointer;
                outline: none; /* Remove default focus outline */
            }

            /* Custom styles for SweetAlert error popup */
            .custom-warning-popup {
                font-family: 'Poppins', sans-serif;
                width: 400px !important; /* Set a larger width */
            }

            .custom-warning-title {
                font-family: 'Poppins', sans-serif;
                color: #e74c3c; /* Custom title color for error */
            }

            .custom-warning-confirm {
                font-family: 'Poppins', sans-serif;
                font-size: 17px;
                background-color: #089451;
                color: #fff;
                border-radius: 10px;
                cursor: pointer;
                outline: none; /* Remove default focus outline */
            }

            .custom-warning-cancel {
                font-family: 'Poppins', sans-serif;
                font-size: 17px;
                background-color: #e74c3c;
                color: #fff;
                border-radius: 10px;
                cursor: pointer;
                outline: none; /* Remove default focus outline */
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
        </style>
    </head>

    <body>
        <nav class="navbar">
            <h2>Edit Project</h2> 

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
        </nav>
        
        <div class="sidebar">
            <div class="logo">
                <img src="images/logo.png" alt="Logo">
            </div>

            <ul class="menu">
                <li><a href="ccs-dash.php" class="active"><img src="images/home.png">Dashboard</a></li>
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

        <div class="content-editor">
            <div class="form-container">
                <h3>Edit Project</h3>

                <form action="ccs-editproj.php" method="POST">

                    <div class="form-group">
                        <label for="id">Project ID:</label>
                        <input type="text" name="id" id="id" value="<?php echo htmlspecialchars($project['id']); ?>" readonly>
                    </div>

                    <div class="form-group">
                        <label for="date_of_sub">Date of Submission:</label>
                        <input type="date" name="date_of_sub" id="date_of_sub" 
                        value="<?php echo isset($project['date_of_sub']) ? htmlspecialchars($project['date_of_sub']) : ''; ?>" 
                            required>
                    </div>

                    <div class="form-group">
                        <label for="semester">Semester:</label>
                        <select name="semester" id="semester" required>
                            <option value="1st Semester" <?php echo $project['semester'] == '1st Semester' ? 'selected' : ''; ?>>1st Semester</option>
                            <option value="2nd Semester" <?php echo $project['semester'] == '2nd Semester' ? 'selected' : ''; ?>>2nd Semester</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="lead_person">Lead Person:</label>
                        <input type="text" name="lead_person" value="<?php echo htmlspecialchars($project['lead_person']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="dept">Department:</label>
                        <input type="text" id="dept" name="dept" value="<?php echo htmlspecialchars($project['dept'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="implementor">Implementor:</label>
                        <select id="implementor" name="implementor" required>
                            <option value="Students" <?php echo $project['implementor'] == 'Students' ? 'selected' : ''; ?>>Students</option>
                            <option value="Faculty" <?php echo $project['implementor'] == 'Faculty' ? 'selected' : ''; ?>>Faculty</option>
                            <option value="Faculty and Students" <?php echo $project['implementor'] == 'Faculty and Students' ? 'selected' : ''; ?>>Faculty and Students</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="proj_title">Project Title:</label>
                        <input type="text" name="proj_title" value="<?php echo htmlspecialchars($project['proj_title']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="classification">Classification:</label>
                        <input type="text" name="classification" value="<?php echo htmlspecialchars($project['classification']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="specific_activity">Specific Activity:</label>
                        <input type="text" name="specific_activity" value="<?php echo htmlspecialchars($project['specific_activity']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="dateof_imple">Date of Implementation:</label>
                        <input type="date" name="dateof_imple" value="<?php echo htmlspecialchars($project['dateof_imple']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="time_from">Time From:</label>
                        <input type="text" name="time_from" value="<?php echo htmlspecialchars($project['time_from']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="time_to">Time To:</label>
                        <input type="text" name="time_to" value="<?php echo htmlspecialchars($project['time_to']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="district">District:</label>
                        <select name="district" id="district" required>
                            <option value="District 1" <?php echo $project['district'] == 'District 1' ? 'selected' : ''; ?>>District 1</option>
                            <option value="District 2" <?php echo $project['district'] == 'District 2' ? 'selected' : ''; ?>>District 2</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="barangay">Barangay:</label>
                        <select id="barangay" name="barangay" required>
                            
                            <?php foreach ($barangay as $barangay): ?>
                                <option value="<?php echo htmlspecialchars($barangay['id']); ?>" <?php echo ($barangay['id'] == $project['barangay']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($barangay['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="beneficiary">Beneficiary:</label>
                        <input type="text" name="beneficiary" value="<?php echo htmlspecialchars($project['beneficiary']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="duration">Duration of Project:</label>
                        <select id="duration" name="duration" required>
                            <option value="" disabled>Select Duration</option>
                            <option value="One Day" <?php echo $project['duration'] == 'One Day' ? 'selected' : ''; ?>>One Day</option>
                            <option value="Sustained" <?php echo $project['duration'] == 'Sustained' ? 'selected' : ''; ?>>Sustained</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="status">Status:</label>
                        <input type="text" name="status" value="<?php echo htmlspecialchars($project['status']); ?>" required>
                    </div>

                    <div class="button-container">
                        <button type="submit">Update Project</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            // Function to update barangays based on the selected district
            function updateBarangays(selectedBarangay = '') {
                const district = document.getElementById('district').value;
                const barangaySelect = document.getElementById('barangay');

                // Clear existing options
                barangaySelect.innerHTML = '';

                let barangays = [];

                if (district === 'District 1') {
                    barangays = [
                        'Bagong Ilog', 'Bagong Katipunan', 'Bambang', 'Buting', 'Caniogan',
                        'Kalawaan', 'Kapasigan', 'Kapitolyo', 'Malinao', 'Oranbo',
                        'Palatiw', 'Pineda', 'Sagad', 'San Antonio', 'San Joaquin',
                        'San Jose', 'San Nicolas', 'Sta. Cruz', 'Sta. Rosa', 'Sto. Tomas',
                        'Sumilang', 'Ugong'
                    ];
                } else if (district === 'District 2') {
                    barangays = [
                        'Dela Paz', 'Manggahan', 'Maybunga', 'Pinagbuhatan', 'Rosario',
                        'San Miguel', 'Sta. Lucia', 'Santolan'
                    ];
                }

                // Add default option
                const defaultOption = document.createElement('option');
                defaultOption.value = '';
                defaultOption.disabled = true;
                defaultOption.selected = true;
                defaultOption.textContent = 'Select Barangay';
                barangaySelect.appendChild(defaultOption);

                // Add new options and select the correct barangay
                barangays.forEach(barangay => {
                    const option = document.createElement('option');
                    option.value = barangay;
                    option.textContent = barangay;

                    // Check if this barangay is the one selected for editing
                    if (barangay === selectedBarangay) {
                        option.selected = true; // Select this option if it matches
                    }

                    barangaySelect.appendChild(option);
                });
            }

            // Function to initialize barangay options on page load, if editing
            document.addEventListener('DOMContentLoaded', () => {
                const initialDistrict = document.getElementById('district').value;
                const selectedBarangay = '<?php echo htmlspecialchars($project['barangay']); ?>'; // Get the selected barangay from PHP
                console.log('Selected Barangay:', selectedBarangay); // Debugging line
                updateBarangays(selectedBarangay); // Pass the selected barangay to the function
            });

            // Optional: Update barangays when the district changes
            document.getElementById('district').addEventListener('change', () => {
                updateBarangays(); // Call this without the selected barangay when changing district
            });

            document.addEventListener('DOMContentLoaded', (event) => {
                // Function to show success alert
                function showSuccessAlert(message) {
                    Swal.fire({
                        title: 'Success',
                        text: message,
                        icon: 'success',
                        confirmButtonColor: '#089451',
                        confirmButtonText: 'OK',
                        customClass: {
                            popup: 'custom-swal-popup',
                            title: 'custom-swal-title',
                            confirmButton: 'custom-cancel-confirm'
                        }
                    }).then(() => {
                        window.location.href = "ccs-projlist.php"; // Redirect to the project list
                    });
                }

                function showWarningAlert(message) {
                    Swal.fire({
                        title: 'Warning',
                        text: message,
                        icon: 'warning',
                        showCancelButton: true, // Show cancel button
                        confirmButtonText: 'OK',
                        cancelButtonText: 'Cancel',
                        customClass: {
                            popup: 'custom-warning-popup',
                            title: 'custom-warning-title',
                            confirmButton: 'custom-warning-confirm',
                            cancelButton: 'custom-warning-cancel' 
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Redirect to ccs-projlist.php
                            window.location.href = 'ccs-projlist.php';
                        }
                        // If Cancel is clicked, do nothing (stay on ccs-editproj.php)
                    });
                }

                function showErrorAlert(message) {
                    Swal.fire({
                        title: 'Error',
                        text: message,
                        icon: 'error',
                        confirmButtonColor: '#e74c3c',
                        confirmButtonText: 'OK',
                        customClass: {
                            popup: 'custom-error-popup',
                            title: 'custom-error-title',
                            confirmButton: 'custom-error-confirm'
                        }
                    });
                }

                // Check for success message and show alert
                <?php if (isset($_SESSION['success'])) : ?>
                    showSuccessAlert('<?php echo $_SESSION['success']; ?>');
                    <?php unset($_SESSION['success']); // Unset the session variable ?>
                <?php endif; ?>

                // Check for warning message and show alert
                <?php if (isset($_SESSION['warning'])) : ?>
                    showWarningAlert('<?php echo $_SESSION['warning']; ?>');
                    <?php unset($_SESSION['warning']); // Unset the session variable ?>
                <?php endif; ?>

                // Check for error message and show alert
                <?php if (isset($_SESSION['error'])) : ?>
                    showErrorAlert('<?php echo $_SESSION['error']; ?>');
                    <?php unset($_SESSION['error']); // Unset the session variable ?>
                <?php endif; ?>
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
