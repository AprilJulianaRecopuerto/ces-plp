<?php
session_start(); // Make sure to start the session

// Check if the user is logged in
if (!isset($_SESSION['uname'])) {
    // Redirect to login page if the session variable is not set
    header("Location: loginpage.php");
    exit;
}

// Database credentials
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "budget_utilization";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) { // Use $conn here
    die("Connection failed: " . $conn->connect_error); // Use $conn here
}


// Database credentials for user_registration
$dbname_user_registration = "user_registration";

// Create connection to user_registration database
$conn_user_registration = new mysqli($servername, $username, $password, $dbname_user_registration);

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
                margin-left: 340px; /* Align with the sidebar */
                padding: 20px;
            }

            /* Add Button Style */
            .add-button {
                padding: 10px 20px;
                font-size: 16px;
                font-family: 'Poppins', sans-serif;
                background-color: #4CAF50; /* Green background */
                color: white;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                margin-bottom: 20px;
                text-decoration: none;
                margin-top:110px !important;
            }

            .add-button:hover {
                background-color: #45a049; /* Darker green */
            }

            .button-container {
                display: flex;
                justify-content: flex-end; /* Align buttons to the right */
                margin-bottom: 20px; /* Space below the buttons */
                margin-right: 20px;
            }

            .button-container button {
                margin-top: 110px;
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

            .data-details h2 {
                font-family: 'Poppins', sans-serif;
            }

            /* Container for the table */
            .table-container {
                width: 100%;
                margin-left: -12px;
                overflow-x: auto;
                margin-top: 1px; /* Space above the table */
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
                text-align: center;
                border: 1px solid #ddd;
                padding: 10px;
                white-space: nowrap; /* Prevent text from wrapping */
            }

            .crud-table th {
                text-align: center; 
                background-color: #4CAF50;
                color: white;
                height: 40px;
                width: 13px; /* Set a fixed width for table headers */
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

            .custom-swal-input {
                font-family: 'Poppins', sans-serif;
                font-size: 17px;
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

            .custom-event-popup {
                font-family: 'Poppins', sans-serif;
                width: 600px;
                background: #f8f9fa; /* Light background for the popup */
                border-radius: 8px; /* Rounded corners */
                padding: 15px; /* Padding inside the popup */
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); /* Subtle shadow for depth */
            }

            .custom-event-title {
                font-family: 'Poppins', sans-serif;
                color: #343a40; /* Dark color for the title */
                font-size: 1.5em; /* Font size for the title */
                margin-bottom: 10px; /* Spacing below the title */
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
                background-color: #6c757d !important; /* Gray for deny button */
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
                background-color: #e74c3c;
                color: white !important; /* White text on the button */
                padding: 10px 20px; /* Padding inside the button */
                border-radius: 4px; /* Rounded corners for button */
                border: none; /* No border */
                transition: background-color 0.3s ease; /* Smooth background transition */
            }

            .custom-event-deny:hover {
                background-color: #5a6268; /* Darker gray on hover */
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
            <h2>
                <span style="color: yellow; font-size: 28px;"><?php echo htmlspecialchars($departmentName); ?></span> Budget
            </h2>

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

                <li><a href="ccs-budget-utilization.php" class="active"><img src="images/budget.png">Budget Allocation</a></li>

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

        <div class="content-budget">
            <div class="button-container">
                <button onclick="logAndRedirect('Add Budget','ccs-add-budget.php')">Add Budget</button>
            </div>

            <div class="data-details">
                <h2>Budget Details</h2>
                <div class="table-container">
                    <table class="crud-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Semester</th>
                                <th>District</th>
                                <th>Barangay</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Pagination variables for ccs_details
                            $limit = 5; // Number of records per page
                            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Current page
                            $offset = ($page - 1) * $limit; // Offset for SQL query

                            // Count total ccs_details records
                            $countSql = "SELECT COUNT(*) as total FROM ccs_details";
                            $countResult = $conn->query($countSql);
                            $totalRecords = $countResult->fetch_assoc()['total'];
                            $totalPages = ceil($totalRecords / $limit); // Calculate total pages

                            // Fetch paginated ccs_details
                            $detailsSql = "SELECT * FROM ccs_details ORDER BY id LIMIT $limit OFFSET $offset";
                            $resultDetails = $conn->query($detailsSql);

                            if ($resultDetails && $resultDetails->num_rows > 0) {
                                while ($row = $resultDetails->fetch_assoc()) {
                                    echo "<tr>
                                        <td>" . htmlspecialchars($row["id"]) . "</td>
                                        <td>" . htmlspecialchars($row["semester"]) . "</td>
                                        <td>" . htmlspecialchars($row["district"]) . "</td>
                                        <td>" . htmlspecialchars($row["barangay"]) . "</td>
                                        <td class='edit'>
                                            <a href='ccs-edit-budget.php?id=" . $row["id"] . "'>EDIT</a>
                                            <button class='delete-button' data-id='" . $row["id"] . "'>DELETE</button>
                                        </td>
                                    </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5'>No details found.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                <br>
                <div class="table-container">
                    <table class="crud-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Event Title</th>
                                <th>Total Budget</th>
                                <th>Expenses</th>
                                <th>Remaining Budget</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Fetch all budget items (removing pagination for items)
                            $itemsSql = "SELECT * FROM ccs_budget ORDER BY details_id"; // Removed LIMIT and OFFSET for items
                            $resultItems = $conn->query($itemsSql);

                            if ($resultItems && $resultItems->num_rows > 0) {
                                $itemsData = [];
                                
                                // Group items by details_id
                                while ($row = $resultItems->fetch_assoc()) {
                                    $itemsData[$row['details_id']][] = $row; // Grouping items by details_id
                                }

                                // Output rows with calculated rowspans for details_id
                                foreach ($itemsData as $detailsId => $items) {
                                    $firstRow = true; // Track the first row for details_id

                                    foreach ($items as $index => $item) {
                                        echo "<tr>";

                                        // Display Details ID only once per unique details_id
                                        if ($firstRow) {
                                            echo "<td rowspan='" . count($items) . "'>" . htmlspecialchars($detailsId) . "</td>";
                                            $firstRow = false; // Set to false after the first row
                                        }

                                        // Output remaining columns for the current item
                                        echo "<td>" . htmlspecialchars($item["event_title"]) . "</td>
                                        
                                            <td>" . htmlspecialchars($item["total_budget"]) . "</td>
                                            <td>" . htmlspecialchars($item["expenses"]) . "</td>
                                            <td>" . htmlspecialchars($item["remaining_budget"]) . "</td>
                                            </tr>";
                                    }
                                }
                            } else {
                                echo "<tr><td colspan='5'>No budget items found.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
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

        <script>
           document.addEventListener("DOMContentLoaded", function () {
                // Select all delete buttons with the class 'delete-button'
                const deleteButtons = document.querySelectorAll(".delete-button");

                deleteButtons.forEach(button => {
                    button.addEventListener("click", function () {
                        const detailsId = this.getAttribute("data-id");

                        // Check if multiple events are associated with this details ID
                        fetch('ccs-budget-delete.php', {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json"
                            },
                            body: JSON.stringify({ id: detailsId, action: 'count' })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.count > 1) {
                                // Multiple events found: Prompt user to delete all or specify a title
                                Swal.fire({
                                    title: "Multiple Events Found",
                                    text: "Do you want to delete all events associated with this ID?",
                                    icon: "warning",
                                    showCancelButton: true,
                                    confirmButtonText: "Yes, delete all",
                                    cancelButtonText: "Cancel", // This now simply cancels the dialog
                                    showDenyButton: true,
                                    denyButtonText: "No, enter specific Event Title", // This prompts for a specific title
                                    customClass: {
                                        popup: 'custom-event-popup',
                                        title: 'custom-event-title',
                                        confirmButton: 'custom-event-confirm',
                                        cancelButton: 'custom-event-cancel',
                                        denyButton: 'custom-event-deny'
                                    }
                                }).then(result => {
                                    if (result.isConfirmed) {
                                        // Delete all events
                                        deleteBudget(detailsId, 'all');
                                    } else if (result.dismiss === Swal.DismissReason.cancel) {
                                        // Close the dialog without further action
                                        return; // Do nothing on cancel
                                    } else if (result.isDenied) {
                                        // Ask for a specific event title to delete
                                        Swal.fire({
                                            title: 'Enter Specific Event Title',
                                            input: 'text',
                                            inputLabel: 'Event Title',
                                            inputPlaceholder: 'Enter the specific event title',
                                            showCancelButton: true,
                                            background: '#f8f9fa',
                                            customClass: {
                                                popup: 'custom-swal-popup',
                                                input: 'custom-swal-input',
                                                title: 'custom-swal-title',
                                                confirmButton: 'custom-swal-confirm',
                                                cancelButton: 'custom-swal-cancel'
                                            }
                                        }).then(titleResult => {
                                            if (titleResult.isConfirmed && titleResult.value) {
                                                deleteBudget(detailsId, 'specific', titleResult.value);
                                            }
                                        });
                                    }
                                });

                                } else {
                                // Only one event found; confirm delete for single event
                                Swal.fire({
                                    title: 'Are you sure?',
                                    text: 'Do you really want to delete this single event?',
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
                                        deleteBudget(detailsId, 'single');
                                    }
                                });
                            }
                        })
                        .catch(error => {
                            console.error("Error fetching event count:", error);
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

                // Function to handle the deletion
                function deleteBudget(detailsId, action, eventTitle = null) {
                    fetch('ccs-budget-delete.php', {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify({ id: detailsId, action: action, event_title: eventTitle })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: 'Deleted!',
                                text: 'The budget record has been deleted.',
                                icon: 'success',
                                background: '#f8f9fa',
                                customClass: {
                                    popup: 'custom-swal-popup',
                                    title: 'custom-swal-title',
                                    confirmButton: 'custom-swal-confirm'
                                }
                            }).then(() => location.reload()); // Reload page after deletion
                        } else {
                            Swal.fire({
                                title: 'Error',
                                text: 'An error occurred while deleting the record: ' + (data.message || 'Unknown error.'),
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
                        console.error("Error deleting budget:", error);
                        Swal.fire({
                            title: 'Error',
                            text: 'An error occurred while deleting the record.',
                            icon: 'error',
                            background: '#f8f9fa',
                            customClass: {
                                popup: 'custom-error-popup',
                                title: 'custom-error-title'
                            }
                        });
                    });
                }
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
                    logAndRedirect("Edit Budget", url); // Log the action and redirect
                });
            });

            // Log clicks on action buttons (Delete)
            document.querySelectorAll(".delete-button").forEach(function(button) {
                button.addEventListener("click", function() {
                    logAction("Delete Budget"); // Log deletion action
                    // Additional logic for deletion can be added here if needed
                });
            });
        });
        </script>
    </body>
</html>