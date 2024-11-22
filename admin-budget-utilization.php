<?php
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: roleaccount.php");
    exit;
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
        <!-- SweetAlert 2 CDN -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


        <style>
            @import url('https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,400;0,600;1,500&display=swap');
            @import url('https://fonts.cdnfonts.com/css/glacial-indifference-2');
            @import url('https://fonts.googleapis.com/css2?family=Saira+Condensed:wght@500&display=swap');

            body {
                margin: 0;
                background-color: #F6F5F5; /* Light gray background color */
                font-family: 'Glacial Indifference', sans-serif;
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

            .content-budget h3 {
                font-family: 'Poppins', sans-serif;
                font-size: 22px; /* Adjust the font size as needed */
            }

            .filter-button {
                font-family: 'Poppins', sans-serif;
                background-color: #4CAF50;
                color: white;
                border: none;
                padding: 10px 20px;
                margin-right: 10px;
                margin-top: 110px;
                border-radius: 5px;
                font-size: 16px;
                cursor: pointer;
                transition: background-color 0.3s;
            }

            .filter-button:hover {
                background-color: #45a049;
            }

            .table-container {
                text-align: center;
                width: 100%;               /* Full width of the parent */
                margin-left: -12px;
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

                /* Custom Popup */
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
           
            .allotted-budget{
                font-size:20px;
            }

            .remaining-budget{
                font-size:20px;
            }
            
            .smaller-alert {
            font-size: 14px; /* Adjust text size for a compact look */
            padding: 20px;   /* Adjust padding to mimic a smaller alert box */
            }

            .update-budget-btn {
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
        </style>
    </head>

    <body>
        <nav class="navbar">
            <h2>Colleges - Budget Allocation</h2> 

            <div class="profile-container">
                <!-- Chat Icon with Notification Badge -->
                <a href="chat.php" class="chat-icon" onclick="resetNotifications()">
                    <i class="fa fa-comments"></i>
                    <span class="notification-badge" id="chatNotification" style="display: none;">!</span>
                </a>
            <div>

            <div class="profile" id="profileDropdown">
                <?php
                    // Check if a profile picture is set in the session
                    if (!empty($_SESSION['picture'])) {
                        // Show the profile picture
                        echo '<img src="' . htmlspecialchars($_SESSION['picture']) . '" alt="Profile Picture">';
                    } else {
                        // Get the first letter of the username for the placeholder
                        $firstLetter = strtoupper(substr($_SESSION['username'], 0, 1));
                        echo '<div class="profile-placeholder">' . htmlspecialchars($firstLetter) . '</div>';
                    }
                ?>

                <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>

                <i class="fa fa-chevron-down dropdown-icon"></i>
                <div class="dropdown-menu">
                    <a href="admin-your-profile.php">Profile</a>
                    <a class="signout" href="roleaccount.php" onclick="confirmLogout(event)">Sign out</a>
                </div>
            </div>
        </nav>
        
        <div class="sidebar">
            <div class="logo">
                <img src="images/logo.png" alt="Logo">
            </div>

            <ul class="menu">
                <li><a href="admin-dash.php"><img src="images/home.png">Dashboard</a></li>
                <li><a href="admin-projlist.php"><img src="images/project-list.png">Project List</a></li>
                <li><a href="admin-calendar.php"><img src="images/calendar.png">Event Calendar</a></li>

                <!-- Dropdown for Resource Utilization -->
                <button class="dropdown-btn">
                    <img src="images/resource.png"> Resource Utilization
                    <i class="fas fa-chevron-down"></i> <!-- Dropdown icon -->
                </button>
                <div class="dropdown-container">
                    <a href="admin-tor.php">Term of Reference</a>
                    <a href="admin-requi.php">Requisition</a>
                    <a href="admin-venue.php">Venue</a>
                </div>

                <li><a href="admin-budget-utilization.php" class="active"><img src="images/budget.png">Budget Allocation</a></li>

                <!-- Dropdown for Task Management -->
                <li><a href="admin-mov.php"><img src="images/task.png">Mode of Verification</a></li>

                <li><a href="admin-responses.php"><img src="images/feedback.png">Responses</a></li>

                <!-- Dropdown for Audit Trails -->
                <button class="dropdown-btn">
                    <img src="images/logs.png"> Audit Trails
                    <i class="fas fa-chevron-down"></i> <!-- Dropdown icon -->
                </button>
                <div class="dropdown-container">
                    <a href="admin-history.php">Log In History</a>
                    <a href="admin-logs.php">Activity Logs</a>
                </div>
            </ul>
        </div>

        <div class="content-budget">
    <div class="button-container">
        <button class="filter-button" onclick="filterTable('cas')">CAS</button>
        <button class="filter-button" onclick="filterTable('cba')">CBA</button>
        <button class="filter-button" onclick="filterTable('ccs')">CCS</button>
        <button class="filter-button" onclick="filterTable('coed')">COED</button>
        <button class="filter-button" onclick="filterTable('coe')">COE</button>
        <button class="filter-button" onclick="filterTable('cihm')">CIHM</button>
        <button class="filter-button" onclick="filterTable('con')">CON</button>
    </div>
    <?php
// Database credentials
$servername = "alv4v3hlsipxnujn.cbetxkdyhwsb.us-east-1.rds.amazonaws.com";
$username = "ctk6gpo1v7sapq1l";
$password = "u1cgfgry8lu5rliz";
$dbname = "oshzbyiasuos5kn4"; // Database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch allotted budget for each department
$allottedBudgetSql = "SELECT department_name, allotted_budget FROM allotted_budget";
$allottedBudgetResult = $conn->query($allottedBudgetSql);
$allottedBudgets = [];

if ($allottedBudgetResult && $allottedBudgetResult->num_rows > 0) {
    while ($row = $allottedBudgetResult->fetch_assoc()) {
        $allottedBudgets[$row['department_name']] = $row['allotted_budget'];
    }
}

$colleges = [
    'cas' => 'cas_budget',
    'cba' => 'cba_budget',
    'ccs' => 'ccs_budget',
    'coed' => 'coed_budget',
    'coe' => 'coe_budget',
    'cihm' => 'cihm_budget',
    'con' => 'con_budget'
];
foreach ($colleges as $college => $budgetTable) {
    echo "<div id='{$college}_section' class='college-section' style='display: none;'>"; // Hidden by default

    // Title for the College
    echo "<h3 style = 'font-size:30px;'>" . strtoupper($college) . " - Budget Allocation</h3>";

    // Fetch the allotted budget for the current college
    $allottedBudget = isset($allottedBudgets[strtoupper($college)]) ? number_format($allottedBudgets[strtoupper($college)], 2) : 'Not Available';

    // Calculate the total expenses for the current college
    $expensesSql = "SELECT SUM(expenses) AS total_expenses FROM $budgetTable";
    $expensesResult = $conn->query($expensesSql);
    $remainingBudget = 0;

    if ($expensesResult && $expensesResult->num_rows > 0) {
        $expenseRow = $expensesResult->fetch_assoc();
        $totalExpenses = $expenseRow['total_expenses'] ? $expenseRow['total_expenses'] : 0;
        $remainingBudget = $allottedBudgets[strtoupper($college)] - $totalExpenses;
    }

    // Display the allotted budget
    echo "<p class='allotted-budget'><strong>Allotted Budget: </strong>
            <input type='text' class='allotted-budget' id='{$college}_budget' value='" . $allottedBudget . "' />
            <button class='update-budget-btn' onclick='updateBudget(\"{$college}\")'>Update</button>
          </p>";

    // Display the remaining budget
    echo "<p class='remaining-budget'><strong>Remaining Budget: </strong>
            <input type='text' class='remaining-budget' id='{$college}_remaining_budget' value='" . number_format($remainingBudget, 2) . "' disabled />
          </p>";
    
    // Fetch budget records for the budget table
    $budgetSql = "SELECT * FROM $budgetTable ORDER BY details_id";
    $budgetResult = $conn->query($budgetSql);

    // Budget Table: Event Budget Details
    echo "<div class='table-container'>";
    echo "<table class='crud-table'>";
    echo "<thead>
            <tr>
                <th>ID</th>
                <th>Event Title</th>
                <th>Semester</th>
                <th>Expenses</th>
            </tr>
        </thead>
        <tbody>";

    if ($budgetResult && $budgetResult->num_rows > 0) {
        $budgetData = [];

        // Group budget details by details_id
        while ($row = $budgetResult->fetch_assoc()) {
            $budgetData[$row['details_id']][] = $row; // Grouping items by details_id
        }

        // Output rows with calculated rowspans for details_id
        foreach ($budgetData as $detailsId => $items) {
            $firstRow = true; // Track the first row for details_id

            foreach ($items as $index => $item) {
                echo "<tr>";

                // Display Details ID only once per unique details_id
                if ($firstRow) {
                    echo "<td rowspan='" . count($items) . "'>{$detailsId}</td>";
                    $firstRow = false; // Set to false after the first row
                }

                // Output remaining columns for the current item
                echo "<td>{$item['proj_title']}</td>
                      <td>{$item['semester']}</td>
                      <td>" . number_format((float)str_replace(',', '', $item['expenses']), 2) . "</td>
                      </tr>";
            }
        }
    } else {
        echo "<tr><td colspan='5'>No budget event details found for this college</td></tr>";
    }

    echo "</tbody></table></div>";

    echo "</div>"; // End of college section
}


$conn->close(); // Close the connection after processing
?>

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
                popup: 'smaller-alert' // Custom class for further styling if needed
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
            fetch('logout.php?action=logout')
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


    function filterTable(college) {
        // Hide all college sections
        const sections = document.querySelectorAll('.college-section');
        sections.forEach(section => {
            section.style.display = 'none';
        });

        // Show the selected college section
        const selectedSection = document.getElementById(`${college}_section`);
        if (selectedSection) {
            selectedSection.style.display = 'block';
        }
    }

    // Show CAS section by default on page load
    document.addEventListener('DOMContentLoaded', function() {
        filterTable('cas');
    });

    function updateBudget(college) {
    // Get the updated budget value
    const updatedBudget = document.getElementById(`${college}_budget`).value;

    // Create an AJAX request to send the updated value to the server
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'admin-update_budget.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    // Send the data (college and updated budget) to the server
    xhr.send(`college=${college}&updatedBudget=${updatedBudget}`);

    // Handle the response from the server
    xhr.onload = function() {
        if (xhr.status == 200) {
            // Update the remaining budget field based on the updated budget and new expenses
            updateRemainingBudget(college);

            // Show SweetAlert success message
            Swal.fire({
                icon: 'success',
                title: 'Budget Updated',
                text: 'The budget has been successfully updated!',
                confirmButtonText: 'OK'
            });
        } else {
            // Show SweetAlert error message
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'There was an error updating the budget!',
                confirmButtonText: 'OK'
            });
        }
    };
}


function updateRemainingBudget(college) {
    // Fetch the current remaining budget for the selected college
    const remainingBudgetInput = document.getElementById(`${college}_remaining_budget`);
    
    // Get the total expenses for the current college from the server
    const xhr = new XMLHttpRequest();
    xhr.open('GET', `admin-get_remaining_budget.php?college=${college}`, true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            // Update the remaining budget input with the new value
            remainingBudgetInput.value = xhr.responseText; // Response contains the updated remaining budget
        } else {
            alert('Error fetching remaining budget!');
        }
    };
    xhr.send();
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

            // Log clicks on the "Profile" link
            document.querySelector('.dropdown-menu a[href="cas-your-profile.php"]').addEventListener("click", function() {
                logAction("Profile");
            });
        });

</script>
</body>
</html>
