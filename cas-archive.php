<?php
session_start(); // Start the session

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
if ($conn_mov->connect_error) {
    die("Connection failed: " . $conn_mov->connect_error);
}

// Define the recycle bin directory and metadata file path
$recycleBinDir = 'movuploads/cas-recycle/';
$metadata_file = $recycleBinDir . 'metadata.json'; // Metadata file is inside the recycle bin

// Ensure the metadata file exists
if (!file_exists($metadata_file)) {
    file_put_contents($metadata_file, json_encode([], JSON_PRETTY_PRINT)); // Create an empty JSON file
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['restore_file'])) {
    $file_to_restore = $_POST['restore_file'];  // Get the file name from the form

    if (file_exists($metadata_file)) {
        $metadata = json_decode(file_get_contents($metadata_file), true);

        if (isset($metadata[$file_to_restore])) {
            $original_path = $metadata[$file_to_restore];
            $original_dir = dirname($original_path);
            if (!is_dir($original_dir)) {
                mkdir($original_dir, 0777, true);
            }

            $file_to_restore_path = $recycleBinDir . '/' . $file_to_restore;

            if (file_exists($file_to_restore_path)) {
                if (rename($file_to_restore_path, $original_path)) {
                    unset($metadata[$file_to_restore]);
                    file_put_contents($metadata_file, json_encode($metadata, JSON_PRETTY_PRINT));
                    $_SESSION['success_message'] = "File restored to its original location!";
                } else {
                    $_SESSION['error_message'] = "Error restoring the file.";
                }
            } else {
                $_SESSION['error_message'] = "File not found in the recycle bin.";
            }
        }
    }

    // Redirect to the same page to clear POST data
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Get the item path from the form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_item') {
    $itemPath = $_POST['item_path'];

    // Check if the file exists and it's an image or PDF (optional validation)
    if (file_exists($itemPath)) {
        // If it's an image or PDF, delete it
        $file_extension = strtolower(pathinfo($itemPath, PATHINFO_EXTENSION));

        if (in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif', 'pdf'])) {
            if (unlink($itemPath)) {
                $_SESSION['success_message'] = "File deleted permanently!";
            } else {
                $_SESSION['error_message'] = "Error deleting the file.";
            }
        } else {
            $_SESSION['error_message'] = "Invalid file type for deletion.";
        }
    } else {
        $_SESSION['error_message'] = "File not found.";
    }

    // Redirect to the same page to clear POST data
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

$servername_ur = "l3855uft9zao23e2.cbetxkdyhwsb.us-east-1.rds.amazonaws.com";
$username_ur = "equ6v8i5llo3uhjm"; 
$password_ur = "vkfaxm2are5bjc3q"; 
$dbname_user_registration = "ylwrjgaks3fw5sdj";

// Fetch profile picture for the logged-in user
$conn_profile = new mysqli($servername_ur, $username_ur, $password_ur, $dbname_user_registration);
if ($conn_profile->connect_error) {
    die("Connection failed: " . $conn_profile->connect_error);
}

$uname = $_SESSION['uname'];
$sql_profile = "SELECT picture FROM colleges WHERE uname = ?";
$stmt = $conn_profile->prepare($sql_profile);
$stmt->bind_param("s", $uname);
$stmt->execute();
$result_profile = $stmt->get_result();

$profilePicture = null;
if ($result_profile && $row_profile = $result_profile->fetch_assoc()) {
    $profilePicture = $row_profile['picture'];
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
                text-overflow: ellipsis;
                white-space: nowrap;
                overflow: hidden;
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
            <div class="upload-images">
                <!-- Back Arrow Button with Image -->
                <a href="cas-mov.php" class="back-button">
                    <img src="images/left-arrow.png" alt="Back" class="back-arrow" /> Back
                </a>
            </div>

            <div class="uploaded-images">
                <h2>Archived Items</h2>

                <div class="images-and-names">
                    <?php
                    // Combine folders, images, and PDFs
                    $recycled_items = array_merge(
                        array_filter(glob($recycleBinDir . '*'), 'is_dir'), // Get folders
                        glob($recycleBinDir . "*.{jpg,jpeg,png,gif,pdf}", GLOB_BRACE) // Get images and PDFs
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
                            elseif (preg_match('/\.(jpg|jpeg|png|gif)$/i', $item)): // Check if it's an image
                            ?>
                                <div class="image-item" data-image="<?php echo htmlspecialchars($item); ?>" oncontextmenu="showContextMenu(event, '<?php echo htmlspecialchars($item); ?>')">
                                    <img src="<?php echo htmlspecialchars($item); ?>" alt="<?php echo basename($item); ?>" class="uploaded-img">
                                    <p class="file-name"><?php echo htmlspecialchars(basename($item)); ?></p>
                                </div>
                            <?php
                            elseif (preg_match('/\.pdf$/i', $item)): // Check if it's a PDF
                            ?>
                                <div class="image-item" data-pdf="<?php echo htmlspecialchars($item); ?>" oncontextmenu="showContextMenu(event, '<?php echo htmlspecialchars($item); ?>')">
                                    <div class="pdf-icon">
                                        <img src="images/pdf.png.png" alt="PDF Icon" class="file-icon"> <!-- Add a PDF icon -->
                                    </div>
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

           // Attach event listeners to all items (images and PDFs)
            document.querySelectorAll('.image-item').forEach(item => {
                item.addEventListener('contextmenu', function(event) {
                    const itemPath = item.getAttribute('data-image') || item.getAttribute('data-pdf'); // Handle both images and PDFs
                    handleContextMenu(event, itemPath);
                });
            });


            // Hide context menu when clicking anywhere else
            window.addEventListener('click', function(event) {
                if (!contextMenu.contains(event.target)) {
                    contextMenu.style.display = 'none'; // Hide context menu
                }
            });

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
                        const selectedItemName = selectedItemPath.split('/').pop(); // Get the file name
                        console.log("Restoring file:", selectedItemName);

                        // Create a hidden form for restoring the item
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = ''; // The same page

                        const actionInput = document.createElement('input');
                        actionInput.type = 'hidden';
                        actionInput.name = 'action';
                        actionInput.value = 'restore_image';

                        const restoreFileInput = document.createElement('input');
                        restoreFileInput.type = 'hidden';
                        restoreFileInput.name = 'restore_file';
                        restoreFileInput.value = selectedItemName;

                        form.appendChild(actionInput);
                        form.appendChild(restoreFileInput);
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