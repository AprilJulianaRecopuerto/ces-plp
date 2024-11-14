<?php

session_start();
    if (!isset($_SESSION['uname'])) {
        // Redirect to login page if the session variable is not set
        header("Location: collegelogin.php");
        exit;
    }

$currentUser = $_SESSION['uname']; // Get the currently logged-in username

// Database credentials
$servername = "localhost";
$username = "root"; // your database username
$password = ""; // your database password
$dbname = "user_registration"; // your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
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
            margin-left: 310px; /* Align with the sidebar */
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
            margin-left: 10px;
            overflow-x: auto;
            margin-top: 20px; /* Space above the table */
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
		
.swal2-popup {
    border-radius: 10px; /* Rounded corners */
    font-family: 'Arial', sans-serif; /* Consistent font */
    background: #ffffff; /* White background */
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15); /* Soft shadow */
    width: 450px; /* Set a fixed width for all pop-ups */
    padding: 20px; /* Consistent padding inside pop-ups */
    box-sizing: border-box; /* Include padding in width */
}

/* Custom styles for SweetAlert title */
.swal2-title {
    font-size: 24px; /* Font size for title */
    font-weight: bold; /* Bold title */
    color: #333; /* Darker color for contrast */
    margin: 0; /* No margin for title */
    text-align: center; /* Center the title */
}

/* Custom styles for SweetAlert content */
.swal2-content {
    font-size: 18px; /* Font size for content */
    color: #555; /* Lighter color for content */
    text-align: center; /* Center the text */
    margin: 10px 0; /* Small margin above and below text for spacing */
}

/* Button styles */
.swal2-confirm {
    background-color: #28a745; /* Green confirm button */
    color: white; /* Confirm button text color */
    border: none; /* Remove border */
    border-radius: 5px; /* Rounded corners */
    padding: 10px 20px; /* Padding */
    font-weight: bold; /* Bold text */
    width: 100%; /* Full width button */
}

.swal2-confirm:hover {
    background-color: #218838; /* Darker shade on hover */
}

/* Error styles */
.swal2-error {
    background-color: #dc3545; /* Red error background */
    color: white; /* Error text color */
}

.swal2-error:hover {
    background-color: #c82333; /* Darker shade on hover */
}

/* Loading spinner styles */
.swal2-loading {
    color: #007bff; /* Loading spinner color */
}

/* Cancel button styles */
.swal2-cancel {
    background-color: #6c757d; /* Gray cancel button */
    color: white; /* Cancel button text color */
}

.swal2-cancel:hover {
    background-color: #5a6268; /* Darker shade on hover */
}

/* Set a uniform height for all pop-ups */
.custom-popup {
    height: auto !important; /* Set height to auto to accommodate content */
}

    </style>
</head>

<body>
    <nav class="navbar">
        <h2>Login History</h2> 

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
                    <a href="cba-your-profile.php">Profile</a>
                    <a class="signout" href="roleaccount.php" onclick="confirmLogout(event)">Sign out</a>
                </div>
            </div>
        </nav>
        
        <div class="sidebar">
            <div class="logo">
                <img src="images/logo.png" alt="Logo">
            </div>

            <ul class="menu">
                <li><a href="cihm-dash.php" class="active"><img src="images/home.png">Dashboard</a></li>
                <li><a href="cihm-projlist.php"><img src="images/project-list.png">Project List</a></li>
                <li><a href="cihm-calendar.php"><img src="images/calendar.png">Event Calendar</a></li>

                <!-- Dropdown for Resource Utilization -->
                <button class="dropdown-btn">
                    <img src="images/resource.png"> Resource Utilization
                    <i class="fas fa-chevron-down"></i> <!-- Dropdown icon -->
                </button>
                <div class="dropdown-container">
                    <a href="cihm-tor.php">Term of Reference</a>
                    <a href="cihm-requi.php">Requisition</a>
                    <a href="cihm-venue.php">Venue</a>
                </div>

                <li><a href="cihm-budget-utilization.php"><img src="images/budget.png">Budget Allocation</a></li>

                <!-- Dropdown for Task Management -->
                <button class="dropdown-btn">
                    <img src="images/task.png">Task Management
                    <i class="fas fa-chevron-down"></i> <!-- Dropdown icon -->
                </button>
                <div class="dropdown-container">
                    <a href="cihm-task.php">Upload Files</a>
                    <a href="cihm-mov.php">Mode of Verification</a>
                </div>

                <li><a href="responses.php"><img src="images/setting.png">Responses</a></li>

                <!-- Dropdown for Audit Trails -->
                <button class="dropdown-btn">
                    <img src="images/resource.png"> Audit Trails
                    <i class="fas fa-chevron-down"></i> <!-- Dropdown icon -->
                </button>
                <div class="dropdown-container">
                    <a href="cihm-history.php">Log In History</a>
                    <a href="cihm-activitylogs.php">Activity Logs</a>
                </div>
            </ul>
        </div>
    
<div class="content-projectlist">
        
<?php
session_start(); // Start the session

// Database credentials
$servername = "localhost";
$username = "root"; // your database username
$password = ""; // your database password
$dbname = "user_registration"; // your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if a user is logged in
if (isset($_SESSION['uname'])) {
    $currentUser = $_SESSION['uname']; // Get the current logged-in username

    // Fetch login and logout timestamps for the current user
    $sql = "SELECT uname, ts, logout_ts FROM college_history WHERE uname = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $currentUser);
    $stmt->execute();
    $result = $stmt->get_result();

    // HTML structure for displaying the table
    echo '<div class="content">
            <h2>Log In</h2>
            <div class="table-container">
                <table class="crud-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Log In</th>
                            <th>Log Out</th>
                        </tr>
                    </thead>
                    <tbody id="table-body">';

    // Check if there are results
    if ($result->num_rows > 0) {
        // Output data of each row
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>" . htmlspecialchars($row["uname"]) . "</td>
                    <td>" . htmlspecialchars($row["ts"]) . "</td>
                    <td>" . htmlspecialchars($row["logout_ts"]) . "</td>
                  </tr>";
        }
    } else {
        echo "<tr><td colspan='3'>No records found for the current user.</td></tr>";
    }

    echo '       </tbody>
                </table>
            </div>
          </div>';
    
    $stmt->close();
} else {
    echo "No user is currently logged in.";
}

// Close the connection
$conn->close();
?>

    <script>

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
function confirmLogout(event) {
    event.preventDefault();
    Swal.fire({
        title: 'Are you sure?',
        text: "Do you really want to log out?",
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, log me out',
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

    </script>

</body>
</html>
