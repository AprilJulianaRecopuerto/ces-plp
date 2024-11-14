<?php
// Start the session
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: loginpage.php"); // Redirect to login if not logged in
    exit;
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
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

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


        .content-projectlist {
            margin-left: 340px; /* Align with the sidebar */
            padding: 20px;
        }

        .content-projectlist h2 {
            font-family: 'Poppins', sans-serif;
            font-size: 28px; /* Adjust the font size as needed */
            margin-bottom: 20px; /* Space below the heading */
            color: black; /* Adjust text color */
            margin-top: 110px;
        }


        .table-container {
            width: 100%;
            margin-left: -12px;
            overflow-x: auto;
            margin-top: 130px; /* Space above the table */
        }

        .crud-table {
            width: 100%;
            border-collapse: collapse;
            font-family: 'Poppins', sans-serif;
            background-color: #ffffff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            overflow: hidden;
        }

        .crud-table th, .crud-table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
            white-space: nowrap; /* Prevent text from wrapping */
        }

        .crud-table th {
            background-color: #4CAF50;
            color: white;
            height: 40px;
        }

        .crud-table td {
            height: 50px;
            background-color: #fafafa;
        }

        .crud-table tr:hover {
            background-color: #f1f1f1;
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

		
		       .button-container {
            display: flex;
            justify-content: flex-end; /* Align buttons to the right */
            margin-bottom: 20px; /* Space below the buttons */
            margin-right: 20px;
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
		
		        .btn-success {
            background-color: #28a745; /* Green */
            border-color: #28a745;
        }
        .btn-info {
            background-color: #17a2b8; /* Blue */
            border-color: #17a2b8;
        }
        .btn-warning {
            background-color: #ffc107; /* Yellow */
            border-color: #ffc107;
        }
        .btn-cancel {
            background-color: #dc3545; /* Red */
            border-color: #dc3545;
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
                margin-right:15px !important;
                border-radius: 5px;
                font-size: 14px;
                cursor: pointer;
                transition: background-color 0.3s;
                text-decoration: none;
            }

            .pagination-link:hover {
                background-color: #45a049; /* Darker green on hover */
            }

            .pagination-text {
                margin-right: 18px !important;
            }
    </style>
</head>

<body>
    <nav class="navbar">
        <h2>Logs History</h2> 

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

                <li><a href="admin-budget-utilization.php"><img src="images/budget.png">Budget Allocation</a></li>

                <!-- Dropdown for Task Management -->
                <button class="dropdown-btn">
                    <img src="images/task.png">Task Management
                    <i class="fas fa-chevron-down"></i> <!-- Dropdown icon -->
                </button>
                <div class="dropdown-container">
                    <a href="admin-task.php">Upload Files</a>
                    <a href="admin-mov.php">Mode of Verification</a>
                </div>

                <li><a href="admin-responses.php"><img src="images/feedback.png">Responses</a></li>

                <!-- Dropdown for Audit Trails -->
                <button class="dropdown-btn">
                    <img src="images/logs.png"> Audit Trails
                    <i class="fas fa-chevron-down"></i> <!-- Dropdown icon -->
                </button>
                <div class="dropdown-container">
                    <a href="admin-history.php">Log In History</a>
                    <a href="admin-logs.php" class="active">Activity Logs</a>
                </div>
            </ul>
        </div>
    
        <div class="content-projectlist">
        
        <div class="content">
            <div class="table-container">
                <table class="crud-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Functions</th>
							<th>Time Stamp</th>
							
                        </tr>
                    </thead>
                    <tbody id="table-body">
                        <?php
                        // Check if the user is logged in
                        if (isset($_SESSION['username'])) {
                            $loggedInUser = $_SESSION['username'];

                            // Database credentials
                            $servername = "localhost";
                            $username = "root"; 
                            $password = ""; 
                            $dbname = "user_registration"; 

                            // Create connection
                            $conn = new mysqli($servername, $username, $password, $dbname);

                            // Check connection
                            if ($conn->connect_error) {
                                die("Connection failed: " . $conn->connect_error);
                            }

                            $limit = 5; // Number of records per page
                            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Current page
                            $offset = ($page - 1) * $limit; // Offset for SQL query

                            // Count total requisition records
                            $countSql = "SELECT COUNT(*) as total FROM activity_logs";
                            $countResult = $conn->query($countSql);
                            $totalRecords = $countResult->fetch_assoc()['total'];
                            $totalPages = ceil($totalRecords / $limit); // Calculate total pages

                            // Fetch logs for the logged-in user
                            $sql = "SELECT username, button_name, timestamp FROM activity_logs WHERE username = ? ORDER BY id DESC LIMIT $limit OFFSET $offset";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("s", $loggedInUser);
                            $stmt->execute();
                            $result = $stmt->get_result();

                            // Check if there are results
                            if ($result->num_rows > 0) {
                                // Output data of each row
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>
                                            <td>" . htmlspecialchars($row["username"]) . "</td>
                                            <td>" . htmlspecialchars($row["button_name"]) . "</td>
                                            <td>" . htmlspecialchars($row["timestamp"]) . "</td>
                                        </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='3'>No activity found for the current user.</td></tr>";
                            }

                            // Close the statement and connection
                            $stmt->close();
                            $conn->close();
                        } else {
                            echo "<tr><td colspan='3'>User not logged in.</td></tr>";
                        }
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

    
    <script>

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
            fetch('logout.php?action=logout')
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