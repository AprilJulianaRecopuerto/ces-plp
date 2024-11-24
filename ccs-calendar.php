<?php
session_start(); // Start a session

// Check if the user is logged in
if (!isset($_SESSION['uname'])) {
    header("Location: roleaccount.php");
    exit;
}

// Database credentials for proj_list
$servername_proj = "ryvdxs57afyjk41z.cbetxkdyhwsb.us-east-1.rds.amazonaws.com";
$username_proj = "zf8r3n4qqjyrfx7o";
$password_proj = "su6qmqa0gxuerg98";
$dbname_proj_list = "hpvs3ggjc4qfg9jp";

// Create connection to proj_list database
$conn_proj_list = new mysqli($servername_proj, $username_proj, $password_proj, $dbname_proj_list);

// Check connection
if ($conn_proj_list->connect_error) {
    die("Connection failed: " . $conn_proj_list->connect_error);
}

// Check if 'month' and 'year' parameters are present in the GET request for event days
if (isset($_GET['month']) && isset($_GET['year'])) {
    // Get the selected month and year from the request
    $selectedMonth = $_GET['month'];
    $selectedYear = $_GET['year'];

    // Initialize an array to store event dates
    $eventDays = [];

    // List of tables to query
    $tables = ['ccs'];

    foreach ($tables as $table) {
        // Use prepared statements to prevent SQL injection
        $stmt = $conn_proj_list->prepare("SELECT DAY(dateof_imple) AS event_day FROM $table WHERE MONTH(dateof_imple) = ? AND YEAR(dateof_imple) = ?");
        $stmt->bind_param("ii", $selectedMonth, $selectedYear);
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if the query was successful
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                // Add the event day to the eventDays array
                $eventDays[] = (int) $row['event_day'];
            }
        }

        $stmt->close();
    }

    // Return the event days in JSON format
    echo json_encode($eventDays);
    exit;
}

// Check if a 'date' parameter is present in the GET request for event details
if (isset($_GET['date'])) {
    // Get the selected date from the request
    $selectedDate = $_GET['date'];

    // Validate the date format (YYYY-MM-DD)
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $selectedDate)) {
        echo "<p style='color: red;'>Invalid date format.</p>";
        exit;
    }

    // Fetch events for the specified date from each table
    $events = []; // Initialize an array to store events
    $tables = ['cas', 'cba', 'ccs','coed','coe','cihm','coe']; // List of tables to query

    foreach ($tables as $table) {
        // Use prepared statements to prevent SQL injection
        $stmt = $conn_proj_list->prepare("SELECT dept, proj_title, dateof_imple FROM $table WHERE dateof_imple = ?");
        $stmt->bind_param("s", $selectedDate);
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if the query was successful
        if ($result) {
            while ($row = $result->fetch_assoc()) { // Fetch each row
                // Add the event to the events array
                $events[] = [
                    'department' => htmlspecialchars($row['dept']),          // Escape output for security
                    'title'      => htmlspecialchars($row['proj_title']),    // Escape output for security
                    'date'       => htmlspecialchars($row['dateof_imple'])   // Escape output for security
                ];
            }
        } else {
            echo "<p style='color: red;'>Error in query for $table: " . htmlspecialchars($conn_proj_list->error) . "</p>"; // Output error if the query fails
            exit;
        }

        $stmt->close();
    }

    // Close the connection
    $conn_proj_list->close();

    // Output the events in a structured way
    echo "<p style='font-weight: bold; 
            font-size: 23px; 
            color: black; 
            margin-left: 10px;
            margin-bottom: 5px;
            margin-top: 10px;'>Event Details</p>"; // Styled "Event Details" text

    if (empty($events)) {
        // Message when no events are found
        echo "<p style='color: red;'>No events found for this date.</p>"; // Style for no event message
    } else {
        foreach ($events as $event) {
            echo "<div style='border-bottom: 1px solid #ddd; padding: 10px 0;'>";

            echo "<p style='font-size: 16px; 
                    color: black;   
                    margin-left: 10px;
                    margin-top: 5px;'>
                    <strong>Department:</strong> " . htmlspecialchars($event['department']) . "
                </p>";

            echo "<p style='font-size: 15px;  
                    margin-left: 10px;'>Project Title: " . $event['title'] . "</p>";

            echo "<p style='font-size: 15px; 
                    color: black;   
                    margin-left: 10px;'>Date: " . $event['date'] . "</p>";
            echo "</div>";
        }
    }

    exit; // Stop executing the rest of the file.
}

