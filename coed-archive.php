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

// Define the recycle bin directory
$recycleBinDir = 'movuploads/coed-recycle/'; // Changed to coed-recycle

// Fetch folders in the recycle bin
$recycled_folders = array_filter(glob($recycleBinDir . '*'), 'is_dir');

// Fetch all image files from the recycle bin directory
$recycled_images = glob($recycleBinDir . "*.{jpg,jpeg,png,gif}", GLOB_BRACE);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['action'] === 'restore_image') {
        $itemPath = $_POST['item_path']; // Path to the image or folder in the recycle bin
        $itemName = basename($itemPath); // Get the item name

        // Check if the restored item is a folder or an image
        if (is_dir($itemPath)) {
            // Restoring a folder
            $restoreFolderPath = 'movuploads/coed-mov/' . $itemName; // Change to coed-mov

            // Check if the original folder exists
            if (!is_dir($restoreFolderPath)) {
                // Move the folder from recycle bin to movuploads
                if (rename($itemPath, $restoreFolderPath)) {
                    // Insert folder back into the database
                    $sql = "INSERT INTO coed_mov (folder_name) VALUES ('$itemName')";
                    if ($conn_mov->query($sql) === TRUE) {
                        $_SESSION['success_message'] = 'Folder restored successfully!';
                    } else {
                        $_SESSION['error_message'] = 'Failed to update database: ' . $conn_mov->error;
                    }
                } else {
                    $_SESSION['error_message'] = 'Failed to restore folder.';
                }
            } else {
                $_SESSION['error_message'] = 'Original folder already exists.';
            }
        } else {
            // Restoring an image
            // Assuming the original folder name is stored as a prefix in the image name
            $folderName = explode('_', $itemName)[0]; // Extract folder name from image name
            $originalFolder = 'movuploads/coed-mov/' . $folderName; // Change to coed-mov

            // Restore the image to the original folder
            if (is_dir($originalFolder)) {
                if (is_writable($originalFolder)) {
                    $restorePath = $originalFolder . '/' . $itemName;

                    // Check if the file exists in the recycle bin before restoring
                    if (file_exists($itemPath)) {
                        if (rename($itemPath, $restorePath)) {
                            $_SESSION['success_message'] = 'Image restored successfully!';
                        } else {
                            $_SESSION['error_message'] = 'Failed to move image to original folder.';
                        }
                    } else {
                        $_SESSION['error_message'] = 'Image not found in recycle bin.';
                    }
                } else {
                    $_SESSION['error_message'] = 'Original folder is not writable.';
                }
            } else {
                $_SESSION['error_message'] = 'Original folder not found.';
            }
        }

        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}

