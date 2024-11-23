<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['uname'])) {
    header("Location: roleaccount.php");
    exit;
}

// Database credentials
$servername = "wp433upk59nnhpoh.cbetxkdyhwsb.us-east-1.rds.amazonaws.com";
$username_db = "wbepy9iso2pubu7f";
$password_db = "l0a6y3bl2x7lfiyy";
$dbname_mov = "qlajsw6auv4giknn";

// Create connection to the database
$conn_mov = new mysqli($servername, $username_db, $password_db, $dbname_mov);

// Check connection
if ($conn_mov->connect_error) {
    die("Connection failed: " . $conn_mov->connect_error);
}

// Base directory for CAS MOV
$base_directory = 'movuploads/cas-mov/';

// Fetch folder and subfolder from GET parameters
$folder_name = isset($_GET['folder']) ? urldecode($_GET['folder']) : null;
$subfolder_name = isset($_GET['subfolder']) ? urldecode($_GET['subfolder']) : null;

if (!$folder_name) {
    die("No folder specified.");
}

$folder_path = $base_directory . $folder_name;

if (!is_dir($folder_path)) {
    die("The specified folder does not exist.");
}

if ($subfolder_name) {
    $subfolder_path = $folder_path . '/' . $subfolder_name;
    if (!is_dir($subfolder_path)) {
        die("The specified subfolder does not exist.");
    }
}

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['upload_file'])) {
    $target_dir = $subfolder_path . '/'; // Target directory
    $target_file = $target_dir . basename($_FILES['upload_file']['name']);
    $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check file type
    if (!in_array($file_type, ['jpg', 'jpeg', 'png', 'pdf'])) {
        $error_message = "Only JPG, JPEG, PNG, and PDF files are allowed.";
    } elseif (move_uploaded_file($_FILES['upload_file']['tmp_name'], $target_file)) {
        $success_message = "File uploaded successfully!";
    } else {
        $error_message = "Error uploading the file.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_file'])) {
    $file_to_delete = $subfolder_path . '/' . basename($_POST['delete_file']);
    $archive_dir = 'movuploads/cas-recycle'; // Directory to store deleted files
    $metadata_file = $archive_dir . '/metadata.json';

    if (file_exists($file_to_delete)) {
        // Ensure the archive directory exists
        if (!is_dir($archive_dir)) {
            mkdir($archive_dir, 0777, true);
        }

        // Archive path
        $archive_path = $archive_dir . '/' . basename($file_to_delete);

        // Read existing metadata
        $metadata = file_exists($metadata_file) ? json_decode(file_get_contents($metadata_file), true) : [];

        // Add metadata for the deleted file
        $metadata[basename($file_to_delete)] = $file_to_delete;

        // Save updated metadata
        file_put_contents($metadata_file, json_encode($metadata, JSON_PRETTY_PRINT));

        // Move the file to the archive
        if (rename($file_to_delete, $archive_path)) {
            $success_message = "File deleted and archived successfully!";
        } else {
            $error_message = "Error deleting the file. Please check permissions or file path.";
        }
    } else {
        $error_message = "File does not exist.";
    }
}


// Handle folder creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_folder'])) {
    $new_folder_name = trim($_POST['new_folder_name']);
    if (!empty($new_folder_name)) {
        $new_folder_path = $subfolder_name 
            ? $subfolder_path . '/' . $new_folder_name 
            : $folder_path . '/' . $new_folder_name;

            if (!file_exists($new_folder_path)) {
                if (mkdir($new_folder_path, 0777, true)) {
                    $success_message = htmlspecialchars("Folder '$new_folder_name' created successfully!");
                } else {
                    $error_message = htmlspecialchars("Error creating the folder.");
                }
            } else {
                $error_message = htmlspecialchars("Folder '$new_folder_name' already exists.");
            }
        }            
}

