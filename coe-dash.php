<?php
session_start();

if (!isset($_SESSION['uname'])) {
    // Redirect to login page if the session variable is not set
    header("Location: roleaccount.php");
    exit;
}

// Initialize an empty array in the session to track notified tasks
if (!isset($_SESSION['notified_tasks'])) {
    $_SESSION['notified_tasks'] = [];
}

// Database credentials for proj_list
$servername = "d6ybckq58s9ru745.cbetxkdyhwsb.us-east-1.rds.amazonaws.com";
$username_db = "t9riamok80kmok3h";
$password_db = "lzh13ihy0axfny6d";
$dbname_proj_list = "g8ri1hhtsfx77ptb";//projlist

// Create connection to proj_list database
$conn = new mysqli($servername, $username_db, $password_db, $dbname_proj_list);

// Initialize unread count
$unreadCount = 0;

// Query to count unread notifications
$query = "SELECT COUNT(*) as count FROM coe_notifications WHERE coe_status = 'unread'";
$result = $conn->query($query);

if ($result && $row = $result->fetch_assoc()) {
    $unreadCount = $row['count'];
}

// Fetch all notifications (both read and unread)
$sql = "SELECT id, task_description, created_at, coe_status FROM coe_notifications ORDER BY created_at DESC";
$result = $conn->query($sql);

$notifications = [];

if ($result->num_rows > 0) {
    // Fetch all notifications
    while($row = $result->fetch_assoc()) {
        $notifications[] = [
            'id' => $row['id'],
            'task_description' => $row['task_description'],
            'created_at' => $row['created_at'],
            'coe_status' => $row['coe_status'],
        ];
    }
} else {
    $notifications[] = ['message' => 'No notifications found.'];
}

// Handle AJAX request to delete notification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_notification'])) {
    $notificationId = $_POST['delete_notification'];

    // Database connection for deletion
    $conn = new mysqli($servername, $username_db, $password_db, $dbname_proj_list);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Delete the notification
    $sql = "DELETE FROM coe_notifications WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $notificationId);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete notification.']);
    }

    $stmt->close();
    $conn->close();
    exit;
}

// Set up date ranges (1 day, 1 week, and today)

date_default_timezone_set('Asia/Manila');

$oneWeekInterval = new DateInterval('P7D'); // 1 week
$currentDate = new DateTime(); // Current date

$oneWeekLater = clone $currentDate;
$oneWeekLater->add($oneWeekInterval);
$oneWeekLaterFormatted = $oneWeekLater->format('Y-m-d');


$oneDayInterval = new DateInterval('P1D'); // 1 day
$today = $currentDate->format('Y-m-d'); // Current date in Y-m-d format

// Create a copy of the current date and add the interval separately
$oneDayLater = clone $currentDate;
$oneDayLater->add($oneDayInterval);
$oneDayLaterFormatted = $oneDayLater->format('Y-m-d');

// SQL query to fetch tasks that are due today, in 1 week, or in 1 day
$sql_due_date = "
    SELECT id, task_description, due_date
    FROM coe_tasks
    WHERE status = 'pending'
    AND (
        due_date = ? OR  -- Task due today
        due_date <= ? OR  -- Task due in 1 week
        due_date <= ?     -- Task due in 1 day
    )
    ORDER BY due_date ASC
";

$stmt = $conn->prepare($sql_due_date);
$stmt->bind_param("sss", $today, $oneWeekLaterFormatted, $oneDayLaterFormatted);
$stmt->execute();
$result_due_date = $stmt->get_result();

if ($result_due_date->num_rows > 0) {
    // Loop through tasks and display them
    while ($row = $result_due_date->fetch_assoc()) {
        $taskId = $row['id'];
        $taskDescription = $row['task_description'];
        $dueDate = $row['due_date'];

        // Check if the task has already been notified
        if (!in_array($taskId, $_SESSION['notified_tasks'])) {
            // Check the due date and send notifications accordingly
            $notificationMessage = '';
            if ($dueDate == $today) {
                $notificationMessage = "The task '$taskDescription' is due today.";
            } elseif ($dueDate == $oneWeekLaterFormatted) {
                $notificationMessage = "The task '$taskDescription' is due in 1 week.";
            } elseif ($dueDate == $oneDayLaterFormatted) {
                $notificationMessage = "The task '$taskDescription' is due in 1 day.";
            }

            if (!empty($notificationMessage)) {
                // Insert notification for the user with 'unread' status
                $insertNotification = $conn->prepare("INSERT INTO coe_notifications (department, task_description, coe_status, created_at) VALUES (?, ?, 'unread', NOW())");
                $department = 'COE'; // Set department explicitly (or dynamically if needed)
                $insertNotification->bind_param("ss", $department, $notificationMessage);
                $insertNotification->execute();
                $insertNotification->close();

                // Add the task ID to the notified tasks session array to prevent future notifications
                $_SESSION['notified_tasks'][] = $taskId;
            }
        }
    }
}

$sn = "l3855uft9zao23e2.cbetxkdyhwsb.us-east-1.rds.amazonaws.com";
$un = "equ6v8i5llo3uhjm";
$psd = "vkfaxm2are5bjc3q";
$dbname_user_registration = "ylwrjgaks3fw5sdj";
// Fetch the profile picture from the colleges table in user_registration

$conn_profile = new mysqli($sn, $un, $psd, $dbname_user_registration);
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


