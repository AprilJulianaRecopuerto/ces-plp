<?php
session_start();

// Database connection details
$servername = "l3855uft9zao23e2.cbetxkdyhwsb.us-east-1.rds.amazonaws.com";
$username = "equ6v8i5llo3uhjm"; // replace with your database username
$password = "vkfaxm2are5bjc3q"; // replace with your database password
$dbname = "ylwrjgaks3fw5sdj";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: roleaccount.php");
    exit;
}


// Get the logged-in user's username from the session
$username = $_SESSION['username'];

// Fetch user details from the database using prepared statement
$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
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
    header("Location: admin-change-password.php");
    exit;
}

// Handle Change Password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['current_password'], $_POST['new_password'], $_POST['confirm_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate new password and confirmation
    if ($new_password !== $confirm_password) {
        $_SESSION['error'] = 'New passwords do not match.';
    } else {
        // Fetch the user's current password from the database
        $username = $_SESSION['username']; // Assuming username is stored in session
        $password_query = "SELECT password FROM users WHERE username = ?";
        $password_stmt = $conn->prepare($password_query);
        $password_stmt->bind_param("s", $username);
        $password_stmt->execute();
        $password_result = $password_stmt->get_result();

        if ($password_result->num_rows > 0) {
            $password_row = $password_result->fetch_assoc();
            $db_password = $password_row['password'];

            // Check if the current password is correct (assuming plain text passwords for simplicity)
            if ($current_password === $db_password) {
                // Update the new password in the database
                $update_sql = "UPDATE users SET password = ? WHERE username = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("ss", $new_password, $username);
                
                if ($update_stmt->execute()) {
                    $_SESSION['success'] = 'You have successfully changed your password. Pleas log in again.';
                    $_SESSION['alert_shown'] = true; // Set session variable to show alert
                } else {
                    $_SESSION['error'] = 'Error updating password.';
                }
            } else {
                $_SESSION['error'] = 'Current password is incorrect.';
            }
        } else {
            $_SESSION['error'] = 'User not found.';
        }
    }

    // Redirect to the same page to prevent resubmission
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Check if the alert should be shown
if (isset($_SESSION['alert_shown']) && $_SESSION['alert_shown']) {
    echo "<script>
        Swal.fire({
            title: 'Success!',
            text: 'You have successfully changed your password. Please log in again.',
            icon: 'success',
            confirmButtonText: 'OK'
        });
    </script>";
    unset($_SESSION['alert_shown']); // Clear the session variable
}
?>

