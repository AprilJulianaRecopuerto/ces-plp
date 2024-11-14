<?php
session_start();

$servername = "localhost"; // Database server
$db_username = "root"; // Database username (change as needed)
$db_password = ""; // Database password (change as needed)
$dbname = "user_registration"; // Database name

// Create connection
$conn = new mysqli($servername, $db_username, $db_password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if user is logged in
if (!isset($_SESSION['uname'])) {
    header("Location: loginpage.php");
    exit;
}

// Get the logged-in user's username from the session
$username = $_SESSION['uname'];

// Fetch user details from the database using prepared statement
$stmt = $conn->prepare("SELECT * FROM colleges WHERE uname = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Retrieve user details and store them in session variables
    $row = $result->fetch_assoc();
    $_SESSION['role'] = $row['role'];
    $_SESSION['id_number'] = $row['id'];
    $_SESSION['department'] = $row['department'];
    $_SESSION['picture'] = $row['picture']; // Store the profile image path
    $db_password_hash = $row['password']; // Store the password hash
} else {
    echo "User not found.";
    exit;
}

// Handle Change Password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['current_password'], $_POST['new_password'], $_POST['confirm_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate new password and confirmation
    if ($new_password !== $confirm_password) {
        echo "<script>alert('New passwords do not match.');</script>";
    } else {
        // Fetch the user's current password from the database
        $password_query = "SELECT password FROM colleges WHERE uname = '$username'";
        $password_result = $conn->query($password_query);

        if ($password_result->num_rows > 0) {
            $password_row = $password_result->fetch_assoc();
            $db_password = $password_row['password'];

            // Check if the current password is correct (for plain text passwords)
            if ($current_password === $db_password) {
                // Update the new password in the database
                $update_sql = "UPDATE colleges SET password = '$new_password' WHERE uname = '$username'";
                if ($conn->query($update_sql) === TRUE) {
                    echo "<script>alert('Password changed successfully.');</script>";
                } else {
                    echo "<script>alert('Error updating password.');</script>";
                }
            } else {
                echo "<script>alert('Current password is incorrect.');</script>";
            }
        } else {
            echo "<script>alert('User not found.');</script>";
        }
    }
}

