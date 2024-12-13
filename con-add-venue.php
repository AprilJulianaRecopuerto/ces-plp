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

// Database credentials for proj_list
$servername_proj = "ryvdxs57afyjk41z.cbetxkdyhwsb.us-east-1.rds.amazonaws.com";
$username_proj = "zf8r3n4qqjyrfx7o";
$password_proj = "su6qmqa0gxuerg98";
$dbname_proj_list = "hpvs3ggjc4qfg9jp";

$conn_proj_list = new mysqli($servername_proj, $username_proj, $password_proj, $dbname_proj_list);

// Check connection
if ($conn_proj_list->connect_error) {
    die("Connection failed: " . $conn_proj_list->connect_error);
}

// Ensure the ID is passed and valid
if (isset($_GET['id'])) {
    $project_id = $_GET['id']; // Retrieve the ID from the URL

    // Use this ID to fetch the project details from the database or perform other actions
    $sql = "SELECT * FROM con WHERE id = ?";
    $stmt = $conn_proj_list->prepare($sql);
    $stmt->bind_param("i", $project_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $project = $result->fetch_assoc();
        // You can now use $project to display information about the selected project
    } else {
        echo "Project not found!";
    }
} else {
    echo "No project ID provided.";
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Step 1: Set venue_sub as the project ID
    $venue_sub = $project_id;  // Make sure $project_id is valid

    // Step 2: Insert into con_reservation with venue_sub
    $stmt = $conn->prepare("INSERT INTO con_reservation (venue_sub, date_of_request, name, college_name, event_activity, event_date, time_of_event) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssss", $venue_sub, $_POST['date'], $_POST['name'], $_POST['college_name'], $_POST['event'], $_POST['event_date'], $_POST['time_of_event']);

    if ($stmt->execute()) {
        $reservation_id = $conn->insert_id;

        // Step 3: Insert venue requests
        if (isset($_POST['venue_requests'])) {
            // Corrected the SQL statement to match your database schema
            $venue_stmt = $conn->prepare("INSERT INTO con_venue_request (reservation_id, venue_name) VALUES (?, ?)");
            if (!$venue_stmt) {
                $_SESSION['error'] = "Preparation failed for venue request: " . $conn->error;
                header("Location: con-venue.php"); // Redirect back to the form page
                exit;
            }

            foreach ($_POST['venue_requests'] as $venue) {
                if ($venue === "Other" && isset($_POST['other_venue'])) {
                    foreach ($_POST['other_venue'] as $custom_venue) {
                        if (!empty($custom_venue)) {
                            $venue_stmt->bind_param("is", $reservation_id, $custom_venue);
                            if (!$venue_stmt->execute()) {
                                $_SESSION['error'] = "Execution failed for custom venue: " . $venue_stmt->error;
                                header("Location: con-venue.php"); // Redirect back to the form page
                                exit;
                            }
                        }
                    }
                } else {
                    $venue_stmt->bind_param("is", $reservation_id, $venue);
                    if (!$venue_stmt->execute()) {
                        $_SESSION['error'] = "Execution failed for venue: " . $venue_stmt->error;
                        header("Location: con-venue.php"); // Redirect back to the form page
                        exit;
                    }
                }
            }
            $venue_stmt->close(); // Close the venue statement after the loop
        }

        // Step 4: Insert additional requests
        if (isset($_POST['additional_requests'])) {
            $request_stmt = $conn->prepare("INSERT INTO con_addedrequest (reservation_id, additional_request, quantity) VALUES (?, ?, ?)");
            if (!$request_stmt) {
                $_SESSION['error'] = "Preparation failed for additional request: " . $conn->error;
                header("Location: con-venue.php"); // Redirect back to the form page
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
                                header("Location: con-venue.php"); // Redirect back to the form page
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
                        header("Location: con-venue.php"); // Redirect back to the form page
                        exit;
                    }
                }
            }
            $request_stmt->close(); // Close the request statement after the loop
        }

        $_SESSION['success'] = "Reservation submitted successfully!";
    } else {
        $_SESSION['error'] = "Error inserting reservation: " . $stmt->error;
        header("Location: con-venue.php"); // Redirect back to the form page
        exit;
    }

    // Close the main statement
    $stmt->close();
}