// Refresh the uploaded files list
$uploaded_files = [];
if ($subfolder_name && is_dir($subfolder_path)) {
    $uploaded_files = array_diff(scandir($subfolder_path), ['.', '..']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($subfolder_name ? $subfolder_name : $folder_name); ?></title>

    <link rel="icon" href="images/logoicon.png">

    <!-- SweetAlert2 CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.0/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.0/dist/sweetalert2.min.js"></script>

    <!-- Font Awesome for folder icon -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

    <style>
            @import url('https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,400;0,600;1,500&display=swap');
            @import url('https://fonts.cdnfonts.com/css/glacial-indifference-2');
            @import url('https://fonts.googleapis.com/css2?family=Saira+Condensed:wght@500&display=swap');

            body {
                margin: 0;
                background-color: #F6F5F5; /* Light gray background color */
                font-family: "Glacial Indifference", sans-serif;
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
                margin-top:-80px;
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
                margin-top:80px;
                font-family: 'Poppins', sans-serif;
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
                height: 110px;
                text-align: center; /* Center text */
            }

            .folder-icon {
                margin-top:5px;
                width: 50px; /* Set a fixed width for icons */
                height: 50px; /* Set a fixed height for icons */
                margin-bottom: 15px; /* Space between icon and folder name */
                
            }

            .folder-link {
                text-decoration: none; /* Remove underline from link */
                color: inherit; /* Inherit color from parent */
                transition: transform 0.2s; /* Smooth scaling effect */
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
                
            }

            .btn-create {
                font-family: 'Poppins', sans-serif !important;
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

            .back-button {
                font-family: 'Poppins', sans-serif;
                font-size: 17px;
                margin-bottom: 20px; /* Space below the button */
                background-color: transparent; /* No background */
                border: none; /* Remove border */
                cursor: pointer; /* Change cursor to pointer */
                font-weight: bold; /* Bold text */
                color: inherit; /* Inherit text color */
            }

            .back-button a {
                text-decoration: none; /* Remove text underline */
                color: inherit; /* Ensure the link color matches the parent */
            }

            .back-arrow {
                height: 25px; /* Set the size of the arrow */
                margin-right: 6px;
                vertical-align: middle; /* Align image vertically with text */
            }

            //* Folder List */
            .folder-list {
                display: flex;
                flex-wrap: wrap;
                gap: 20px;
                margin-bottom: 20px;
            }

            .folder-item {
                display: flex;
                flex-direction: column;
                align-items: center;
                text-align: center;
                padding: 10px;
                width: 120px;
                height: 100px;
                background-color: #f9f9f9; /* Light Green */
                border: 1px solid #ccc; /* Optional border */
                border-radius: 5px; /* Rounded corners */
                text-decoration: none;
                color: #155724; /* Dark Green Text */
                font-family: "Glacial Indifference", sans-serif;
            }

            .folder-item i {
                font-size: 36px;
                margin-bottom: 10px;
                color: inherit;
            }

            /* File List */
            .file-list {
                margin-top: 20px;
            }

            .file-items {
                display: flex;
                flex-wrap: wrap;
                gap: 20px;
                list-style: none;
                padding: 0;
                margin: 0;
            }

            .file-item {
                width: 135px; /* Consistent width for each file item */
                margin: 10px;
                padding: 10px;
                text-align: center;
                border: 1px solid #ddd;
                border-radius: 8px;
                background-color: #f4f4f4;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                overflow: hidden; /* Prevent content overflow */
            }

            .file-box {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: flex-start; /* Align items starting from the top */
                padding: 10px;
                text-align: center;
                height: 180px; /* Consistent height */
                gap: 10px; /* Space between file image/icon and name */
                overflow: hidden;
            }

            .file-image img,
            .file-icon img {
                width: 100%; /* Full width of the container */
                height: auto; /* Maintain aspect ratio */
                max-height: 110px; /* Ensure image doesn't overflow container */
                object-fit: cover; /* Crop the image if necessary */
                border-radius: 5px; /* Optional: rounded corners */
            }

            .file-name {
                font-family: 'Poppins', sans-serif;
                font-size: 14px;
                color: #000;
                font-weight: bold;
                text-align: center;
                white-space: normal; /* Allow text wrapping */
                overflow: hidden; /* Prevent text overflow */
                text-overflow: ellipsis; /* Add ellipsis if text overflows */
                margin-top: 8px;
                width: 100%; /* Full width of the parent container */
                line-height: 1.2; /* Adjust line height */
                max-height: 3.6em; /* Limit height to 3 lines */
            }


            /* Button styles */
            .btn-delete {
                background-color: #ff4d4d;
                color: white;
                border: none;
                padding: 5px 10px;
                font-size: 12px;
                cursor: pointer;
                border-radius: 3px;
            }

            .btn-delete:hover {
                background-color: #e60000;
            }

            .delete-form {
                margin-top: 5px;
            }

            .btn-create {
                display: inline-block;
                padding: 10px 15px;
                color: #fff;
                background-color: #28a745; /* Green */
                text-decoration: none;
                border-radius: 5px;
                border:none;
                font-size: 14px;
                font-family: "Glacial Indifference", sans-serif;
                margin-bottom:15px;
            }

            .btn-create:hover {
                background-color: #218838; /* Dark Green */
            }

            .btn-upload {
                display: inline-block;
                padding: 10px 15px;
                color: #fff;
                background-color: #28a745; /* Green */
                text-decoration: none;
                border-radius: 5px;
                border:none;
                font-size: 14px;
                font-family: "Glacial Indifference", sans-serif;
                margin-top:15px;
            }

            .btn-upload:hover {
                background-color: #218838; /* Dark Green */
            }

            .input-create {
                width: 30%; /* Full width to take up the container's width */
                padding: 8px;
                border: 1px solid #c3e6cb; /* Green Border */
                border-radius: 5px;
                font-family: "Poppins", sans-serif;
                font-size: 14px;
            }

            input[type="file"] {
                margin-bottom: 20px; /* Space below the file input */
                font-family: 'Poppins', sans-serif;
                display: block;
                width: 100%;
                height: 38px;
                margin-top: 5px;
                padding: 0;
                border: 1px solid #ced4da;
                border-radius: 4px;
                font-size: 16px;
                color: #495057;
                background-color: #fff;
                background-clip: padding-box;
                transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
            }

            input[type="file"]::file-selector-button {
                font-family: 'Poppins', sans-serif;
                width: 120px;
                padding: 6px 12px;
                margin-right: 10px;
                background-color: #3085d6; /* Custom background color */
                color: white;
                border: 1px solid #3085d6;;
                border-radius: 4px;
                cursor: pointer;
            }

            .upload-section button {
                font-family: 'Poppins', sans-serif;
                background-color: #4CAF50; /* Green background */
                color: white; /* White text */
                padding: 10px 15px; /* Padding for the button */
                border: none; /* Remove border */
                border-radius: 5px; /* Round corners */
                cursor: pointer; /* Change cursor on hover */
                font-size: 16px; /* Font size for the button */
            }

            .upload-section button:hover {
                background-color: #45a049; /* Darker green on hover */
            }

            .upload-section label {
                font-family: 'Poppins', sans-serif;
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
            <div class="header">
                <h1>Folder Name: <?= htmlspecialchars($subfolder_name ? $subfolder_name : $folder_name); ?></h1>
                <?php if ($subfolder_name): ?>

                    <div class="back-button">
                        <a href="cas-subfolder.php?folder=<?= urlencode($folder_name); ?>" class="back-link">
                            <img src="images/left-arrow.png" class="back-arrow" alt="Back to Subfolders" />
                            Back
                        </a>
                    </div>

                <?php else: ?>
                    <div class="back-button">
                        <a href="cas-mov.php">
                            <img src="images/left-arrow.png" class="back-arrow" alt="Back to folders" />
                            Back
                        </a>
                    </div>

                    <!-- Folder Creation Form -->
                    <div class="create-folder-section">
                        <form class="create-folder-form" method="POST">
                            <label for="new_folder_name">Create New Folder:</label>
                            <input type="text" class="input-create" name="new_folder_name" id="new_folder_name" placeholder="Enter folder name" required>
                            <button type="submit" class="btn-create" name="create_folder">Create</button>
                        </form>
                    </div>
                    <p>Select a subfolder to manage.</p>
                <?php endif; ?>
            </div>

            <?php if (isset($success_message)): ?>
                <script>
                    Swal.fire({
                        title: 'Success!',
                        text: "<?= htmlspecialchars($success_message); ?>",
                        icon: 'success',
                        showConfirmButton: false, // Hide the "OK" button
                        timer: 2000, // 2 seconds before closing
                        timerProgressBar: true, // Optional: show progress bar
                        customClass: {
                            popup: 'custom-swal-popup', // Custom class for the popup
                        }
                    });
                </script>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <script>
                    Swal.fire({
                        title: 'Error!',
                        text: "<?= addslashes($error_message); ?>", // Use addslashes to escape the quotes for JavaScript
                        icon: 'error',
                        showConfirmButton: true,
                        customClass: {
                            popup: 'custom-error-popup',
                            confirmButton: 'custom-error-confirm'
                        }
                    });
                </script>
            <?php endif; ?>


            <?php if ($subfolder_name): ?>
                <!-- File Upload Form -->
                <div class="upload-section">
                    <form class="upload-form" method="POST" enctype="multipart/form-data">
                        <label for="upload_file">Choose file to upload:</label>
                        <input type="file" name="upload_file" id="upload_file" required>
                        <button type="submit">Upload</button>
                    </form>
                </div>
            <?php endif; ?>

            <?php if ($subfolder_name): ?>
                <!-- Display Uploaded Files -->
                <div class="file-list">
                    <h3>Uploaded Files:</h3>

                    <?php if (empty($uploaded_files)): ?>
                        <p class="no-files">No uploaded files.</p>
                    <?php else: ?>

                        <ul class="file-items">
                            <?php foreach ($uploaded_files as $file): ?>
                                <li class="file-item" oncontextmenu="showContextMenu(event, '<?= htmlspecialchars($file) ?>')">
                                    <?php
                                    $file_extension = pathinfo($file, PATHINFO_EXTENSION);
                                    $file_icon = '';
                                    $file_preview = '';

                                    if (in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif'])) {
                                        // Image file, display image preview
                                        $file_preview = '<img src="' . htmlspecialchars($subfolder_path . '/' . $file) . '" alt="Image preview" class="file-preview">';
                                    } elseif ($file_extension === 'pdf') {
                                        // PDF file, display PDF image instead of icon
                                        $file_icon = '<img src="images/pdf.png.png" alt="PDF Icon" class="file-icon">';
                                    } else {
                                        // Default file icon for other types
                                        $file_icon = '<i class="fas fa-file"></i>';
                                    }
                                    ?>

                                    <a href="<?= htmlspecialchars($subfolder_path . '/' . $file); ?>" target="_blank" class="file-link">
                                        <div class="file-box">
                                            <?php if ($file_preview): ?>
                                                <div class="file-image"><?= $file_preview; ?></div>
                                            <?php else: ?>
                                                <div class="file-icon"><?= $file_icon; ?></div>
                                            <?php endif; ?>
                                            <div class="file-name"><?= htmlspecialchars($file); ?></div>
                                        </div>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>

                        <div id="contextMenu" class="context-menu" style="display:none;">
                            <ul>
                                <li id="deleteImage" onclick="deleteFile()">Delete</li>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                
                <!-- Subfolder List -->
                <div class="folder-list">
                    <?php
                    $subfolders = array_diff(scandir($folder_path), ['.', '..']);
                    foreach ($subfolders as $subfolder):
                        $encoded_subfolder = urlencode($subfolder);
                    ?>
                        <a href="cas-subfolder.php?folder=<?= urlencode($folder_name); ?>&subfolder=<?= $encoded_subfolder; ?>" class="folder-item">
                            <div class='folder-icon'>
                                <img src='images/folder.png' alt='File Icon' class='folder-icon'>
                            </div>
                            <span><?= htmlspecialchars($subfolder); ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <script>
            let selectedFile = '';
                function showContextMenu(event, file) {
                    event.preventDefault();
                    selectedFile = file;
                    const contextMenu = document.getElementById('contextMenu');
                    contextMenu.style.left = `${event.pageX}px`;
                    contextMenu.style.top = `${event.pageY}px`;
                    contextMenu.style.display = 'block';
                }

                // Hide context menu on click outside
                window.addEventListener('click', function() {
                    document.getElementById('contextMenu').style.display = 'none';
                });

                function deleteFile() {
                    // Submit the form to delete the file
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '';

                    const fileInput = document.createElement('input');
                    fileInput.type = 'hidden';
                    fileInput.name = 'delete_file';
                    fileInput.value = selectedFile;
                    form.appendChild(fileInput);

                    document.body.appendChild(form);
                    form.submit();
                }

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

            // Log clicks on the "Profile" link
            document.querySelector('.dropdown-menu a[href="cas-your-profile.php"]').addEventListener("click", function() {
                logAction("Profile");
            });
        });
    </script>
</body>
</html>
