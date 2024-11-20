<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['uname'])) {
    header("Location: collegelogin.php");
    exit;
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader
require 'vendor/autoload.php';
// Database connection
$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "budget_utilization";

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Initialize and validate form data
    $semester = $_POST['semester'] ?? '';
    $district = $_POST['district'] ?? '';
    $barangay = $_POST['barangay'] ?? '';
    $event_titles = $_POST['event_title'] ?? [];
    $total_budgets = $_POST['total_budget'] ?? [];
    $expenses = $_POST['expenses'] ?? [];

    // Prepare to insert into cba_details table
    $detailsStmt = $conn->prepare("INSERT INTO cba_details (semester, district, barangay) VALUES (?, ?, ?)");
    if (!$detailsStmt) {
        die("Prepare failed: " . $conn->error);
    }
    $detailsStmt->bind_param("sss", $semester, $district, $barangay);

    // Execute the statement for cba_details
    if ($detailsStmt->execute()) {
        // Get the last inserted details ID
        $details_id = $conn->insert_id;

        // Loop through the event titles, total budgets, and expenses arrays
        foreach ($event_titles as $index => $event_title) {
            $total_budget = isset($total_budgets[$index]) ? floatval($total_budgets[$index]) : 0;
            $expense = isset($expenses[$index]) ? floatval($expenses[$index]) : 0;

            // Calculate the total expenses for the current details_id (sum of all expenses)
            $total_expenses_query = $conn->prepare("SELECT SUM(expenses) AS total_expenses FROM cba_budget WHERE details_id = ?");
            $total_expenses_query->bind_param("i", $details_id);
            $total_expenses_query->execute();
            $total_expenses_result = $total_expenses_query->get_result();
            $total_expenses_row = $total_expenses_result->fetch_assoc();
            $total_expenses = $total_expenses_row['total_expenses'] ?? 0;

            // Add the current expense to the total expenses
            $total_expenses += $expense;

            // Calculate the remaining budget after all expenses (including current)
            $remaining_budget = $total_budget - $total_expenses;

            // Prepare to insert into cba_budget table
            $budgetStmt = $conn->prepare("INSERT INTO cba_budget (details_id, event_title, total_budget, expenses, remaining_budget) VALUES (?, ?, ?, ?, ?)");
            if (!$budgetStmt) {
                die("Prepare failed: " . $conn->error);
            }
            // Insert the current event budget entry into cba_budget table
            $budgetStmt->bind_param("isddi", $details_id, $event_title, $total_budget, $expense, $remaining_budget);
            if (!$budgetStmt->execute()) {
                $_SESSION['error'] = "Error submitting budget entry: " . $budgetStmt->error;
            }

            // Close the budget statement after each entry
            $budgetStmt->close();
        }

        $_SESSION['success'] = "Budget entry submitted successfully!";
        
        $dbname_mov = "mov"; // For notifications
        $conn_mov = new mysqli($db_host, $db_user, $db_pass, $dbname_mov);

        if ($conn_mov->connect_error) {
            die("Connection to 'mov' database failed: " . $conn_mov->connect_error);
        }

        // Prepare the notification message
        $notification_message = "A new budget entry has been submitted for project: $event_title, $district, $barangay, by College of Business and Accountancy.";

        // Insert notification into the notifications table in 'mov' database
        $notification_sql = "INSERT INTO notifications (project_name, notification_message) 
        VALUES ('$event_title', '$notification_message')";

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

                // Send email using PHPMailer
                require 'vendor/autoload.php';
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
                    $mail->Subject = 'New Budget Entry Notification';
                    $mail->Body    = "A new budget entry has been submitted for project: <strong>$event_title, $district, $barangay</strong>.<br><br>Best regards,<br>PLP CES";

                    $mail->send();
                } catch (Exception $e) {
                    $_SESSION['error'] = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                    header("Location: cba-add-budget.php");
                    exit;
                }
            } else {
                $_SESSION['error'] = "No admin user found for email notification.";
                header("Location: cba-add-budget.php");
                exit;
            }

            // Close the user database connection
            $conn_users->close();
        } else {
            $_SESSION['error'] = "Error inserting notification: " . $conn_mov->error;
            header("Location: cba-add-budget.php");
            exit;
        }

        // Close the notifications connection
        $conn_mov->close();

    } else {
        $_SESSION['error'] = "Error submitting details: " . $detailsStmt->error;
    }

    // Close the details statement and connection
    $detailsStmt->close();
    $conn->close();

    // Redirect or display a success/error message
    header("Location: cba-add-budget.php"); // Adjust this to your needs
    exit();
}
?>

