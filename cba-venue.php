<?php
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['uname'])) {
    // Redirect to login page if the session variable is not set
    header("Location: roleaccount.php");
    exit;
}

// Database credentials
$servername_resource = "mwgmw3rs78pvwk4e.cbetxkdyhwsb.us-east-1.rds.amazonaws.com";
$username_resource = "dnr20srzjycb99tw";
$password_resource = "ndfnpz4j74v8t0p7";
$dbname_resource = "x8uwt594q5jy7a7o";

// Create connection
$conn = new mysqli($servername_resource, $username_resource, $password_resource, $dbname_resource);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Step 1: Insert into cba_reservation
    $stmt = $conn->prepare("INSERT INTO cba_reservation (date_of_request, name, college_name, event_activity, date_of_event, time_of_event, remarks) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $_POST['date'], $_POST['name'], $_POST['college_name'], $_POST['event'], $_POST['event_date'], $_POST['time'], $_POST['remarks']);
    
    if ($stmt->execute()) {
        $reservation_id = $conn->insert_id;

        // Step 2: Insert venue requests
        if (isset($_POST['venue_requests'])) {
            $venue_stmt = $conn->prepare("INSERT INTO cba_venue_request (reservation_id, venue_requests) VALUES (?, ?)");
            if (!$venue_stmt) {
                $_SESSION['error'] = "Preparation failed for venue request: " . $conn->error;
                header("Location: cba-venue.php"); // Redirect back to the form page
                exit;
            }

            foreach ($_POST['venue_requests'] as $venue) {
                if ($venue === "Other" && isset($_POST['other_venue'])) {
                    foreach ($_POST['other_venue'] as $custom_venue) {
                        if (!empty($custom_venue)) {
                            $venue_stmt->bind_param("is", $reservation_id, $custom_venue);
                            if (!$venue_stmt->execute()) {
                                $_SESSION['error'] = "Execution failed for custom venue: " . $venue_stmt->error;
                                header("Location:cba-venue.php"); // Redirect back to the form page
                                exit;
                            }
                        }
                    }
                } else {
                    $venue_stmt->bind_param("is", $reservation_id, $venue);
                    if (!$venue_stmt->execute()) {
                        $_SESSION['error'] = "Execution failed for venue: " . $venue_stmt->error;
                        header("Location: cba-venue.php"); // Redirect back to the form page
                        exit;
                    }
                }
            }
        }

        // Step 3: Insert additional requests
        if (isset($_POST['additional_requests'])) {
            $request_stmt = $conn->prepare("INSERT INTO cba_addedrequest (reservation_id, additional_request, quantity) VALUES (?, ?, ?)");
            if (!$request_stmt) {
                $_SESSION['error'] = "Preparation failed for additional request: " . $conn->error;
                header("Location: cba-venue.php"); // Redirect back to the form page
                exit;
            }

            foreach ($_POST['additional_requests'] as $request) {
                if ($request === "Other" && isset($_POST['other_request']) && isset($_POST['quantityOther'])) {
                    foreach ($_POST['other_request'] as $key => $custom_request) {
                        $quantity = is_numeric($_POST['quantityOther'][$key]) ? intval($_POST['quantityOther'][$key]) : 0;

                        if (!empty($custom_request)) {
                            $request_stmt->bind_param("isi", $reservation_id, $custom_request, $quantity);
                            if (!$request_stmt->execute()) {
                                $_SESSION['error'] = "Execution failed for custom request: " . $request_stmt->error;
                                header("Location: cba-venue.php"); // Redirect back to the form page
                                exit;
                            }
                        }
                    }
                } else {
                    $quantityName = "quantity" . str_replace([' ', '(', ')'], '', $request);
                    $quantity = isset($_POST[$quantityName]) && is_numeric($_POST[$quantityName]) ? intval($_POST[$quantityName]) : 0;
                    $request_stmt->bind_param("isi", $reservation_id, $request, $quantity);
                    if (!$request_stmt->execute()) {
                        $_SESSION['error'] = "Execution failed for additional request: " . $request_stmt->error;
                        header("Location: cba-venue.php"); // Redirect back to the form page
                        exit;
                    }
                }
            }
        }

        $_SESSION['success'] = "Reservation submitted successfully!";
    } else {
        $_SESSION['error'] = "Error inserting reservation: " . $stmt->error;
        header("Location: cba-venue.php"); // Redirect back to the form page
        exit;
    }

    // Close the statements
    $stmt->close();
    if (isset($venue_stmt)) {
        $venue_stmt->close();
    }
    if (isset($request_stmt)) {
        $request_stmt->close();
    }
}