$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>CES PLP</title>

        <link rel="icon" href="images/icoon.png">

        <!-- SweetAlert CSS and JavaScript -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>  <!-- Include Chart.js -->

        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

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
                margin-left: 320px;
                padding: 20px;
            }

            .activities-container {
                display: grid; /* Use CSS Grid for layout */
                grid-template-columns: repeat(2, 1fr); /* Two equal columns */
                gap: -15px !important; /* Space between the grid items */
                margin-top: 110px; /* Adjust this value based on your navbar height */
            }

            .total-activities, .pending-activities {
                width: 94.7%; /* Adjust width to fit side-by-side */
                height: 90%;
                padding: 10px;
                border: 1px solid #ddd;
                border-radius: 5px;
                background-color: #ffffff;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                margin-right: 3%; /* Add a small margin between the containers */
                cursor:pointer;
            }

            .total-activities a {
                text-decoration: none; /* Remove underline from link */
                color: inherit; /* Inherit text color */
            }

            .total-activities:hover {
                background-color: #f0f0f0; /* Optional: Add hover effect */
            }

            .total-activities img, .pending-activities img {
                width: 20%;
                height: 20%;
                margin-top: -32px;
                margin-right: 15px;
                margin-bottom: 5px;
                align-items: center;
            }

            .total-activities h2, .pending-activities h2 {
                font-family: "Poppins", sans-serif;
                font-size: 25px;
                margin-left: 15px;
                margin-top: 5px;
                margin-bottom: 3px;
            }

            .activities-details {
                display: flex;
                align-items: center; /* Center items vertically */
                justify-content: space-between; /* Space between count and image */
            }

            .total-activities-count, .pending-activities-count {
                font-family: "Poppins", sans-serif;
                font-size: 35px;
                margin-left: 25px;
                margin-top: 5px;
                margin-bottom: 3px;
            }

            .project-updates {
                font-family: "Poppins", sans-serif;
                background-color: #FAFAFA;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                border: 1px solid #ddd;
                padding: 20px;
                width: 95.5%;
                border-radius: 10px;
                height: auto;
                margin-top: 40px;
                position: relative;
            }

            .project-updates h2 {
                margin-top: 5px;
            }

            .updates-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 10px; /* Adjust margin as needed */
            }

            .custom-swal-popup {
                font-family: 'Poppins', sans-serif;
                font-size: 16px; /* Increase the font size */
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

            .custom-error-popup {
                font-family: 'Poppins', sans-serif;
                width: 400px !important; /* Set a larger width */
            }

            .custom-error-confirm {
                font-family: 'Poppins', sans-serif;
                font-size: 17px;
                background-color: #e74c3c;
                color: #fff;
                border-radius: 10px;
                cursor: pointer;
                outline: none; /* Remove default focus outline */
            }

            canvas {
                font-family: 'Poppins', sans-serif !important;
                max-height: 330px;
                width: 500px;
                margin: auto;
                display: block;
            }
            
            .filters {
                font-family: 'Poppins', sans-serif;
                margin-bottom: 20px;
                text-align: center;
            }

            .filters select {
                font-family: 'Poppins', sans-serif;
                font-size: 14px;
                padding: 5px;
                margin: 0 10px;
            }

            .filters button {
                background-color: #4CAF50;
                border: none;
                color: white;
                padding: 6.5px 20px;
                margin-left: 10px;
                border-radius: 5px;
                font-size: 14px;
                cursor: pointer;
                transition: background-color 0.3s;
                font-family: 'Poppins', sans-serif;
            }

            .content-demo {
                font-family: 'Poppins', sans-serif;
                background-color: white;       /* Set background color to white */
                width: 540px;   
                height:   450px;           /* Set width to 600 pixels */
                padding: 15px; 
                margin-top: 25px;       
                margin-bottom: 15px;         /* Set padding to 15 pixels on all sides */
                border-radius: 5px;           /* Rounded corners with a radius of 10 pixels */
                box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); /* Add shadow effect */
                text-align: center;            /* Center align the text inside */
            }


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
                font-family: 'Poppins', sans-serif;
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

            .notification { 
                position: relative; 
            }

            .notification-count {
                position: absolute;
                top: -18px;
                margin-left: 265px;
                background: red;
                color: white;
                border-radius: 50%;
                padding: 5px 10px;
                font-size: 10px;
            }

            .notification-container { 
                display: none; 
            }
			
			.container {
				font-family: 'Poppins', sans-serif;
                max-width: 550px;
                margin: auto;
				height:635px;
                background-color: white;    
                margin-left: 925px;
                margin-top: -690px;
                padding: 10px;
                border: 1px solid #ddd;
                border-radius: 5px;
                background-color: #ffffff;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                overflow-x:auto;
            }

            .container h2 {
                text-align:center;
                font-family: 'Poppins', sans-serif;
            }
            
            .notification-container {
                font-family: 'Poppins', sans-serif;
                display: none; /* Initially hide the container */
                border: 1px solid #ccc;
                border-radius: 8px; /* Slightly more rounded corners */
                background-color: #fff; /* White background for better contrast */
                position: absolute; /* Position it below the icon */
                margin-top: 18px;
                margin-left: -15px;
                padding: 15px; /* Increased padding for a more spacious feel */
                width: 280px; /* Increased width */
                max-height: 300px; /* Set a max height to allow for scrolling */
                overflow-y: auto; /* Enable vertical scrolling */
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2); /* More pronounced shadow for depth */
                z-index: 1000; /* Ensure it's above other elements */
            }

            .notification-icon {
                position: absolute;
                font-size: 18px;
                cursor: pointer;
                color: #333; /* Adjust color as needed */
                margin-left: 285px;  
                margin-top: -4px;
            }

            .notification-container ul {
                font-family: 'Poppins', sans-serif;
                list-style-type: none; /* Removes bullets */
                padding: 0; /* Removes padding */
                margin: 0; /* Removes margin */
            }

			/* TASK SECTION */
			.task-item {
				padding: 10px;
				border: 1px solid #ccc;
				margin-bottom: 10px;
			}

			.task-item button.delete-task {
				background-color: #f44336;
				color: white;
				border: none;
				padding: 5px 10px;
				cursor: pointer;
			}

			.task-item button.delete-task:hover {
				background-color: #d32f2f;
			}
			
			.done-button {
				background-color: #4CAF50;
                border: none;
                color: white;
                padding: 10px 20px;
                margin-left: 10px;
                border-radius: 5px;
                font-size: 14px;
                cursor: pointer;
                transition: background-color 0.3s;
                font-family: 'Poppins', sans-serif;
			}

            /* Style for the delete button */
            .delete-btn {
                font-family: 'Poppins', sans-serif;
                position: absolute;
                right: 10px; /* Adjust to your preference for spacing */
                transform: translateY(-50%); /* Vertically center the button */
                padding: 5px 10px;
                background-color: #e74c3c;
                color: white;
                border: none;
                border-radius: 5px;
                cursor: pointer;
            }

            .delete-btn:hover {
                background-color: darkred;
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

            .recommend {
				font-family: 'Poppins', sans-serif;
                max-width: 550px;
                margin: auto;
                background-color: white;    
                margin-left: 500px;
                
                padding: 10px;
                border: 1px solid #ddd;
                border-radius: 5px;
                background-color: #ffffff;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                overflow-x:auto;
            }

            table {
                width: 100%;
                border-collapse: collapse;
            }
            th, td {
                padding: 8px 12px;
                text-align: left;
                border: 1px solid #ddd;
            }
            th {
                background-color: #f4f4f4;
            }
            .smaller-alert {
                font-size: 14px; /* Adjust text size for a compact look */
                padding: 20px;   /* Adjust padding to mimic a smaller alert box */
            }
        </style>
    </head>

    <body>
        <nav class="navbar">
            <h2>Dashboard</h2> 

            <div class="notification">
                <i class="fa fa-bell notification-icon" onclick="toggleNotifications()"></i>

                <!-- Display the count of unread notifications in the badge -->
                <?php if ($unreadCount > 0): ?>
                    <span class="notification-count" id="notification-count"><?php echo $unreadCount; ?></span>
                <?php endif; ?>

                <div class="notification-container" id="notification-container" style="display:none;">
                    <h3 style="display: inline-block;">Notifications</h3>

                    <?php
                    // Check if $notifications is set and is a non-empty array
                    if (isset($notifications) && is_array($notifications) && count($notifications) > 0): ?>
                        <ul>
                        <?php foreach ($notifications as $notification): ?>
                            <?php if (isset($notification['task_description'], $notification['created_at'])): ?>
                                <li id="notification-<?php echo $notification['id']; ?>" class="notification-item">
                                    <div class="notification-content">
                                        <strong><?php echo htmlspecialchars($notification['task_description']); ?></strong>
                                        <span><?php echo htmlspecialchars($notification['created_at']); ?></span>
                                    </div>
                                    <button class="delete-btn" onclick="deleteNotification(<?php echo $notification['id']; ?>)">Delete</button>
                                </li>
                                <hr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <!-- Show this message if there are no notifications -->
                        <p style="text-align: center; color: #666; font-style: italic; margin-top: 10px;">No notifications available.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="profile-container">
                <!-- Chat Icon with Notification Badge -->
                <a href="coe-chat.php" class="chat-icon" onclick="resetNotifications()">
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
                        <a href="coe-your-profile.php">Profile</a>
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
                <li><a href="coe-dash.php" class="active"><img src="images/home.png">Dashboard</a></li>
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
                <li><a href="coe-mov.php"><img src="images/task.png">Mode of Verification</a></li>

                <li><a href="coe-responses.php"><img src="images/feedback.png">Responses</a></li>

                <!-- Dropdown for Audit Trails -->
                <button class="dropdown-btn">
                    <img src="images/logs.png"> Audit Trails
                    <i class="fas fa-chevron-down"></i> <!-- Dropdown icon -->
                </button>
                <div class="dropdown-container">
                    <a href="coe-history.php">Log In History</a>
                    <a href="coe-activitylogs.php">Activity Logs</a>
                </div>
            </ul>
        </div>
        
        <div class="content">
            <div class="activities-container">
                <div class="total-activities">
                    <a href="coe-projlist.php">
                        <h2>Total Activities</h2>
                        <div class="activities-details">
                            <div class="total-activities-count">
                                <?php
                                // Database credentials
                                $servername = "ryvdxs57afyjk41z.cbetxkdyhwsb.us-east-1.rds.amazonaws.com";
                                $username = "zf8r3n4qqjyrfx7o";
                                $password = "su6qmqa0gxuerg98";
                                $dbname = "hpvs3ggjc4qfg9jp";

                                // Create connection
                                $conn = new mysqli($servername, $username, $password, $dbname);

                                // Check connection
                                if ($conn->connect_error) {
                                    die("Connection failed: " . $conn->connect_error);
                                }

                                // SQL query to count total activities
                                $sql = "SELECT COUNT(*) as total FROM coe";
                                $result = $conn->query($sql);

                                if ($result->num_rows > 0) {
                                    $row = $result->fetch_assoc();
                                    echo $row['total'];
                                } else {
                                    echo "0";
                                }

                                $conn->close();
                                ?>
                            </div>
                            <img src="images/total.png" alt="Up Icon">
                        </div>
                    </a>
                </div>
            </div>

            <div class="content-demo">
                <div class="filters">
                    <h3>Number of Projects per College</h3>

                    <form method="POST" action="">
                        <label for="month">Month: </label>
                        <select name="month" id="month">
                            <option value="" <?php echo isset($_POST['month']) && $_POST['month'] == '' ? 'selected' : ''; ?>>All Months</option>
                            <?php
                            $months = [
                                "01" => "January",
                                "02" => "February",
                                "03" => "March",
                                "04" => "April",
                                "05" => "May",
                                "06" => "June",
                                "07" => "July",
                                "08" => "August",
                                "09" => "September",
                                "10" => "October",
                                "11" => "November",
                                "12" => "December",
                            ];

                            // Get selected month from POST request
                            $selectedMonth = isset($_POST['month']) ? $_POST['month'] : ''; 

                            foreach ($months as $value => $name) {
                                $selected = ($value == $selectedMonth) ? 'selected' : ''; // Keep selected if form was submitted
                                echo "<option value=\"$value\" $selected>$name</option>";
                            }
                            ?>
                        </select>

                        <label for="year">Year: </label>
                        <select name="year" id="year">
                            <option value="" <?php echo isset($_POST['year']) && $_POST['year'] == '' ? 'selected' : ''; ?>>All Years</option>
                            <?php
                            $currentYear = date("Y");

                            // Get selected year from POST request
                            $selectedYear = isset($_POST['year']) ? $_POST['year'] : ''; 

                            for ($year = 2015; $year <= $currentYear; $year++) {
                                $selected = ($year == $selectedYear) ? 'selected' : ''; // Keep selected if form was submitted
                                echo "<option value=\"$year\" $selected>$year</option>";
                            }
                            ?>
                        </select>
                        <button type="submit">Filter</button>
                    </form>
                </div>

                <canvas id="projectsChart"></canvas>
                <?php
                // Database connection details
                $host = 'ryvdxs57afyjk41z.cbetxkdyhwsb.us-east-1.rds.amazonaws.com';  // or your host
                $db = 'hpvs3ggjc4qfg9jp';     // database name
                $user = 'zf8r3n4qqjyrfx7o'; // database username
                $pass = 'su6qmqa0gxuerg98'; // database password

                // Create a connection to the database
                $conn = new mysqli($host, $user, $pass, $db);

                // Check if connection was successful
                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }

                // Initialize selected month and year as empty strings
                $selectedMonth = '';
                $selectedYear = '';

                // Check if form is submitted
                if ($_SERVER["REQUEST_METHOD"] == "POST") {
                    // Get the selected month and year from the form
                    $selectedMonth = $_POST['month'] ?? ''; // Use POST variable
                    $selectedYear = $_POST['year'] ?? ''; // Use POST variable
                }

                // Array to store the number of projects for each college
                $colleges = ['cas', 'cba', 'ccs', 'coed', 'coe', 'cihm', 'con'];
                $projectCounts = [];

                // Loop through each college and get the project count
                foreach ($colleges as $college) {
                    // Modify SQL query to filter by month and year if provided
                    $sql = "SELECT COUNT(*) as project_count FROM $college WHERE 1=1";
                    
                    // Add month and year filter if selected
                    if (!empty($selectedMonth)) {
                        $sql .= " AND MONTH(date_of_sub) = '$selectedMonth'";  // Replace 'date_of_sub' with the actual column name that stores date in your table
                    }
                    if (!empty($selectedYear)) {
                        $sql .= " AND YEAR(date_of_sub) = '$selectedYear'";
                    }

                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $projectCounts[] = $row['project_count'];
                        }
                    } else {
                        $projectCounts[] = 0;  // If no projects found
                    }
                }

                // Close the connection
                $conn->close();
                ?>
                </div>
			</div>
			
            <div class="container">
                <h2>Colleges To-Do List</h2>

                <?php
                // Database connection details
                $servername = "d6ybckq58s9ru745.cbetxkdyhwsb.us-east-1.rds.amazonaws.com";
                $username = "t9riamok80kmok3h";
                $password = "lzh13ihy0axfny6d";
                $dbname = "g8ri1hhtsfx77ptb";  // Database for tasks

                // Create connection
                $conn = new mysqli($servername, $username, $password, $dbname);

                // Check connection
                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }

                $taskDoneMessage = '';  // Variable to store the success message
                $taskDescription = '';  // Variable to store task description

                // Handle task marking as done
                if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['task_id'])) {
                    $task_id = $_POST['task_id'];

                    // Fetch the task description before updating
                    $stmt = $conn->prepare("SELECT task_description FROM coe_tasks WHERE id = ?");
                    $stmt->bind_param("i", $task_id);
                    $stmt->execute();
                    $stmt->bind_result($taskDescription);
                    $stmt->fetch();
                    $stmt->close();

                    // Update the task status to 'done'
                    $stmt = $conn->prepare("UPDATE coe_tasks SET status = 'done' WHERE id = ?");
                    $stmt->bind_param("i", $task_id);

                    if ($stmt->execute()) {
                        // Set the success message
                        $taskDoneMessage = 'Task Done: ' . htmlspecialchars($taskDescription);

                        // Database credentials for 'mov' (notifications)
                        $servername_mov = "arfo8ynm6olw6vpn.cbetxkdyhwsb.us-east-1.rds.amazonaws.com";
                        $username_mov = "tz8thfim1dq7l3rf";
                        $password_mov = "wzt4gssgou2ofyo7";
                        $dbname_mov = "uv1qyvm0b8oicg0v";

                        $conn_mov = new mysqli($servername_mov, $username_mov, $password_mov, $dbname_mov);

                        // Check connection for 'mov'
                        if ($conn_mov->connect_error) {
                            die("Connection to 'mov' database failed: " . $conn_mov->connect_error);
                        }

                        // Insert notification into mov database
                        $insertNotification = $conn_mov->prepare("INSERT INTO notifications (project_name, department, notification_message, status, created_at) VALUES (?, 'College of Arts and Sciences', ?, 'unread', NOW())");
                        $notificationMessage = "A new task has been submitted by College of Arts and Sciences.";
                        $insertNotification->bind_param("ss", $taskDescription, $notificationMessage);
                        $insertNotification->execute();
                        $insertNotification->close();
                        $conn_mov->close();

                        // Show SweetAlert message
                        echo "<script>
                            Swal.fire({
                                title: 'Task Done!',
                                text: 'Successfully Submitted to Admin: " . htmlspecialchars($taskDescription) . "',
                                icon: 'success',
                                timer: 2000, // Automatically close after 2 seconds
                                showConfirmButton: false, // Hide the OK button
                                customClass: {
                                    popup: 'custom-swal-popup',
                                    title: 'custom-swal-title',
                                    confirmButton: 'custom-swal-confirm',
                                    cancelButton: 'custom-swal-cancel'
                                }
                            }).then(() => {
                                window.location.href = window.location.href;  // Reload the page after showing the message
                            });
                        </script>";
                    } else {
                        echo "<script>
                            Swal.fire({
                                title: 'Error',
                                text: 'Error marking task as done: " . $stmt->error . "',
                                icon: 'error',
                                 timer: 2000, // Automatically close after 2 seconds
                                showConfirmButton: false, // Hide the OK button
                                customClass: {
                                    popup: 'custom-error-popup',
                                    title: 'custom-error-title',
                                    confirmButton: 'custom-error-confirm',
                                    cancelButton: 'custom-error-cancel'
                                }
                            });
                        </script>";
                    }

                    $stmt->close();
                    exit(); // Prevent further processing after update
                }

                // SQL query to fetch tasks that are pending
                $sql = "SELECT id, task_description, due_date FROM coe_tasks WHERE status = 'pending' ORDER BY created_at DESC";
                $result = $conn->query($sql);

                // Check if there are tasks
                if ($result->num_rows > 0) {
                    echo "<h3>To-Do List</h3>"; // Title for To-Do section
                    // Loop through tasks and display them with a "Done" button
                    while ($row = $result->fetch_assoc()) {
                        echo "<div class='task-item' style='margin-bottom: 20px; display: flex; justify-content: space-between; align-items: flex-start;'>";

                        // Display task description
                        echo "<div style='flex-grow: 1;'>";
                        echo "<strong>" . htmlspecialchars($row['task_description']) . "</strong><br>";

                        // Display due date
                        echo "<strong><label>Due: </label></strong><span>" . htmlspecialchars($row['due_date']) . "</span>";
                        echo "</div>"; // End task description and due date

                        // Form to mark task as done
                        echo "<form method='POST' style='display:inline;' id='task-form-" . $row['id'] . "'>";
                        echo "<input type='hidden' name='task_id' value='" . $row['id'] . "'>";
                        echo "<button type='button' class='done-button' onclick='showConfirmation(" . $row['id'] . ");'>Done</button>";
                        echo "</form>";

                        echo "</div><hr>";
                    }
                } else {
                    echo "<p>No tasks found in To-Do List.</p>";
                }

                // SQL query to fetch tasks that are done
                $sql_done = "SELECT id, task_description, due_date FROM coe_tasks WHERE status = 'done' ORDER BY created_at DESC";
                $result_done = $conn->query($sql_done);

                // Check if there are done tasks
                if ($result_done->num_rows > 0) {
                    echo "<h3>Done Tasks</h3>"; // Title for Done section
                    // Loop through done tasks and display them
                    while ($row = $result_done->fetch_assoc()) {
                        echo "<div class='task-item' style='margin-bottom: 20px;'>";
                        echo "<strong>" . htmlspecialchars($row['task_description']) . "</strong><br>";
                        echo "<strong><label>Due: </label></strong><span>" . htmlspecialchars($row['due_date']) . "</span>";
                        echo "</div><hr>";
                    }
                } else {
                    echo "<p>No tasks found in Done List.</p>";
                }
                $conn->close();
                ?>
            </div>

            <!-- Chatbot button -->
            <button class="chatbot-button" onclick="toggleChatbot()">
                <i class="fas fa-comment-dots"></i> <!-- Chat icon -->
            </button>

            <!-- Chatbot container -->
            <div class="chatbot-container" id="chatbot">
                <div class="chatbot-header">
                    Chat with us
                    <button class="close-button" onclick="toggleChatbot()">×</button> <!-- Close button -->
                </div>
                <div class="chatbot-messages" id="chatMessages"></div>
                <div class="chatbot-input">
                    <input type="text" id="chatInput" placeholder="Type your message...">
                    <button onclick="sendMessage()">Send</button>
                </div>
            </div>
           
            <?php
                // Function to fetch the recommended events for a department from Flask API
                $url = "https://ces-python-1da82b6d81f5.herokuapp.com/get_recommended_events/College%20of%20Arts%20and%20Sciences";
                
                // Use cURL to fetch the data from the Flask API
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                $response = curl_exec($ch);
                curl_close($ch);
                
                // Decode the JSON response
                $events = json_decode($response, true);
                
                // If the response includes raw HTML, inject it into the page.
                if ($response) {
                    // Optionally add inline styles for font-family here
                    echo "<style>body { font-family: 'Poppins', sans-serif; }</style>";
                    echo $response;  // Display the fetched HTML content
                }
            ?>
            
		</div>

        <script>
            // Data from PHP directly into JavaScript variables
            var projectCounts = <?php echo json_encode($projectCounts); ?>;
            var collegeLabels = ['CAS', 'CBA', 'CCS', 'COED', 'COE', 'CIHM', 'CON'];

            // Create the bar chart using Chart.js
            const ctx = document.getElementById('projectsChart').getContext('2d');
            const projectsChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: collegeLabels,
                    datasets: [{
                        label: 'Number of Projects',
                        data: projectCounts,
                        backgroundColor: [
                            'rgba(128, 0, 128, 0.5)', // Purple
                            'rgba(255, 255, 0, 0.5)',  // Yellow
                            'rgba(128, 128, 128, 0.5)', // Gray
                            'rgba(0, 0, 255, 0.5)',     // Blue
                            'rgba(255, 165, 0, 0.5)',   // Orange
                            'rgba(128, 0, 0, 0.5)',     // Maroon
                            'rgba(255, 192, 203, 0.5)'  // Pink
                        ],
                        borderColor: [
                            'rgba(128, 0, 128, 1)', // Purple border
                            'rgba(255, 255, 0, 1)',  // Yellow border
                            'rgba(128, 128, 128, 1)', // Gray border
                            'rgba(0, 0, 255, 1)',     // Blue border
                            'rgba(255, 165, 0, 1)',   // Orange border
                            'rgba(128, 0, 0, 1)',     // Maroon border
                            'rgba(255, 192, 203, 1)'  // Pink border
                        ],
                        borderWidth: 2,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            labels: {
                                font: {
                                    family: 'Poppins',
                                    size: 14,
                                }
                            }
                        },
                        tooltip: {
                            bodyFont: {
                                family: 'Poppins',
                                size: 12,
                            },
                            titleFont: {
                                family: 'Poppins',
                                size: 14,
                            }
                        },
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            min: 0,
                            max: 50,
                            ticks: {
                                font: {
                                    family: 'Poppins',
                                    size: 12,
                                },
                                callback: function(value, index, values) {
                                    if ([0, 10, 15, 20, 25, 30, 35, 40, 45].includes(value)) {
                                        return value;
                                    }
                                    return '';
                                },
                            }
                        },
                        x: {
                            barPercentage: 0.5,
                            categoryPercentage: 0.8,
                            ticks: {
                                font: {
                                    family: 'Poppins',
                                    size: 12,
                                }
                            },
                            title: {
                                display: true,
                                text: 'Colleges',
                                font: {
                                    family: 'Poppins',
                                    size: 14,
                                }
                            }
                        }
                    },
                    elements: {
                        bar: {
                            borderWidth: 2,
                            borderRadius: 10, // Set border radius for bars
                        }
                    }
                }
            });

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
            };
			
            //NOTIFICATIONBELL
			let notificationInterval;

            function toggleNotifications() {
                const notificationContainer = document.getElementById("notification-container");
                const notificationCountElement = document.querySelector('.notification-count');

                // Toggle the visibility of the notification dropdown
                notificationContainer.style.display = notificationContainer.style.display === "none" ? "block" : "none";

                // Hide the notification badge (count) when toggling
                notificationCountElement.style.display = "none";

                // If notifications are not already loaded, fetch them
                if (!notificationContainer.querySelector('ul')) {
                    fetchNotifications();
                } else {
                    // Mark notifications as read when the bell is opened
                    markNotificationsAsRead();
                }
            }

            // Set an interval to check for due tasks every minute (60000 milliseconds)
            setInterval(function() {
                // AJAX call to check for due tasks
                fetch('check_due_tasks.php') // Path to the PHP script that checks for due tasks
                    .then(response => response.json()) // Assume the PHP script returns a JSON response
                    .then(data => {
                        if (data.status === 'success') {
                            // Handle the response if needed (e.g., display a notification)
                            console.log('Tasks checked successfully');
                        } else {
                            // Handle failure (if any)
                            console.log('No tasks due or error occurred');
                        }
                    })
                    .catch(error => {
                        console.error('Error checking due tasks:', error);
                    });
            }, 1000); // 60000ms = 1 minute


            let lastFetchTime = new Date().toISOString(); // Initialize with the current timestamp

            // Function to mark notifications as read in the database
            function markNotificationsAsRead() {
                fetch('coe-mark-notifications-read.php', {
                    method: 'POST',
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        console.log('Notifications marked as read.');
                    } else {
                        console.error('Failed to mark notifications as read.');
                    }
                })
                .catch(error => console.error('Error marking notifications as read:', error));
            }

           // Function to delete a notification
            function deleteNotification(notificationId) {
                // SweetAlert confirmation dialog with custom classes
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'Do you really want to delete this notification?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it!',
                    reverseButtons: true,
                    customClass: {
                        popup: 'custom-swal-popup',
                        title: 'custom-swal-title',
                        confirmButton: 'custom-swal-confirm',
                        cancelButton: 'custom-swal-cancel'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Send a DELETE request to the server to delete the notification
                        fetch('coe-delete-notification.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({ id: notificationId }) // Pass the notification ID
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'success') {
                                // Remove the notification from the UI
                                const notificationElement = document.getElementById(`notification-${notificationId}`);
                                notificationElement.remove();

                                // Show SweetAlert success message with custom classes
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Notification Deleted',
                                    text: 'The notification has been deleted successfully.',
                                    timer: 2000, // Show for 2 seconds before refreshing
                                    showConfirmButton: false,
                                    customClass: {
                                        popup: 'custom-swal-popup',
                                        title: 'custom-swal-title'
                                    }
                                }).then(() => {
                                    // Reload the page after the alert is shown
                                    location.reload();
                                });

                            } else {
                                console.error('Error deleting notification:', data.message);

                                // Show SweetAlert error message with custom classes
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Oops...',
                                    text: 'There was an error deleting the notification.',
                                    customClass: {
                                        popup: 'custom-error-popup',
                                        title: 'custom-error-title',
                                        confirmButton: 'custom-error-confirm'
                                    }
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);

                            // Show SweetAlert error message with custom classes for failure
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: 'Something went wrong. Please try again later.',
                                customClass: {
                                    popup: 'custom-error-popup',
                                    title: 'custom-error-title',
                                    confirmButton: 'custom-error-confirm'
                                }
                            });
                        });
                    }
                });
            }
			
			function showConfirmation(taskId) {
				Swal.fire({
					title: 'Are you sure?',
					text: 'You want to mark this task as done.',
					icon: 'warning',
					showCancelButton: true,
					confirmButtonText: 'Yes, mark as done!',
					cancelButtonText: 'No, keep it pending',
					reverseButtons: true,
					customClass: {
						popup: 'custom-swal-popup',  // Custom class for the popup
						title: 'custom-swal-title',  // Custom class for the title
						confirmButton: 'custom-swal-confirm',  // Custom class for the confirm button
						cancelButton: 'custom-swal-cancel'  // Custom class for the cancel button
					}
				}).then((result) => {
					if (result.isConfirmed) {
						// Find the form associated with the task ID
						var form = document.getElementById('task-form-' + taskId);

						// Submit the form after SweetAlert confirmation
						form.submit(); // This submits the form that is identified by taskId
					}
				});
			}

            function logAction(actionDescription) {
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "college_logs.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.send("action=" + encodeURIComponent(actionDescription));
            }

            // Add event listeners when the page is fully loaded
            document.addEventListener("DOMContentLoaded", function() {
                // Log clicks on main menu links
                document.querySelectorAll(".menu > li > a").forEach(function(link) {
                    link.addEventListener("click", function() {
                        logAction(link.textContent.trim()); // Log the main menu link
                    });
                });

                // Handle dropdown button clicks
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

                // Log clicks on dropdown links
                document.querySelectorAll(".dropdown-container a").forEach(function(link) {
                    link.addEventListener("click", function(event) {
                        event.stopPropagation(); // Prevent click from closing the dropdown
                        logAction(link.textContent.trim()); // Log the dropdown link
                    });
                });

                // Log clicks on the "Profile" link
                document.querySelector('.dropdown-menu a[href="coe-your-profile.php"]').addEventListener("click", function() {
                    logAction("Profile");
                });
            });

                      
            // Toggle chatbot visibility
            function toggleChatbot() {
                const chatbot = document.getElementById('chatbot');
                const chatbotButton = document.querySelector('.chatbot-button');

                if (chatbot.style.display === 'block') {
                    chatbot.style.display = 'none';  // Hide the chatbot
                    chatbotButton.style.display = 'block'; // Show the chatbot button
                } else {
                    chatbot.style.display = 'block'; // Show the chatbot
                    chatbotButton.style.display = 'none'; // Hide the chatbot button
                }
            }

            // Function to send user messages
            function sendMessage() {
                const inputField = document.getElementById('chatInput');
                const message = inputField.value.trim();
                if (message !== '') {
                    appendMessage('user', message);  // Display user message
                    inputField.value = ''; // Clear input field
                    setTimeout(() => {
                        botResponse(message);  // Placeholder bot response
                    }, 500);
                }
            }

            // Function to append messages to chat window
            function appendMessage(sender, message) {
                const chatMessages = document.getElementById('chatMessages');
                const messageElement = document.createElement('div');
                messageElement.classList.add('chatbot-message', sender);
                messageElement.textContent = message;
                chatMessages.appendChild(messageElement);
                chatMessages.scrollTop = chatMessages.scrollHeight; // Auto scroll to the bottom
            }

            // Function to display typing indicator
            function showTypingIndicator() {
                const chatMessages = document.getElementById('chatMessages');
                const typingElement = document.createElement('div');
                typingElement.classList.add('chatbot-message', 'bot', 'chatbot-typing');
                typingElement.id = 'typingIndicator';
                typingElement.textContent = '. . .'; // Typing indicator
                chatMessages.appendChild(typingElement);
                chatMessages.scrollTop = chatMessages.scrollHeight; // Auto scroll to the bottom
            }

            // Function to remove typing indicator
            function hideTypingIndicator() {
                const typingIndicator = document.getElementById('typingIndicator');
                if (typingIndicator) {
                    typingIndicator.remove();
                }
            }

            // Placeholder bot responses with typing indicator
            function botResponse(message) {
                showTypingIndicator(); // Show typing indicator

                setTimeout(() => {
                    hideTypingIndicator(); // Hide typing indicator

                    let response = "Sorry, I didn't understand that.";
                    const lowerMessage = message.toLowerCase();

                    // Greeting responses
                    if (lowerMessage.includes('hello') || lowerMessage.includes('hi')) {
                        response = "Hi! How can I assist you today?";
                    } 
                    // Project-related responses
                    else if (lowerMessage.includes('project')) {
                        response = "You can view and add projects in the 'Project List' section.";
                    } 
                    else if (lowerMessage.includes('add project')) {
                        response = "To add a new project, click on the 'Project List' section and then on the 'Add New Project' button.";
                    } 
                    else if (lowerMessage.includes('view projects')) {
                        response = "You can view all projects in the 'Project List' section.";
                    } 
                    else if (lowerMessage.includes('project details')) {
                        response = "To view project details, click on the project name in the 'Project List' section.";
                    } 
                    // Event-related responses
                    else if (lowerMessage.includes('event')) {
                        response = "You can view all the events in the 'Event Calendar' section.";
                    } 
                    else if (lowerMessage.includes('add event')) {
                        response = "To add an event, head to the 'Event Calendar' section, and you can add your event there.";
                    } 
                    else if (lowerMessage.includes('view events')) {
                        response = "You can view all upcoming events in the 'Event Calendar' section.";
                    } 
                    // Task management
                    else if (lowerMessage.includes('task')) {
                        response = "You can manage tasks in the 'Task Management' section.";
                    } 
                    else if (lowerMessage.includes('add task')) {
                        response = "Go to 'Task Management' to add new tasks for your projects.";
                    } 
                    else if (lowerMessage.includes('view tasks')) {
                        response = "You can view all your tasks in the 'Task Management' section.";
                    } 
                    else if (lowerMessage.includes('task status')) {
                        response = "You can check the status of your tasks in the 'Task Management' section.";
                    } 
                    // Profile-related responses
                    else if (lowerMessage.includes('profile')) {
                        response = "You can update your profile by clicking on your username in the top-right corner.";
                    } 
                    // Logout responses
                    else if (lowerMessage.includes('logout') || lowerMessage.includes('log out')) {
                        response = "You can sign out by clicking the 'Sign Out' button in the sidebar.";
                    } 
                    // Progress report
                    else if (lowerMessage.includes('progress report') || lowerMessage.includes('report')) {
                        response = "You can view and generate progress reports under the 'Progress Report' section.";
                    } 
                    // Notifications
                    else if (lowerMessage.includes('notifications')) {
                        response = "You can check your notifications in the notifications panel.";
                    } 
                    // Help and guidance
                    else if (lowerMessage.includes('help')) {
                        response = "I can assist with tasks like viewing projects, adding events, or updating your profile. What would you like to do?";
                    } 
                    else if (lowerMessage.includes('what can you do')) {
                        response = "I can help you with project management, event scheduling, task management, and profile updates.";
                    } 
                    // General inquiries
                    else if (lowerMessage.includes('thank you') || lowerMessage.includes('thanks')) {
                        response = "You're welcome! If you have any more questions, feel free to ask.";
                    } 
                    else if (lowerMessage.includes('goodbye') || lowerMessage.includes('bye')) {
                        response = "Goodbye! Have a great day! If you need anything else, just let me know.";
                    } 
                    else if (lowerMessage.includes('how to use') || lowerMessage.includes('guide')) {
                        response = "To use the system, start by logging in and exploring the dashboard. You can create projects, manage tasks, and view scheduled events. Let me know if you need more specific instructions!";
                    } 
                    else if (lowerMessage.includes('reset password') || lowerMessage.includes('forgot password')) {
                        response = "To reset your password, click on the 'Forgot Password' link on the login page. Follow the instructions sent to your email.";
                    } 
                    else if (lowerMessage.includes('budget') || lowerMessage.includes('utilization')) {
                        response = "With budget utilization, you can add, edit, and delete expenses, helping you track and manage your budget effectively.";
                    }
                    // Inquiry about system features
                    else if (lowerMessage.includes('features')) {
                        response = "Our system offers project management, event scheduling, and task management.";
                    } 
                    // Closing for unknown queries
                    else {
                        response = "Sorry, I didn't understand that. Could you please clarify?";
                    }

                    appendMessage('bot', response); // Display bot response
                }, 2000); // Simulate a delay in bot response
            }

            document.addEventListener("DOMContentLoaded", () => {
                function checkNotifications() {
                    fetch('coe-check_notifications.php')
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
                
                function fetchNotifications() {
                    const notificationContainer = document.getElementById("notification-container");

                    // Fetch notifications from the server
                    fetch(`coe-fetch_task.php?last_fetch_time=${encodeURIComponent(lastFetchTime)}`)
                        .then(response => response.json())
                        .then(data => {
                            const notificationCountElement = document.querySelector('.notification-count');

                            if (data.notifications.length > 0) {
                                // Update the last fetch time to the latest notification's timestamp
                                lastFetchTime = new Date(data.notifications[0].created_at).toISOString();

                                // If there are new notifications, display them
                                const ul = notificationContainer.querySelector('ul') || document.createElement('ul');
                                ul.innerHTML = ''; // Clear previous notifications
                                data.notifications.forEach(notification => {
                                    const li = document.createElement('li');
                                    li.id = `notification-${notification.id}`;
                                    li.classList.add('notification-item');
                                    li.style.display = 'flex';
                                    li.style.justifyContent = 'space-between';
                                    li.style.alignItems = 'center';
                                    li.style.flexWrap = 'wrap'; // Ensures content can wrap if needed

                                    li.innerHTML = `
                                        <div style="flex: 1; text-align: justify; word-wrap: break-word; overflow-wrap: break-word; white-space: normal; margin-right: 85px;">
                                            <strong class="task-desc">${notification.task_description}</strong><br>
                                            <small>${notification.created_at}</small>
                                        </div>
                                        <button class="delete-btn" style="flex-shrink: 0; align-self: center;" onclick="deleteNotification(${notification.id})">Delete</button>
                                        <hr style="width: 100%;">
                                    `;
                                    ul.appendChild(li);
                                });

                                if (!notificationContainer.contains(ul)) {
                                    notificationContainer.appendChild(ul);
                                }

                                // Update the notification count
                                notificationCountElement.textContent = data.notifications.filter(n => n.coe_status === 'unread').length;
                            } else {
                                notificationContainer.innerHTML = "<p>No new notifications.</p>";
                            }
                        })
                        .catch(error => console.error('Error fetching notifications:', error));
                }
                // Function to fetch new notifications every 3 seconds
                setInterval(fetchNotifications, 1000); // Fetch every 3 seconds
                fetchNotifications();
            });

            function fetchNotificationCount() {
                fetch('?fetch_notification_count=1')
                    .then(response => response.json())
                    .then(data => {
                        const notificationCountElement = document.querySelector('.notification-count');
                        const unreadCount = data.unreadCount;

                        if (unreadCount > 0) {
                            notificationCountElement.textContent = unreadCount;
                            notificationCountElement.style.display = "inline"; // Show the count
                        } else {
                            notificationCountElement.textContent = '';
                            notificationCountElement.style.display = "none"; // Hide if zero
                        }
                    })
                    .catch(error => console.error('Error fetching notification count:', error));
            }

            // Fetch notification count every 3 seconds (5000 milliseconds)
            setInterval(fetchNotificationCount, 3000);
        </script>
    </body>
</html>
