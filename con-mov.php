<?php
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['uname'])) {
    header("Location: collegelogin.php"); // Redirect to login page
    exit;
}

// Database credentials
$servername = "localhost";
$username_db = "root";
$password_db = "";
$dbname_mov = "task_mov";

// Create connection to the database
$conn_mov = new mysqli($servername, $username_db, $password_db, $dbname_mov);

// Check connection
if ($conn_mov->connect_error) {
    die("Connection failed: " . $conn_mov->connect_error);
}

// Define the base directory for folders
$base_directory = 'movuploads/con-mov/';

// Handle folder creation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'create') {
    $folder_name = $conn_mov->real_escape_string(trim($_POST['folder_name']));
    $folder_path = $base_directory . $folder_name; // Full path for new folder

    // Check if folder already exists
    $check_sql = "SELECT * FROM con_mov WHERE folder_name = '$folder_name'";
    $result = $conn_mov->query($check_sql);

    if ($result->num_rows > 0) {
        $_SESSION['warning'] = 'The folder name already exists. Please choose a different name.';
        header('Location: con-mov.php'); // Redirect after setting the session
        exit();
    }

    // Create the folder in the filesystem
    if (mkdir($folder_path)) {
        // Insert folder name into the con_mov table
        $sql = "INSERT INTO con_mov (folder_name) VALUES ('$folder_name')";
        if ($conn_mov->query($sql) === TRUE) {
            $_SESSION['folder_create_success'] = 'Folder created successfully';
            header('Location: con-mov.php'); // Redirect after success
            exit();
        } else {
            $_SESSION['folder_error'] = 'Error creating folder in database: ' . $conn_mov->error;
            header('Location: con-mov.php'); // Redirect after error
            exit();
        }
    } else {
        $_SESSION['folder_error'] = 'Error creating folder: ' . error_get_last()['message'];
        header('Location: con-mov.php'); // Redirect after error
        exit();
    }
}