// Close the database connection
$conn->close();

$sn = "l3855uft9zao23e2.cbetxkdyhwsb.us-east-1.rds.amazonaws.com";
$un = "equ6v8i5llo3uhjm";
$psd = "vkfaxm2are5bjc3q";
$dbname_user_registration = "ylwrjgaks3fw5sdj";

// Fetch the profile picture from the colleges table in user_registration
$conn_profile = new mysqli($sn, $un, $psd, $dbname_user_registration);

if ($conn_profile->connect_error) {
    die("Connection failed: " . $conn_profile->connect_error);
}

$uname = $_SESSION['uname'];
$sql_profile = "SELECT picture FROM colleges WHERE uname = ?"; // Adjust 'username' to your matching column
$stmt = $conn_profile->prepare($sql_profile);
$stmt->bind_param("s", $uname);
$stmt->execute();
$result_profile = $stmt->get_result();

$profilePicture = null;
if ($result_profile && $row_profile = $result_profile->fetch_assoc()) {
    $profilePicture = $row_profile['picture']; // Fetch the 'picture' column
}

$stmt->close();
$conn_profile->close();
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

            .content-venue {
                margin-left: 320px; /* Align with the sidebar */
                padding: 20px;
            }

            .content-venue h2 {
                font-family: 'Poppins', sans-serif;
                font-size: 28px; /* Adjust the font size as needed */
                margin-bottom: 20px; /* Space below the heading */
                color: black; /* Adjust text color */
                margin-top: 110px;
            }

            .content-venue p {
                font-family: 'Poppins', sans-serif;
                font-size: 16px;
                font-style: Italic;
                margin-top: 110px;
                margin-left: 620px;
                margin-bottom: 20px;
            }

            .data-details .table-container {
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
                height: 40px;
            }

            .crud-table td {
                text-align: center;
                height: 50px;
                background-color: #fafafa;
            }

            .crud-table tr:hover {
                background-color: #f1f1f1;
            }
            .button-container {
                display: flex;
                justify-content: left; /* Align buttons to the right */
                margin-bottom: 20px; /* Space below the buttons */
                margin-right: 20px;
                margin-top: 110px;
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

            .data-details {
                font-family: 'Poppins', sans-serif;
                margin-left: 10px;
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

            .delete-button {
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

            .delete-button:hover {
                background-color: #ef233c; /* Darker green on hover */
            }

            .custom-swal-popup {
                font-family: 'Poppins', sans-serif;
                width: 400px !important; /* Set a larger width */
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

            .custom-swal-input {
                font-family: 'Poppins', sans-serif;
                font-size: 17px;
            }

            .custom-error-popup {
                font-family: 'Poppins', sans-serif;
                width: 400px !important; /* Set a larger width */
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

            .custom-event-popup {
                font-family: 'Poppins', sans-serif;
                width: 600px;
                background: #f8f9fa; /* Light background for the popup */
                border-radius: 8px; /* Rounded corners */
                padding: 15px; /* Padding inside the popup */
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); /* Subtle shadow for depth */
            }

            .custom-event-confirm {
                font-family: 'Poppins', sans-serif;
                font-size: 17px;
                background-color: #089451;
                border: 0.5px #089451 !important;
                color: #fff;
                border-radius: 10px;
                cursor: pointer;
                outline: none !important; /* Remove default focus outline */
            }

            .custom-event-confirm:hover {
                background-color: #218838; /* Darker green on hover */
            }

            .custom-event-cancel {
                font-family: 'Poppins', sans-serif;
                font-size: 17px;
                background-color: #e74c3c;
                color: #fff;
                border-radius: 10px;
                cursor: pointer;
                outline: none; /* Remove default focus outline */
            }

            .custom-event-cancel:hover {
                background-color: #c82333; /* Darker red on hover */
            }

            .custom-event-deny {
                font-family: 'Poppins', sans-serif;
                font-size: 17px;
                background-color: #6c757d !important; /* Gray for deny button */
                color: white !important; /* White text on the button */
                padding: 10px 20px; /* Padding inside the button */
                border-radius: 4px; /* Rounded corners for button */
                border: none; /* No border */
                transition: background-color 0.3s ease; /* Smooth background transition */
            }

            .custom-event-deny:hover {
                background-color: #5a6268; /* Darker gray on hover */
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
            .smaller-alert {
            font-size: 14px; /* Adjust text size for a compact look */
            padding: 20px;   /* Adjust padding to mimic a smaller alert box */
            }
        </style>
    </head>

    <body>
        <nav class="navbar">
            <h2>Venue Details</h2> 

            <div class="profile-container">
                <!-- Chat Icon with Notification Badge -->
                <a href="cba-chat.php" class="chat-icon" onclick="resetNotifications()">
                    <i class="fa fa-comments"></i>
                    <span class="notification-badge" id="chatNotification" style="display: none;">!</span>
                </a>

                <div class="profile" id="profileDropdown">
                    <?php
                        // Check if a profile picture is set in the session
                        if (!empty($profilePicture)) {
                            // Display the profile picture
                            echo '<img src="' . htmlspecialchars($profilePicture) . '" alt="Profile Picture">';
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

                <li><a href="cba-budget-utilization.php"><img src="images/budget.png">Budget Allocation</a></li>

                <!-- Dropdown for Task Management -->
                <li><a href="cba-mov.php"><img src="images/task.png">Mode of Verification</a></li>

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

        <div class="content-venue">
            <div class="button-container">
               <button onclick="logAndRedirect('Add Venue', 'cba-venue-list.php')">Facilities Reservation Form</button>
            </div>

            <?php
            // Database credentials
            $servername_resource = "mwgmw3rs78pvwk4e.cbetxkdyhwsb.us-east-1.rds.amazonaws.com";
            $username_resource = "dnr20srzjycb99tw";
            $password_resource = "ndfnpz4j74v8t0p7";
            $dbname_resource = "x8uwt594q5jy7a7o";

            // Create connection
            $conn = new mysqli($servername_resource, $username_resource, $password_resource, $dbname_resource);

            // Check connection
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }
            ?>

            <div class="data-details">
                <!-- Table 1: Request Made By -->
                <h3>Request Made By</h3>
                <div class="table-container">
                <table class="crud-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Date of Request</th>
                        <th>Name</th>
                        <th>College Name</th>
                        <th>Event Activity</th>
                        <th>Date of Event</th>
                        <th>Time of Event</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                  $requestSql = "SELECT * FROM cba_reservation ORDER BY id DESC";

                    $requestResult = $conn->query($requestSql);

                        if ($requestResult && $requestResult->num_rows > 0) {
                            while ($row = $requestResult->fetch_assoc()) {
                                echo "<tr>
                                    <td>" . $row["id"] . "</td>
                                    <td>" . $row["date_of_request"] . "</td>
                                    <td>" . $row["name"] . "</td>
                                    <td>" . $row["college_name"] . "</td>
                                    <td>" . $row["event_activity"] . "</td>
                                    <td>" . $row["event_date"] . "</td>
                                    <td>" . $row["time_of_event"] . "</td>
                                    <td class='edit'>
                                        <a href='cba-edit-venue.php?id=" . $row["id"] . "'>EDIT</a>
                                        <button class='delete-button' data-id='" . $row["id"] . "'>DELETE</button>
                                        <a href='cba-venue-download.php?id=" . $row["id"] . "' class='download-button'>Download Report</a>
                                    </td>
                                </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='8'>No records found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <!-- Table 2: Venue Requests -->
            <h3>Venue Requests</h3>
            <div class="table-container">
                <table class="crud-table">
                    <thead>
                        <tr>
                            <th>Reservation ID</th>
                            <th>Venue Requests</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                       $venueSql = "SELECT reservation_id, venue_name FROM cba_venue_request ORDER BY reservation_id DESC";
                        $venueResult = $conn->query($venueSql);

                        if ($venueResult && $venueResult->num_rows > 0) {
                            $venueData = []; // Array to hold venue data grouped by reservation ID

                            // Group data by reservation ID
                            while ($row = $venueResult->fetch_assoc()) {
                                $venueData[$row['reservation_id']][] = $row['venue_name'];
                            }

                            foreach ($venueData as $reservationId => $venues) {
                                echo "<tr>";
                                echo "<td rowspan='" . count($venues) . "'>" . htmlspecialchars($reservationId) . "</td>"; // Display Reservation ID once
                                
                                // Display the first venue request
                                echo "<td>" . htmlspecialchars($venues[0]) . "</td>";
                                echo "</tr>";

                                // Display remaining venue requests
                                for ($i = 1; $i < count($venues); $i++) {
                                    echo "<tr><td>" . htmlspecialchars($venues[$i]) . "</td></tr>";
                                }
                            }
                        } else {
                            echo "<tr><td colspan='2'>No records found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <!-- Table 3: Additional Requests -->
            <h3>Additional Requests</h3>
            <div class="table-container">
                <table class="crud-table">
                    <thead>
                        <tr>
                            <th>Reservation ID</th>
                            <th>Additional Request</th>
                            <th>Quantity</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                       $additionalSql = "SELECT reservation_id, additional_request, quantity FROM cba_addedrequest ORDER BY reservation_id DESC";

                        $additionalResult = $conn->query($additionalSql);

                        if ($additionalResult && $additionalResult->num_rows > 0) {
                            $additionalData = []; // Array to hold additional requests grouped by reservation ID

                            // Group data by reservation ID
                            while ($row = $additionalResult->fetch_assoc()) {
                                $additionalData[$row['reservation_id']][] = $row; // Grouping additional requests by reservation ID
                            }

                            foreach ($additionalData as $reservationId => $requests) {
                                echo "<tr>";
                                echo "<td rowspan='" . count($requests) . "'>" . htmlspecialchars($reservationId) . "</td>"; // Display Reservation ID once
                                
                                // Display the first additional request
                                echo "<td>" . htmlspecialchars($requests[0]['additional_request']) . "</td>
                                <td>" . htmlspecialchars($requests[0]['quantity']) . "</td>";
                                echo "</tr>";

                                // Display remaining additional requests
                                for ($i = 1; $i < count($requests); $i++) {
                                echo "<tr>
                                    <td>" . htmlspecialchars($requests[$i]['additional_request']) . "</td>
                                    <td>" . htmlspecialchars($requests[$i]['quantity']) . "</td>
                                    </tr>";
                                }
                            }
                        } else {
                            echo "<tr><td colspan='4'>No records found.</td></tr>";
                        }

                        $conn->close();
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <script>
            let inactivityTime = function () {
            let time;

                // List of events to reset the inactivity timer
                window.onload = resetTimer;
                document.onmousemove = resetTimer;
                document.onkeypress = resetTimer;
                document.onscroll = resetTimer;
                document.onclick = resetTimer;

                // If logged out due to inactivity, prevent user from accessing dashboard
                if (sessionStorage.getItem('loggedOut') === 'true') {
                    // Ensure the user cannot access the page and is redirected
                    window.location.replace('loadingpage.php');
                }

                function logout() {
                    // SweetAlert2 popup styled similar to the standard alert
                    Swal.fire({
                        title: 'Session Expired',
                        text: 'You have been logged out due to inactivity.',
                        icon: 'warning',
                        confirmButtonText: 'OK',
                        width: '400px',   // Adjust width (close to native alert size)
                        heightAuto: false, // Prevent automatic height adjustment
                        customClass: {
                            popup: 'custom-swal-popup',
                            confirmButton: 'custom-swal-confirm'
                        }
                    }).then(() => {
                        // Set sessionStorage to indicate user has been logged out due to inactivity
                        sessionStorage.setItem('loggedOut', 'true');

                        // Redirect to loadingpage.php
                        window.location.replace('loadingpage.php');
                    });
                }

                function resetTimer() {
                    clearTimeout(time);
                    // Set the inactivity timeout to 100 seconds (100000 milliseconds)
                    time = setTimeout(logout, 300000);  // 100 seconds = 100000 ms
                }

                // Check if the user is logged in and clear the loggedOut flag
                if (sessionStorage.getItem('loggedOut') === 'false') {
                    sessionStorage.removeItem('loggedOut');
                }
            };

            // Start the inactivity timeout function
            inactivityTime();

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
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Execute the logout action (send a request to the server to log out)
                        fetch('college-logout.php?action=logout')
                            .then(response => response.text())
                            .then(data => {
                                console.log(data); // Log response for debugging

                                // Redirect the user to the role account page after logout
                                window.location.href = 'roleaccount.php';

                                // Modify the history to prevent back navigation after logout
                                window.history.pushState(null, '', window.location.href);
                                window.onpopstate = function () {
                                    window.history.pushState(null, '', window.location.href);
                                };
                            })
                            .catch(error => console.error('Error:', error));
                    }
                });
            }

            // This should only run when you're on a page where the user has logged out
            if (window.location.href !== 'roleaccount.php') {
                window.history.pushState(null, '', window.location.href);
                window.onpopstate = function () {
                    window.history.pushState(null, '', window.location.href);
                };
            }

            // Dropdown menu toggle
            document.getElementById('profileDropdown').addEventListener('click', function() {
                const dropdown = this.querySelector('.dropdown-menu');
                dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
            });

            // Close dropdown if clicked outside
            window.addEventListener('click', function(event) {
                if (!document.getElementById('profileDropdown').contains(event.target)) {
                    const dropdown = document.querySelector('.dropdown-menu');
                    if (dropdown) {
                        dropdown.style.display = 'none';
                    }
                }
            });

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

           // Function to toggle the visibility of additional requests
            function toggleOtherInput() {
                const otherCheckbox = document.getElementById('otherCheckbox');
                const otherInputsContainer = document.getElementById('otherInputsContainer');
                const addMoreButton = document.getElementById('addMoreButton');

                if (otherCheckbox.checked) {
                    otherInputsContainer.style.display = 'block'; // Show the container
                    addMoreButton.style.display = 'block'; // Show the Add More button
                } else {
                    otherInputsContainer.style.display = 'none'; // Hide the container
                    addMoreButton.style.display = 'none'; // Hide the Add More button

                    // Clear all other request inputs when unchecked
                    const otherRequests = document.querySelectorAll('.other-request');
                    otherRequests.forEach((request) => {
                        request.querySelector('input[type="text"]').value = ''; // Clear text input
                        request.querySelector('input[type="number"]').value = ''; // Clear quantity input
                    });
                }
            }

            // Function to add more request inputs
            function addMoreRequest() {
                const container = document.getElementById('addedRequestsContainer'); // Get the added requests container
                const newRequestDiv = document.createElement('div');
                newRequestDiv.classList.add('other-request');
                newRequestDiv.innerHTML = `
                    <input type="text" name="other_request[]" placeholder="Please specify">
                    <input type="number" name="quantityOther[]" class="quantity-input" placeholder="Quantity">
                    <button type="button" class="delete" onclick="deleteRequest(this)">Delete Request</button>
                `;
                container.appendChild(newRequestDiv); // Append new request at the end of the container

                // Show the Add More button if the Other checkbox is checked
                const otherCheckbox = document.getElementById('otherCheckbox');
                if (otherCheckbox.checked) {
                    document.getElementById('addMoreButton').style.display = 'block'; // Show the button if checkbox is checked
                }
            }

            // Function to delete a request input
            function deleteRequest(button) {
                const requestDiv = button.parentElement; // Get the parent div of the button
                requestDiv.remove(); // Remove the request div

                // Check if there are any requests left
                const addedRequestsContainer = document.getElementById('addedRequestsContainer');
                if (addedRequestsContainer.querySelectorAll('.other-request').length === 0) {
                    document.getElementById('addMoreButton').style.display = 'none'; // Hide the Add More button if no requests left
                }
            }


            // Function to toggle the visibility of other venue inputs
            function toggleOtherVenueInput() {
                const otherCheckbox = document.getElementById('otherVenueCheckbox');
                const otherVenueInputsContainer = document.getElementById('otherVenueInputsContainer');
                const addMoreVenueButton = document.getElementById('addMoreVenueButton');

                if (otherCheckbox.checked) {
                    otherVenueInputsContainer.style.display = 'block'; // Show the container
                    addMoreVenueButton.style.display = 'block'; // Ensure the Add More button is shown
                } else {
                    otherVenueInputsContainer.style.display = 'none'; // Hide the container

                    // Clear all other request inputs when unchecked
                    const otherRequests = document.querySelectorAll('.other-request');
                    otherRequests.forEach((request) => {
                        request.querySelector('input[type="text"]').value = ''; // Clear input
                    });
                }
            }

            // Function to add more venue requests
            function addMoreVenueRequest() {
                const container = document.getElementById('venueRequestsContainer');
                const newRequestDiv = document.createElement('div');
                newRequestDiv.classList.add('other-request'); // Add class for styling

                newRequestDiv.innerHTML = `
                    <input type="text" name="other_venue[]" placeholder="Please specify">
                    <button type="button" class="delete" onclick="deleteVenueRequest(this)">Delete Request</button>
                `;

                // Insert the new request above the Add More button
                container.appendChild(newRequestDiv); // Append new request at the end of the container

                // Move the Add More button after the new request
                const addMoreButton = document.getElementById('addMoreVenueButton');
                container.appendChild(addMoreButton); // Place the button below the last added request
            }

            // Function to delete a venue request
            function deleteVenueRequest(button) {
                const requestDiv = button.parentElement; // Get the parent div of the button
                requestDiv.remove(); // Remove the request div

                // Check if there are no venue requests left
                const venueRequestsContainer = document.getElementById('venueRequestsContainer');

                // If no venue requests are left, hide the Add More button
                if (venueRequestsContainer.querySelectorAll('.other-request').length === 0) {
                    document.getElementById('addMoreVenueButton').style.display = 'none'; // Hide the Add More button
                }
            }

            // Function to show success SweetAlert
            function showSuccessAlert() {
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: 'Events saved successfully.',
                    confirmButtonColor: '#089451',
                    confirmButtonText: 'Continue',
                    customClass: {
                        popup: 'custom-swal-popup',
                        title: 'custom-swal-title',
                        confirmButton: 'custom-swal-confirm'
                    }
                }).then(() => {
                    window.location.href = "cba-resource.php"; // Redirect to the dashboard or desired page
                });
            }

            // Function to show error SweetAlert
            function showErrorAlert(message) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: message,
                    confirmButtonColor: '#e74c3c',
                    confirmButtonText: 'Try Again',
                    customClass: {
                        popup: 'custom-error-popup',
                        title: 'custom-error-title',
                        confirmButton: 'custom-error-confirm'
                    }
                });
            }

            // Check for success message in session and show alert
            <?php if (isset($_SESSION['success'])): ?>
                showSuccessAlert();
                <?php unset($_SESSION['success']); ?> // Clear the message after displaying
            <?php endif; ?>

            // Check for error message in session and show alert
            <?php if (isset($_SESSION['error'])): ?>
                showErrorAlert('<?php echo addslashes($_SESSION['error']); ?>');
                <?php unset($_SESSION['error']); ?> // Clear the message after displaying
            <?php endif; ?>

            document.addEventListener("DOMContentLoaded", function () {
            const deleteButtons = document.querySelectorAll(".delete-button");

            deleteButtons.forEach(button => {
                button.addEventListener("click", function (event) {
                    event.preventDefault();
                    const reservationId = this.getAttribute("data-id");

                    // Fetch the count of venues and additional requests for the reservation
                    fetch(`cba-venue-delete.php?reservation_id=${reservationId}`)
                        .then(response => response.json())
                        .then(data => {
                            const venueCount = data.venue_count || 0;
                            const requestCount = data.request_count || 0;

                            if (venueCount > 1 || requestCount > 1) {
                                // Show SweetAlert for multiple venues or additional requests
                                Swal.fire({
                                    title: "Multiple Found",
                                    text: "There are multiple venues and requests for this ID. Choose your action:",
                                    icon: "warning",
                                    showDenyButton: true,
                                    showCancelButton: true,
                                    confirmButtonText: "Yes, delete all",
                                    denyButtonText: "Delete specific",
                                    cancelButtonText: "Cancel",
                                    background: '#f8f9fa',
                                    customClass: {
                                        popup: 'custom-event-popup',
                                        title: 'custom-event-title',
                                        confirmButton: 'custom-event-confirm',
                                        denyButton: 'custom-event-deny',
                                        cancelButton: 'custom-event-cancel'
                                    }
                                }).then(result => {
                                    if (result.isConfirmed) {
                                        // Delete all records for this reservation ID
                                        deleteRequisition(reservationId, 'all', 'all');
                                    } else if (result.isDenied) {
                                        // Show options for specific venue or request deletion
                                        Swal.fire({
                                            title: 'Select Deletion Type',
                                            text: 'Do you want to delete a specific venue or request?',
                                            icon: 'question',
                                            showCancelButton: true,
                                            confirmButtonText: 'Delete Specific Venue',
                                            cancelButtonText: 'Delete Specific Request',
                                            background: '#f8f9fa',
                                            customClass: {
                                                popup: 'custom-event-popup',
                                                title: 'custom-event-title',
                                                confirmButton: 'custom-event-confirm',
                                                cancelButton: 'custom-event-cancel'
                                            }
                                        }).then(typeResult => {
                                            if (typeResult.isConfirmed) {
                                                // User chose to delete a specific venue
                                                Swal.fire({
                                                    title: 'Enter Specific Venue Name',
                                                    input: 'text',
                                                    inputLabel: 'Venue Name',
                                                    inputPlaceholder: 'Enter the venue name',
                                                    showCancelButton: true,
                                                    background: '#f8f9fa',
                                                    customClass: {
                                                        popup: 'custom-swal-popup',
                                                        input: 'custom-swal-input',
                                                        title: 'custom-swal-title',
                                                        confirmButton: 'custom-swal-confirm',
                                                        cancelButton: 'custom-swal-cancel'
                                                    }
                                                }).then(inputResult => {
                                                    if (inputResult.isConfirmed && inputResult.value) {
                                                        const specificVenueName = inputResult.value.trim();
                                                        deleteRequisition(reservationId, specificVenueName, null);
                                                    }
                                                });
                                            } else if (typeResult.isDismissed) {
                                                // User chose to delete a specific request
                                                Swal.fire({
                                                    title: 'Enter Specific Request Name',
                                                    input: 'text',
                                                    inputLabel: 'Request Name',
                                                    inputPlaceholder: 'Enter the request name',
                                                    showCancelButton: true,
                                                    background: '#f8f9fa',
                                                    customClass: {
                                                        popup: 'custom-swal-popup',
                                                        input: 'custom-swal-input',
                                                        title: 'custom-swal-title',
                                                        confirmButton: 'custom-swal-confirm',
                                                        cancelButton: 'custom-swal-cancel'
                                                    }
                                                }).then(inputResult => {
                                                    if (inputResult.isConfirmed && inputResult.value) {
                                                        const specificRequestName = inputResult.value.trim();
                                                        deleteRequisition(reservationId, null, specificRequestName);
                                                    }
                                                });
                                            }
                                        });
                                    }
                                });
                            } else if (venueCount === 1 && requestCount === 1) {
                                // Only one venue and one request; prompt to delete all data for this reservation
                                Swal.fire({
                                    title: 'Are you sure?',
                                    text: 'This action will delete all data associated with this reservation.',
                                    icon: 'warning',
                                    showCancelButton: true,
                                    confirmButtonText: 'Yes, delete all',
                                    cancelButtonText: 'Cancel',
                                    background: '#f8f9fa',
                                    customClass: {
                                        popup: 'custom-swal-popup',
                                        title: 'custom-swal-title',
                                        confirmButton: 'custom-swal-confirm',
                                        cancelButton: 'custom-swal-cancel'
                                    }
                                }).then(result => {
                                    if (result.isConfirmed) {
                                        // Delete all records associated with this reservation ID
                                        deleteRequisition(reservationId, 'all', 'all');
                                    }
                                });
                            } else if (venueCount === 1) {
                                // Only one venue; confirm deletion
                                Swal.fire({
                                    title: 'Are you sure?',
                                    text: 'Do you really want to delete this venue?',
                                    icon: 'warning',
                                    showCancelButton: true,
                                    confirmButtonText: 'Yes, delete',
                                    cancelButtonText: 'Cancel',
                                    background: '#f8f9fa',
                                    customClass: {
                                        popup: 'custom-swal-popup',
                                        title: 'custom-swal-title',
                                        confirmButton: 'custom-swal-confirm',
                                        cancelButton: 'custom-swal-cancel'
                                    }
                                }).then(result => {
                                    if (result.isConfirmed) {
                                        deleteRequisition(reservationId, 'single', null);
                                    }
                                });
                            } else if (requestCount === 1) {
                                // Only one request; confirm deletion
                                Swal.fire({
                                    title: 'Are you sure?',
                                    text: 'Do you really want to delete this request?',
                                    icon: 'warning',
                                    showCancelButton: true,
                                    confirmButtonText: 'Yes, delete',
                                    cancelButtonText: 'Cancel',
                                    background: '#f8f9fa',
                                    customClass: {
                                        popup: 'custom-swal-popup',
                                        title: 'custom-swal-title',
                                        confirmButton: 'custom-swal-confirm',
                                        cancelButton: 'custom-swal-cancel'
                                    }
                                }).then(result => {
                                    if (result.isConfirmed) {
                                        deleteRequisition(reservationId, null, 'single');
                                    }
                                });
                            }
                        })
                        .catch(error => {
                            console.error("Error fetching venue/request count:", error);
                            Swal.fire({
                                title: 'Error',
                                text: 'Unable to proceed with delete action.',
                                icon: 'error',
                                background: '#f8f9fa',
                                customClass: {
                                    popup: 'custom-error-popup',
                                    title: 'custom-error-title'
                                }
                            });
                        });
                    });
                });
            });

            function deleteRequisition(reservationId, venueName, requestName) {
                const data = new URLSearchParams();
                data.append('reservation_id', reservationId);
                if (venueName) {
                    data.append('venue_name', venueName);
                }
                if (requestName) {
                    data.append('additional_request', requestName);
                }

                fetch('cba-venue-delete.php', {
                    method: 'POST',
                    body: data,
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    }
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        Swal.fire({
                            title: 'Success',
                            text: 'Records deleted successfully.',
                            icon: 'success',
                            background: '#f8f9fa',
                            customClass: {
                                popup: 'custom-swal-popup',
                                title: 'custom-swal-title',
                                confirmButton: 'custom-swal-confirm',
                                cancelButton: 'custom-swal-cancel'
                            }
                        }).then(() => {
                            location.reload(); // Reload the page to reflect changes
                        });
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: result.message || 'Failed to delete records.',
                            icon: 'error',
                            background: '#f8f9fa',
                            customClass: {
                                popup: 'custom-error-popup',
                                title: 'custom-error-title'
                            }
                        });
                    }
                })
                .catch(error => {
                    console.error("Error during deletion:", error);
                    Swal.fire({
                        title: 'Error',
                        text: 'Unable to delete records at this time.',
                        icon: 'error',
                        background: '#f8f9fa',
                        customClass: {
                            popup: 'custom-error-popup',
                            title: 'custom-error-title'
                        }
                    });
                });
            }

            function logAction(actionDescription) {
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "college_logs.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.send("action=" + encodeURIComponent(actionDescription));
            }

            function logAndRedirect(actionDescription, url) {
                logAction(actionDescription); // Log the action
                setTimeout(function() {
                    window.location.href = url; // Redirect after logging
                }, 100); // Delay to ensure logging completes
            }

            // Add event listeners when the page is fully loaded
            document.addEventListener("DOMContentLoaded", function() {
                // Log clicks on main menu links
                document.querySelectorAll(".menu > li > a").forEach(function(link) {
                    link.addEventListener("click", function() {
                        logAction(link.textContent.trim());
                    });
                });

                // Handle dropdown button clicks
                var dropdowns = document.getElementsByClassName("dropdown-btn");
                for (let i = 0; i < dropdowns.length; i++) {
                    dropdowns[i].addEventListener("click", function () {
                        let dropdownContents = document.getElementsByClassName("dropdown-container");
                        for (let j = 0; j < dropdownContents.length; j++) {
                            dropdownContents[j].style.display = "none";
                        }
                        let dropdownContent = this.nextElementSibling;
                        if (dropdownContent.style.display === "block") {
                            dropdownContent.style.display = "none";
                        } else {
                            dropdownContent.style.display = "block";
                        }
                    });
                }

                // Log clicks on dropdown links
                document.querySelectorAll(".dropdown-container a").forEach(function(link) {
                    link.addEventListener("click", function(event) {
                        event.stopPropagation();
                        logAction(link.textContent.trim());
                    });
                });

                document.querySelectorAll("td.edit a").forEach(function(link) {
                link.addEventListener("click", function(event) {
                    event.preventDefault(); // Prevent the default action
                    var url = this.href; // Get the URL from the href attribute
                    logAndRedirect("Edit Venue", url); // Log the action and redirect
                });
            });

            // Log clicks on action buttons (Delete)
            document.querySelectorAll(".delete-button").forEach(function(button) {
                button.addEventListener("click", function() {
                    logAction("Delete Venue"); // Log deletion action
                    // Additional logic for deletion can be added here if needed
                });
            });

            // Log clicks on the "Profile" link
            document.querySelector('.dropdown-menu a[href="cba-your-profile.php"]').addEventListener("click", function() {
                logAction("Profile");
            });
        });

            document.addEventListener("DOMContentLoaded", () => {
                function checkNotifications() {
                    fetch('cba-check_notifications.php')
                        .then(response => response.json())
                        .then(data => {
                            const chatNotification = document.getElementById('chatNotification');
                            if (data.unread_count > 0) {
                                chatNotification.style.display = 'inline-block';
                            } else {
                                chatNotification.style.display = 'none';
                            }
                        })
                        .catch(error => console.error('Error checking notifications:', error));
                }

                // Check for notifications every 2 seconds
                setInterval(checkNotifications, 2000);
                checkNotifications(); // Initial check when page loads
            });
        </script>
    </body>
</html>