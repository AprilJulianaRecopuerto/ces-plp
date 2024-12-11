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
    $sql = "SELECT * FROM ccs WHERE id = ?";
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

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Initialize and validate form data
    $date = $_POST['date'] ?? '';
    $name = $_POST['name'] ?? '';
    $position = $_POST['position'] ?? '';
    $college_name = $_POST['college_name'] ?? 'College of Arts and Science';

    // Assign project_id to requi_sub
    $requi_sub = $project_id; // Assuming the project ID is meant to be used as requi_sub

    // Prepare to insert requisition data
    $stmt = $conn->prepare("INSERT INTO ccs_requisition (requi_sub, date, name, position, college_name) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss",  $requi_sub, $date, $name, $position, $college_name);

    // Execute the statement for requisition
    if ($stmt->execute()) {
        // Get the last inserted requisition ID
        $requisition_id = $conn->insert_id;

        // Prepare to insert items associated with this requisition
        $itemStmt = $conn->prepare("INSERT INTO ccs_items (requisition_id, item_name, total_items, total_usage) VALUES (?, ?, ?, ?)");

        // Loop through the items
        if (isset($_POST['item']) && is_array($_POST['item'])) {
            for ($i = 0; $i < count($_POST['item']); $i++) {
                $item_name = $_POST['item'][$i] ?? '';
                $total_items = $_POST['total_items'][$i] ?? 0;
                $total_usage = $_POST['total_usage'][$i] ?? 0;

                // Ensure that these values are not empty or invalid before executing
                if (!empty($item_name) && $total_items >= 0 && $total_usage >= 0) {
                    // Bind parameters and execute the item insertion statement
                    $itemStmt->bind_param("isii", $requisition_id, $item_name, $total_items, $total_usage);

                    // Execute the item insertion statement
                    $itemStmt->execute(); // No echo needed here
                } else {
                    echo "Invalid data for item " . ($i + 1) . "<br>";
                }

            }
        }

        // Close the item statement
        $itemStmt->close();

        $_SESSION['success'] = "Requisition submitted successfully!";
    } else {
        $_SESSION['error'] = "Error submitting requisition.";
        echo "Error: " . $stmt->error . "<br>";
    }

    // Close the requisition statement and connection
    $stmt->close();
    $conn->close();
}
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

            .content-requisition {
                margin-left: 340px; /* Align with the sidebar */
                padding: 20px;
            }

            .content-requisition h2 {
                font-family: 'Poppins', sans-serif;
                font-size: 28px; /* Adjust the font size as needed */
                margin-bottom: 20px; /* Space below the heading */
                color: black; /* Adjust text color */
                margin-top: 110px;
            }

            .content-requisition p {
                font-family: 'Poppins', sans-serif;
                font-size: 16px;
                font-style: Italic;
                margin-top: 110px;
                margin-left: 620px;
                margin-bottom: 20px;
            }

            .form-container {
                font-family: 'Poppins', sans-serif;
                margin-left: -20px;
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
                font-family: 'Poppins', sans-serif;
                font-size: 16px; /* Increase the font size */
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
            <h2>Requisition (for Materials)</h2> 

            <div class="profile-container">
                <!-- Chat Icon with Notification Badge -->
                <a href="ccs-chat.php" class="chat-icon" onclick="resetNotifications()">
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
                <li><a href="ccs-mov.php"><img src="images/task.png">Mode of Verification</a></li>

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

        <div class="content-requisition">
            <div class="form-container">

                <div class="form-group">
                    <label for="requi_sub">ID:</label>
                    <input type="text"  id="requi_sub" name="project_id" value="<?= $project['id']; ?>" readonly>
                </div>

                <h2>Requisition Submission Form</h2>
                
                <form id="requisitionForm" action="" method="POST">

                    <div class="form-group">
                        <label for="date">Date:</label>
                        <input type="date" id="date" name="date" required>
                    </div>

                    <div class="form-group">
                        <label for="name">Name:</label>
                        <input type="text" id="name" name="name" placeholder="Enter your Name" required>
                    </div>

                    <div class="form-group">
                        <label for="position">Designation/Position:</label>
                        <input type="text" id="position" name="position" placeholder="Enter Designation or Position" required>
                    </div>

                    <div class="form-group">
                        <label for="college_name">Office/College:</label>
                        <input type="text" id="college_name" name="college_name" value="College of Arts and Science" placeholder="Enter College Name" required>
                    </div>

                    <h2>Item/s Requested</h2>
                    <div id="itemContainer">
                        <div class="item-section">
                            <h3>Item 1</h3>

                            <div class="form-group">
                                <label for="item_1">Item Name:</label>
                                <input type="text" id="item_1" name="item[]" placeholder="Enter Name of Item" required>
                            </div>

                            <div class="form-group">
                                <label for="total_items_1">Total Item Requested:</label>
                                <input type="text" id="total_items_1" name="total_items[]" placeholder="Enter Total of Meals (e.g 500)" required>
                            </div>

                            <div class="form-group">
                                <label for="total_usage_1">Total Usage:</label>
                                <input type="text" id="total_usage_1" name="total_usage[]" placeholder="Enter Total Usage (e.g 450)" required>
                            </div>

                            <button type="button" class="remove-btn" onclick="removeEvent(this)">Remove Item</button>
                        </div>
                    </div>

                    <button type="button" class="add-btn" onclick="addItem()">Add Another Item</button><br><br>

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

            let itemCount = 1;

            function addItem() {
                itemCount++;
                const itemContainer = document.getElementById('itemContainer');
                const newItemSection = document.createElement('div');
                newItemSection.className = 'item-section';
                newItemSection.innerHTML = `
                    <h3>Item ${itemCount}</h3>

                    <div class="form-group">
                        <label for="item_${itemCount}">Item Name:</label>
                        <input type="text" id="item_${itemCount}" name="item[]" placeholder="Enter Name of Item" required>
                    </div>

                    <div class="form-group">
                        <label for="total_items_${itemCount}">Total Item Requested:</label>
                        <input type="text" id="total_items_${itemCount}" name="total_items[]" placeholder="Enter Total of Meals (e.g 500 meals)" >
                    </div>

                    <div class="form-group">
                        <label for="total_usage_${itemCount}">Total Usage:</label>
                        <input type="text" id="total_usage_${itemCount}" name="total_usage[]" placeholder="Enter Total Usage (e.g 450 meals)" >
                    </div>

                    <button type="button" class="remove-btn" onclick="removeItem(this)">Remove Item</button>
                `;
                itemContainer.appendChild(newItemSection);
            }

            function removeItem(element) {
                element.parentElement.remove();
            }

            // Function to show success SweetAlert
            function showSuccessAlert(message) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: message,
                    confirmButtonColor: '#089451',
                    confirmButtonText: 'OK',
                    customClass: {
                            popup: 'custom-swal-popup',
                            title: 'custom-swal-title',
                            confirmButton: 'custom-swal-confirm'
                        }
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = "ccs-requi.php"; // Redirect to the same page or desired page
                    }
                });
            }

            // Check for success message in session and show alert
            <?php if (isset($_SESSION['success'])): ?>
                showSuccessAlert('<?php echo addslashes($_SESSION['success']); ?>');
                <?php unset($_SESSION['success']); ?> // Clear the message after displaying
            <?php endif; ?>

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
            document.querySelector('.dropdown-menu a[href="ccs-your-profile.php"]').addEventListener("click", function() {
                logAction("Profile");
            });
        });

            document.addEventListener("DOMContentLoaded", () => {
                function checkNotifications() {
                    fetch('ccs-check_notifications.php')
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