// Close the statements and connection
$stmt->close();
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

        .content-settings {
            margin-left: 340px; /* Align content with sidebar */
            padding-top: 90px; /* Space for navbar */
            padding: 20px;
            display: flex; /* Use flexbox to center the content */
            justify-content: center; /* Center the content horizontally */
            align-items: center; /* Center the content vertically */
            height: calc(100vh - 100px); /* Full height minus navbar */
           
        }

        .profile-section {
            width: 300px; /* Adjust width as needed */
            height: 390px;
            padding: 20px;
            margin-top: 90px;
            margin-right: 25px;
            border: 1px solid #ccc;
            border-radius: 8px;
            text-align: center;
            background-color: #f9f9f9; /* Light background color */
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1); /* Subtle shadow */
        }

        .profile-section img {
            width: 170px; /* Increased size of the profile picture */
            height: 170px; /* Set the size of the profile picture */
            border-radius: 50%; /* Circular frame */
        }

        .profile-pic {
            width: 220px; /* Adjust size as needed */
            height: 220px; /* Adjust size as needed */
            border-radius: 50%; /* Circular profile picture */
            object-fit: cover; /* Ensures the image covers the area */
            margin-bottom: 10px; /* Space between image and ID number */
        }

        .profile-section p {
            font-family: 'Poppins', sans-serif;
            font-size: 17px; /* Font size for ID number */
            color: #333; /* Text color */
            text-align: left;
        }

        .info-section {
            font-family: 'Poppins', sans-serif;
            font-size: 20px; /* Font size for ID number */
            width: 970px; /* Adjust width as needed */
            height: 200px;
            border: 1px solid #ccc;
            margin-right: 20px;
            margin-top: -138px;
            border-radius: 8px;
            text-align: left;
            background-color: #f9f9f9; /* Light background color */
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1); /* Subtle shadow */
        }

        .info-section p,h3 {
            padding-top: 15px;
            padding-bottom: 15px;
            padding-left: 25px;
            line-height: 2px;
        }

        .buttons {
            margin-top: -250px;
            margin-left: 680px;
        }

        .change-password-button,
        .edit-profile-button {
            background-color: #22901C; /* Button color */
            color: white; /* Text color */
            border: none; /* Remove border */
            border-radius: 5px; /* Rounded corners */
            padding: 12px 20px; /* Increased padding for button */
            cursor: pointer; /* Cursor change on hover */
            font-size: 18px; /* Increased font size */
            font-family: "Poppins", sans-serif; /* Font family */
            margin-right: 10px;
            text-decoration: none;
            display: inline-block; /* Keeps it inline while allowing margins */
            transition: background-color 0.3s; /* Smooth transition for hover effect */
        }

        .change-password-button:hover,
        .edit-profile-button:hover {
            background-color: #1a7d13; /* Darker green on hover */
        }

        .profile-placeholder-letter {
            font-family: "Poppins", sans-serif; /* Font family */
            width: 170px; /* Adjust size as needed */
            height: 170px; /* Adjust size as needed */
            border-radius: 50%; /* Circular profile picture */
            object-fit: cover; /* Ensures the image covers the area */
            background-color: #ccc; /* Placeholder background color */
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 35px; /* Font size for the letter */
            color: green;
            font-weight: bold; /* Make it bold if desired */
            margin-left: 45px;
            margin-bottom: 40px;
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
    </style>
</head>

    <body>
        <nav class="navbar">
            <h2>Profile</h2> 

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
                    <a href="cihm-your-profile.php">Profile</a>
                    <a class="signout" href="roleaccount.php" onclick="confirmLogout(event)">Sign out</a>
                </div>
            </div>
        </nav>
        
        <div class="sidebar">
            <div class="logo">
                <img src="images/logo.png" alt="Logo">
            </div>

            <ul class="menu">
                <li><a href="cihm-dash.php"><img src="images/home.png">Dashboard</a></li>
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
                    <a href="cihm-login.php">Log In History</a>
                    <a href="cihm-activitylogs.php">Activity Logs</a>
                </div>
            </ul>
        </div>

        <div class="content-settings">
            <div class="profile-section">
                <?php 
                    // Check if a profile picture is set in the session
                    if (!empty($_SESSION['picture'])) {
                        // Show the profile picture
                        echo '<img src="' . htmlspecialchars($_SESSION['picture']) . '" alt="Profile Picture">';
                    } else {
                        // Use a default placeholder image
                        echo '<div class="profile-placeholder-letter">' . htmlspecialchars($firstLetter) . '</div>'; //New class
                    }
                ?>

                <p>ID Number: <?php echo $_SESSION['id_number']; ?></p>
                <p>Department: <?php echo $_SESSION['department']; ?></p>
                <p>Role: <?php echo $_SESSION['role']; ?></p>
            </div>
        
            <div class="info-section">
            <h3>Account Information</h3>
                <p>Name: <?php echo $_SESSION['uname']; ?></p>
                <p>Email: <?php echo $row['email']; // Assuming you have an email column ?></p>
            </div>
            </div>
        </div>

        <div class= "buttons">
            <a href="cihm-edit-profile.php" class="edit-profile-button">Change Profile Picture</a>
            <a href="cihm-change-password.php" class="change-password-button">Change Password</a>
        </div>

        <script>
            function confirmLogout(event) {
                event.preventDefault(); // Prevent the default link behavior
                Swal.fire({
                    title: 'Are you sure?',
                    text: "Do you really want to log out?",
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, log me out',
                    customClass: {
                        popup: 'custom-swal-popup',
                        confirmButton: 'custom-swal-confirm',
                        cancelButton: 'custom-swal-cancel'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'roleaccount.php'; // Redirect to the logout page
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
        </script>
    </body>
</html>
