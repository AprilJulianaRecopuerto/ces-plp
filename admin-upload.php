<?php
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: roleaccount.php"); // Redirect to login page
    exit;
}

// Initialize the folder name and college variables
$folder_name = null;
$college = null;
$subfolders = [];
$files = [];

// Check if a folder is selected (e.g., through a GET parameter)
if (isset($_GET['folder'])) {
    $folder_parts = explode('/', $_GET['folder']); // Split the folder string
    if (count($folder_parts) == 2) {
        $college = $folder_parts[0]; // Get the college part
        $folder_name = $folder_parts[1]; // Get the actual folder name
    } elseif (count($folder_parts) > 2) {
        // It's a deeper subfolder
        $college = $folder_parts[0]; 
        $folder_name = implode('/', array_slice($folder_parts, 1)); // Get subfolder path
    }
}

// Define the project directory based on the college
$projectDir = 'movuploads/' . $college . '/';

// Check if the directory exists
if (!is_dir($projectDir)) {
    echo "Project directory does not exist.";
    exit;
}

// If no specific folder is selected, list all subfolders inside the project folder
if (!$folder_name) {
    // Get all subfolders inside the project folder
    $subfolders = array_filter(glob($projectDir . '*'), 'is_dir');
} else {
    // If a specific folder is selected, display its contents (images, PDFs, or further subfolders)
    $uploadDir = $projectDir . $folder_name . '/';
    
    if (is_dir($uploadDir)) {
        // Get all subfolders in the selected folder
        $subfolders = array_filter(glob($uploadDir . '*'), 'is_dir');
        // Get all files (images and PDFs) in the selected folder
        $files = glob($uploadDir . "*.{jpg,jpeg,png,gif,pdf}", GLOB_BRACE);
    } else {
        echo "The selected folder does not exist.";
        exit;
    }
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

            .content-image {
                margin-left: 340px; /* Align with the sidebar */
                padding: 20px;
            }

            .uploaded-images {
                font-family: 'Poppins', sans-serif;
                margin-top:110px;
                text-align: left; /* Align text to the left */
            }

            .uploaded-images h1 {
                font-size: 24px; /* Font size for the header */
                margin-top: 22px;
                margin-bottom: 25px; /* Space below the header */
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

            .images-and-names {
                display: flex;
                flex-wrap: wrap;
            }

            .image-item {
                margin: 10px;
                text-align: center;
            }

            .file-name {
                font-size: 14px;
                color: #333;
            }

            /* General styles for folder and file listings */
            .folder-list, .files-list {
                display: flex;
                flex-wrap: wrap;
                gap: 20px;
                margin-top: 20px;
            }

            .file-item {
                background-color: #f8f9fa;
                padding: 15px;
                border-radius: 8px;
                border: 1px solid #ccc; /* Optional border */
                width: 120px;
                text-align: center;
                cursor: pointer;
                text-decoration: none;
                color: #333;
            }

            .file-item {
                background-color: #fff;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            }

            .file-item .file-name {
                margin-top: 10px;
                font-size: 14px;
                color: #333;
                text-overflow: ellipsis;
                overflow: hidden;
                white-space: nowrap;
            }

            .file-item .uploaded-img {
                width: 80%; /* Set full width of the container */
                height: 110px; /* Set a fixed height for consistency */
                object-fit: cover; /* Maintain aspect ratio, cropping as necessary */
                border-radius: 5px; /* Optional: add rounded corners */
            }




            /* Style specific to image files */
            .file-img {
                width: 100% !important; /* Full width of the container */
                height: 120px; /* Adjust the height as needed for image files */
                object-fit: cover; /* Crop the image to fit the dimensions without distortion */
                border-radius: 5px; /* Add rounded corners (optional) */
            }


            /* Folder navigation */
            h1, h3 {
                font-size: 22px;
                color: #333;
            }

            h3 {
                margin-top: 20px;
                font-size: 18px;
                color: #6c757d;
            }

            /* Styling for the folder list container */
            .folder-list {
                margin-top: 20px;
                display: flex;
                flex-wrap: wrap;
                gap: 20px;  /* Space between folder items */
            }

            /* Folder item container */
            .folder-item {
                background-color: #f9f9f9;
                border: 1px solid #ccc;
                font-size: 16px;
                color: #495057;
                padding: 12px;
                border-radius: 5px;
                text-align: center;
                width: 120px; /* Fixed width for folders */
                height: 100px;
                text-decoration: none;
                display: flex;
                flex-direction: column; /* Stack icon and folder name vertically */
                align-items: center; /* Center contents horizontally */
                justify-content: center; /* Center contents vertically */
            }

            /* Style for the folder icon */
            .folder-icon {
                width: 50px; /* Fixed width for icons */
                height: 50px; /* Fixed height for icons */
                margin-bottom: 5px; /* Space between icon and folder name */
            }


            /* Style for the file name */
            .file-name {
                font-weight: bold;
                font-size: 14px;
                color: #333;
                margin-top: 5px;  /* Optional: space between file icon and file name */
            }

            /* Style for the folder name */
            .folder-name {
                font-weight: bold;
                font-size: 14px;
                color: #333;
            }

            /* Optionally, you can adjust the icon color in the above class */
            .folder-item i {
                font-size: 24px;
                color: #007bff;  /* If you use icon fonts */
            }

            h1, h2, h3 {
                color: #333;
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
                max-width: 45%; /* Max width for the modal */
                max-height: 90%; /* Max height for the modal */
                text-align: center;
                position: relative; /* To position the close button */
                margin-left: 400px;
                margin-top: 70px;
                border-radius: 5px;
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
                max-width: 90%; /* Ensure image doesn't exceed modal width */
                max-height: 70vh; /* Limit height to 70% of viewport height */
                object-fit: contain; /* Maintain aspect ratio */
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
                        echo '<img src="' . $_SESSION['picture'] . '" alt="Profile Picture">';
                    } else {
                        // Get the first letter of the username for the placeholder
                        $firstLetter = strtoupper(substr($_SESSION['uname'], 0, 1));
                        echo '<div class="profile-placeholder">' . $firstLetter . '</div>';
                    }
                ?>
                <span><?php echo htmlspecialchars($_SESSION['uname']); ?></span>

                <i class="fa fa-chevron-down dropdown-icon"></i>
                <div class="dropdown-menu">
                    <a href="cas-your-profile.php">Profile</a>
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
                <li><a href="admin-mov.php" class="active"><img src="images/task.png">Mode of Verification</a></li>

                <li><a href="responses.php"><img src="images/setting.png">Responses</a></li>

                <!-- Dropdown for Audit Trails -->
                <button class="dropdown-btn">
                    <img src="images/resource.png"> Audit Trails
                    <i class="fas fa-chevron-down"></i> <!-- Dropdown icon -->
                </button>
                <div class="dropdown-container">
                    <a href="admin-login.php">Log In History</a>
                    <a href="admin-activitylogs.php">Activity Logs</a>
                </div>
            </ul>
        </div>

<!-- Display Files and Subfolders -->
<div class="content-image">
    <div class="uploaded-images">
        <!-- Back button -->
        <a href="javascript:history.back()" class="back-button">
            <img src="images/left-arrow.png" alt="Back" class="back-arrow" /> Back
        </a>


        <?php if (!$folder_name): ?>
            <!-- List top-level subfolders -->
            <h3>Select a Folder:</h3>
            <div class="folder-list">
                <?php foreach ($subfolders as $subfolder): ?>
                    <a href="admin-upload.php?folder=<?= urlencode($college . '/' . basename($subfolder)); ?>" class="folder-item">
                        <?= htmlspecialchars(basename($subfolder)); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <!-- Display contents of the selected folder -->
            <h1>Folder Name: <?= htmlspecialchars($folder_name); ?></h1>

            <!-- List subfolders inside the current folder -->
            <?php if (!empty($subfolders)): ?>
                <h3>Subfolders:</h3>
                <div class="folder-list">
                    <?php foreach ($subfolders as $subfolder): ?>
                        <a href="admin-upload.php?folder=<?= urlencode($college . '/' . $folder_name . '/' . basename($subfolder)); ?>" class="folder-item">
                            <!-- Folder icon on top -->
                            <img src="images/folder.png" alt="Folder Icon" class="folder-icon">
                            <span><?= htmlspecialchars(basename($subfolder)); ?></span> <!-- Folder name -->
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Display files (images and PDFs) in the current folder -->
            <?php if (!empty($files) || !empty($subfolders)): ?>
                <div class="files-list">
                    <?php foreach ($files as $file): ?>
                        <div class="file-item" data-file="<?= htmlspecialchars($file); ?>">
                            <?php
                                $file_extension = pathinfo($file, PATHINFO_EXTENSION);
                                if (in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif'])) {
                                    // Display image files
                                    echo '<img src="' . htmlspecialchars($file) . '" alt="' . htmlspecialchars(basename($file)) . '" class="uploaded-img file-img" />';
                                    echo '<p class="file-name">' . htmlspecialchars(basename($file)) . '</p>';  // Display image name
                                } elseif ($file_extension == 'pdf') {
                                    // Display PDF files as clickable links
                                    echo '<a href="' . htmlspecialchars($file) . '" target="_blank">
                                            <img src="images/pdf.png.png" alt="PDF File" class="uploaded-img" />
                                        </a>';
                                    echo '<p class="file-name">' . htmlspecialchars(basename($file)) . '</p>';  // Display PDF name
                                }
                            ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php elseif (empty($files) && empty($subfolders)): ?>
                <p>No files available in this folder.</p>
            <?php endif; ?>
        <?php endif; ?>
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
           // Function to show modal with image details
function showModal(imageName, imagePath) {
    // Set the modal image name and image path
    document.getElementById('modalImageName').innerText = imageName; // Set the filename
    document.getElementById('modalImage').src = imagePath; // Set the image source
    document.getElementById('imageModal').style.display = 'block'; // Display the modal
}

// Close the modal
document.getElementById('closeModal').onclick = function() {
    document.getElementById('imageModal').style.display = 'none'; // Close the modal when clicked
}

// Add event listener for double-click on images
document.querySelectorAll('.uploaded-img').forEach(function(img) {
    img.addEventListener('dblclick', function() {
        const imagePath = img.src; // Get the image source (path)
        const imageExtension = imagePath.split('.').pop().toLowerCase(); // Get the file extension

        // Check if the file is an image based on its extension
        if (['jpg', 'jpeg', 'png', 'gif'].includes(imageExtension)) {
            const imageName = img.alt; // Get the image name (filename from the alt attribute)
            showModal(imageName, imagePath); // Show the modal with the image and filename
        }
    });
});

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