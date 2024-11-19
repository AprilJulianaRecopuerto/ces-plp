<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['uname'])) {
    // Redirect to login page if the session variable is not set
    header("Location: roleaccount.php");
    exit;
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader
require 'vendor/autoload.php';

// Database credentials for proj_list and user_registration databases
$servername = "ryvdxs57afyjk41z.cbetxkdyhwsb.us-east-1.rds.amazonaws.com";
$username_db = "zf8r3n4qqjyrfx7o"; // MySQL username (e.g., root for local development)
$password_db = "su6qmqa0gxuerg98"; // MySQL password (e.g., empty for local development)
$dbname_proj_list = "hpvs3ggjc4qfg9jp";

// Create connection to the proj_list database
$conn_proj_list = new mysqli($servername, $username_db, $password_db, $dbname_proj_list);

// Check connection for the proj_list database
if ($conn_proj_list->connect_error) {
    die("Connection failed: " . $conn_proj_list->connect_error);
}

$sn_ur = "l3855uft9zao23e2.cbetxkdyhwsb.us-east-1.rds.amazonaws.com";
$username_ur = "equ6v8i5llo3uhjm"; // MySQL username (e.g., root for local development)
$pass_ur = "vkfaxm2are5bjc3q"; // MySQL password (e.g., empty for local development)
$dbname_user_registration = "ylwrjgaks3fw5sdj";


// Fetch the profile picture from the colleges table in user_registration
$conn_profile = new mysqli($sn_ur, $username_ur, $pass_ur, $dbname_user_registration);  
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

            .add-button {
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

            .add-button:hover {
                background-color: #45a049; /* Darker green on hover */
            }
        </style>
    </head>

    <body>
        <nav class="navbar">
            <h2>Add Budget</h2> 

            <div class="profile-container">
                <!-- Chat Icon with Notification Badge -->
                <a href="cas-chat.php" class="chat-icon" onclick="resetNotifications()">
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
                        <a href="cas-your-profile.php">Profile</a>
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
                <li><a href="cas-dash.php"><img src="images/home.png">Dashboard</a></li>
                <li><a href="cas-projlist.php"><img src="images/project-list.png">Project List</a></li>
                <li><a href="cas-calendar.php"><img src="images/calendar.png">Event Calendar</a></li>

                <!-- Dropdown for Resource Utilization -->
                <button class="dropdown-btn">
                    <img src="images/resource.png"> Resource Utilization
                    <i class="fas fa-chevron-down"></i> <!-- Dropdown icon -->
                </button>
                <div class="dropdown-container">
                    <a href="cas-tor.php">Term of Reference</a>
                    <a href="cas-requi.php">Requisition</a>
                    <a href="cas-venue.php">Venue</a>
                </div>

                <li><a href="cas-budget-utilization.php" class="active"><img src="images/budget.png">Budget Allocation</a></li>

                <!-- Dropdown for Task Management -->
                <button class="dropdown-btn">
                    <img src="images/task.png">Task Management
                    <i class="fas fa-chevron-down"></i> <!-- Dropdown icon -->
                </button>
                <div class="dropdown-container">
                    <a href="cas-task.php">Upload Files</a>
                    <a href="cas-mov.php">Mode of Verification</a>
                </div>

                <li><a href="cas-responses.php"><img src="images/feedback.png">Responses</a></li>

                <!-- Dropdown for Audit Trails -->
                <button class="dropdown-btn">
                    <img src="images/logs.png"> Audit Trails
                    <i class="fas fa-chevron-down"></i> <!-- Dropdown icon -->
                </button>
                <div class="dropdown-container">
                    <a href="cas-history.php">Log In History</a>
                    <a href="cas-activitylogs.php">Activity Logs</a>
                </div>
            </ul>
        </div>

        <div class="content-budget">
            <h2>All Projects in CAS</h2>
            <table class="crud-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Project Title</th>
                        <th>Lead Person</th>
                        <th>Semester</th>
                        <th>Date of Submission</th>
                        <th>Date of Implementation</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    <?php
                        $projectsSqlModal = "
                        SELECT id, proj_title, lead_person, semester, date_of_sub, dateof_imple
                        FROM cas
                        WHERE proj_title NOT IN (
                            SELECT proj_title FROM budget_utilization.cas_budget
                        )
                        ORDER BY id";
                        
                    $resultProjectsModal = $conn_proj_list->query($projectsSqlModal);

                    if ($resultProjectsModal && $resultProjectsModal->num_rows > 0) {
                        while ($project = $resultProjectsModal->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td id='proj_id_" . $project["id"] . "'>" . htmlspecialchars($project["id"]) . "</td>";
                            echo "<td id='proj_title_" . $project["id"] . "'>" . htmlspecialchars($project["proj_title"]) . "</td>";
                            echo "<td id='lead_person_" . $project["id"] . "'>" . htmlspecialchars($project["lead_person"]) . "</td>";
                            echo "<td id='semester_" . $project["id"] . "'>" . htmlspecialchars($project["semester"]) . "</td>";
                            echo "<td>" . htmlspecialchars($project["date_of_sub"]) . "</td>";
                            echo "<td>" . htmlspecialchars($project["dateof_imple"]) . "</td>";
                            echo "<td><button class='add-button' onclick='addProjectToBudget(" . $project["id"] . ")'>Add</button></td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='7'>No projects found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <script>
            function addProjectToBudget(projectId) {
                var projTitle = document.getElementById("proj_title_" + projectId).innerText;
                var leadPerson = document.getElementById("lead_person_" + projectId).innerText;
                var semester = document.getElementById("semester_" + projectId).innerText;
                var expenses = 0; 
                var totalBudget = 40000; 

                var xhr = new XMLHttpRequest();
                xhr.open("POST", "cas-add_to_budget.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.onreadystatechange = function () {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        alert(xhr.responseText);
                    }
                };

                var data = "projectId=" + projectId + "&projTitle=" + encodeURIComponent(projTitle) +
                        "&leadPerson=" + encodeURIComponent(leadPerson) +
                        "&semester=" + encodeURIComponent(semester) +
                        "&expenses=" + expenses +
                        "&totalBudget=" + totalBudget;

                xhr.send(data);
            }
            function addProjectToBudget(projectId) {
    var projTitle = document.getElementById("proj_title_" + projectId).innerText;
    var leadPerson = document.getElementById("lead_person_" + projectId).innerText;
    var semester = document.getElementById("semester_" + projectId).innerText;

    // Use SweetAlert for inputting expenses
    Swal.fire({
        title: 'Enter the expenses for the new project:',
        input: 'text',
        inputPlaceholder: 'Enter expenses here...',
        showCancelButton: true,
        confirmButtonText: 'Submit',
        cancelButtonText: 'Cancel',
        preConfirm: (value) => {
            if (!value || isNaN(value) || value <= 0) {
                Swal.showValidationMessage('Please enter a valid amount for the expenses');
            }
            return value;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            var expenses = parseFloat(result.value);

            // Check if the new expense exceeds the allotted budget
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "cas-check_budget_limit.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    var response = xhr.responseText.split('|');
                    if (response[0] === "error") {
                        Swal.fire({
                            icon: 'error',
                            title: 'Budget Exceeded!',
                            text: response[1],
                            showConfirmButton: true
                        });
                    } else if (response[0] === "success") {
                        Swal.fire({
                            icon: 'success',
                            title: 'Budget Check Passed!',
                            text: response[1],
                            showConfirmButton: true
                        }).then(() => {
                            // Proceed to add project to budget
                            var xhrAdd = new XMLHttpRequest();
                            xhrAdd.open("POST", "cas-add_to_budget.php", true);
                            xhrAdd.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                            xhrAdd.onreadystatechange = function () {
                                if (xhrAdd.readyState == 4 && xhrAdd.status == 200) {
                                    if (xhrAdd.responseText === "success") {
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'Project successfully added!',
                                            showConfirmButton: false,
                                            timer: 1500
                                        }).then(function() {
                                            window.location.href = 'cas-budget-utilization.php';
                                        });
                                    } else {
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Error!',
                                            text: xhrAdd.responseText,
                                            showConfirmButton: true
                                        });
                                    }
                                }
                            };

                            var data = "projectId=" + projectId +
                                       "&projTitle=" + encodeURIComponent(projTitle) +
                                       "&leadPerson=" + encodeURIComponent(leadPerson) +
                                       "&semester=" + encodeURIComponent(semester) +
                                       "&expenses=" + encodeURIComponent(expenses);
                            xhrAdd.send(data);
                        });
                    }
                }
            };

            var checkBudgetData = "newExpense=" + expenses;
            xhr.send(checkBudgetData);
        }
    });
}


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
                        window.location.href = 'cas-budget-utilization.php';
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
                        window.location.href = 'cas-add-budget.php';
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
            document.querySelector('.dropdown-menu a[href="cas-your-profile.php"]').addEventListener("click", function() {
                logAction("Profile");
            });
        });

                document.addEventListener("DOMContentLoaded", () => {
                function checkNotifications() {
                    fetch('cas-check_notifications.php')
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