// Fetch folders from the database if needed
$result = $conn_mov->query("SELECT * FROM coed_mov");
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

            .uploaded-images {
                font-family: 'Poppins', sans-serif;
                
            }

            .images-and-names {
                display: flex; /* Use flexbox to align images and names side by side */
                flex-wrap: wrap; /* Allow items to wrap if necessary */
                margin-top: 25px;
            }

            .image-item, .folder-item {
                display: flex; /* Use flex to allow vertical alignment */
                flex-direction: column; /* Stack image and file name vertically */
                align-items: center; /* Center items in the column */
                margin-right: 20px; /* Space between image items */
                margin-bottom: 20px; /* Space below each image-item block */
                width: 120px; /* Set a fixed width for each item container */
                border: 1px solid #ccc; /* Add a border */
                border-radius: 5px; /* Optional: add rounded corners */
                padding: 15px; /* Space inside the item */
                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); /* Add shadow for better depth */
            }

            .file-icon {
                margin-top:5px;
                width: 80px; /* Set a fixed width for icons */
                height: 80px; /* Set a fixed height for icons */
                margin-bottom: 5px; /* Space between icon and folder name */
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
                font-size: 13px; /* Optional: adjust font size */
                margin-top: 5px; /* Space above the file name */
                line-height: 1.2; /* Adjust line height for better readability */
            }

            .upload-images {
                font-family: 'Poppins', sans-serif;
                margin-top:110px;
                text-align: left; /* Align text to the left */
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
                    <a href="coed-your-profile.php">Profile</a>
                    <a class="signout" href="roleaccount.php" onclick="confirmLogout(event)">Sign out</a>
                </div>
            </div>
        </nav>
        
        <div class="sidebar">
            <div class="logo">
                <img src="images/logo.png" alt="Logo">
            </div>

            <ul class="menu">
                <li><a href="coed-dash.php"><img src="images/home.png">Dashboard</a></li>
                <li><a href="coed-projlist.php"><img src="images/project-list.png">Project List</a></li>
                <li><a href="coed-calendar.php"><img src="images/calendar.png">Event Calendar</a></li>

                <!-- Dropdown for Resource Utilization -->
                <button class="dropdown-btn">
                    <img src="images/resource.png"> Resource Utilization
                    <i class="fas fa-chevron-down"></i> <!-- Dropdown icon -->
                </button>
                <div class="dropdown-container">
                    <a href="coed-tor.php">Term of Reference</a>
                    <a href="coed-requi.php">Requisition</a>
                    <a href="coed-venue.php">Venue</a>
                </div>

                <li><a href="coed-budget-utilization.php"><img src="images/budget.png">Budget Allocation</a></li>

                <!-- Dropdown for Task Management -->
                <button class="dropdown-btn">
                    <img src="images/task.png">Task Management
                    <i class="fas fa-chevron-down"></i> <!-- Dropdown icon -->
                </button>
                <div class="dropdown-container">
                    <a href="coed-task.php">Upload Files</a>
                    <a href="coed-mov.php">Mode of Verification</a>
                </div>

                <li><a href="responses.php"><img src="images/setting.png">Responses</a></li>

                <!-- Dropdown for Audit Trails -->
                <button class="dropdown-btn">
                    <img src="images/resource.png"> Audit Trails
                    <i class="fas fa-chevron-down"></i> <!-- Dropdown icon -->
                </button>
                <div class="dropdown-container">
                    <a href="coed-login.php">Log In History</a>
                    <a href="coed-activitylogs.php">Activity Logs</a>
                </div>
            </ul>
        </div>
        
        <div class="content-task">
            <div class="upload-images">
                <!-- Back Arrow Button with Image -->
                <a href="coed-mov.php" class="back-button">
                    <img src="images/left-arrow.png" alt="Back" class="back-arrow" /> Back
                </a>
            </div>

            <div class="uploaded-images">
            <h2>Archived Items</h2>
            <div class="images-and-names">
                <?php
                // Combine folders and images
                $recycled_items = array_merge(
                    array_filter(glob($recycleBinDir . '*'), 'is_dir'), // Get folders
                    glob($recycleBinDir . "*.{jpg,jpeg,png,gif}", GLOB_BRACE) // Get images
                );

                if (!empty($recycled_items)):
                    foreach ($recycled_items as $item):
                        if (is_dir($item)): // Check if it's a folder
                        ?>
                            <div class="folder-item" data-folder="<?php echo htmlspecialchars($item); ?>" oncontextmenu="showContextMenu(event, '<?php echo htmlspecialchars($item); ?>')">
                                <div class="folder-icon">
                                    <img src="images/folder.png" alt="Folder Icon" class="file-icon">
                                </div>
                                <p class="file-name"><?php echo htmlspecialchars(basename($item)); ?></p>
                            </div>
                        <?php
                        else: // Otherwise, it's an image
                        ?>
                            <div class="image-item" data-image="<?php echo htmlspecialchars($item); ?>" oncontextmenu="showContextMenu(event, '<?php echo htmlspecialchars($item); ?>')">
                                <img src="<?php echo htmlspecialchars($item); ?>" alt="<?php echo basename($item); ?>" class="uploaded-img">
                                <p class="file-name"><?php echo htmlspecialchars(basename($item)); ?></p>
                            </div>
                        <?php
                        endif;
                    endforeach;
                else:
                ?>
                    <p>No archived items found.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Context Menu -->
        <div id="contextMenu" class="context-menu" style="display:none;">
            <ul>
                <li id="restoreImage">Restore</li>
                <li id="deleteImage">Delete</li>
            </ul>
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

            // JavaScript code for context menu actions
            const contextMenu = document.getElementById('contextMenu');
            let selectedItemPath = ''; // Store the selected item's path

            // Function to handle right-click on folder or image
            function handleContextMenu(event, itemPath) {
                event.preventDefault(); // Prevent default context menu
                selectedItemPath = itemPath; // Store the selected item path

                // Position the context menu
                contextMenu.style.display = 'block';
                contextMenu.style.left = `${event.pageX}px`;
                contextMenu.style.top = `${event.pageY}px`;
            }

            // Attach event listeners to image items
            document.querySelectorAll('.image-item').forEach(imageItem => {
                imageItem.addEventListener('contextmenu', function(event) {
                    handleContextMenu(event, imageItem.getAttribute('data-image'));
                });
            });

            // Attach event listeners to folder items
            document.querySelectorAll('.folder-item').forEach(folderItem => {
                folderItem.addEventListener('contextmenu', function(event) {
                    handleContextMenu(event, folderItem.getAttribute('data-folder'));
                });
            });

            // Hide context menu when clicking anywhere else
            window.addEventListener('click', function(event) {
                if (!contextMenu.contains(event.target)) {
                    contextMenu.style.display = 'none'; // Hide context menu
                }
            });

            // Restore action
            document.getElementById('restoreImage').onclick = function() {
                contextMenu.style.display = 'none'; // Hide context menu
                Swal.fire({
                    title: 'Restore Item',
                    text: `Are you sure you want to restore "${selectedItemPath.split('/').pop()}"?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Restore',
                    cancelButtonText: 'Cancel',
                    customClass: {
                        popup: 'custom-swal-popup',
                        title: 'custom-swal-title',
                        confirmButton: 'custom-swal-confirm',
                        cancelButton: 'custom-swal-cancel'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Create a hidden form for restoring the item
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = ''; // The same page

                        const actionInput = document.createElement('input');
                        actionInput.type = 'hidden';
                        actionInput.name = 'action';
                        actionInput.value = 'restore_image';

                        const itemPathInput = document.createElement('input');
                        itemPathInput.type = 'hidden';
                        itemPathInput.name = 'item_path'; // Generic name for both images and folders
                        itemPathInput.value = selectedItemPath;

                        // Assuming you have logic to fetch the original folder name
                        const originalFolderInput = document.createElement('input');
                        originalFolderInput.type = 'hidden';
                        originalFolderInput.name = 'original_folder';
                        originalFolderInput.value = 'movuploads/'; // Update this if the folder path changes

                        form.appendChild(actionInput);
                        form.appendChild(itemPathInput);
                        form.appendChild(originalFolderInput);
                        document.body.appendChild(form);
                        form.submit(); // Submit the form
                    }
                });
            };

            // Delete action
            document.getElementById('deleteImage').onclick = function() {
                contextMenu.style.display = 'none'; // Hide context menu
                Swal.fire({
                    title: 'Delete Item',
                    text: `Are you sure you want to delete "${selectedItemPath.split('/').pop()}" permanently?`,
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
                        // Create a hidden form for deleting the item
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = ''; // The same page

                        const actionInput = document.createElement('input');
                        actionInput.type = 'hidden';
                        actionInput.name = 'action';
                        actionInput.value = 'delete_item'; // Change to a generic action

                        const itemPathInput = document.createElement('input');
                        itemPathInput.type = 'hidden';
                        itemPathInput.name = 'item_path'; // Generic name for both images and folders
                        itemPathInput.value = selectedItemPath;

                        form.appendChild(actionInput);
                        form.appendChild(itemPathInput);
                        document.body.appendChild(form);
                        form.submit(); // Submit the form
                    }
                });
            };

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
            <?php if (isset($_SESSION['success_message'])): ?>
                showSuccessAlert('<?php echo addslashes($_SESSION['success_message']); ?>').then(() => {
                    window.location.href = "<?php echo $_SERVER['PHP_SELF']; ?>"; // Redirect after closing the alert
                });
                <?php unset($_SESSION['success_message']); ?>
            <?php elseif (isset($_SESSION['error_message'])): ?>
                showErrorAlert('<?php echo addslashes($_SESSION['error_message']); ?>').then(() => {
                    window.location.href = "<?php echo $_SERVER['PHP_SELF']; ?>"; // Redirect after closing the alert
                });
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>
        </script>
    </body>
</html>