<!-- Include SweetAlert -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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

        .content {
            margin-left: 320px; /* Align content with sidebar */
            padding-top: 90px; /* Space for navbar */
            padding: 20px;
            display: flex; /* Use flexbox to center the content */
            justify-content: center; /* Center the content horizontally */
            align-items: center; /* Center the content vertically */
            height: calc(100vh - 100px); /* Full height minus navbar */
           
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

        .form-group select, .form-group input[type="password"]{
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

        .password-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .password-wrapper input {
            font-family: 'Poppins', sans-serif;
            font-size: 16px;
            width: 100%; /* Full width */
            height: 40px; /* Fixed height */
            padding: 10px; /* Padding inside the input */
            padding-right: 40px; /* Space for the eye icon */
            border-radius: 5px;
            line-height: normal; /* Ensure consistent line height */
            box-sizing: border-box; /* Ensure padding does not affect total width/height */
        }

        .password-wrapper i {
            position: absolute;
            right: 10px;
            color: #999;
            cursor: pointer; /* Change cursor to pointer */
        }

        .capslock-warning {
            color: #053F5E; /* Warning message color */
            font-style: italic; /* Italic style */
            font-weight: bold; /* Make text bold */
            display: none; /* Hidden by default */
            margin-top: 5px; /* Space above the warning */
        }
		
		/* Chat styles */
		.navbar .profile-container {
			display: flex;
			align-items: center;
		}

		.chat-icon {
			margin-right: 15px;
			font-size: 20px;
			color: #333;
			text-decoration: none;
			position: relative; /* To position the badge correctly */
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
		
		/* Chatbot style */
        .chatbot-container {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 400px; /* Increased width for a more rectangular shape */
            max-height: 400px; /* Limit height of the entire chatbot */
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            display: none; /* Hidden by default */
            flex-direction: column;
            z-index: 1000;
        }

        .chatbot-header {
            background-color: #4CAF50;
            color: white;
            padding: 15px;
            text-align: center;
            font-family: 'Poppins', sans-serif;
            font-size: 18px;
            border-radius: 10px 10px 0 0;
        }

        .chatbot-messages {
            flex-grow: 1; /* Allows the messages area to grow */
            padding: 10px;
            overflow-y: auto; /* Make the messages scrollable */
            border-bottom: 1px solid #ddd;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            max-height: 300px; /* Set a maximum height for the messages area */
            display: flex;
            flex-direction: column;
            gap: 10px; /* Add gap between messages */
        }

        .chatbot-input {
            display: flex;
            padding: 10px;
            border-radius: 0 0 10px 10px;
            background-color: #f1f1f1;
        }

        .chatbot-input input {
            flex-grow: 1;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
        }

        .chatbot-input button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px;
            margin-left: 5px;
            border-radius: 5px;
            cursor: pointer;
        }

        .chatbot-button {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 15px;
            border-radius: 50%;
            cursor: pointer;
            z-index: 1001;
            font-size: 24px; /* Adjust size as needed */
            display: flex; /* Center the icon */
            align-items: center; /* Center the icon vertically */
            justify-content: center; /* Center the icon horizontally */
        }

        .chatbot-message {
            margin-bottom: 10px;
            padding: 8px 12px; /* Add padding for better readability */
            border-radius: 8px;
            display: inline-block; /* Inline-block ensures the width fits the content */
            max-width: 75%; /* Restrict message width to avoid too-wide text blocks */
            word-wrap: break-word; /* Prevent long words from overflowing */
        }

        .chatbot-message.user {
            background-color: #f1f1f1; /* Light background for user messages */
            color: #333; /* Darker text for contrast */
            text-align: right;
            align-self: flex-end; /* Align user messages to the right */
            margin-right: 10px; /* Add some space between the edge of the container and the message */
        }

        .chatbot-message.bot {
            background-color: #4CAF50; /* Match bot messages with the green theme */
            color: white; /* White text for readability */
            text-align: left;
            align-self: flex-start; /* Align bot messages to the left */
            margin-left: 10px; /* Add some space between the edge of the container and the message */
        }

        .close-button {
            background: none;
            border: none;
            color: white;
            font-size: 20px; /* Adjust size as needed */
            cursor: pointer;
            float: right; /* Position to the right */
            margin-left: 10px; /* Spacing from the title */
        }

        /* Typing indicator styling */
        .chatbot-typing {
            font-style: italic; /* Italic style to distinguish the typing message */
            color: #999; /* Lighter color to indicate it's not a permanent message */
            background-color: transparent; /* No background for typing indicator */
            text-align: left;
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

        #passwordCriteria {
            font-family: "Poppins", sans-serif !important;
            list-style-type: none; 
            padding: 0; 
            font-size:15px; 
            margin-left: 5px;
        }

         /* Custom Popup */
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

        .smaller-alert {
            font-size: 14px; /* Adjust text size for a compact look */
            padding: 20px;   /* Adjust padding to mimic a smaller alert box */
        }

    </style>
</head>

<body>
<nav class="navbar">
    <h2>My Profile</h2> 

	    <div class="profile-container">
            <!-- Chat Icon with Notification Badge -->
            <a href="chat.php" class="chat-icon" onclick="resetNotifications()">
                <i class="fa fa-comments"></i>
                <span class="notification-badge" id="chatNotification" style="display: none;">!</span>
            </a>
        <div>

		<!-- Profile Section -->
		<div class="profile" id="profileDropdown">
			<?php
				// Check if a profile picture is set in the session
				if (!empty($_SESSION['pictures'])) {
					echo '<img src="' . $_SESSION['pictures'] . '" alt="Profile Picture">';
				} else {
					// Get the first letter of the username for the placeholder
					$firstLetter = strtoupper(substr($_SESSION['username'], 0, 1));
					echo '<div class="profile-placeholder">' . $firstLetter . '</div>';
				}
			?>
			<span class="username"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
			<i class="fa fa-chevron-down dropdown-icon"></i>
			<div class="dropdown-menu">
				<a href="your-profile.php">Profile</a>
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
                <li><a href="admin-mov.php"><img src="images/task.png">Mode of Verification</a></li>

                <li><a href="admin-responses.php"><img src="images/feedback.png">Responses</a></li>

                <!-- Dropdown for Audit Trails -->
                <button class="dropdown-btn">
                    <img src="images/logs.png"> Audit Trails
                    <i class="fas fa-chevron-down"></i> <!-- Dropdown icon -->
                </button>
                <div class="dropdown-container">
                    <a href="admin-history.php">Log In History</a>
                    <a href="admin-logs.php">Activity Logs</a>
                </div>
            </ul>
        </div>

    <div class="content-editor">
        <div class="form-container">
            <h3>Change Password</h3>

            <form action="admin-change-password.php" method="POST">
                <div class="form-group">
                    <label for="current_password">Current Password:</label>
                    <div class="password-wrapper">
                        <input type="password" id="current_password" name="current_password" required>
                        <i class="fa fa-eye-slash" id="toggleCurrentPassword" style="cursor: pointer;"></i>
                    </div>
                    <div class="capslock-warning" id="currentCapsLockWarning">Caps Lock is on</div>
                </div>

				<div class="form-group">
                    <label for="new_password">New Password:</label>
                    <div class="password-wrapper">
                        <input type="password" id="new_password" name="new_password" required oninput="checkPasswordStrength()">
                        <i class="fa fa-eye-slash" id="toggleNewPassword" style="cursor: pointer;"></i>
                    </div>
                    <div class="capslock-warning" id="newCapsLockWarning">Caps Lock is on</div>
                    <div id="passwordStrengthMessage"></div> <!-- Password strength message -->
                    <ul id="passwordCriteria">
                        <li id="lengthCriteria" class="criteria">Minimum 8 characters</li>
                        <li id="uppercaseCriteria" class="criteria">At least 1 uppercase letter</li>
                        <li id="numberCriteria" class="criteria">At least 1 number</li>
                        <li id="specialCharCriteria" class="criteria">At least 1 special character (e.g., !@#$%^&*)</li>
                    </ul>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm New Password:</label>
                    <div class="password-wrapper">
                        <input type="password" id="confirm_password" name="confirm_password" required>
                        <i class="fa fa-eye-slash" id="toggleConfirmPassword" style="cursor: pointer;"></i>
                    </div>
                    <div class="capslock-warning" id="confirmCapsLockWarning">Caps Lock is on</div>
                    <div id="passwordMatchIndicator" class="match-indicator"></div> <!-- Added match indicator -->
                </div>

                <div class="button-container">
                    <button type="submit">Change Password</button>
                </div>
            </form>
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
                        fetch('logout.php?action=logout')
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

            // Check for success message and display SweetAlert if present
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
                    window.location.href = 'roleaccount.php';
                }
            });

            <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            // Check for error message and display SweetAlert if present
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

            // Toggle visibility for current password
            document.getElementById('toggleCurrentPassword').addEventListener('click', function() {
                const currentPasswordField = document.getElementById('current_password');
                const currentPasswordIcon = document.getElementById('toggleCurrentPassword');
                
                // Check the type of the input field
                if (currentPasswordField.type === 'password') {
                    currentPasswordField.type = 'text';  // Show password
                    currentPasswordIcon.classList.remove('fa-eye-slash'); // Change to open eye
                    currentPasswordIcon.classList.add('fa-eye'); // Show open eye icon
                } else {
                    currentPasswordField.type = 'password';  // Hide password
                    currentPasswordIcon.classList.remove('fa-eye'); // Change to closed eye
                    currentPasswordIcon.classList.add('fa-eye-slash'); // Show closed eye icon
                }
            });

            // Toggle visibility for new password
            document.getElementById('toggleNewPassword').addEventListener('click', function() {
                const newPasswordField = document.getElementById('new_password');
                const newPasswordIcon = document.getElementById('toggleNewPassword');
                
                if (newPasswordField.type === 'password') {
                    newPasswordField.type = 'text';
                    newPasswordIcon.classList.remove('fa-eye-slash');
                    newPasswordIcon.classList.add('fa-eye');
                } else {
                    newPasswordField.type = 'password';
                    newPasswordIcon.classList.remove('fa-eye');
                    newPasswordIcon.classList.add('fa-eye-slash');
                }
            });

            // Toggle visibility for confirm password
            document.getElementById('toggleConfirmPassword').addEventListener('click', function() {
                const confirmPasswordField = document.getElementById('confirm_password');
                const confirmPasswordIcon = document.getElementById('toggleConfirmPassword');
                
                if (confirmPasswordField.type === 'password') {
                    confirmPasswordField.type = 'text';
                    confirmPasswordIcon.classList.remove('fa-eye-slash');
                    confirmPasswordIcon.classList.add('fa-eye');
                } else {
                    confirmPasswordField.type = 'password';
                    confirmPasswordIcon.classList.remove('fa-eye');
                    confirmPasswordIcon.classList.add('fa-eye-slash');
                }
            });

            // Caps Lock detection logic
            function checkCapsLock(event, warningElementId) {
                const isCapsLockOn = event.getModifierState && event.getModifierState('CapsLock');
                const warningElement = document.getElementById(warningElementId);
                warningElement.style.display = isCapsLockOn ? 'block' : 'none'; // Show or hide warning
            }

            // Attach event listeners to password fields
            const passwordFields = [
                { id: 'current_password', warningId: 'currentCapsLockWarning' },
                { id: 'new_password', warningId: 'newCapsLockWarning' },
                { id: 'confirm_password', warningId: 'confirmCapsLockWarning' },
            ];

            passwordFields.forEach(field => {
                const inputField = document.getElementById(field.id);
                inputField.addEventListener('keyup', function(event) {
                    checkCapsLock(event, field.warningId);
                });
            });

            function checkPasswordStrength() {
                const password = document.getElementById('new_password').value;
                const strengthMessage = document.getElementById('passwordStrengthMessage');
                
                // Elements for criteria
                const lengthCriteria = document.getElementById('lengthCriteria');
                const uppercaseCriteria = document.getElementById('uppercaseCriteria');
                const numberCriteria = document.getElementById('numberCriteria');
                const specialCharCriteria = document.getElementById('specialCharCriteria');
                
                // Basic criteria for password strength
                const isLengthValid = password.length >= 8;
                const isUppercaseValid = /[A-Z]/.test(password);
                const isNumberValid = /\d/.test(password);
                const isSpecialCharValid = /[!@#$%^&*(),.?":{}|<>]/.test(password);

                // Update criteria visibility
                lengthCriteria.style.color = isLengthValid ? 'green' : 'red';
                uppercaseCriteria.style.color = isUppercaseValid ? 'green' : 'red';
                numberCriteria.style.color = isNumberValid ? 'green' : 'red';
                specialCharCriteria.style.color = isSpecialCharValid ? 'green' : 'red';

                // Determine overall password strength
                const criteriaMet = [isLengthValid, isUppercaseValid, isNumberValid, isSpecialCharValid].filter(Boolean).length;

                let strength = '';
                if (criteriaMet <= 2) {
                    strength = 'Weak';
                    strengthMessage.style.color = 'red';
                } else if (criteriaMet === 3) {
                    strength = 'Moderate';
                    strengthMessage.style.color = 'orange';
                } else {
                    strength = 'Strong';
                    strengthMessage.style.color = 'green';
                }

                // Display the strength message
                strengthMessage.textContent = 'Password Strength: ' + strength;
            }

            document.addEventListener('DOMContentLoaded', function () {
        const username = "<?php echo $_SESSION['username']; ?>"; // Get the username from PHP session

        // Function to log activity
        function logActivity(buttonName) {
            const timestamp = new Date().toISOString(); // Get current timestamp

            fetch('log_activity.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    buttonFunction: buttonName // Updated to match the PHP variable
                }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'error') {
                    console.error('Error logging activity:', data.message);
                } else {
                    console.log('Activity logged successfully:', data);
                }
            })
            .catch(error => {
                console.error('Error logging activity:', error);
            });
        }

        // Add event listeners specifically to buttons and links
        const trackableElements = document.querySelectorAll('button, a'); // Select all buttons and links
        trackableElements.forEach(element => {
            element.addEventListener('click', function (event) {
                const buttonName = this.tagName === 'BUTTON' ? this.innerText.trim() || "Unnamed Button" : this.textContent.trim() || "Unnamed Link";
                logActivity(buttonName); // Log the button/link activity
            });
        });
    });
        </script>
</body>
</html>