// Close the database connection if no specific query is made
$conn_proj_list->close();

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
                overflow-y: hidden;
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

            .content-cal {
                margin-left: 320px;
                padding: 20px;
            }

            .calendar-container {
                font-family: "Poppins", sans-serif;
                background: #ffffcc; /* Matching the yellowish background */
                padding: 20px;
                width: 1130px;
                height: 520px !important;
                border-radius: 5px;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                display: flex;
                margin-top: 104px;
            }

            .calendar-container #month, #year {
                font-family: "Poppins", sans-serif;
                font-size: 15px; /* Increased font size */
            }

            .calendar {
                display: grid;
                grid-template-columns: repeat(7, 1fr);
                gap: 2px; /* Reduce gap size */
                text-align: center;
                margin-top: 10px;
            }

            .day {
                padding: 18px; /* Increase padding for better spacing */
                border: 1px solid #ddd;
                border-radius: 3px;
                background-color: #ffffe0; /* Light yellow background for cells */
                cursor: pointer;
                user-select: none; /* Prevent text selection */
                width: 60px;
                height: -40px !important;
            }

            .header {
                font-weight: bold;
                background-color: #ffffcc; /* Same background as the body */
                margin-top: 5px;
            }

            select {
                margin: 10px;
                padding: 10px;
                border: 1px solid #ccc;
                border-radius: 5px;
            }   

            .event-details {
                margin-left: 30px;
                padding: 10px;
                border: 1px solid #ddd;
                border-radius: 5px;
                background-color: #fff;
                width: 500px;
            }

            h2 {
                margin-top: 5px;
                margin-bottom: 10px; /* Reduce margin */
            }

            h3 {
                font-size: 23px;
                margin-left: 10px;
                margin-bottom: 5px;
                margin-top: 10px;
            }

            p {
                margin-left: 10px;
            }

            .event-day {
                background-color: #ffd700; /* Dark yellow for event days */
                font-weight: bold;
                color: #333; /* Change text color for visibility */
            }

            .custom-swal-popup {
                font-family: "Poppins", sans-serif !important;
                width: 400px;
            }

            .custom-swal-confirm {
                font-family: "Poppins", sans-serif !important;
            }

            .custom-swal-cancel {
                font-family: "Poppins", sans-serif !important;
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
            <h2>Community Extension Event Calendar</h2> 

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
                <li><a href="ccs-calendar.php" class="active"><img src="images/calendar.png">Event Calendar</a></li>

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
        
        <div class="content-cal">
            <!-- Calendar Container -->
            <div class="calendar-container">
                <div>
                    <select id="month" onchange="updateCalendar()"></select>
                    <select id="year" onchange="updateCalendar()"></select>
                    <div class="calendar" id="calendar"></div>
                </div>
                <div class="event-details" id="eventDetails">
                    <h3>Event Details</h3>
                    <p>Select a date to see the event details.</p>
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

            // Dropdown elements
            const monthDropdown = document.getElementById('month');
            const yearDropdown = document.getElementById('year');
            const calendar = document.getElementById('calendar');
            const eventDetails = document.getElementById('eventDetails');
            const monthNames = [
                'January', 'February', 'March', 'April', 'May', 'June',
                'July', 'August', 'September', 'October', 'November', 'December'
            ];

            // Populate month dropdown
            monthNames.forEach((month, index) => {
                const option = document.createElement('option');
                option.value = index; // Month values start from 0
                option.textContent = month;
                monthDropdown.appendChild(option);
            });

            // Populate year dropdown
            const currentYear = new Date().getFullYear();
            for (let i = currentYear; i >= currentYear - 100; i--) {
                const option = document.createElement('option');
                option.value = i;
                option.textContent = i;
                yearDropdown.appendChild(option);
            }

            // Initialize calendar
            const initMonth = new Date().getMonth();
            const initYear = currentYear;
            monthDropdown.value = initMonth;
            yearDropdown.value = initYear;
            updateCalendar();

            let activeDayCell = null; // To keep track of the currently active day

            function updateCalendar() {
            const month = parseInt(monthDropdown.value);
            const year = parseInt(yearDropdown.value);
            calendar.innerHTML = '';

            const firstDay = new Date(year, month, 1);
            const lastDay = new Date(year, month + 1, 0);
            const totalDays = lastDay.getDate();

            // Fetch event days for the selected month and year
            fetch(`<?php echo basename(__FILE__); ?>?month=${month + 1}&year=${year}`)
            .then(response => response.json())
            .then(eventDays => {
                // Add headers for days of the week
                const daysOfWeek = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
                daysOfWeek.forEach(day => {
                    const dayHeader = document.createElement('div');
                    dayHeader.className = 'day header';
                    dayHeader.textContent = day;
                    calendar.appendChild(dayHeader);
                });

                // Add empty cells for days before the first day of the month
                for (let i = 0; i < firstDay.getDay(); i++) {
                    const emptyCell = document.createElement('div');
                    emptyCell.className = 'day';
                    calendar.appendChild(emptyCell);
                }

                // Add days of the month
                for (let day = 1; day <= totalDays; day++) {
                    const dayCell = document.createElement('div');
                    dayCell.className = 'day';
                    dayCell.textContent = day;

                    // Check if the current day has an event
                    if (eventDays.includes(day)) {
                        dayCell.classList.add('event-day'); // Highlight the day
                    }

                    // Add click event listener to each day cell
                    dayCell.addEventListener('click', () => {
                        const selectedDate = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;

                        // Remove 'active' class from previously active day
                        if (activeDayCell) {
                            activeDayCell.classList.remove('active');
                        }

                        // Add 'active' class to the clicked day
                        dayCell.classList.add('active');
                        activeDayCell = dayCell;

                        displayEventDetails(selectedDate);
                    });

                    calendar.appendChild(dayCell);
                    }
                });
            }

            function displayEventDetails(date) {
                // Fetch event details from the server (same file with 'date' parameter)
                const xhr = new XMLHttpRequest();
                xhr.open('GET', `<?php echo basename(__FILE__); ?>?date=${date}`, true);
                xhr.onload = function () {
                    if (this.status === 200) {
                        // Update the event details section with the response
                        eventDetails.innerHTML = this.responseText;
                    } else {
                        eventDetails.innerHTML = '<p style="color: red;">Error fetching event details.</p>';
                    }
                };
                xhr.send();
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
