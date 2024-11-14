<?php
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['uname'])) {
    // Redirect to login page if the session variable is not set
    header("Location: roleaccount.php");
    exit;
}

// Database credentials
$servername = "localhost";
$username_db = "root";
$password_db = "";
$dbname_mov = "task_mov";
$dbname_user_registration = "user_registration";

// Create connection to the database
$conn_mov = new mysqli($servername, $username_db, $password_db, $dbname_mov);

// Check connection
if ($conn_mov->connect_error) {
    die("Connection failed: " . $conn_mov->connect_error);
}

// Fetch folders from the database
$result = $conn_mov->query("SELECT * FROM cas_mov");

// Initialize the folder name variable
$folder_name = null;

// Check if a folder is selected (e.g., through a GET parameter)
if (isset($_GET['folder'])) {
    $folder_name = basename($_GET['folder']);
} elseif ($result->num_rows > 0) {
    // If no folder is selected, default to the first folder
    $first_folder = $result->fetch_assoc();
    $folder_name = $first_folder['folder_name']; // Assuming there's a column 'folder_name'
}


// Handle folder creation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'create') {
    $folder_name = $conn_mov->real_escape_string(trim($_POST['folder_name']));
    $folder_path = $base_directory . $folder_name; // Full path for new folder

    // Check if folder already exists
    $check_sql = "SELECT * FROM cas_mov WHERE folder_name = '$folder_name'";
    $result = $conn_mov->query($check_sql);

    if ($result->num_rows > 0) {
        $_SESSION['warning'] = 'The folder name already exists. Please choose a different name.';
        header('Location: cas-subfolder.php'); // Redirect after setting the session
        exit();
    }

    // Create the folder in the filesystem
    if (mkdir($folder_path)) {
        // Insert folder name into the cas_mov table
        $sql = "INSERT INTO cas_mov (folder_name) VALUES ('$folder_name')";
        if ($conn_mov->query($sql) === TRUE) {
            $_SESSION['folder_create_success'] = 'Folder created successfully';
            header('Location: cas-mov.php'); // Redirect after success
            exit();
        } else {
            $_SESSION['folder_error'] = 'Error creating folder in database: ' . $conn_mov->error;
            header('Location: cas-mov.php'); // Redirect after error
            exit();
        }
    } else {
        $_SESSION['folder_error'] = 'Error creating folder: ' . error_get_last()['message'];
        header('Location: cas-mov.php'); // Redirect after error
        exit();
    }
}

