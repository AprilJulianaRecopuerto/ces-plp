<?php
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['uname'])) {
    // Redirect to login page if the session variable is not set
    header("Location: roleaccount.php");
    exit;
}

// Database credentials
$servername = "l7cup2om0gngra77.cbetxkdyhwsb.us-east-1.rds.amazonaws.com";
$username_db = "gv5xdrzqyrg1qyvs";
$password_db = "uv4wrt6zlfqzrpni";
$dbname_mov = "tcbjgh4zgu5wj4bo";

// Create connection to the database
$conn_mov = new mysqli($servername, $username_db, $password_db, $dbname_mov);


$servername_ur = "l3855uft9zao23e2.cbetxkdyhwsb.us-east-1.rds.amazonaws.com";
$username_ur = "equ6v8i5llo3uhjm"; 
$password_ur = "vkfaxm2are5bjc3q"; 
$dbname_user_registration = "ylwrjgaks3fw5sdj";

$servername_pl = "ryvdxs57afyjk41z.cbetxkdyhwsb.us-east-1.rds.amazonaws.com";
$username_pl = "zf8r3n4qqjyrfx7o";
$password_pl = "su6qmqa0gxuerg98"; 
$dbname_proj_list = "hpvs3ggjc4qfg9jp";


$conn_proj = new mysqli($servername_pl, $username_pl, $password_pl, $dbname_proj_list); // Connection to proj_list database

// Check connection
if ($conn_mov->connect_error || $conn_proj->connect_error) {
    die("Connection failed: " . $conn_mov->connect_error);
}

// Define the base directory for folders
$base_directory = 'movuploads/cas-mov/';

// Fetch the latest project title and dateof_imple from the proj_list database
$query = "SELECT proj_title, dateof_imple FROM cas ORDER BY id DESC LIMIT 1"; // Adjusted to fetch both columns
$result_proj = $conn_proj->query($query);

// Check if the query was successful
if ($result_proj === false) {
    // Query failed, show an error
    die("Error executing query: " . $conn_proj->error);
}

if ($result_proj->num_rows > 0) {
    // Get the project title and date of implementation
    $row = $result_proj->fetch_assoc();
    $proj_title = $row['proj_title'];
    $dateof_imple = $row['dateof_imple'];
    
    // Format dateof_imple if it's not already in the desired format (assuming it's in a valid date format)
    $formatted_date = date('m-d-Y', strtotime($dateof_imple));
    
    // Format folder name (e.g., Project Title 05-20-2024)
    $folder_name = $proj_title . ' ' . $formatted_date;
    
    // Folder path in 'movuploads/cas-mov/'
    $folder_path = $base_directory . $folder_name;

    // Check if the folder entry already exists in the cas_mov table
    $check_existing_folder_query = "SELECT * FROM cas_mov WHERE folder_name = '$folder_name'";
    $result_check = $conn_mov->query($check_existing_folder_query);

    if ($result_check->num_rows == 0) {
        // Folder entry does not exist in the database, proceed to check filesystem and create folder
        if (!file_exists($folder_path)) {
            // Create the folder in the filesystem
            if (mkdir($folder_path, 0777, true)) {
                // Define the subfolders to be created
                $subfolders = ['Program - Colloquium', 'Profile of Presenters', 'Presenters', 'Presentation per Presented', 'Post Evaluation Survey/Feedback', 'Photos', 'Certificate', 'Attendance'];

                // Create each subfolder inside the main folder
                foreach ($subfolders as $subfolder) {
                    $subfolder_path = $folder_path . '/' . $subfolder;
                    if (!mkdir($subfolder_path, 0777, true)) {
                        $_SESSION['folder_error'] = 'Error creating subfolder: ' . $subfolder;
                        break; // Stop creating further subfolders if one fails
                    }
                }

                // Insert the folder name into the cas_mov table for tracking
                $insert_folder_sql = "INSERT INTO cas_mov (folder_name) VALUES ('$folder_name')";
                if ($conn_mov->query($insert_folder_sql) === TRUE) {
                    $_SESSION['folder_create_success'] = 'Folder and subfolders created successfully for event: ' . $folder_name;
                } else {
                    $_SESSION['folder_error'] = 'Error inserting folder name into database: ' . $conn_mov->error;
                }
            } else {
                $_SESSION['folder_error'] = 'Error creating folder for event: ' . $folder_name;
            }
        } else {
            // The folder already exists in the filesystem but not in the database
            $_SESSION['folder_error'] = 'The folder already exists in the filesystem but not in the database.';
        }
    } else {
        // The folder entry already exists in the database, no action needed
        $_SESSION['folder_info'] = 'The folder entry already exists in the database.';
    }
}

// Handle folder creation via manual input
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'create') {
    $folder_name = $conn_mov->real_escape_string(trim($_POST['folder_name']));
    $folder_path = $base_directory . $folder_name; // Full path for new folder

    // Check if folder already exists
    $check_sql = "SELECT * FROM cas_mov WHERE folder_name = '$folder_name'";
    $result = $conn_mov->query($check_sql);

    if ($result->num_rows > 0) {
        $_SESSION['warning'] = 'The folder name already exists. Please choose a different name.';
        header('Location: cas-mov.php'); // Redirect after setting the session
        exit();
    }
    
    // Create the folder in the filesystem
    if (mkdir($folder_path, 0777, true)) {
        // Define subfolders
        $subfolders = ['Program - Colloquium', 'Profile of Presenters', 'Presenters', 'Presentation per Presented', 'Post Evaluation Survey/Feedback', 'Photos', 'Certificate', 'Attendance'];
        foreach ($subfolders as $subfolder) {
            $subfolder_path = $folder_path . '/' . $subfolder;
            if (!mkdir($subfolder_path, 0777, true)) {
                $_SESSION['folder_error'] = 'Error creating subfolder: ' . $subfolder;
                break;
            }
        }

        // Insert folder name into the cas_mov table
        $sql = "INSERT INTO cas_mov (folder_name) VALUES ('$folder_name')";
        if ($conn_mov->query($sql) === TRUE) {
            $_SESSION['folder_create_success'] = 'Folder and subfolders created successfully';
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


// Fetch folders from the database, excluding deleted records
$query = "SELECT * FROM cas_mov WHERE folder_name IS NOT NULL"; // Adjusted to filter out deleted records
$result = $conn_mov->query($query);

// Check if the query was successful
if ($result === false) {
    // Query failed, show an error
    die("Error executing query: " . $conn_mov->error);
}
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
                height: auto;
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
                <li><a href="cas-mov.php" class="active"><img src="images/task.png">Mode of Verification</a></li>

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
            <div class="button-container">
                <a href="cas-archive.php" id="archive" class="archive-button">Archive</a>
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

                // Log clicks on the "Archive" link
                document.getElementById("archive").addEventListener("click", function(event) {
                    event.preventDefault(); // Prevent default action to allow logging first
                    logAndRedirect("Archive", "cas-archive.php");
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
