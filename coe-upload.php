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

// Fetch folders from the database
$result = $conn_mov->query("SELECT * FROM coe_mov");

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

// Define the uploads directory based on the folder name
$uploadDir = 'movuploads/coe-mov/' . $folder_name . '/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Define the recycle bin directory
$recycleBinDir = 'movuploads/coe-recycle/';
if (!is_dir($recycleBinDir)) {
    mkdir($recycleBinDir, 0777, true);
}

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['images'])) {
    foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
        $file_name = basename($_FILES['images']['name'][$key]);
        $file_name_with_folder = $folder_name . '_' . $file_name;
        $target_file = $uploadDir . $file_name_with_folder;

        // Move uploaded file to the designated directory
        if (move_uploaded_file($tmp_name, $target_file)) {
            $_SESSION['upload_success'] = "Uploaded: " . htmlspecialchars($file_name_with_folder);
        } else {
            $_SESSION['upload_error'] = "Failed to upload: " . htmlspecialchars($file_name);
        }
    }

    $_SESSION['action_taken'] = true;
    header("Location: " . $_SERVER['PHP_SELF'] . "?folder=" . urlencode($folder_name));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Rename Image
    if (isset($_POST['action']) && $_POST['action'] === 'rename_image') {
        $oldImagePath = $uploadDir . basename($_POST['old_image_name']);
        $newImageName = basename($_POST['new_image_name']);

        // Extract the folder name from the original path
        $folderName = explode('_', basename($oldImagePath))[0];

        // Add the folder prefix to the new image name
        $newImageNameWithPrefix = "{$folderName}_{$newImageName}";

        // Ensure the correct extension
        $fileExtension = pathinfo($oldImagePath, PATHINFO_EXTENSION);
        if (pathinfo($newImageNameWithPrefix, PATHINFO_EXTENSION) !== $fileExtension) {
            $newImageNameWithPrefix .= '.' . $fileExtension;
        }

        $newImagePath = $uploadDir . $newImageNameWithPrefix;

        if (file_exists($oldImagePath) && !file_exists($newImagePath)) {
            rename($oldImagePath, $newImagePath);
            $_SESSION['folder_rename_success'] = 'Image renamed successfully!';
        } else {
            $_SESSION['folder_rename_error'] = 'Failed to rename image.';
        }
    }

    // Delete Image
    if (isset($_POST['action']) && $_POST['action'] === 'delete_image') {
        $imagePath = $uploadDir . basename($_POST['image_path']); // Full path of the image to delete
        $newImagePath = $recycleBinDir . basename($imagePath); // Move to the coe-recycle

        // Move the file to the recycle bin if it exists
        if (file_exists($imagePath)) {
            if (rename($imagePath, $newImagePath)) {
                $_SESSION['folder_delete_success'] = 'Image moved to recycle bin successfully!';
            } else {
                $_SESSION['folder_delete_error'] = 'Failed to move image to recycle bin.';
            }
        } else {
            $_SESSION['folder_delete_error'] = 'Image does not exist.';
        }
    }

    $_SESSION['action_taken'] = true;
    header("Location: " . $_SERVER['PHP_SELF'] . "?folder=" . urlencode($folder_name));
    exit;
}