// Handle folder renaming
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'rename') {
    $old_folder_name = $conn_mov->real_escape_string(trim($_POST['old_folder_name']));
    $new_folder_name = $conn_mov->real_escape_string(trim($_POST['new_folder_name']));
    $old_folder_path = $base_directory . $old_folder_name; // Old path
    $new_folder_path = $base_directory . $new_folder_name; // New path

    // Check if the new folder name already exists
    $check_sql = "SELECT * FROM con_mov WHERE folder_name = '$new_folder_name'";
    $result = $conn_mov->query($check_sql);

    if ($result->num_rows > 0) {
        $_SESSION['folder_error'] = 'The new folder name already exists. Please choose a different name.';
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

    // Rename the folder in the filesystem
    if (rename($old_folder_path, $new_folder_path)) {
        // Update the folder name in the database
        $sql = "UPDATE con_mov SET folder_name = '$new_folder_name' WHERE folder_name = '$old_folder_name'";
        if ($conn_mov->query($sql) === TRUE) {
            $_SESSION['folder_rename_success'] = "Folder renamed successfully.";
        } else {
            $_SESSION['folder_error'] = "Error updating database: " . $conn_mov->error;
        }
    } else {
        $_SESSION['folder_error'] = "Error renaming folder in filesystem.";
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Handle folder deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'delete') {
    $folder_name = $conn_mov->real_escape_string(trim($_POST['folder_name']));
    $folder_path = $base_directory . $folder_name; // Define the directory path
    $recycle_bin_path = 'movuploads/con-recycle/' . $folder_name; // Path to con-recycle

    // Create con-recycle directory if it doesn't exist
    if (!is_dir('movuploads/con-recycle')) {
        mkdir('movuploads/con-recycle', 0777, true);
    }

    // Move the folder to the con-recycle
    if (is_dir($folder_path)) {
        if (rename($folder_path, $recycle_bin_path)) {
            $_SESSION['folder_delete_success'] = "Folder moved to recycle bin successfully.";
            // Delete the folder from the database
            $sql = "DELETE FROM con_mov WHERE folder_name = '$folder_name'";
            if ($conn_mov->query($sql) !== TRUE) {
                $_SESSION['folder_error'] = "Error deleting folder entry from database: " . $conn_mov->error;
            }
        } else {
            $_SESSION['folder_error'] = "Error moving folder to recycle bin.";
        }
    } else {
        $_SESSION['folder_error'] = "The folder does not exist: $folder_path";
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Fetch folders from the database
$result = $conn_mov->query("SELECT * FROM con_mov");
$conn_mov->close();
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
                margin-top:110px;
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

            .custom-swal-input {
                width: 90% !important;
                margin-left: 19px !important;
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

            /* Custom styles for SweetAlert error popup */
            .custom-warning-popup {
                font-family: 'Poppins', sans-serif;
                width: 400px !important; /* Set a larger width */
            }

            .custom-warning-title {
                font-family: 'Poppins', sans-serif;
                color: #e74c3c; /* Custom title color for error */
            }

            .custom-warning-confirm {
                font-family: 'Poppins', sans-serif;
                font-size: 17px;
                background-color: #e74c3c;
                color: #fff;
                border-radius: 10px;
                cursor: pointer;
                outline: none; /* Remove default focus outline */
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

            .folder-display {
                font-family: 'Poppins', sans-serif;
                display: flex; /* Use flexbox to layout folders */
                flex-wrap: wrap; /* Allow wrapping to the next line */
                gap: 20px; /* Space between folder items */
            }

            .folder {
                display: flex; /* Use flex to align items */
                flex-direction: column; /* Stack items vertically */
                align-items: center; /* Center items horizontally */
                padding: 10px;
                border: 1px solid #ccc; /* Optional border */
                border-radius: 5px; /* Rounded corners */
                background-color: #f9f9f9; /* Background color */
                width: 120px; /* Fixed width for folders */
                height: 110px;
                text-align: center; /* Center text */
            }

            .file-icon {
                margin-top:5px;
                width: 50px; /* Set a fixed width for icons */
                height: 45px; /* Set a fixed height for icons */
                margin-bottom: 5px; /* Space between icon and folder name */
            }

            .folder-link {
                text-decoration: none; /* Remove underline from link */
                color: inherit; /* Inherit color from parent */
                transition: transform 0.2s; /* Smooth scaling effect */
            }

            .folder-link:hover {
                transform: scale(1.05); /* Scale up slightly on hover */
            }

            .folder-name {
                margin-top: 5px; /* Space above folder name */
                font-weight: normal; /* Make folder name bold */
            }

            .alert {
                padding: 10px;
                margin-bottom: 20px;
                border-radius: 5px;
            }

            .alert-error {
                background-color: #f8d7da;
                color: #721c24;
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
                    <a href="con-your-profile.php">Profile</a>
                    <a class="signout" href="roleaccount.php" onclick="confirmLogout(event)">Sign out</a>
                </div>
            </div>
        </nav>
        
        <div class="sidebar">
            <div class="logo">
                <img src="images/logo.png" alt="Logo">
            </div>

            <ul class="menu">
                <li><a href="con-dash.php"><img src="images/home.png">Dashboard</a></li>
                <li><a href="con-projlist.php"><img src="images/project-list.png">Project List</a></li>
                <li><a href="con-calendar.php"><img src="images/calendar.png">Event Calendar</a></li>

                <!-- Dropdown for Resource Utilization -->
                <button class="dropdown-btn">
                    <img src="images/resource.png"> Resource Utilization
                    <i class="fas fa-chevron-down"></i> <!-- Dropdown icon -->
                </button>
                <div class="dropdown-container">
                    <a href="con-tor.php">Term of Reference</a>
                    <a href="con-requi.php">Requisition</a>
                    <a href="con-venue.php">Venue</a>
                </div>

                <li><a href="con-budget-utilization.php"><img src="images/budget.png">Budget Allocation</a></li>

                <!-- Dropdown for Task Management -->
                <button class="dropdown-btn">
                    <img src="images/task.png">Task Management
                    <i class="fas fa-chevron-down"></i> <!-- Dropdown icon -->
                </button>
                <div class="dropdown-container">
                    <a href="con-task.php">Upload Files</a>
                    <a href="con-mov.php">Mode of Verification</a>
                </div>

                <li><a href="responses.php"><img src="images/setting.png">Responses</a></li>

                <!-- Dropdown for Audit Trails -->
                <button class="dropdown-btn">
                    <img src="images/resource.png"> Audit Trails
                    <i class="fas fa-chevron-down"></i> <!-- Dropdown icon -->
                </button>
                <div class="dropdown-container">
                    <a href="con-login.php">Log In History</a>
                    <a href="con-activitylogs.php">Activity Logs</a>
                </div>
            </ul>
        </div>
        
        <div class="content-task">
            <div class="button-container">
                <button id="createFolderBtn">Create Folder</button>
                <a href="con-archive.php" id="archive" class="archive-button">Archive</a>
            </div>

           <!-- Modal for folder creation -->
            <div id="folderModal" class="modal" style="display: none;">
                <div class="modal-content">
                    <h2>Folder Name</h2>
                    <p>Enter Event Name and Date of Event (e.g. Blood Letting 05-20-2024)</p>
                    <form method="POST" action="">
                        <input type="text" name="folder_name" id="folderName" placeholder="Enter Folder Name" required />
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
                                    <a href='con-upload.php?folder=" . urlencode($folder_name) . "' class='folder-link'>
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

            // JavaScript code for context menu actions
            const contextMenu = document.getElementById('contextMenu');
            const folderElements = document.querySelectorAll('.folder');

            folderElements.forEach(folder => {
                folder.addEventListener('contextmenu', function (event) {
                    event.preventDefault();
                    const folderName = folder.getAttribute('data-folder-name');

                    // Position the context menu
                    contextMenu.style.display = 'block';
                    contextMenu.style.left = `${event.pageX}px`;
                    contextMenu.style.top = `${event.pageY}px`;

                    // Rename Folder action
                    document.getElementById('renameFolder').onclick = function () {
                        contextMenu.style.display = 'none'; // Hide context menu
                        Swal.fire({
                            title: 'Rename Folder',
                            input: 'text',
                            inputValue: folderName, // Pre-fill with current folder name
                            inputPlaceholder: 'Enter new folder name',
                            showCancelButton: true,
                            confirmButtonText: 'Rename',
                            cancelButtonText: 'Cancel',
                            customClass: {
                                popup: 'custom-swal-popup',
                                title: 'custom-swal-title',
                                input: 'custom-swal-input',
                                confirmButton: 'custom-swal-confirm',
                                cancelButton: 'custom-swal-cancel' // Custom class for the cancel button
                            },
                            preConfirm: (newName) => {
                                if (!newName) {
                                    Swal.showValidationMessage('Folder name cannot be empty');
                                }
                                return newName;
                            }
                        }).then((result) => {
                            if (result.isConfirmed) {
                                const newFolderName = result.value;

                                // Create a hidden form for renaming the folder
                                const form = document.createElement('form');
                                form.method = 'POST';
                                form.action = ''; // The same page

                                const actionInput = document.createElement('input');
                                actionInput.type = 'hidden';
                                actionInput.name = 'action';
                                actionInput.value = 'rename';

                                const oldNameInput = document.createElement('input');
                                oldNameInput.type = 'hidden';
                                oldNameInput.name = 'old_folder_name';
                                oldNameInput.value = folderName;

                                const newNameInput = document.createElement('input');
                                newNameInput.type = 'hidden';
                                newNameInput.name = 'new_folder_name';
                                newNameInput.value = newFolderName;

                                form.appendChild(actionInput);
                                form.appendChild(oldNameInput);
                                form.appendChild(newNameInput);
                                document.body.appendChild(form);
                                form.submit(); // Submit the form
                            }
                        });
                    };

                    // Delete Folder action
                    document.getElementById('deleteFolder').onclick = function () {
                        contextMenu.style.display = 'none'; // Hide context menu
                        Swal.fire({
                            title: 'Delete Folder',
                            text: `Are you sure you want to delete "${folderName}"?`,
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Delete',
                            cancelButtonText: 'Cancel',
                            customClass: {
                                popup: 'custom-swal-popup',
                                title: 'custom-swal-title',
                                confirmButton: 'custom-swal-confirm',
                                cancelButton: 'custom-swal-cancel'
                            }
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // Create a hidden form for deleting the folder
                                const form = document.createElement('form');
                                form.method = 'POST';
                                form.action = ''; // The same page

                                const actionInput = document.createElement('input');
                                actionInput.type = 'hidden';
                                actionInput.name = 'action';
                                actionInput.value = 'delete';

                                const nameInput = document.createElement('input');
                                nameInput.type = 'hidden';
                                nameInput.name = 'folder_name';
                                nameInput.value = folderName;

                                form.appendChild(actionInput);
                                form.appendChild(nameInput);
                                document.body.appendChild(form);
                                form.submit(); // Submit the form
                            }
                        });
                    };
                });
            });

            // Hide context menu when clicking anywhere else
            window.addEventListener('click', function () {
                contextMenu.style.display = 'none';
            });


           // Function to show success SweetAlert
            function showSuccessAlert(message) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: message,
                    confirmButtonColor: '#089451',
                    confirmButtonText: 'Continue',
                    customClass: {
                        popup: 'custom-swal-popup',
                        title: 'custom-swal-title',
                        confirmButton: 'custom-swal-confirm'
                    }
                }).then(() => {
                    window.location.href = "con-mov.php"; // Redirect to the desired page
                });
            }

            // Function to show error SweetAlert
            function showErrorAlert(message) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: message,
                    confirmButtonColor: '#e74c3c',
                    confirmButtonText: 'Try Again',
                    customClass: {
                        popup: 'custom-error-popup',
                        title: 'custom-error-title',
                        confirmButton: 'custom-error-confirm'
                    }
                });
            }

            // Function to show warning SweetAlert
            function showWarningAlert(message) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Warning',
                    text: message,
                    confirmButtonText: 'Okay',
                    customClass: {
                        popup: 'custom-warning-popup',
                        title: 'custom-warning-title',
                        confirmButton: 'custom-warning-confirm'
                    }
                });
            }

            // Check for folder creation success message
            <?php if (isset($_SESSION['folder_create_success'])): ?>
                showSuccessAlert('<?php echo addslashes($_SESSION['folder_create_success']); ?>');
                <?php unset($_SESSION['folder_create_success']); ?> // Clear the message
            <?php endif; ?>

            // Check for folder rename success message
            <?php if (isset($_SESSION['folder_rename_success'])): ?>
                showSuccessAlert('<?php echo addslashes($_SESSION['folder_rename_success']); ?>');
                <?php unset($_SESSION['folder_rename_success']); ?> // Clear the message
            <?php endif; ?>

            // Check for folder deletion success message
            <?php if (isset($_SESSION['folder_delete_success'])): ?>
                showSuccessAlert('<?php echo addslashes($_SESSION['folder_delete_success']); ?>');
                <?php unset($_SESSION['folder_delete_success']); ?> // Clear the message
            <?php endif; ?>

            // Check for error messages
            <?php if (isset($_SESSION['folder_error'])): ?>
                showErrorAlert('<?php echo addslashes($_SESSION['folder_error']); ?>');
                <?php unset($_SESSION['folder_error']); ?> // Clear the message
            <?php endif; ?>

            // Check for warning message in session and show alert
            <?php if (isset($_SESSION['warning'])): ?>
                showWarningAlert('<?php echo addslashes($_SESSION['warning']); ?>');
                <?php unset($_SESSION['warning']); ?> // Clear the message after displaying
            <?php endif; ?>
        </script>
    </body>
</html>