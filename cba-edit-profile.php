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
    $db_password_hash = $row['password']; // Store the password hash
} else {
    $_SESSION['error'] = 'User not found.';
    header("Location: cba-edit-profile.php");
    exit;
}

// Handle profile picture update
if (isset($_POST['update_profile_picture'])) {
    // Initialize the profile picture path variable
    $profile_picture_path = null;

    // Check if a file has been uploaded
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $file_tmp = $_FILES['profile_picture']['tmp_name'];
        $file_name = basename($_FILES['profile_picture']['name']);
        $upload_dir = 'uploads/'; // Make sure this directory exists and is writable

        // Ensure the file is an image
        $imageFileType = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_types = array('jpg', 'jpeg', 'png', 'gif');
        if (in_array($imageFileType, $allowed_types)) {
            // Create a unique name for the file
            $new_file_name = uniqid() . '.' . $imageFileType;

            // Move the uploaded file to the desired directory
            if (move_uploaded_file($file_tmp, $upload_dir . $new_file_name)) {
                $profile_picture_path = $upload_dir . $new_file_name;
                $_SESSION['success'] = 'Profile picture uploaded successfully.';

                // Update the database with the new profile picture path
                $update_stmt = $conn->prepare("UPDATE colleges SET picture = ? WHERE uname = ?");
                if ($update_stmt === false) {
                    die("Error preparing statement: " . $conn->error);
                }
                $update_stmt->bind_param("ss", $profile_picture_path, $username);

                // Execute the statement
                if ($update_stmt->execute()) {
                    $_SESSION['picture'] = $profile_picture_path; // Update session with new profile picture
                } else {
                    $_SESSION['error'] = 'Error updating profile picture: ' . $update_stmt->error;
                }
                
                // Close the update statement
                $update_stmt->close();
            } else {
                $_SESSION['error'] = 'Error uploading profile picture.';
            }
        } else {
            $_SESSION['error'] = 'Invalid file type. Only JPG, JPEG, PNG & GIF files are allowed.';
        }
    } else {
        $_SESSION['error'] = 'No profile picture uploaded.';
    }
}

