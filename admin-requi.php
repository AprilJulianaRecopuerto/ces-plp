<?php
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: loginpage.php");
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
                margin-left: 340px; /* Align with the sidebar */
                padding: 20px;
            }

            .content-venue h3 {
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

            .data-details .table-container {
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
            .data-details {
                font-family: 'Poppins', sans-serif;
                margin-left: 10px;
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
            <h2>Colleges - Requisition Details</h2> 

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
                    <a href="admin-requi.php" class="active">Requisition</a>
                    <a href="admin-venue.php">Venue</a>
                </div>

                <li><a href="admin-budget-utilization.php"><img src="images/budget.png">Budget Allocation</a></li>

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

        <div class="content-venue">

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

            // Array of colleges
            $colleges = ['cas', 'cba', 'ccs', 'coed', 'coe', 'cihm', 'con'];

            foreach ($colleges as $college) {
                echo "<div id='{$college}_section' class='college-section' style='display: none;'>"; // Hidden by default

                echo "<h3>" . strtoupper($college) . " - Requisition Form Details</h3>";

                // Fetch requisition records for each college
                $requisitionTable = "{$college}_requisition";
                $requisitionSql = "SELECT * FROM $requisitionTable";
                $requisitionResult = $conn->query($requisitionSql);

                echo "<div class='table-container'>";
                echo "<table class='crud-table'>";
                echo "<thead>
                        <tr>
                            <th>ID</th>
                            <th>Date</th>
                            <th>Name</th>
                            <th>Position</th>
                            <th>College Name</th>
                        </tr>
                    </thead>
                    <tbody>";

                if ($requisitionResult && $requisitionResult->num_rows > 0) {
                    while ($row = $requisitionResult->fetch_assoc()) {
                        echo "<tr>
                                <td>{$row['requisition_id']}</td>
                                <td>{$row['date']}</td>
                                <td>{$row['name']}</td>
                                <td>{$row['position']}</td>
                                <td>{$row['college_name']}</td>
                            </tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>No requisitions found for this college</td></tr>";
                }
                echo "</tbody></table></div>";

                echo "<h3>" . strtoupper($college) . " - Items for Requisitions</h3>";

                // Fetch items associated with requisitions for each college
                $itemsTable = "{$college}_items";
                $itemsSql = "SELECT * FROM $itemsTable ORDER BY requisition_id";
                $itemsResult = $conn->query($itemsSql);

                echo "<div class='table-container'>";
                echo "<table class='crud-table'>";
                echo "<thead>
                        <tr>
                            <th>Requisition ID</th>
                            <th>Item Name</th>
                            <th>Total Items Requested</th>
                            <th>Total Usage</th>
                            <th>Utilization %</th>
                        </tr>
                    </thead>
                    <tbody>";

                if ($itemsResult && $itemsResult->num_rows > 0) {
                    $itemsData = [];
                    
                    // Group items by requisition_id
                    while ($row = $itemsResult->fetch_assoc()) {
                        $itemsData[$row['requisition_id']][] = $row; // Grouping items by requisition_id
                    }

                    // Output rows with calculated rowspans for requisition_id
                    foreach ($itemsData as $requisitionId => $items) {
                        $firstRow = true; // Track the first row for requisition_id

                        foreach ($items as $index => $item) {
                            echo "<tr>";

                            // Display Requisition ID only once per unique requisition_id
                            if ($firstRow) {
                                echo "<td rowspan='" . count($items) . "'>{$requisitionId}</td>";
                                $firstRow = false; // Set to false after the first row
                            }

                            // Output remaining columns for the current item
                            echo "<td>{$item['item_name']}</td>
                                <td>{$item['total_items']}</td>
                                <td>{$item['total_usage']}</td>
                                <td>{$item['utilization_percentage']}%</td>
                                </tr>";
                        }
                    }
                } else {
                    echo "<tr><td colspan='5'>No items found for requisitions in this college</td></tr>";
                }
                echo "</tbody></table></div>";

                echo "</div>"; // End of college section
            }

            $conn->close();
            ?>
        </div>

        <script>
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

            var dropdown = document.getElementsByClassName("dropdown-btn");
                var i;

                for (i = 0; i < dropdown.length; i++) {
                dropdown[i].addEventListener("click", function() {
                    var dropdownContent = this.nextElementSibling;
                    if (dropdownContent.style.display === "block") {
                    dropdownContent.style.display = "none";
                    } else {
                    dropdownContent.style.display = "block";
                    }
                });
            }

            document.addEventListener('DOMContentLoaded', function () {
        const username = "<?php echo $_SESSION['username']; ?>"; // Get the username from PHP session

        // Function to log activity
        function logActivity(buttonName) {
            const timestamp = new Date().toISOString(); // Get current timestamp

            fetch('log_activity.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    buttonFunction: buttonName // Updated to match the PHP variable
                }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'error') {
                    console.error('Error logging activity:', data.message);
                } else {
                    console.log('Activity logged successfully:', data);
                }
            })
            .catch(error => {
                console.error('Error logging activity:', error);
            });
        }

        // Add event listeners specifically to buttons and links
        const trackableElements = document.querySelectorAll('button, a'); // Select all buttons and links
        trackableElements.forEach(element => {
            element.addEventListener('click', function (event) {
                const buttonName = this.tagName === 'BUTTON' ? this.innerText.trim() || "Unnamed Button" : this.textContent.trim() || "Unnamed Link";
                logActivity(buttonName); // Log the button/link activity
            });
        });
    });
        </script>
    </body>
</html>