// Close the database connection
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
                margin-left: 320px;
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

            .form-container {
                font-family: 'Poppins', sans-serif;
                margin-top:110px;
                background-color: #ffffff;
                padding: 20px;
                border-radius: 10px;
                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            }

            .form-container h2 {
                margin-top: 0;
                font-family: 'Poppins', sans-serif;
                font-size: 24px;
                color: black;
            }

            .form-group {
                margin-bottom: 15px;
            }

            .form-group label {
                font-family: 'Poppins', sans-serif;
                display: block;
                font-weight: bold;
                margin-bottom: 5px;
            }

            .form-group select, .form-group input[type="text"], .form-group input[type="date"], .form-group input[type="number"] {
                width: 100%;
                padding: 8px;
                border: 1px solid #ddd;
                border-radius: 5px;
                font-size: 16px;
                box-sizing: border-box;
                font-family: 'Poppins', sans-serif;
                margin-bottom: 10px;
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
                margin-bottom: 20px; /* Space below the buttons */
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
                font-family: "Poppins", sans-serif !important;
                width: 400px;
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

            .item-section {
                border: 1px solid #ccc;
                padding: 15px;
                margin: 10px 0;
            }
            .remove-btn {
                background-color: #e74c3c;
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

            .remove-btn:hover {
                background-color: #e74c1c;
                color: white;
            }

            .add-btn {
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
            .add-btn:hover {
                background-color: #45a049; /* Darker green on hover */
            }

            #addMoreVenueButton, #addMoreButton {
                background-color: #4CAF50;
                border: none;
                color: white;
                padding: 10px 15px;
                margin-top:10px;
                border-radius: 5px;
                font-size: 14px;
                cursor: pointer;
                transition: background-color 0.3s;
                font-family: 'Poppins', sans-serif;
            }

            .delete {
                background-color: #e74c3c;
                border: none;
                color: white;
                padding: 10px 15px;
                border-radius: 5px;
                font-size: 14px;
                cursor: pointer;
                transition: background-color 0.3s;
                font-family: 'Poppins', sans-serif;
            }

            .checkbox-options label {
                font-weight: normal;
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
            <h2>Add Venue Details</h2> 

            <div class="profile-container">
                <!-- Chat Icon with Notification Badge -->
                <a href="con-chat.php" class="chat-icon" onclick="resetNotifications()">
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
                        <a href="con-your-profile.php">Profile</a>
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
                <li><a href="con-dash.php"><img src="images/home.png">Dashboard</a></li>
                <li><a href="con-projlist.php"><img src="images/project-list.png">Project List</a></li>
                <li><a href="con-calendar.php"><img src="images/calendar.png">Event Calendar</a></li>

                <!-- Dropdown for Resource Utilization -->
                <button class="dropdown-btn">
                    <img src="images/resource.png"> Resource Utilization
                    <i class="fas fa-chevron-down"></i> <!-- Dropdown icon -->
                </button>
                <div class="dropdown-container">
                    <a href="con-tor.php">Term of Reference</a>
                    <a href="con-requi.php">Requisition</a>
                    <a href="con-venue.php">Venue</a>
                </div>

                <li><a href="con-budget-utilization.php"><img src="images/budget.png">Budget Allocation</a></li>

                <!-- Dropdown for Task Management -->
                <li><a href="con-mov.php"><img src="images/task.png">Mode of Verification</a></li>

                <li><a href="con-responses.php"><img src="images/feedback.png">Responses</a></li>

                <!-- Dropdown for Audit Trails -->
                <button class="dropdown-btn">
                    <img src="images/logs.png"> Audit Trails
                    <i class="fas fa-chevron-down"></i> <!-- Dropdown icon -->
                </button>
                <div class="dropdown-container">
                    <a href="con-history.php">Log In History</a>
                    <a href="con-activitylogs.php">Activity Logs</a>
                </div>
            </ul>
        </div>

        <div class="content-venue">
            <div class="form-container">

                <div class="form-group">
                    <label for="requi_sub">ID:</label>
                    <input type="text"  id="requi_sub" name="project_id" value="<?= $project['id']; ?>" readonly>
                </div>

                <h2>Facilities Reservation Form</h2>
                
                <form id="venueForm" action="" method="POST">

                    <div class="form-group">
                        <label for="date">Date of Request:</label>
                        <input type="date" id="date" name="date" required>
                    </div>

                    <h3>Requested made by:</h3>
                    <div class="form-group">
                        <label for="name">Name:</label>
                        <input type="text" id="name" name="name" placeholder="Enter your Name" required>
                    </div>

                    <div class="form-group">
                        <label for="college_name">Office/College:</label>
                        <input type="text" id="college_name" name="college_name" value="College of Nursing" placeholder="Enter College Name" required>
                    </div>

                    <div class="form-group">
                        <label for="event">Event/Activity:</label>
                        <input type="text" id="event" name="event"   value="<?= $project['proj_title']; ?>" required >
                    </div>

                    <div class="form-group">
                        <label for="event_date">Date of Event:</label>
                        <input type="date" id="event_date" name="event_date" value="<?= $project['dateof_imple']; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="time_of_event">Time of Event:</label>
                        <input type="text" id="time_of_event" name="time_of_event" value="<?= $project['time_from']; ?>" required >
                    </div>

                    <div class="form-group">
                        <h3>Facility/Venue Requested</h3>

                        <!-- Checkbox options for facility/venue requests -->
                        <div class="checkbox-options" id="venueRequestsContainer">
                            <label>
                                <input type="checkbox" name="venue_requests[]" value="HM Function Hall" onclick="toggleVenueInput(this)"> HM Function Hall
                            </label>
                            <label>
                                <input type="checkbox" name="venue_requests[]" value="HM Banquet Hall" onclick="toggleVenueInput(this)"> HM Banquet Hall
                            </label>
                            <label>
                                <input type="checkbox" name="venue_requests[]" value="PLP Auditorium" onclick="toggleVenueInput(this)"> PLP Auditorium
                            </label>
                            <label>
                                <input type="checkbox" name="venue_requests[]" value="PLP Gymnasium" onclick="toggleVenueInput(this)"> PLP Gymnasium
                            </label>
                            <label>
                                <input type="checkbox" name="venue_requests[]" value="AVR 1" onclick="toggleVenueInput(this)"> AVR 1
                            </label>
                            <label>
                                <input type="checkbox" name="venue_requests[]" value="AVR 2" onclick="toggleVenueInput(this)"> AVR 2
                            </label>
                            <label>
                                <input type="checkbox" name="venue_requests[]" value="AVR 3" onclick="toggleVenueInput(this)"> AVR 3
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <h3>Materials Requested</h3>

                        <!-- Checkbox options for additional items -->
                        <div class="checkbox-options">
                            <label>
                                <input type="checkbox" name="additional_requests[]" value="Projector" onclick="toggleQuantityInput(this)"> Projector
                                <input type="number" name="quantityProjector" class="quantity-input" placeholder="Quantity" style="display: none;">
                            </label>
                            <label>
                                <input type="checkbox" name="additional_requests[]" value="Widescreen" onclick="toggleQuantityInput(this)"> Widescreen
                                <input type="number" name="quantityWidescreen" class="quantity-input" placeholder="Quantity" style="display: none;">
                            </label>
                            <label>
                                <input type="checkbox" name="additional_requests[]" value="InternetConnectivityWiFi" onclick="toggleQuantityInput(this)"> Internet Connectivity (WiFi)
                                <input type="number" name="quantityInternetConnectivityWiFi" class="quantity-input" placeholder="Quantity" style="display: none;">
                            </label>
                            <label>
                                <input type="checkbox" name="additional_requests[]" value="SoundSystem" onclick="toggleQuantityInput(this)"> Sound System
                                <input type="number" name="quantitySoundSystem" class="quantity-input" placeholder="Quantity" style="display: none;">
                            </label>
                            <label>
                                <input type="checkbox" name="additional_requests[]" value="Microphone" onclick="toggleQuantityInput(this)"> Microphone
                                <input type="number" name="quantityMicrophone" class="quantity-input" placeholder="Quantity" style="display: none;">
                            </label>
                            <label>
                                <input type="checkbox" name="additional_requests[]" value="TablesRound" onclick="toggleQuantityInput(this)"> Tables (Round)
                                <input type="number" name="quantityTablesRound" class="quantity-input" placeholder="Quantity" style="display: none;">
                            </label>
                            <label>
                                <input type="checkbox" name="additional_requests[]" value="TablesSquare" onclick="toggleQuantityInput(this)"> Tables (Square)
                                <input type="number" name="quantityTablesSquare" class="quantity-input" placeholder="Quantity" style="display: none;">
                            </label>
                            <label>
                                <input type="checkbox" name="additional_requests[]" value="ChairsMonoblock" onclick="toggleQuantityInput(this)"> Chairs (Monoblock)
                                <input type="number" name="quantityChairsMonoblock" class="quantity-input" placeholder="Quantity" style="display: none;">
                            </label>
                            <label>
                                <input type="checkbox" name="additional_requests[]" value="ChairsTiffany" onclick="toggleQuantityInput(this)"> Chairs (Tiffany)
                                <input type="number" name="quantityChairsTiffany" class="quantity-input" placeholder="Quantity" style="display: none;">
                            </label>
                            <label>
                                <input type="checkbox" name="additional_requests[]" value="Rostrum" onclick="toggleQuantityInput(this)"> Rostrum
                                <input type="number" name="quantityRostrum" class="quantity-input" placeholder="Quantity" style="display: none;">
                            </label>
                        </div>
                    </div>

                    <div class="button-container">
                        <button type="submit">Submit</button>
                        <button type="reset">Reset</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            // Set the current date in YYYY-MM-DD format
            window.onload = function() {
                var today = new Date();
                var yyyy = today.getFullYear();
                var mm = today.getMonth() + 1; // Months are zero-based
                var dd = today.getDate();

                // Add leading zero for single-digit day/month
                if (mm < 10) mm = '0' + mm;
                if (dd < 10) dd = '0' + dd;

                // Format the date to match input type="date"
                var formattedDate = yyyy + '-' + mm + '-' + dd;

                // Set the value of the date input field
                document.getElementById('date').value = formattedDate;
            };

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
                    time = setTimeout(logout, 100000);  // 100 seconds = 100000 ms
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

            // Log clicks on the "Profile" link
            document.querySelector('.dropdown-menu a[href="con-your-profile.php"]').addEventListener("click", function() {
                logAction("Profile");
            });
        });

            function toggleQuantityInput(checkbox) {
                const quantityInput = checkbox.nextElementSibling;
                if (checkbox.checked) {
                    quantityInput.style.display = 'inline-block'; // Show the quantity input
                } else {
                    quantityInput.style.display = 'none'; // Hide the quantity input
                    quantityInput.value = ''; // Clear the quantity value when unchecked
                }
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
                    window.location.href = "con-venue.php"; // Redirect to the dashboard or desired page
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

            document.addEventListener("DOMContentLoaded", () => {
                function checkNotifications() {
                    fetch('con-check_notifications.php')
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