// Fetch the profile picture from the colleges table in user_registration
$conn_profile = new mysqli($servername, $username_db, $password_db, $dbname_user_registration);

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

            .content-task {
                margin-left: 340px; /* Align with the sidebar */
                padding: 20px;
            }

            .content-upload {
                margin-left: 340px; /* Align with the sidebar */
                padding: 20px;
                margin-top:110px;
            }

            .button-container {
                display: flex;
                margin-bottom: 20px; /* Space below the buttons */
                margin-left: -10px;
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

            .archive-button {
                background-color: #4CAF50;
                border: none;
                color: white;
                padding: 10px 20px;
                margin-left: 10px;
                border-radius: 5px;
                font-size: 16px;
                cursor: pointer;
                text-decoration:none;
                transition: background-color 0.3s;
                font-family: 'Poppins', sans-serif;
            }

            .archive-button:hover {
                background-color: #45a049; /* Darker green on hover */
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

            /* Custom styles for SweetAlert error popup */
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
                font-family: 'Glacial Indifference', sans-serif;
                font-size: 17px;
                background-color: #e74c3c;
                color: #fff;
                border-radius: 10px;
                cursor: pointer;
                outline: none; /* Remove default focus outline */
            }

            .create-subfolder {
                font-family: 'Poppins', sans-serif;
                margin-top:110px;
                text-align: left; /* Align text to the left */
            }

            .create-subfolder h1 {
                font-size: 24px; /* Font size for the header */
                margin-top: 22px;
                margin-bottom: 25px; /* Space below the header */
            }

            .create-subfolder p{
                font-size: 18px; /* Font size for the folder name */
                margin-bottom: 20px; /* Space below the paragraph */
            }

          
            .context-menu {
                font-family: 'Poppins', sans-serif;
                display: none; /* Hidden by default */
                position: absolute; /* Position it absolutely */
                background-color: white; /* Background color */
                border: 1px solid #ccc; /* Border */
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2); /* Shadow */
                z-index: 1000; /* Make sure it appears on top */
            }

            .context-menu ul {
                list-style-type: none; /* Remove bullet points */
                margin: 0;
                padding: 5px;
            }

            .context-menu li {
                padding: 8px 12px; /* Padding for menu items */
                cursor: pointer; /* Pointer cursor on hover */
            }

            .context-menu li:hover {
                background-color: #4CAF50;
                color:white;
            }

            
            .modal {
                font-family: 'Poppins', sans-serif;
                display: none; /* Hidden by default */
                position: fixed; /* Stay in place */
                z-index: 1000; /* Sit on top */
                left: 0;
                top: 0;
                width: 100%; /* Full width */
                height: 100%; /* Full height */
                background-color: rgb(0,0,0); /* Fallback color */
                background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
                
            }

            .modal-content {
                border-radius:10px;
                background-color: #fefefe;
                margin: 15% auto; /* 15% from the top and centered */
                margin-left: 540px;
                padding: 20px;
                padding-top:-15px;
                border: 1px solid #888;
                text-align:center;
                width: 25%; /* Could be more or less, depending on screen size */
                height: 36%;
            }

            .modal-content h2 {
                margin-top:10px;
            }

            #submitFolder {
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

            input[type="text"] {
                width: 100%; /* Make the input full-width */
                padding: 10px; /* Add some padding */
                margin: 10px 0; /* Add margin for spacing */
                border: 1px solid #ccc; /* Light gray border */
                border-radius: 4px; /* Rounded corners */
                box-sizing: border-box; /* Include padding and border in element's total width and height */
            }

            input[type="text"]:focus {
                border-color: #4CAF50; /* Change border color when focused */
                outline: none; /* Remove default outline */
            }

            input[type="text"]::placeholder {
                font-family: 'Poppins', sans-serif;
                font-size: 16px;
                color: #999; /* Light gray color for the placeholder text */
                opacity: 1; /* Override default opacity */
                font-style: italic; /* Italic style for placeholder text */
            }

            .close {
                color: #aaa;
                float: right;
                font-size: 28px;
                font-weight: bold;
            }

            .close:hover,
            .close:focus {
                color: black;
                text-decoration: none;
                cursor: pointer;
            }

            .back-button {
                font-family: 'Poppins', sans-serif;
                font-size: 17px;
                margin-bottom: -10px; /* Space below the button */
                background-color: transparent; /* No background */
                border: none; /* Remove border */
                cursor: pointer; /* Change cursor to pointer */
                font-weight: bold; /* Bold text */
                text-decoration: none; /* Remove underline */
                color: inherit; /* Inherit text color */
            }

            .back-arrow {
                height: 25px; /* Set the size of the arrow */
                margin-right: 6px;
                vertical-align: middle; /* Align image vertically with text */
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

            /* Button styling */
            .btn {
                font-family: 'Poppins', sans-serif;
                padding: 10px 20px;
                border: none;
                border-radius: 4px;
                font-size: 17px;
                cursor: pointer;
                margin-top: 10px;
                margin-right: 10px;
                transition: background-color 0.3s ease;
            }

            .btn-create {
                background-color: #28a745; /* Green */
                color: white;
            }

            .btn-create:hover {
                background-color: #218838; /* Darker green */
            }

            .btn-cancel {
                background-color: #e74c3c;
                color: white;
            }

            .btn-cancel:hover {
                background-color: #c82333; /* Darker red */
            }
        </style>
    </head>

    <body>
        <nav class="navbar">
            <h2>Mode of Verification</h2> 

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

                <li><a href="cas-budget-utilization.php"><img src="images/budget.png">Budget Allocation</a></li>

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

        <div class="content-task">
            <div class="create-subfolder">
                <!-- Back Arrow Button with Image -->
                <a href="cas-mov.php" class="back-button">
                    <img src="images/left-arrow.png" alt="Back" class="back-arrow" /> Back
                </a>

                <h1>Folder Name: <?= htmlspecialchars($folder_name); ?></h1>

                <div class="button-container">
                <button id="createFolderBtn">Create Folder</button>
                <a href="cas-archive.php" id="archive" class="archive-button">Archive</a>
            </div>

           <!-- Modal for folder creation -->
            <div id="folderModal" class="modal" style="display: none;">
                <div class="modal-content">
                    <h2>Folder Name</h2>
                    <p>Enter Event Name and Date of Event (e.g. Blood Letting 05-20-2024)</p>
                    <form method="POST" action="">
                        <input type="text" name="subfolder_name" id="folderName" placeholder="Enter Folder Name" required />
                        <input type="hidden" name="action" value="create" /> <!-- Add this hidden input -->
                        <button type="submit" class="btn btn-create">Create</button>
                        <button type="button" id="cancelButton" class="btn btn-cancel">Cancel</button>
                    </form>
                </div>
            </div>

            <!-- Display error message -->
            <?php if (isset($error_message)): ?>
                <div class="alert alert-error"><?= $error_message; ?></div>
            <?php endif; ?>

            <!-- Folder Display Area -->
            <div class="folder-display">
                <?php
                    if ($result && $result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            $folder_name = htmlspecialchars($row['folder_name']); // Sanitize folder name
                            echo "<div class='folder' data-folder-name='$folder_name'>
                                    <a href='cas-subfolder.php?folder=" . urlencode($folder_name) . "' class='folder-link'>
                                        <div class='folder-icon'>
                                            <img src='images/folder.png' alt='File Icon' class='file-icon'>
                                        </div>
                                        <div class='folder-name'>$folder_name</div>
                                    </a>
                                </div>";
                        }
                    } else {
                        echo "<div>No folders created yet.</div>";
                    }
                ?>

            </div>

            <!-- Context Menu -->
            <div id="contextMenu" class="context-menu" style="display: none;">
                <ul>
                    <li id="renameFolder">Rename Folder</li>
                    <li id="deleteFolder">Delete Folder</li>
                </ul>
            </div>
        </div>

        <script>
            // Get modal elements
            var modal = document.getElementById("folderModal");
            var btn = document.getElementById("createFolderBtn");
            var cancelButton = document.getElementById("cancelButton");

            // When the user clicks the button, open the modal
            btn.onclick = function() {
                modal.style.display = "block";
            }

            cancelButton.onclick = function() {
                modal.style.display = "none";
            }

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

       

            // Execute SweetAlert if an action was taken
            <?php if (isset($_SESSION['action_taken'])): ?>
                <?php if (isset($_SESSION['folder_rename_success'])): ?>
                    showSuccessAlert('<?php echo addslashes($_SESSION['folder_rename_success']); ?>').then(() => {
                        window.location.href = "cas-upload.php?folder=<?php echo urlencode($folder_name); ?>"; // Redirect after closing the alert
                    });
                    <?php unset($_SESSION['folder_rename_success']); ?>
                <?php elseif (isset($_SESSION['folder_rename_error'])): ?>
                    showErrorAlert('<?php echo addslashes($_SESSION['folder_rename_error']); ?>').then(() => {
                        window.location.href = "cas-upload.php?folder=<?php echo urlencode($folder_name); ?>"; // Redirect after closing the alert
                    });
                    <?php unset($_SESSION['folder_rename_error']); ?>
                <?php elseif (isset($_SESSION['folder_delete_success'])): ?>
                    showSuccessAlert('<?php echo addslashes($_SESSION['folder_delete_success']); ?>').then(() => {
                        window.location.href = "cas-upload.php?folder=<?php echo urlencode($folder_name); ?>"; // Redirect after closing the alert
                    });
                    <?php unset($_SESSION['folder_delete_success']); ?>
                <?php elseif (isset($_SESSION['folder_delete_error'])): ?>
                    showErrorAlert('<?php echo addslashes($_SESSION['folder_delete_error']); ?>').then(() => {
                        window.location.href = "cas-upload.php?folder=<?php echo urlencode($folder_name); ?>"; // Redirect after closing the alert
                    });
                    <?php unset($_SESSION['folder_delete_error']); ?>
                <?php endif; ?>
                <?php unset($_SESSION['action_taken']); ?>
            <?php endif; ?>

           

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