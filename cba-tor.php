<?php
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['uname'])) {
    header("Location: roleaccount.php");
    exit;
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

            .content-tor {
                margin-left: 320px; /* Align with the sidebar */
                padding: 20px;
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

            .filter-button {
                cursor: pointer; /* Change the cursor to a pointer */
                padding: 10px 20px; /* Add some padding */
                margin: 5px; /* Add margin for spacing */
                background-color: #007bff; /* Change background color */
                color: white; /* Change text color */
                border: none; /* Remove border */
                border-radius: 5px; /* Rounded corners */
            }

            .filter-button:hover {
                background-color: #0056b3; /* Change color on hover */
            }

            .filter-content {
                display: none; /* Hide all by default */
            }

            .filter-content h2, p {
                font-family: 'Poppins', sans-serif;
                margin-left: 10px;
                
            }

            .btn-form button {
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

            .delete-requisition-button {
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

            .delete-requisition-button:hover {
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
            <h2>Terms of Reference (for Food)</h2> 

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

        <div class="content-tor">
            <div class="button-container">
                <button onclick="logAndRedirect('Add TOR','cba-add-resource.php')">TOR Form</button>
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
                <h3>Event Form Details</h3>

                <div class="table-container">
                    <table class="crud-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Procurement Title</th>
                                <th>Agency</th>
                                <th>Date of Delivery</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            
                            <?php
                            // Pagination variables
                            $limit = 5; // Number of records per page
                            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Current page
                            $offset = ($page - 1) * $limit; // Offset for SQL query

                            // Count total records
                            $countSql = "SELECT COUNT(*) as total FROM cba_tor";
                            $countResult = $conn->query($countSql);
                            $totalRecords = $countResult->fetch_assoc()['total'];
                            $totalPages = ceil($totalRecords / $limit); // Calculate total pages

                            // Fetch paginated event form details
                            $eventFormSql = "SELECT * FROM cba_tor ORDER BY id LIMIT $limit OFFSET $offset";
                            $resultEventForm = $conn->query($eventFormSql);

                            if ($resultEventForm && $resultEventForm->num_rows > 0) {
                                while ($row = $resultEventForm->fetch_assoc()) {
                                    echo "<tr>
                                        <td>" . $row["id"] . "</td>
                                        <td>" . $row["procurement_title"] . "</td>
                                        <td>" . $row["agency"] . "</td>
                                        <td>" . $row["date_of_delivery"] . "</td>
                                        <td class='edit'>
                                            <a href='cba-edit-tor.php?id=" . $row["id"] . "'>EDIT</a>
                                            <button class='delete-button' data-id='" . $row["id"] . "'>DELETE</button>
                                            <a href='cba-download-report.php?id=" . $row["id"] . "' class='download-button'>Download Report</a>
                                        </td>
                                    </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='10'>No records found.</td></tr>"; // Message when no records found
                            }
                            ?>
                        </tbody>
                    </table>
                </div>

                <h3>Event Details</h3>
                <div class="table-container">
                    <table class="crud-table">
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
                        <tbody>
                            <?php
                            // Fetch event details
                            $eventDetailsSql = "SELECT * FROM cba_food ORDER BY cba_tor_id, event_date"; // Ensure the table name is correct
                            $resultEventDetails = $conn->query($eventDetailsSql);

                            if ($resultEventDetails && $resultEventDetails->num_rows > 0) {
                                $eventData = [];
                                while ($row = $resultEventDetails->fetch_assoc()) {
                                    $eventData[$row['cba_tor_id']][$row['event_date']][] = $row;
                                }

                                // Output rows with calculated rowspans for both event_form_id and event_date
                                foreach ($eventData as $eventFormId => $dates) {
                                    $firstEventRow = true; // Track the first row for event_form_id

                                    foreach ($dates as $eventDate => $rows) {
                                        $dateRowCount = count($rows); // Number of rows for this event_form_id and event_date
                                        $firstDateRow = true; // Track the first row for event_date

                                        foreach ($rows as $row) {
                                            echo "<tr>";

                                            // Display Event Form ID only once per unique event_form_id
                                            if ($firstEventRow) {
                                                echo "<td rowspan='" . array_sum(array_map("count", $dates)) . "'>" . $eventFormId . "</td>";
                                                $firstEventRow = false;
                                            }

                                            // Display Event Date only once per unique event_date
                                            if ($firstDateRow) {
                                                echo "<td rowspan='" . $dateRowCount . "'>" . $eventDate . "</td>";
                                                $firstDateRow = false;
                                            }

                                            // Output remaining columns
                                            echo "<td>" . $row['event_title'] . "</td>
                                                <td>" . $row['meal_type'] . "</td>
                                                <td>" . $row['menu'] . "</td>
                                                <td>" . $row['total_meals'] . "</td>
                                                <td>" . $row['total_usage'] . "</td>
                                                <td>" . $row['utilization_percentage'] . '%' ."</td>
                                            </tr>";
                                        }
                                    }
                                }
                            } else {
                                echo "<tr><td colspan='7'>No records found.</td></tr>"; // Message when no records found
                            }

                            $conn->close();
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

                let eventCount = 1; // Initialize event counter

                // Function to add a new event section
                function addEvent() {
                    eventCount++; // Increment the event counter
                    const eventContainer = document.getElementById('eventContainer'); // Get the container for events
                    const newEventSection = document.createElement('div'); // Create a new div for the event section
                    newEventSection.className = 'event-section'; // Assign a class name for styling
                    newEventSection.innerHTML = `
                        <h3>Event ${eventCount}</h3>
                        <div class="form-group">
                            <label for="event_date_${eventCount}">Event Date:</label>
                            <input type="date" id="event_date_${eventCount}" name="event_date[]" onblur="checkEventDate()" required>
                        </div>

                        <div class="form-group">
                            <label for="event_title_${eventCount}">Event Title:</label>
                            <input type="text" id="event_title_${eventCount}" name="event_title[]" placeholder="Enter Event Title" required>
                        </div>

                        <div class="form-group">
                            <label for="meal_type_${eventCount}">Food Category:</label>
                            <select id="meal_type_${eventCount}" name="meal_type[]" required>
                                <option value="" disabled selected>Select Meal Type</option>
                                <option value="Packed Meals">Packed Meals</option>
                                <option value="Catering Services">Catering Services</option>
                                <option value="Boxed Meals">Boxed Meals</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="menu_${eventCount}">Menu:</label>
                            <textarea id="menu_${eventCount}" name="menu[]" placeholder="Enter Menu Details" required></textarea>
                        </div>

                        <div class="form-group">
                            <label for="total_meals_${eventCount}">Total of Meals:</label>
                            <input type="text" id="total_meals_${eventCount}" name="total_meals[]" placeholder="Enter Total of Meals (e.g 500 meals)" required>
                        </div>

                        <div class="form-group">
                            <label for="total_usage_${eventCount}">Total Usage:</label>
                            <input type="text" id="total_usage_${eventCount}" name="total_usage[]" placeholder="Enter Total Usage (e.g 450 meals)" required>
                        </div>

                        <button type="button" class="remove-btn" onclick="removeEvent(this)">Remove Event</button>
                    `;
                    eventContainer.appendChild(newEventSection); // Append the new event section to the container
                }

                // Function to remove an event section
                function removeEvent(element) {
                    const eventSection = element.parentElement; // Get the parent div of the button
                    eventSection.remove(); // Remove the event section
                    eventCount--; // Decrement the event counter
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

                document.addEventListener('DOMContentLoaded', function() {
                    const deleteButtons = document.querySelectorAll('.delete-button');

                    deleteButtons.forEach(button => {
                        button.addEventListener('click', function() {
                            const eventId = this.getAttribute('data-id');

                            // AJAX request to check for multiple events with the same event_form_id
                            fetch(`cba-delete-event.php?cba_tor_id=${eventId}`)
                            .then(response => response.json())
                            .then(data => {
                                if (data.eventCount > 1) {
                                    // Multiple events found: ask if user wants to delete all or specify date
                                    Swal.fire({
                                        title: 'Multiple Events Found',
                                        text: 'Do you want to delete all?',
                                        icon: 'warning',
                                        showCancelButton: true,
                                        confirmButtonText: 'Yes, delete all',
                                        cancelButtonText: 'No, enter specific date',
                                        showDenyButton: true, // Enable additional cancel button
                                        denyButtonText: 'Cancel', // Text for additional cancel button
                                        background: '#f8f9fa',
                                        customClass: {
                                            popup: 'custom-event-popup',
                                            title: 'custom-event-title',
                                            confirmButton: 'custom-event-confirm',
                                            cancelButton: 'custom-event-cancel',
                                            denyButton: 'custom-event-deny' // Add styling class for deny button
                                        }
                                    }).then(result => {
                                        if (result.isConfirmed) {
                                            // User chose to delete all
                                            deleteEvent(eventId, 'all');
                                        } else if (result.isDenied) {
                                            // User clicked the additional cancel button
                                            Swal.fire({
                                                title: 'Action cancelled',
                                                text: 'Your action has been cancelled.',
                                                icon: 'info',
                                                background: '#f8f9fa',
                                                customClass: {
                                                    popup: 'custom-error-popup',
                                                    title: 'custom-error-title',
                                                    confirmButton: 'custom-swal-confirm'
                                                }
                                            });
                                        } else if (result.dismiss === Swal.DismissReason.cancel) {
                                            // User wants to specify a date
                                            Swal.fire({
                                                title: 'Enter Specific Date',
                                                input: 'text',
                                                inputLabel: 'Date (YYYY-MM-DD)',
                                                showCancelButton: true,
                                                background: '#f8f9fa',
                                                customClass: {
                                                    popup: 'custom-swal-popup',
                                                    input: 'custom-swal-input',
                                                    title: 'custom-swal-title',
                                                    confirmButton: 'custom-swal-confirm',
                                                    cancelButton: 'custom-swal-cancel'
                                                }
                                            }).then(dateResult => {
                                                if (dateResult.isConfirmed && dateResult.value) {
                                                    deleteEvent(eventId, dateResult.value);
                                                } else if (dateResult.isDismissed) {
                                                    Swal.fire({
                                                        title: 'Cancelled',
                                                        text: 'No date was entered.',
                                                        icon: 'info',
                                                        background: '#f8f9fa',
                                                        customClass: {
                                                            popup: 'custom-error-popup',
                                                            title: 'custom-error-title',
                                                            confirmButton: 'custom-swal-confirm'
                                                        }
                                                    });
                                                }
                                            });
                                        }
                                    });
                                } else {
                                    // Single event found: ask for confirmation to delete
                                    Swal.fire({
                                        title: 'Are you sure?',
                                        text: 'Do you really want to delete this?',
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
                                            deleteEvent(eventId, 'single');
                                        }
                                    });
                                }
                            });
                        });
                    });

                    // Function to handle deletion
                    function deleteEvent(eventId, date) {
                        fetch(`cba-delete-event.php?cba_tor_id=${eventId}&event_date=${date}`, {
                            method: 'POST'
                        })
                        .then(response => response.json())
                        .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Deleted!',
                            text: 'The event has been deleted.',
                            icon: 'success',
                            customClass: {
                                popup: 'custom-swal-popup',
                                title: 'custom-swal-title',
                                confirmButton: 'custom-swal-confirm'
                            }
                        }).then(() => location.reload()); // Reload page after deletion
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: 'An error occurred while deleting the event.',
                            icon: 'error',
                            customClass: {
                                popup: 'custom-error-popup',
                                title: 'custom-error-title'
                            }
                        });
                    }
                });

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

                    document.querySelectorAll("td.edit a").forEach(function(link) {
                    link.addEventListener("click", function(event) {
                        event.preventDefault(); // Prevent the default action
                        var url = this.href; // Get the URL from the href attribute
                        logAndRedirect("Edit TOR", url); // Log the action and redirect
                    });
                });

                /// Log clicks on action buttons (Delete)
                document.querySelectorAll(".delete-button").forEach(function(button) {
                    button.addEventListener("click", function() {
                        logAction("Delete TOR"); // Log deletion action
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