<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        
        <title>CES PLP</title>

        <link rel="icon" href="images/logoicon.png">

        <!-- SweetAlert CSS and JavaScript -->
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

            .content-budget {
                margin-left: 320px; /* Align with the sidebar */
                padding: 20px;
            }

            .content-budget h2 {
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

            .form-group select, .form-group input[type="text"], .form-group input[type="date"], 
            .form-group input[type="time"], .form-group input[type="number"] {
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
                font-size: 16px; /* Increase the font size */
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

            .remove-btn {
                background-color: #e74c3c;
                border: none;
                color: white;
                padding: 10px 20px;
                margin-bottom:10px;
                border-radius: 5px;
                font-size: 16px;
                cursor: pointer;
                transition: background-color 0.3s;
                font-family: 'Poppins', sans-serif;
            }

            .remove-btn:hover {
                background-color: #e74c1c;
                color: white;
            }

            .add-btn {
                background-color: #4CAF50;
                border: none;
                color: white;
                padding: 10px 20px;
                
                border-radius: 5px;
                font-size: 16px;
                cursor: pointer;
                transition: background-color 0.3s;
                font-family: 'Poppins', sans-serif;
            }
            .add-btn:hover {
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
            <h2>Add Budget</h2> 

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
                <li><a href="cba-dash.php"><img src="images/home.png">Dashboard</a></li>
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

                <li><a href="cba-budget-utilization.php" class="active"><img src="images/budget.png">Budget Allocation</a></li>

                <!-- Dropdown for Task Management -->
                <li><a href="cas-mov.php"><img src="images/task.png">Mode of Verification</a></li>

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

        <div class="content-budget">
            <div class="form-container">
                <h3>Budget Submission Form</h3>

                <form id="budgetForm" action="" method="POST">
                    <div class="form-group">
                        <label for="semester">Semester:</label>
                        <select id="semester" name="semester" required>
                            <option value="" disabled selected>Select Semester</option>
                            <option value="1st">1st Semester</option>
                            <option value="2nd">2nd Semester</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="district">District:</label>
                        <select id="district" name="district" onchange="updateBarangays()" required>
                            <option value="" disabled selected>Select District</option>
                            <option value="District 1">District 1</option>
                            <option value="District 2">District 2</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="barangay">Barangay:</label>
                        <select id="barangay" name="barangay" required>
                            <option value="" disabled selected>Select Barangay</option>
                        </select>
                    </div>

                    <h3>Budget Details</h3>
                    <div id="entryContainer">
                        <div class="entry-section">
                            <h4>Entry 1</h4>
                            <div class="form-group">
                                <label for="event_title_1">Event Title:</label>
                                <input type="text" id="event_title_1" name="event_title[]" placeholder="Enter Event Title" required>
                            </div>

                            <div class="form-group">
                                <label for="total_budget_1">Total Budget:</label>
                                <input type="number" id="total_budget_1" name="total_budget[]" placeholder="Total Budget" required oninput="updateTotalBudget()" />
                            </div>

                            <div class="form-group">
                                <label for="expenses_1">Expenses:</label>
                                <input type="number" id="expenses_1" name="expenses[]" placeholder="Enter Total Expenses" required oninput="updateRemainingBudget(this.parentElement.parentElement)" />
                            </div>
                        </div>
                    </div>

                    <button type="button" class="add-btn" onclick="addEntry()">Add Another Entry</button><br><br>

                    <div class="button-container">
                        <button type="submit">Submit</button>
                        <button type="reset">Reset</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            function updateBarangays() {
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

            // Add new options
            barangays.forEach(barangay => {
                const option = document.createElement('option');
                option.value = barangay;
                option.textContent = barangay;
                barangaySelect.appendChild(option);
            });

            // Add default option
                const defaultOption = document.createElement('option');
                defaultOption.value = '';
                defaultOption.disabled = true;
                defaultOption.selected = true;
                defaultOption.textContent = 'Select Barangay';
                barangaySelect.insertBefore(defaultOption, barangaySelect.firstChild);
            }

            // Initialize barangay options based on the default district (if necessary)
            document.addEventListener('DOMContentLoaded', () => {
                updateBarangays();
            });

            let entryCount = 1; // Tracks the number of entries
        let totalBudgetValue = 0; // Holds the total budget from the first entry

        function addEntry() {
            entryCount++;
            const entryContainer = document.getElementById('entryContainer');
            const newEntrySection = document.createElement('div');
            newEntrySection.className = 'entry-section';
            newEntrySection.innerHTML = `
                <h3>Entry ${entryCount}</h3>
                <div class="form-group">
                    <label for="event_title_${entryCount}">Event Title:</label>
                    <input type="text" id="event_title_${entryCount}" name="event_title[]" placeholder="Enter Event Title" required>
                </div>
                <div class="form-group">
                    <label for="total_budget_${entryCount}">Total Budget:</label>
                    <input type="text" id="total_budget_${entryCount}" name="total_budget[]" value="${totalBudgetValue}" placeholder="Total Budget" readonly />
                </div>
                <div class="form-group">
                    <label for="expenses_${entryCount}">Expenses:</label>
                    <input type="text" id="expenses_${entryCount}" name="expenses[]" placeholder="Enter Total Expenses" required oninput="updateRemainingBudget(this.parentElement.parentElement)">
                </div>
                <div class="form-group">
                    <label for="remaining_budget_${entryCount}">Remaining Budget:</label>
                    <input type="text" id="remaining_budget_${entryCount}" name="remaining_budget[]" placeholder="Remaining Budget" readonly />
                </div>
                <button type="button" class="remove-btn" onclick="removeEntry(this)">Remove Entry</button>
            `;
            entryContainer.appendChild(newEntrySection);

            // Set the total budget value for this entry based on the first entry's total budget
            if (entryCount > 1) {
                const firstBudgetInput = document.getElementById('total_budget_1');
                newEntrySection.querySelector(`input[id^="total_budget_"]`).value = firstBudgetInput.value;
                updateRemainingBudget(newEntrySection); // Call to update remaining budget when a new entry is added
            }
        }

        function updateRemainingBudget(entrySection) {
            const expensesInput = entrySection.querySelector('input[id^="expenses_"]');
            const remainingInput = entrySection.querySelector('input[id^="remaining_budget_"]');
            const firstBudgetInput = document.getElementById('total_budget_1');
            const totalBudget = parseFloat(firstBudgetInput.value) || 0; // Get total budget for the first entry

            // Calculate total expenses from all entries
            const totalExpenses = getTotalExpenses();

            // Calculate remaining budget
            const remainingBudget = totalBudget - totalExpenses; // Subtract total expenses from total budget
            remainingInput.value = remainingBudget.toFixed(2); // Update the remaining budget display
        }

        function getTotalExpenses() {
            const expenseInputs = document.querySelectorAll('[id^="expenses_"]');
            let totalExpenses = 0;
            expenseInputs.forEach(input => {
                totalExpenses += parseFloat(input.value) || 0; // Sum up all expenses
            });
            return totalExpenses;
        }

        function removeEntry(element) {
            element.parentElement.remove();
            updateRemainingBudget(); // Recalculate remaining budget after removing an entry
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
                        window.location.href = 'cba-budget-utilization.php';
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
                        window.location.href = 'cba-add-budget.php';
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