// Get uploaded images to display them for the selected folder only
$uploaded_images = glob($uploadDir . "*.{jpg,jpeg,png,gif}", GLOB_BRACE);

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

            .upload-images {
                font-family: 'Poppins', sans-serif;
                margin-top:110px;
                text-align: left; /* Align text to the left */
            }

            .upload-images h1 {
                font-size: 24px; /* Font size for the header */
                margin-top: 22px;
                margin-bottom: 25px; /* Space below the header */
            }

            .upload-images p {
                font-size: 18px; /* Font size for the folder name */
                margin-bottom: 20px; /* Space below the paragraph */
            }

            .upload-images label {
                display: block; /* Make label take full width */
                font-size: 16px; /* Font size for the label */
                margin-bottom: 5px; /* Space below the label */
            }

            .upload-images input[type="file"] {
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

            .upload-images input[type="file"]::file-selector-button {
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

            .upload-images input[type="file"]::file-selector-button:hover {
                background-color: #2579a8;
            }

            .upload-images button {
                font-family: 'Poppins', sans-serif;
                background-color: #4CAF50; /* Green background */
                color: white; /* White text */
                padding: 10px 15px; /* Padding for the button */
                border: none; /* Remove border */
                border-radius: 5px; /* Round corners */
                cursor: pointer; /* Change cursor on hover */
                font-size: 16px; /* Font size for the button */
            }

            .upload-images button:hover {
                background-color: #45a049; /* Darker green on hover */
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

            input[type="file"]::file-selector-button:hover {
                background-color: #2579a8;
            }

            .uploaded-images {
                font-family: 'Poppins', sans-serif;
                margin-top: 25px; /* Space above the uploaded images section */
            }

            .images-and-names {
                display: flex; /* Use flexbox to align images and names side by side */
                flex-wrap: wrap; /* Allow items to wrap if necessary */
                margin-top:25px;
            }

            .image-item {
                display: flex; /* Use flex to allow vertical alignment */
                flex-direction: column; /* Stack image and file name vertically */
                align-items: center; /* Center items in the column */
                margin-right: 20px; /* Space between image items */
                margin-bottom: 20px; /* Space below each image-item block */
                width: 120px; /* Set a fixed width for each item container */
                border: 1px solid #ccc; /* Add a border */
                border-radius: 5px; /* Optional: add rounded corners */
                padding: 12px; /* Space inside the item */
                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); /* Add shadow for better depth */
            }

            .uploaded-img {
                width: 110%; /* Ensure the image uses full width of the container */
                height: 110px; /* Set a fixed height for images */
                object-fit: cover; /* Maintain aspect ratio, cropping as necessary */
                border-radius: 5px; /* Optional: add rounded corners */
            }

            .file-name {
                width: 100%; /* Ensure filenames take the full width of the image item */
                text-align: center; /* Center the filenames */
                word-wrap: break-word; /* Allow long words to break onto the next line */
                overflow-wrap: break-word; /* Support for older browsers */
                white-space: normal; /* Ensure text wraps normally */
                font-size: 12.5px; /* Optional: adjust font size */
                margin-top: 5px; /* Space above the file name */
                line-height: 1.2; /* Adjust line height for better readability */
            }

            .image-item.highlighted {
                border: 2px solid #007BFF; /* Highlight border color */
                background-color: rgba(0, 123, 255, 0.1); /* Optional light background color for highlight */
                /* Adding padding instead of margin to avoid layout shift */
                padding: 5px; /* This padding can help maintain consistent space */
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
                display: none; 
                position: fixed; 
                z-index: 1000; 
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                overflow: auto; 
                background-color: rgba(0, 0, 0, 0.7); 
                align-items: center; /* Center modal content vertically */
                justify-content: center; /* Center modal content horizontally */
                display: flex; /* Flexbox for centering */
            }

            .modal-content {
                background-color: #fefefe;
                padding: 20px;
                border: 1px solid #888;
                max-width: 90%; /* Max width for the modal */
                max-height: 90%; /* Max height for the modal */
                text-align: center;
                position: relative; /* To position the close button */
            }

            .close-button {
                color: #aaa;
                position: absolute; /* Position it in the top-right corner */
                top: 10px;
                right: 15px;
                font-size: 28px;
                font-weight: bold;
                cursor: pointer; /* Change cursor to pointer */
            }

            .close-button:hover,
            .close-button:focus {
                color: black;
                text-decoration: none;
            }

            #modalImage {
                max-width: 100%; /* Ensure image doesn't exceed modal width */
                max-height: 70vh; /* Limit height to 70% of viewport height */
                object-fit: contain; /* Maintain aspect ratio */
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
                    <a href="coe-your-profile.php">Profile</a>
                    <a class="signout" href="roleaccount.php" onclick="confirmLogout(event)">Sign out</a>
                </div>
            </div>
        </nav>
        
        <div class="sidebar">
            <div class="logo">
                <img src="images/logo.png" alt="Logo">
            </div>

            <ul class="menu">
                <li><a href="coe-dash.php"><img src="images/home.png">Dashboard</a></li>
                <li><a href="coe-projlist.php"><img src="images/project-list.png">Project List</a></li>
                <li><a href="coe-calendar.php"><img src="images/calendar.png">Event Calendar</a></li>

                <!-- Dropdown for Resource Utilization -->
                <button class="dropdown-btn">
                    <img src="images/resource.png"> Resource Utilization
                    <i class="fas fa-chevron-down"></i> <!-- Dropdown icon -->
                </button>
                <div class="dropdown-container">
                    <a href="coe-tor.php">Term of Reference</a>
                    <a href="coe-requi.php">Requisition</a>
                    <a href="coe-venue.php">Venue</a>
                </div>

                <li><a href="coe-budget-utilization.php"><img src="images/budget.png">Budget Allocation</a></li>

                <!-- Dropdown for Task Management -->
                <button class="dropdown-btn">
                    <img src="images/task.png">Task Management
                    <i class="fas fa-chevron-down"></i> <!-- Dropdown icon -->
                </button>
                <div class="dropdown-container">
                    <a href="coe-task.php">Upload Files</a>
                    <a href="coe-mov.php">Mode of Verification</a>
                </div>

                <li><a href="responses.php"><img src="images/setting.png">Responses</a></li>

                <!-- Dropdown for Audit Trails -->
                <button class="dropdown-btn">
                    <img src="images/resource.png"> Audit Trails
                    <i class="fas fa-chevron-down"></i> <!-- Dropdown icon -->
                </button>
                <div class="dropdown-container">
                    <a href="coe-login.php">Log In History</a>
                    <a href="coe-activitylogs.php">Activity Logs</a>
                </div>
            </ul>
        </div>

        <div class="content-task">
            <div class="upload-images">
                <!-- Back Arrow Button with Image -->
                <a href="coe-mov.php" class="back-button">
                    <img src="images/left-arrow.png" alt="Back" class="back-arrow" /> Back
                </a>

                <h1>Folder Name: <?= htmlspecialchars($folder_name); ?></h1>
                
                <form method="POST" enctype="multipart/form-data">
                    <label for="file-upload">Upload your Images:</label>
                    <input type="file" name="images[]" id="file-upload" multiple required />
                    <!-- Hidden input to maintain folder context -->
                    <input type="hidden" name="current_folder" value="<?= htmlspecialchars($folder_name); ?>" />
                    <button type="submit">Upload</button>
                </form>
            </div>

            <div class="uploaded-images">
                <?php if (!empty($uploaded_images)): ?>
                    <h2>Uploaded Images:</h2>
                    <div class="images-and-names">
                        <?php foreach ($uploaded_images as $image): ?>
                            <div class="image-item" data-image="<?= htmlspecialchars($image); ?>">
                                <img src="<?= htmlspecialchars($image); ?>" alt="Uploaded Image" class="uploaded-img" />
                                <p class="file-name"><?php echo htmlspecialchars(basename($image)); ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Context Menu -->
            <div id="contextMenu" class="context-menu">
                <ul>
                    <li id="renameImage">Rename Image</li>
                    <li id="deleteImage">Delete Image</li>
                </ul>
            </div>
        </div>

        <!-- Modal for showing image -->
        <div id="imageModal" class="modal" style="display: none;">
            <div class="modal-content">
                <span class="close-button" id="closeModal">&times;</span>
                <h3>File Name: <span id="modalImageName"></span></h3>
                <img id="modalImage" src="" alt="Selected Image" />
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

            document.addEventListener('DOMContentLoaded', function() {
            const imageItems = document.querySelectorAll('.image-item');

            // Highlight image on click
            imageItems.forEach(item => {
                item.addEventListener('click', function() {
                    // Remove highlight from all items
                    imageItems.forEach(img => img.classList.remove('highlighted'));
                    // Add highlight to the clicked item
                    this.classList.add('highlighted');
                });
            });

            // Handle deletion on Delete key press
            document.addEventListener('keydown', function(event) {
                if (event.key === 'Delete') {
                    const selectedItems = document.querySelectorAll('.highlighted');
                        selectedItems.forEach(item => {
                            const imageSrc = item.getAttribute('data-image'); // Get the image source
                            item.remove(); // Remove the image item from the DOM
                            // You can send an AJAX request here to delete the image on the server if needed
                            console.log('Deleted:', imageSrc); // For debugging
                        });
                    }
                });
            });

            // JavaScript code for context menu actions
            const contextMenu = document.getElementById('contextMenu');
            const imageItems = document.querySelectorAll('.image-item');
            let selectedImagePath = ''; // Store the selected image path

            imageItems.forEach(imageItem => {
                imageItem.addEventListener('contextmenu', function (event) {
                    event.preventDefault();
                    selectedImagePath = imageItem.getAttribute('data-image');

                    // Position the context menu
                    contextMenu.style.display = 'block';
                    contextMenu.style.left = `${event.pageX}px`;
                    contextMenu.style.top = `${event.pageY}px`;
                });
            });

            document.getElementById('renameImage').onclick = function () {
                contextMenu.style.display = 'none'; // Hide context menu

                const currentImageName = selectedImagePath.split('/').pop();
                const folderName = selectedImagePath.split('/').slice(0, -1).pop();

                Swal.fire({
                    title: 'Rename Image',
                    input: 'text',
                    inputValue: currentImageName.replace(`${folderName}_`, ""), // Show only image name
                    inputPlaceholder: 'Enter new image name',
                    showCancelButton: true,
                    confirmButtonText: 'Rename',
                    cancelButtonText: 'Cancel',
                }).then((result) => {
                    if (result.isConfirmed) {
                        let newImageName = result.value.trim();

                        // Prevent renaming to include the folder name explicitly
                        if (newImageName.startsWith(folderName + '_')) {
                            Swal.fire({
                                title: 'Invalid Name',
                                text: 'The image name cannot include the folder name.',
                                icon: 'error',
                            });
                            return;
                        }

                        // Ensure the extension is present
                        const fileExtension = currentImageName.split('.').pop();
                        if (!newImageName.endsWith(`.${fileExtension}`)) {
                            newImageName += `.${fileExtension}`;
                        }

                        // Submit form with the new image name
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = ''; 

                        const actionInput = document.createElement('input');
                        actionInput.type = 'hidden';
                        actionInput.name = 'action';
                        actionInput.value = 'rename_image';

                        const oldNameInput = document.createElement('input');
                        oldNameInput.type = 'hidden';
                        oldNameInput.name = 'old_image_name';
                        oldNameInput.value = selectedImagePath;

                        const newNameInput = document.createElement('input');
                        newNameInput.type = 'hidden';
                        newNameInput.name = 'new_image_name';
                        newNameInput.value = newImageName;

                        form.appendChild(actionInput);
                        form.appendChild(oldNameInput);
                        form.appendChild(newNameInput);
                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            };

            // Delete Image action
            document.getElementById('deleteImage').onclick = function () {
                contextMenu.style.display = 'none'; // Hide context menu

                Swal.fire({
                    title: 'Delete Image',
                    text: `Are you sure you want to delete this image?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Delete',
                    cancelButtonText: 'Cancel',
                    customClass: {
                        popup: 'custom-swal-popup',
                        confirmButton: 'custom-swal-confirm',
                        cancelButton: 'custom-swal-cancel'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Create a hidden form for deleting the image
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = ''; // Submit to the same page

                        const actionInput = document.createElement('input');
                        actionInput.type = 'hidden';
                        actionInput.name = 'action';
                        actionInput.value = 'delete_image';

                        const imagePathInput = document.createElement('input');
                        imagePathInput.type = 'hidden';
                        imagePathInput.name = 'image_path';
                        imagePathInput.value = selectedImagePath;

                        form.appendChild(actionInput);
                        form.appendChild(imagePathInput);
                        document.body.appendChild(form);
                        form.submit(); // Submit the form
                    }
                });
            };

            // Hide context menu when clicking anywhere else
            window.addEventListener('click', function () {
                contextMenu.style.display = 'none';
            });

            // Function to show success SweetAlert
            function showSuccessAlert(message) {
                return Swal.fire({
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
                });
            }

            // Function to show error SweetAlert
            function showErrorAlert(message) {
                return Swal.fire({
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

            // Execute SweetAlert if an action was taken
            <?php if (isset($_SESSION['action_taken'])): ?>
                <?php if (isset($_SESSION['folder_rename_success'])): ?>
                    showSuccessAlert('<?php echo addslashes($_SESSION['folder_rename_success']); ?>').then(() => {
                        window.location.href = "coe-upload.php?folder=<?php echo urlencode($folder_name); ?>"; // Redirect after closing the alert
                    });
                    <?php unset($_SESSION['folder_rename_success']); ?>
                <?php elseif (isset($_SESSION['folder_rename_error'])): ?>
                    showErrorAlert('<?php echo addslashes($_SESSION['folder_rename_error']); ?>').then(() => {
                        window.location.href = "coe-upload.php?folder=<?php echo urlencode($folder_name); ?>"; // Redirect after closing the alert
                    });
                    <?php unset($_SESSION['folder_rename_error']); ?>
                <?php elseif (isset($_SESSION['folder_delete_success'])): ?>
                    showSuccessAlert('<?php echo addslashes($_SESSION['folder_delete_success']); ?>').then(() => {
                        window.location.href = "coe-upload.php?folder=<?php echo urlencode($folder_name); ?>"; // Redirect after closing the alert
                    });
                    <?php unset($_SESSION['folder_delete_success']); ?>
                <?php elseif (isset($_SESSION['folder_delete_error'])): ?>
                    showErrorAlert('<?php echo addslashes($_SESSION['folder_delete_error']); ?>').then(() => {
                        window.location.href = "coe-upload.php?folder=<?php echo urlencode($folder_name); ?>"; // Redirect after closing the alert
                    });
                    <?php unset($_SESSION['folder_delete_error']); ?>
                <?php endif; ?>
                <?php unset($_SESSION['action_taken']); ?>
            <?php endif; ?>

            document.addEventListener('DOMContentLoaded', function() {
                const imageItems = document.querySelectorAll('.image-item');
                const modal = document.getElementById('imageModal');
                const modalImage = document.getElementById('modalImage');
                const modalImageName = document.getElementById('modalImageName');
                const closeModal = document.getElementById('closeModal');

                imageItems.forEach(item => {
                    item.addEventListener('dblclick', function() {
                        const imgSrc = item.getAttribute('data-image');
                        const fileName = item.querySelector('.file-name').textContent;

                        modalImage.src = imgSrc;
                        modalImageName.textContent = fileName;
                        modal.style.display = 'flex'; // Show modal
                    });
                });

                closeModal.addEventListener('click', function() {
                    modal.style.display = 'none'; // Hide modal
                });

                // Close modal when clicking outside of the modal content
                window.addEventListener('click', function(event) {
                    if (event.target === modal) {
                        modal.style.display = 'none';
                    }
                });
            });
        </script>
    </body>
</html>