// Handle delete profile picture action
if (isset($_POST['delete_picture'])) {
    // Remove the picture from the database and session
    $delete_stmt = $conn->prepare("UPDATE colleges SET picture = NULL WHERE uname = ?");
    $delete_stmt->bind_param("s", $username);
    
    if ($delete_stmt->execute()) {
        // Check if the picture exists in the session
        if (!empty($_SESSION['picture']) && file_exists($_SESSION['picture'])) {
            // Remove the picture file from the server
            unlink($_SESSION['picture']);
        }

        // Clear the picture from the session
        unset($_SESSION['picture']);
        $_SESSION['success'] = 'Profile picture deleted successfully.'; // Success message
    } else {
        $_SESSION['error'] = 'Error deleting profile picture: ' . $delete_stmt->error;
    }

    // Close the delete statement
    $delete_stmt->close();
}

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>CES PLP</title>

        <link rel="icon" href="images/icoon.png">

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
                margin-left: 290px; /* Align content with sidebar */
                padding-top: 90px; /* Space for navbar */
                padding: 20px;
                display: flex; /* Use flexbox to center the content */
                justify-content: center; /* Center the content horizontally */
                align-items: center; /* Center the content vertically */
                height: calc(100vh - 90px); /* Full height minus navbar */
            }

            .profile-section {
                background-color: #fff; /* White background */
                border-radius: 10px; /* Rounded corners */
                padding: 40px; /* Increased padding for bigger look */
                box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3); /* Larger box shadow */
                display: flex; /* Flexbox for alignment */
                align-items: center; /* Center align items vertically */
                gap: 40px; /* Increased space between items */
                max-width: 800px; /* Increased max width for the section */
                width: 100%; /* Allow full width utilization */
                margin: auto; /* Center the section */
            }

            .profile-section img {
                width: 150px; /* Increased size of the profile picture */
                height: 150px; /* Set the size of the profile picture */
                border-radius: 50%; /* Circular frame */
            }
            
            .profile img {
                margin-right: 10px; /* Adjust the value to increase/decrease space */
            }


            .profile-info {
                font-family: "Poppins", sans-serif;
                color: black;
            }

            .profile-info h3 {
                margin: 0; /* Remove default margin */
                font-size: 28px; /* Increased font size for name */
            }

            .profile-info p {
                margin: 10px 0; /* Space between paragraphs */
                font-size: 20px; /* Increased font size for role and ID */
            }

            .change-password-button,
            .edit-profile-button {
                background-color: #22901C; /* Button color */
                color: white; /* Text color */
                border: none; /* Remove border */
                border-radius: 5px; /* Rounded corners */
                padding: 15px 20px; /* Increased padding for button */
                cursor: pointer; /* Cursor change on hover */
                font-size: 18px; /* Increased font size */
                font-family: "Poppins", sans-serif; /* Font family */
                margin-top: 10px; /* Space above the button */
                transition: background-color 0.3s; /* Smooth transition for hover effect */
            }

            .change-password-button:hover,
            .edit-profile-button:hover {
                background-color: #1a7d13; /* Darker green on hover */
            }

            .content-editor{
                margin-left: 340px; /* Align with the sidebar */
                padding: 20px;
            }

            .content-editor h2 {
                font-family: 'Poppins', sans-serif;
                font-size: 28px; /* Adjust the font size as needed */
                margin-bottom: 20px; /* Space below the heading */
                color: black; /* Adjust text color */
                margin-top: 110px;
            }

            .form-container {
                font-family: 'Glacial Indifference', sans-serif;
                margin-left: -20px;
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
                font-family: 'Poppins', sans-serif;
                display: block;
                font-weight: bold;
                margin-bottom: 5px;
            }

            .form-group select, .form-group input[type="text"] {
                width: 100%;
                padding: 8px;
                border: 1px solid #ddd;
                border-radius: 5px;
                font-size: 16px;
                box-sizing: border-box;
                font-family: 'Poppins', sans-serif;
            }

            .button-container {
                display: flex;
                justify-content: flex-end;
                margin-top: 20px;
            }

            .button-container button {
                font-family: 'Poppins', sans-serif;
                background-color: #22901C;
                color: white;
                border: none;
                padding: 10px 20px;
                border-radius: 5px;
                font-size: 16px;
                cursor: pointer;
                transition: background-color 0.3s;
            }

            .button-container button:hover {
                background-color: #1b7a0f;
            }

            /* Custom styles for SweetAlert success popup */
            .custom-popupcp {
                font-family: 'Poppins', sans-serif;
                font-size: 17px; /* Adjust the font size */
                width: 400px !important; /* Set a larger width */
            }

            .custom-titlecp {
                font-family: 'Poppins', sans-serif;
                color: #3085d6; /* Custom title color */
            }

            .custom-confirmcp {
                font-family: 'Poppins', sans-serif;
                font-size: 17px;
                background-color: #089451; /* Success button color */
                border: 0.5px #089451 !important;
                color: #fff;
                border-radius: 10px;
                cursor: pointer;
                outline: none !important; /* Remove default focus outline */
            }

            /* Custom styles for SweetAlert error popup */
            .custom-popuper {
                font-family: 'Poppins', sans-serif;
                font-size: 17px; /* Adjust the font size */
                width: 400px !important; /* Set a larger width */
            }

            .custom-titleer {
                font-family: 'Poppins', sans-serif;
                color: #e74c3c; /* Custom title color for error */
            }

            .custom-confirmer {
                font-family: 'Poppins', sans-serif;
                font-size: 17px;
                background-color: #e74c3c; /* Error button color */
                color: #fff;
                border-radius: 10px;
                cursor: pointer;
                outline: none; /* Remove default focus outline */
            }

            /* Style the popup container */
            .my-popup {
                font-family: 'Poppins', sans-serif;
                font-size: 17px; /* Adjust the font size */
                width: 400px !important; /* Set a larger width */
            }

            /* Style the title text */
            .my-title {
                font-family: 'Poppins', sans-serif;
                font-size: 17px;
                color: #333;
                font-weight: bold;
            }

            /* Style the confirmation text */
            .my-text {
                font-family: 'Poppins', sans-serif;
                font-size: 17px;
                color: #666;
            }

            /* Style the confirm button */
            .my-confirm-btn {
                font-family: 'Poppins', sans-serif;
                font-size: 17px;
                background-color: #e74c3c; /* Error button color */
                color: #fff;
                border-radius: 10px;
                cursor: pointer;
                outline: none; /* Remove default focus outline */
            }

            /* Style the cancel button */
            .my-cancel-btn {
                font-family: 'Poppins', sans-serif;
                font-size: 17px;
                background-color: #d33; /* Custom color */
                color: white;
                border-radius: 10px;
            }

            .delete-picture-btn {
                font-family: 'Poppins', sans-serif;
                background-color: #d33; /* Custom color */
                color: white;
                border: none;
                padding: 10px 20px;
                border-radius: 5px;
                font-size: 14px;
                cursor: pointer;
                transition: background-color 0.3s;
            }

            ..delete-picture-btn button:hover {
                background-color: #1b7a0f;
            }

            .file-upload-container {
                display: flex;
                align-items: center;
                margin: 10px 0;
            }

            .file-upload-label {
                font-size: 14px; /* Font size for the file name */
                display: inline-block;
                background-color: #3085d6; /* Custom background color */
                color: white;
                padding: 10px 20px;
                border-radius: 5px;
                cursor: pointer;
                transition: background-color 0.3s ease;
                font-weight: normal !important; /* Ensure the text is not bold */
            }

            .file-upload-label:hover {
                background-color: #2579a8; /* Darker shade on hover */
            }

            .file-input {
                display: none; /* Hide the default file input */
            }

            .file-name-display {
                margin-left: 10px;
                font-size: 14px;
                color: #333; /* Text color */
                display: inline-block; /* Align with the label */
            }

            .file-name-display {
                margin-left: 10px; /* Add some space between the image and the file name */
                font-size: 14px; /* Font size for the file name */
                color: #333; /* Text color */
                display: inline-block; /* Keep it inline with the image */
                vertical-align: top; /* Align it to the top of the image */
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
        </style>
    </head>

    <body>
        <nav class="navbar">
            <h2>Profile</h2> 

            <div class="profile-container">
                <!-- Chat Icon with Notification Badge -->
                <a href="cas-chat.php" class="chat-icon" onclick="resetNotifications()">
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
                            // Use a default placeholder image
                            echo '<div class="profile-placeholder-letter">' . htmlspecialchars($firstLetter) . '</div>'; //New class
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
                <li><a href="cba-dash.php" class="active"><img src="images/home.png">Dashboard</a></li>
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
                <button class="dropdown-btn">
                    <img src="images/task.png">Task Management
                    <i class="fas fa-chevron-down"></i> <!-- Dropdown icon -->
                </button>
                <div class="dropdown-container">
                    <a href="cba-task.php">Upload Files</a>
                    <a href="cba-mov.php">Mode of Verification</a>
                </div>

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

        <div class="content-editor">
            <div class="form-container">
                <h3>Change Profile Picture</h3>
            
                <form action="cba-edit-profile.php" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="profile_picture">Profile Picture:</label>
                        <div class="file-upload-container">
                            <label for="profile_picture" class="file-upload-label">
                                Choose a file
                                <input type="file" id="profile_picture" name="profile_picture" accept="image/*" class="file-input" onchange="displayFileName()">
                            </label>

                            <span id="file-name" class="file-name-display"></span> <!-- Display file name here -->
                        </div>

                        
                        <?php if (!empty($_SESSION['picture'])): ?>
                            <div class="current-picture"> 
                                <br>
                                <label>Current Picture:</label>
                                <img src="<?php echo htmlspecialchars($_SESSION['picture']); ?>" alt="Profile Picture" class="current_pic" width="150" height="150">
                                
                                <!-- Add a span to display the file name -->
                                <span id="current-file-name" class="file-name-display"><?php echo htmlspecialchars(basename($_SESSION['picture'])); ?></span>

                                <br><br>
                                <!-- Separate the delete button and add an onclick event -->
                                <button type="button" class="delete-picture-btn" onclick="confirmDeletion()">Delete Profile Picture</button>
                            </div>
                        <?php else: ?>
                            <div class="no-picture">
                                <p>No profile picture set.</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="button-container">
                        <!-- This button should not trigger the delete confirmation -->
                        <button type="submit" name="update_profile_picture">Update Profile Picture</button>
                    </div>
                </form>
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

            // Display success message if profile picture is uploaded
            <?php if (isset($_SESSION['success'])): ?>
                Swal.fire({
                        title: 'Success!',
                        text: '<?php echo $_SESSION['success']; ?>',
                        icon: 'success',
                        confirmButtonText: 'OK',
                        customClass: {
                            popup: 'custom-popupcp',      // Custom class for the popup
                            title: 'custom-titlecp',      // Custom class for the title
                            confirmButton: 'custom-confirmcp'  // Custom class for the confirm button
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Redirect to role-account.php after clicking OK
                            window.location.href = 'cba-your-profile.php';
                        }
                    });

                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            // Display error message if there is an error
            <?php if (isset($_SESSION['error'])): ?>
                Swal.fire({
                    title: 'Error!',
                    text: '<?php echo $_SESSION['error']; ?>',
                    icon: 'error',
                    confirmButtonText: 'OK',
                    customClass: {
                        popup: 'custom-popuper',      // Custom class for the popup
                        title: 'custom-titleer',      // Custom class for the title
                        confirmButton: 'custom-confirmer'  // Custom class for the confirm button
                    }
                });

                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            function confirmDeletion() {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "Do you want to delete your profile picture?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!',
                    customClass: {
                        popup: 'my-popup',
                        title: 'my-title',
                        text: 'my-text',
                        confirmButton: 'my-confirm-btn',
                        cancelButton: 'my-cancel-btn'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Create a hidden form to submit the delete action
                        let form = document.createElement('form');
                        form.method = 'POST';
                        form.action = 'cba-edit-profile.php';
                        
                        let input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'delete_picture';
                        input.value = 'true';
                        form.appendChild(input);
                        
                        // Append and submit the form
                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            }  

            function displayFileName() {
                const fileInput = document.getElementById('profile_picture');
                const fileNameDisplay = document.getElementById('file-name');

                // Get the selected file name
                const fileName = fileInput.files.length > 0 ? fileInput.files[0].name : 'No file chosen';
                
                // Display the file name
                fileNameDisplay.textContent = fileName;
            }
        </script>
    </body>
</html>
