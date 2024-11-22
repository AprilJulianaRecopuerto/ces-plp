<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: roleaccount.php"); // Redirect to login if not logged in
    exit;
}

// Database credentials for proj_list
$servername_proj = "ryvdxs57afyjk41z.cbetxkdyhwsb.us-east-1.rds.amazonaws.com";
$username_proj = "zf8r3n4qqjyrfx7o";
$password_proj = "su6qmqa0gxuerg98";
$dbname_proj_list = "hpvs3ggjc4qfg9jp";

// Create connection to proj_list database
$conn_proj_list = new mysqli($servername_proj, $username_proj, $password_proj, $dbname_proj_list);

// Check connection
if ($conn_proj_list->connect_error) {
    die("Connection failed: " . $conn_proj_list->connect_error);
	
$query = "SELECT ProjectTitle, Description, StartDate FROM proj_list ORDER BY StartDate DESC LIMIT 5";
$result = mysqli_query($conn_proj_list, $query);
}

// Database credentials for proj_list
$servername_mov = "arfo8ynm6olw6vpn.cbetxkdyhwsb.us-east-1.rds.amazonaws.com";
$username_mov = "tz8thfim1dq7l3rf";
$password_mov = "wzt4gssgou2ofyo7";
$dbname_mov = "uv1qyvm0b8oicg0v";

$conn_mov = new mysqli($servername_mov, $username_mov, $password_mov, $dbname_mov);

// Check connection for MOV database
if ($conn_mov->connect_error) {
    die("Connection failed: " . $conn_mov->connect_error);
}

// Fetch all notifications from the mov database, regardless of their status
$notificationQuery = "SELECT * FROM notifications ORDER BY created_at DESC";
$notificationResult = $conn_mov->query($notificationQuery);

// Count the number of unread notifications for the notification icon
$unreadCountQuery = "SELECT COUNT(*) as unread_count FROM notifications WHERE status = 'unread'";
$unreadCountResult = $conn_mov->query($unreadCountQuery);
$unreadCount = $unreadCountResult ? $unreadCountResult->fetch_assoc()['unread_count'] : 0;

// Initialize notifications array
$notifications = [];

// Fetch notifications from the mov database
if ($notificationResult && $notificationResult->num_rows > 0) {
    while ($row = $notificationResult->fetch_assoc()) {
        $notifications[] = $row;
    }
}

// Mark notifications as read if requested
if (isset($_GET['mark_as_read'])) {
    $updateQuery = "UPDATE notifications SET status = 'read' WHERE status = 'unread'";
    if ($conn_mov->query($updateQuery) === TRUE) {
        // Fetch the new unread count after updating
        $unreadCountQuery = "SELECT COUNT(*) as unread_count FROM notifications WHERE status = 'unread'";
        $unreadCountResult = $conn_mov->query($unreadCountQuery);
        $unreadCount = $unreadCountResult ? $unreadCountResult->fetch_assoc()['unread_count'] : 0;

        // Return the new unread count as JSON
        echo json_encode(['message' => 'Notifications marked as read', 'unreadCount' => $unreadCount]);
    } else {
        // Handle error in updating notifications
        echo json_encode(['message' => 'Error marking notifications as read', 'error' => $conn_mov->error]);
    }
    exit; // Terminate the script after handling the request
}

// Fetch notifications via AJAX
if (isset($_GET['fetch_notifications'])) {
    header('Content-Type: application/json');
    echo json_encode($notifications); // $notifications array populated from your database
    exit; // Stop further processing
}

// Fetch unread notification count via AJAX
if (isset($_GET['fetch_notification_count'])) {
    header('Content-Type: application/json');
    echo json_encode(['unreadCount' => $unreadCount]);
    exit; // Stop further processing
}

if (isset($_POST['delete_notification'])) {
    $notificationId = $_POST['delete_notification'];

    // Delete the notification from the database
    $deleteQuery = "DELETE FROM notifications WHERE id = ?";
    $stmt = $conn_mov->prepare($deleteQuery);
    $stmt->bind_param("i", $notificationId);

    // Check if the deletion was successful
    if ($stmt->execute()) {
        // Return a success response without a message
        echo json_encode([
            'success' => true,
            'status' => 'success'
        ]);
    } else {
        // Return a failure response
        echo json_encode([
            'success' => false,
            'status' => 'error',
            'message' => 'Failed to delete notification'
        ]);
    }

    $stmt->close();
    exit; // Stop further processing
}
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

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>

        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

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
                gap: -5px !important; /* Space between the grid items */
                margin-top: 110px; /* Adjust this value based on your navbar height */
            }

            .total-activities {
                width: 94.7%; /* Adjust width to fit side-by-side */
                height: 90%;
                padding: 10px;
                border: 1px solid #ddd;
                border-radius: 5px;
                background-color: #ffffff;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                margin-right: 10px; /* Add a small margin between the containers */
                cursor:pointer;
            }

            .total-activities a {
                text-decoration: none; /* Remove underline from link */
                color: inherit; /* Inherit text color */
            }

            .total-activities:hover {
                background-color: #f0f0f0; /* Optional: Add hover effect */
            }

            .total-activities img{
                width: 20%;
                height: 20%;
                margin-top: -32px;
                margin-right: 15px;
                margin-bottom: 5px;
                align-items: center;
            }

            .total-activities h2 {
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

            .total-activities-count{
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

            .container {
                font-family: 'Poppins', sans-serif;
                max-width: 550px;
                margin: auto;
				height: 635px;
                background-color: white;    
                margin-left: 585px;
                margin-top: -669.5px;
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

            .due-date-form label {
                font-family: 'Poppins', sans-serif;
                margin-left: 10px;
                margin-bottom: 7px;
            }

            .due-date-form input[type="date"] {
                width: 95%;
                padding: 8px;
                margin-left:10px;
                border: 1px solid #ddd;
                border-radius: 5px;
                font-size: 16px;
                box-sizing: border-box;
                font-family: 'Poppins', sans-serif;
                margin-bottom: 10px;
            }

            .todo-form input[type="text"] {
                width: 72.5%;
                padding: 8px;
                margin-left:10px;
                border: 1px solid #ddd;
                border-radius: 5px;
                font-size: 16px;
                box-sizing: border-box;
                font-family: 'Poppins', sans-serif;
                margin-bottom: 10px;
            }

            #addTaskButton {
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

            .addTaskButton:hover {
                background: #218838;
            }

            #dueDateDisplay {
                margin-left:10px;
                font-family: 'Poppins', sans-serif;
            }

            .todo-list {
                list-style-type: none;
                padding: 0;
                margin: 20px 0;
            }

            /* Styling for each task item */
            .todo-item {
                margin-left: 10px;
                font-family: 'Poppins', sans-serif;
                padding: 10px;
                background-color: #d0d0d3; /* Light gray color */
                margin-bottom: 10px;
                border-radius: 4px;
                display: flex;
                justify-content: space-between; /* Ensures the delete button is aligned to the right */
                align-items: center; /* Vertically center the content */
            }

            /* Task description section */
            .task-content {
                display: flex;
                align-items: center;
                flex-grow: 1; /* Ensures content grows to take available space */
                word-wrap: break-word; /* Allow word wrapping for long task descriptions */
            }

            /* Styling for checkboxes */
            .task-checkbox {
                width: 20px;
                height: 20px;
                margin-right: 10px;
                border-radius: 5px;
                background-color: #fff;
                border: 2px solid #007bff;
                transition: background-color 0.3s, border-color 0.3s;
                cursor: pointer;
            }

            /* Checkbox checked styling */
            .task-checkbox:checked {
                background-color: #007bff;
                border-color: #0056b3;
            }

            /* Checkmark inside checkbox when checked */
            .task-checkbox:checked::before {
                content: '\2713'; /* Unicode check mark */
                font-size: 14px;
                color: white;
                position: absolute;
                left: 4px;
                top: 2px;
            }

            /* Checkbox hover effect */
            .task-checkbox:hover {
                background-color: #f0f8ff;
                border-color: #0056b3;
            }

            /* Delete button styling */
            .delete-button {
                background-color: #e74c3c;
                border: none;
                color: white;
                padding: 7px 20px;
                border-radius: 5px;
                font-size: 14px;
                cursor: pointer;
                transition: background-color 0.3s;
                font-family: 'Poppins', sans-serif;
            }

            .delete-button:hover {
                background-color: #c0392b; /* Darker red on hover */
            }

            /* Notification button styling */
            .notify-button {
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
                margin-left: 340px;
            }

            .notify-button:hover {
                background-color: #45a049; /* Darker green on hover */
            }

            .Pending-button {
                background-color: #4CAF50;
                border: none;
                color: white;
                padding: 10px 20px;
                border-radius: 5px;
                font-size: 14px;
                cursor: pointer;
                transition: background-color 0.3s;
                font-family: 'Poppins', sans-serif;
                margin-top: 10px;
            }

            .Pendin-button:hover {
                background-color: #45a049; /* Darker green on hover */
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
            
            .notification-container {
                font-family: 'Poppins', sans-serif;
                display: none; /* Initially hide the container */
                border: 1px solid #ccc;
                border-radius: 8px; /* Slightly more rounded corners */
                background-color: #fff; /* White background for better contrast */
                position: absolute; /* Position it below the icon */
                margin-top: 18px;
                margin-left: -10px;
                padding: 15px; /* Increased padding for a more spacious feel */
                width: 270px; /* Increased width */
                max-height: 300px; /* Set a max height to allow for scrolling */
                overflow-y: auto; /* Enable vertical scrolling */
                overflow-x: hidden;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2); /* More pronounced shadow for depth */
                z-index: 1000; /* Ensure it's above other elements */
            }

            .notification-icon {
                position: absolute;
                font-size: 18px;
                cursor: pointer;
                color: #333; /* Adjust color as needed */
                margin-left: 280px;  
                margin-top: -4px;
            }

            .notification-container ul {
                font-family: 'Poppins', sans-serif;
                list-style-type: none; /* Removes bullets */
                padding: 0; /* Removes padding */
                margin: 0; /* Removes margin */
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

            .tasks-container {
                font-family: 'Poppins', sans-serif;
                background-color: #ffffff; /* White background for the container */
                color: black;
                padding: 20px;
                border-radius: 8px; /* Rounded corners */
                box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1); /* Soft shadow for the container */
                width: 96%; /* Set container width to 96% of the page width */
                max-height: 500px; /* Maximum height for the container */
                min-height: 300px; /* Minimum height to prevent the container from shrinking */
                margin-top: -180px; /* Removed margin-top */
                top: 200px; /* Fixed position from the top of the screen */
                overflow-y: auto; /* Enable vertical scrolling */
                position: relative; /* Keep it in the normal flow */
            }

            .table-container {
                padding: 6px;
                width: 100%;
                margin-left: -12px;
                overflow-x: auto;
                margin-top: 20px; /* Space above the table */
            }

            .table-container h2 {
                margin-left: 10px;
                margin-top: -10px; /* Space above the table */
            }

            label {
                font-size: 16px;
                color: #555555;
                margin-right: 10px;
                margin-left: 10px;
                margin-bottom: 20px;
            }

            select {
                font-family: 'Poppins', sans-serif;
                margin-bottom: 20px;
                padding: 8px;
                font-size: 16px;
                border-radius: 5px;
                border: 1px solid #ccc;
            }

            .crud-table {
                width: 100%;
                border-collapse: collapse;
                font-family: 'Poppins', sans-serif;
                background-color: #ffffff;
                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
                border-radius: 10px;
                overflow: hidden;
            }

            .crud-table th, .crud-table td {
                border: 1px solid #ddd;
                padding: 10px;
                text-align: center;
                color: black;
                white-space: nowrap; /* Prevent text from wrapping */
            }

            .crud-table th {
                background-color: #4CAF50;
                color: white;
                height: 40px;
            }

            .crud-table td {
                height: 50px;
                background-color: #fafafa;
            }

            .crud-table tr:hover {
                background-color: #f1f1f1;
            }

            .task-description {
                margin-left: 10px; /* Add space between checkbox and description */
                word-wrap: break-word; /* Ensure the description wraps when too long */
                white-space: normal; /* Allow the text to break onto the next line */
            }

            .edit-button {
                font-family: 'Poppins', sans-serif;
                right: 5px; /* Adjust to your preference for spacing */
                padding: 5px 10px;
                background-color: #4CAF50;
                color: white;
                border: none;
                border-radius: 5px;
                cursor: pointer;
            }

            .edit-button:hover {
                background-color: #00A800;
            }

            .cancel-button{
                font-family: 'Poppins', sans-serif;
                margin-left:2px;
                padding: 5px 10px;
                background-color: #c0392b;
                color: white;
                border: none;
                border-radius: 5px;
                cursor: pointer;
            }

            .cancel-button:hover {
                background-color: darkred;
            }

            /* Modal background overlay */
            .modal-overlay {
                display: none; /* Hidden by default */
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.5); /* Semi-transparent black background */
                z-index: 1000; /* Make sure the modal content is above the overlay */
            }

            /* Modal styling */
            .modal {
                display: none; /* Hidden by default */
                position: fixed;
                z-index: 1; /* Make sure modal is on top of overlay */
                left: 50%; /* Center modal horizontally */
                top: 50%;  /* Center modal vertically */
                transform: translate(-50%, -50%); /* Offset by 50% to achieve perfect centering */
                width: 50%;
                max-width: 600px; /* Optional, limit the width of the modal */
                height: auto; /* Height will adjust based on content */
                overflow: auto;
                background-color: #fff; /* White background for the modal */
                border-radius: 10px; /* Rounded corners for the modal */
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Add shadow for better visual effect */
                z-index: 1000; /* Make sure the modal content is above the overlay */
            }

            /* Modal content styling */
            .modal-content {
                background-color: #fefefe;
                margin: 0 auto;
                padding: 20px;
                border: 1px solid #888;
                border-radius: 10px; /* Rounded corners inside modal */
            }

            /* Heading styling */
            h4 {
                font-family: 'Poppins', sans-serif; /* Ensure consistency in font */
                margin-bottom: 20px;
                margin-top: 5px;
            }

            #sendNotificationButton {
                background-color: #4CAF50;
                border: none;
                color: white;
                padding: 10px 10px;
                margin-left: 10px;
                border-radius: 5px;
                font-size: 14px;
                cursor: pointer;
                transition: background-color 0.3s;
                font-family: 'Poppins', sans-serif;
            }

            #cancelModalButton {
                background-color: #e74c3c;
                border: none;
                color: white;
                padding: 10px 10px;
                margin-left: 10px;
                border-radius: 5px;
                font-size: 14px;
                cursor: pointer;
                transition: background-color 0.3s;
                font-family: 'Poppins', sans-serif;
            }


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

            /* Reusable class for editable input fields */
            .editable-field {
                font-family: 'Poppins', sans-serif; /* Set font family to Poppins */
                border: 1px solid #ccc;             /* Light gray border */
                border-radius: 4px;                 /* Slight rounded corners */
                padding: 8px;                       /* Space inside the input */
                background-color: white;          /* Light background for inputs */
                transition: border-color 0.3s ease;  /* Smooth transition for border on focus */
                font-size: 14px;                    /* Adjust font size */
            }

            /* Style when input fields are focused */
            .editable-field:focus {
                border-color: white;  /* Change border color on focus */
                outline: none;           /* Remove default outline */
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
                <?php if ($unreadCount > 0): ?>
                    <span class="notification-count"><?php echo $unreadCount; ?></span>
                <?php endif; ?>
                <div class="notification-container" id="notification-container">
                    <h3 style="display: inline-block;">Notifications</h3> 
        
                    <?php if (empty($notifications)): ?>
                        <p>No new notifications.</p>
                    <?php else: ?>
                        <ul>
                            <?php foreach ($notifications as $notification): ?>
                                <li>
                                    <strong><?php echo htmlspecialchars($notification['project_name']); ?></strong><br>
                                    <?php echo htmlspecialchars($notification['notification_message']); ?><br>
                                </li>
                                <hr>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>

            <div class="profile-container">
                <!-- Chat Icon with Notification Badge -->
                <a href="chat.php" class="chat-icon" onclick="resetNotifications()">
                    <i class="fa fa-comments"></i>
                    <span class="notification-badge" id="chatNotification" style="display: none;">!</span>
                </a>
            <div>

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
                    <a href="admin-your-profile.php">Profile</a>
                    <a class="signout" href="roleaccount.php" onclick="confirmLogout(event)">Sign out</a>
                </div>
            </div>
        </nav>
        
        <div class="sidebar">
            <div class="logo">
                <img src="images/logo.png" alt="Logo">
            </div>

            <ul class="menu">
                <li><a href="admin-dash.php" class="active"><img src="images/home.png">Dashboard</a></li>
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
  
        <div class="content">
			<div class="activities-container">
				<div class="total-activities">
				<a href="admin-projlist.php">
				 <h2>Total Activities</h2>
						<div class="activities-details">
							<div class="total-activities-count">
								<?php
								// Database credentials for proj_list
                                $servername_proj = "ryvdxs57afyjk41z.cbetxkdyhwsb.us-east-1.rds.amazonaws.com";
                                $username_proj = "zf8r3n4qqjyrfx7o";
                                $password_proj = "su6qmqa0gxuerg98";
                                $dbname_proj_list = "hpvs3ggjc4qfg9jp";

                                // Create connection to proj_list database
                                $conn_proj_list = new mysqli($servername_proj, $username_proj, $password_proj, $dbname_proj_list);

                                // Check connection
                                if ($conn_proj_list->connect_error) {
                                    die("Connection failed: " . $conn_proj_list->connect_error);
                                }

								// SQL query to count total activities across multiple tables
								$sql = "
									SELECT SUM(total_count) as total
									FROM (
										SELECT COUNT(*) as total_count FROM cas
										UNION ALL
										SELECT COUNT(*) as total_count FROM ccs
										UNION ALL
										SELECT COUNT(*) as total_count FROM cba
									) as combined_counts
								";
								$result = $conn_proj_list->query($sql);

								if ($result && $result->num_rows > 0) {
									$row = $result->fetch_assoc();
									echo $row['total'];
								} else {
									echo "0";
								}

								$conn_proj_list->close();
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
				// Database credentials for proj_list
                $servername_proj = "ryvdxs57afyjk41z.cbetxkdyhwsb.us-east-1.rds.amazonaws.com";
                $username_proj = "zf8r3n4qqjyrfx7o";
                $password_proj = "su6qmqa0gxuerg98";
                $dbname_proj_list = "hpvs3ggjc4qfg9jp";

                // Create connection to proj_list database
                $conn_proj_list = new mysqli($servername_proj, $username_proj, $password_proj, $dbname_proj_list);

                // Check connection
                if ($conn_proj_list->connect_error) {
                    die("Connection failed: " . $conn_proj_list->connect_error);
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

					$result = $conn_proj_list->query($sql);

					if ($result->num_rows > 0) {
						while ($row = $result->fetch_assoc()) {
							$projectCounts[] = $row['project_count'];
						}
					} else {
						$projectCounts[] = 0;  // If no projects found
					}
				}

				// Close the connection
				$conn_proj_list->close();
				?>
			</div>

			<div class="container">
				<h2>Admin To-Do List</h2>
				
				<!-- Due Date Section -->
				<div class="due-date-form">
					<label for="dueDateInput">Due Date:</label>
					<input type="date" id="dueDateInput" required>
				</div>

				<!-- Task Input Section -->
				<div class="todo-form">
					<input type="text" id="taskInput" placeholder="Enter a task" required>
					<button id="addTaskButton">Add Task</button>
				</div>
				
				<!-- Display Due Date and List of Tasks -->
				<div class="todo-list" id="todoList">
					<h4 id="dueDateDisplay" style="display: none;"></h4>
				</div>

				<!-- Notify Colleges Button -->
				<button id="notifyAllButton" class="notify-button">Notify Selected Tasks</button>
			</div>

            <!-- Display All Tasks from the Database -->
            <div class="tasks-container">

                <?php
                // Database connection details
                $servername_todo = "d6ybckq58s9ru745.cbetxkdyhwsb.us-east-1.rds.amazonaws.com";
                $username_todo = "t9riamok80kmok3h";
                $password_todo = "lzh13ihy0axfny6d";
                $dbname_todo = "g8ri1hhtsfx77ptb"; // Database name

                // Create connection
                $conn_todo = new mysqli($servername_todo, $username_todo, $password_todo, $dbname_todo);

                // Check connection
                if ($conn_todo->connect_error) {
                    die("Connection failed: " . $conn_todo->connect_error);
                }

                // Get the selected table from the form, default to cas_tasks if not set
                $selectedTable = isset($_POST['college']) ? $_POST['college'] : 'cas_tasks';

                // Fetch tasks from the selected table
                $sql = "SELECT * FROM $selectedTable";
                $result = $conn_todo->query($sql);

                ?>

                <div class="table-container">
                    <h2>Admin Task List</h2>

                        <!-- Dropdown to select table -->
                        <form method="POST" action="">
                            <label for="college">Select College:</label>
                            <select name="college" id="college" onchange="this.form.submit()">
                                <option value="cas_tasks" <?php echo isset($_POST['college']) && $_POST['college'] == 'cas_tasks' ? 'selected' : ''; ?>>CAS</option>
                                <option value="cba_tasks" <?php echo isset($_POST['college']) && $_POST['college'] == 'cba_tasks' ? 'selected' : ''; ?>>CBA</option>
                                <option value="ccs_tasks" <?php echo isset($_POST['college']) && $_POST['college'] == 'ccs_tasks' ? 'selected' : ''; ?>>CCS</option>
                                <option value="cihm_tasks" <?php echo isset($_POST['college']) && $_POST['college'] == 'cihm_tasks' ? 'selected' : ''; ?>>CIHM</option>
                                <option value="coed_tasks" <?php echo isset($_POST['college']) && $_POST['college'] == 'coed_tasks' ? 'selected' : ''; ?>>COED</option>
                                <option value="coe_tasks" <?php echo isset($_POST['college']) && $_POST['college'] == 'coe_tasks' ? 'selected' : ''; ?>>COE</option>
                                <option value="con_tasks" <?php echo isset($_POST['college']) && $_POST['college'] == 'con_tasks' ? 'selected' : ''; ?>>CON</option>
                            </select>
                        </form>

                    <table class="crud-table">
                        <thead>
                            <tr>
                                <th>Task Description</th>
                                <th>Due Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($result->num_rows > 0) {
                                // Output data of each row
                                while ($row = $result->fetch_assoc()) {
                                    // Set color and visibility based on status
                                    $statusColor = '';
                                    $statusClass = '';
                                    if ($row['status'] == 'pending') {
                                        $statusColor = 'background-color: #ff4d6d; color: black;';
                                        $statusClass = 'pending-task'; // Class for pending tasks
                                    } elseif ($row['status'] == 'done') {
                                        $statusColor = 'background-color: #a1cca5; color: black;';
                                        $statusClass = 'done-task'; // Class for done tasks (hidden by default)
                                    }
                                    // Display task data in the table rows
                                    echo "<tr class='$statusClass'>
                                            <td>" . htmlspecialchars($row["task_description"]) . "</td>
                                            <td>" . htmlspecialchars($row["due_date"]) . "</td>
                                            <td style='$statusColor'>" . htmlspecialchars($row["status"]) . "</td>
                                        
                                        </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5'>No tasks found.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                    <button class="Pending-button" onclick="toggleTasks()">Show Pending Tasks</button>
                </div>

                <?php
                // Close connection
                $conn_todo->close();
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
        </div>

        <!-- Modal Overlay (semi-transparent background) -->
            <div id="modalOverlay" class="modal-overlay"></div>

            <!-- Modal for selecting colleges to notify -->
            <div id="collegeModal" class="modal">
                <div class="modal-content">
                    <h4>Select Colleges to Notify</h4>
                    <form id="notifyForm">
                        <label>
                            <input type="checkbox" class="college-checkbox" value="CAS" />
                            <span>College of Arts and Sciences (CAS)</span>
                        </label><br>
                        <label>
                            <input type="checkbox" class="college-checkbox" value="CBA" />
                            <span>College of Business and Accountancy (CBA)</span>
                        </label><br>
                        <label>
                            <input type="checkbox" class="college-checkbox" value="CCS" />
                            <span>College of Computer Studies (CCS)</span>
                        </label><br>
                        <label>
                            <input type="checkbox" class="college-checkbox" value="COED" />
                            <span>College of Education (COED)</span>
                        </label><br>
                        <label>
                            <input type="checkbox" class="college-checkbox" value="COE" />
                            <span>College of Engineering (COE)</span>
                        </label><br>
                        <label>
                            <input type="checkbox" class="college-checkbox" value="CIHM" />
                            <span>College of International Hospitality Management (CIHM)</span>
                        </label><br>
                        <label>
                            <input type="checkbox" class="college-checkbox" value="CON" />
                            <span>College of Nursing (CON)</span>
                        </label><br><br>
                    
                        <button type="submit" id="sendNotificationButton">Send Notification</button>
                        <button type="button" id="cancelModalButton">Cancel</button>
                    </form>
                </div>
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

             // Function to toggle the visibility of done tasks
             function toggleTasks() {
                var doneTasks = document.querySelectorAll('.done-task');
                var pendingTasks = document.querySelectorAll('.pending-task');

                // Toggle the visibility of done tasks
                doneTasks.forEach(function(task) {
                    task.style.display = task.style.display === 'none' ? '' : 'none';
                });

                // Button label change
                var button = document.querySelector('button');
                if (doneTasks[0].style.display === 'none') {
                    button.textContent = 'Show Done Tasks';
                } else {
                    button.textContent = 'Hide Done Tasks';
                }
            }

            document.addEventListener("DOMContentLoaded", function() {
                const todoList = document.getElementById('todoList');
                let dueDate = '';

                // Load tasks from the database on page load
                loadTasks();

                // Capture due date when it's set
                document.getElementById('dueDateInput').addEventListener('change', function() {
                    dueDate = this.value;
                    document.getElementById('dueDateDisplay').innerText = `Due Date: ${dueDate}`;
                    document.getElementById('dueDateDisplay').style.display = 'block';
                });

                // Add task button functionality
                document.getElementById('addTaskButton').addEventListener('click', function() {
                    const taskInput = document.getElementById('taskInput');

                    if (taskInput.value) {
                        if (!dueDate) {
                            // Use SweetAlert for showing the alert
                            Swal.fire({
                                icon: 'warning',
                                title: 'Missing Due Date',
                                text: 'Please set a due date before adding tasks.',
                                confirmButtonText: 'OK',
                                customClass: {
                                    popup: 'custom-swal-popup',
                                    confirmButton: 'custom-swal-confirm'
                                }
                            });
                            return;
                        }

                        const task = taskInput.value;
                        addTaskToDatabase(task, dueDate); // Add task to the database
                        taskInput.value = ''; // Clear the input after adding
                    } else {
                        // Use SweetAlert for empty task input
                        Swal.fire({
                            icon: 'warning',
                            title: 'Task Required',
                            text: 'Please enter a task.',
                            confirmButtonText: 'OK',
                            customClass: {
                                popup: 'custom-swal-popup',
                                confirmButton: 'custom-swal-confirm'
                            }
                        });
                    }
                });

                // Function to add a task to the database
                function addTaskToDatabase(task, dueDate) {
                    const xhr = new XMLHttpRequest();
                    xhr.open("POST", "admin-add_task.php", true);
                    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

                    xhr.onreadystatechange = function() {
                        if (xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
                            const response = JSON.parse(xhr.responseText);
                            if (response.status === "success") {
                                loadTasks(); // Reload tasks after adding
                            } else {
                                console.error("Failed to add task:", response.message);
                            }
                        }
                    };

                    xhr.send(`task=${encodeURIComponent(task)}&due_date=${encodeURIComponent(dueDate)}`);
                }

                // Function to load tasks from the database and display them
                function loadTasks() {
                    const xhr = new XMLHttpRequest();
                    xhr.open("GET", "admin-get_tasks.php", true);

                    xhr.onreadystatechange = function() {
                        if (xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
                            const tasks = JSON.parse(xhr.responseText);
                            displayTasks(tasks);
                        }
                    };

                    xhr.send();
                }

                function displayTasks(tasks) {
                    todoList.innerHTML = ''; // Clear the current list
                    tasks.forEach(task => {
                        const todoItem = document.createElement('div');
                        todoItem.classList.add('todo-item');
                        todoItem.innerHTML = `
                            <div class="task-content">
                                <input type="checkbox" class="task-checkbox" data-id="${task.id}">
                                <span class="task-description" id="desc-${task.id}">${task.task_description}</span>
                                <span class="task-due-date" id="due-${task.id}">(Due: ${task.due_date})</span>
                            </div>
                            <button onclick="editTask(${task.id})" class="edit-button">Edit</button>
                        `;
                        todoList.appendChild(todoItem);
                    });
                }

                //JavaScript for Editing Tasks
                window.editTask = function(id) {
                const descElem = document.getElementById(`desc-${id}`);
                const dueDateElem = document.getElementById(`due-${id}`);

                // Save original content in case of cancel
                const originalDesc = descElem.textContent;
                const originalDueDate = dueDateElem.textContent.replace('Due: ', '');

                // Turn description and due date into editable fields
                descElem.innerHTML = `<input type="text" id="edit-desc-${id}" class="editable-field" value="${originalDesc}">`;
                dueDateElem.innerHTML = `<input type="date" id="edit-due-${id}" class="editable-field" value="${originalDueDate}">`;

                // Replace the Edit button with Save and Cancel
                const editButton = document.querySelector(`button[onclick="editTask(${id})"]`);
                editButton.textContent = 'Save';
                editButton.onclick = () => saveTask(id);

                // Add a Cancel button
                const cancelButton = document.createElement('button');
                cancelButton.textContent = 'Cancel';
                cancelButton.className = 'cancel-button';
                cancelButton.onclick = () => {
                    // Restore original content and reset buttons
                    descElem.textContent = originalDesc;
                    dueDateElem.textContent = `Due: ${originalDueDate}`;
                    editButton.textContent = 'Edit';
                    editButton.onclick = () => editTask(id);
                    cancelButton.remove();
                };
                editButton.after(cancelButton);
            };

            function saveTask(id) {
                    const descElem = document.getElementById(`desc-${id}`);
                    const dueDateElem = document.getElementById(`due-${id}`);
                    
                    // Get the new description and due date values
                    const newDesc = document.getElementById(`edit-desc-${id}`).value;
                    const newDueDate = document.getElementById(`edit-due-${id}`).value || dueDateElem.textContent.replace('Due: ', ''); // Use original due date if no change

                    if (!newDueDate) {
                        // If there is no due date, set it to the original due date to avoid null or empty values
                        newDueDate = dueDateElem.textContent.replace('Due: ', '');
                    }

                    const xhr = new XMLHttpRequest();
                    xhr.open("POST", "admin-edit_task.php", true);
                    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

                    xhr.onreadystatechange = function() {
                        if (xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
                            const response = JSON.parse(xhr.responseText);
                            if (response.status === "success") {
                                // Update the task display with new values
                                document.getElementById(`desc-${id}`).textContent = newDesc;
                                document.getElementById(`due-${id}`).textContent = `Due: ${newDueDate}`;

                                // Show SweetAlert that the task has been saved
                                Swal.fire({
                                    title: 'Task Saved!',
                                    text: 'The task has been successfully updated.',
                                    icon: 'success',
                                    confirmButtonText: 'OK',
                                    customClass: {
                                        popup: 'custom-swal-popup',
                                        title: 'custom-swal-title',
                                        confirmButton: 'custom-swal-confirm'
                                    }
                                });

                                // Return button to "Edit" and remove Cancel button
                                const editButton = document.querySelector(`button[onclick="editTask(${id})"]`);
                                editButton.textContent = 'Edit';
                                editButton.onclick = () => editTask(id);

                                const cancelButton = document.querySelector('.cancel-button');
                                if (cancelButton) {
                                    cancelButton.remove();
                                }
                            } else {
                                Swal.fire({
                                    title: 'Error',
                                    text: 'Failed to update task.',
                                    icon: 'error',
                                    confirmButtonText: 'OK',
                                    customClass: {
                                        popup: 'custom-error-popup',
                                        title: 'custom-error-title',
                                        confirmButton: 'custom-error-confirm'
                                    }
                                });
                            }
                        }
                    };

                    xhr.send(`id=${encodeURIComponent(id)}&description=${encodeURIComponent(newDesc)}&due_date=${encodeURIComponent(newDueDate)}`);
                }


                            
                // Show the modal and overlay when the 'Notify Selected Tasks' button is clicked
                document.getElementById('notifyAllButton').addEventListener('click', function() {
                    const modal = document.getElementById('collegeModal');
                    const overlay = document.getElementById('modalOverlay');
                    modal.style.display = 'block';
                    overlay.style.display = 'block'; // Show the overlay
                });

                // Close the modal and overlay if the 'Cancel' button is clicked
                document.getElementById('cancelModalButton').addEventListener('click', function() {
                    const modal = document.getElementById('collegeModal');
                    const overlay = document.getElementById('modalOverlay');
                    modal.style.display = 'none';
                    overlay.style.display = 'none'; // Hide the overlay
                });
                // Handle the form submission to notify selected colleges
                document.getElementById('notifyForm').addEventListener('submit', function(event) {
                    event.preventDefault();

                    const selectedColleges = [];
                    
                    // Get selected colleges
                    document.querySelectorAll('.college-checkbox:checked').forEach(function(checkbox) {
                        selectedColleges.push(checkbox.value);
                    });

                    const selectedTasks = [];
                    document.querySelectorAll('.task-checkbox:checked').forEach(function(checkbox) {
                        selectedTasks.push(checkbox.getAttribute('data-id'));
                    });

                    if (selectedTasks.length === 0) {
                        Swal.fire({
                            title: 'No Tasks Selected',
                            text: 'Please select at least one task to notify.',
                            icon: 'warning',
                            confirmButtonText: 'OK',
                            customClass: {
                                popup: 'custom-swal-popup',
                                title: 'custom-swal-title',
                                confirmButton: 'custom-swal-confirm'
                            }
                        });
                        return;
                    }

                    // Send selected tasks and colleges to the server
                    const xhr = new XMLHttpRequest();
                    xhr.open("POST", "admin-notify_colleges.php", true);
                    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

                    xhr.onreadystatechange = function() {
                        if (xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
                            const response = JSON.parse(xhr.responseText);
                            if (response.status === "success") {
                                Swal.fire({
                                    title: 'Success',
                                    text: 'Notification sent to selected colleges.',
                                    icon: 'success',
                                    confirmButtonText: 'OK',
                                    customClass: {
                                        popup: 'custom-swal-popup',
                                        title: 'custom-swal-title',
                                        confirmButton: 'custom-swal-confirm'
                                    }
                                });
                            } else {
                                Swal.fire({
                                    title: 'Error',
                                    text: 'Failed to send notification.',
                                    icon: 'error',
                                    confirmButtonText: 'OK',
                                    customClass: {
                                        popup: 'custom-error-popup',
                                        title: 'custom-error-title',
                                        confirmButton: 'custom-error-confirm'
                                    }
                                });
                            }
                        }
                    };

                    xhr.send(`tasks=${JSON.stringify(selectedTasks)}&colleges=${JSON.stringify(selectedColleges)}`);
                    // Close the modal after sending
                    document.getElementById('collegeModal').style.display = 'none';
                });
            });

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
         
            function toggleNotifications() {
                // Fetch notifications or toggle the visibility (your existing logic)
                const notificationContainer = document.getElementById("notification-container");
                notificationContainer.style.display = notificationContainer.style.display === "none" ? "block" : "none";
                
                // Fetch notifications when the dropdown is opened
                if (notificationContainer.style.display === "block") {
                    fetchNotifications(); // Fetch the latest notifications
                }

                // Send AJAX request to mark notifications as read
                fetch("?mark_as_read=true")
                    .then(response => response.json())
                    .then(data => {
                        // Update notification count
                        const notificationCountElement = document.getElementById("notification-count");
                        if (notificationCountElement) {
                            notificationCountElement.textContent = data.unreadCount; // Set count to zero
                            notificationCountElement.style.display = data.unreadCount === 0 ? "none" : "inline"; // Hide if zero
                        }
                    })
                    .catch(error => console.error('Error:', error));
            }

            let seenNotifications = []; // Array to store seen notification IDs

            // Function to fetch notifications
            function fetchNotifications() {
                fetch('?fetch_notifications=1')
                    .then(response => response.json())
                    .then(data => {
                        const notificationContainer = document.getElementById('notification-container');

                        // Clear previous notifications
                        notificationContainer.innerHTML = '<h3>Notifications</h3>';
                        const ul = document.createElement('ul');
                        notificationContainer.appendChild(ul);

                        if (data.length > 0) {
                            data.forEach(notification => {
                                const li = document.createElement('li');
                                li.style.display = 'flex';
                                li.style.justifyContent = 'space-between';
                                li.style.alignItems = 'center';
                                li.style.flexWrap = 'wrap'; // Allow wrapping to prevent overflow

                                li.innerHTML = `
                                    <div style="flex: 1; min-width: 70%; margin-right: 98px; word-wrap: break-word; overflow-wrap: break-word;">
                                        <strong>${notification.project_name}</strong><br>
                                        <div style="text-align: justify;">
                                            ${notification.notification_message}
                                        </div>
                                        <small>${new Date(notification.created_at).toLocaleString()}</small>
                                    </div>
                                    <button class="delete-btn" style="flex-shrink: 0;" onclick="deleteNotification(${notification.id})">Delete</button>
                                `;

                                ul.appendChild(li);
                                ul.appendChild(document.createElement('hr'));
                            });
                        } else {
                            ul.innerHTML = '<p>No notifications available.</p>';
                        }
                    })
                    .catch(error => console.error('Error fetching notifications:', error));
            }

            // Fetch notifications every 3 seconds (3000 milliseconds)
            setInterval(fetchNotifications, 3000);

            // Initial fetch of notifications when the page loads
            window.addEventListener('load', fetchNotifications);

            // Function to delete a notification
            function deleteNotification(notificationId) {
                // SweetAlert confirmation dialog
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'Do you really want to delete this notification?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel',
                    reverseButtons: true,
                    customClass: {
                        popup: 'custom-swal-popup', // Custom class for the popup
                        title: 'custom-swal-title', // Optional: Custom title class
                        confirmButton: 'custom-swal-confirm', // Custom class for confirm button
                        cancelButton: 'custom-swal-cancel' // Custom class for cancel button
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Send a POST request to the server to delete the notification
                        fetch('', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: `delete_notification=${notificationId}`  // Send the notification ID
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // If successful, show SweetAlert success message without a message
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Notification Deleted', // Only show title, no message
                                    timer: 2000, // Show for 2 seconds
                                    showConfirmButton: false,
                                    customClass: {
                                        popup: 'custom-swal-popup', // Custom class for success popup
                                        confirmButton: 'custom-swal-confirm' // Custom class for success confirm button
                                    }
                                }).then(() => {
                                    // Reload the page after the success alert is dismissed
                                    location.reload();
                                });
                            } else {
                                // If failed, show SweetAlert error message with a message
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Oops...',
                                    text: data.message || 'There was an error deleting the notification.',
                                    customClass: {
                                        popup: 'custom-error-popup', // Custom class for error popup
                                        confirmButton: 'custom-error-confirm' // Custom class for error confirm button
                                    }
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);

                            // Show SweetAlert error message for failure
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: 'Something went wrong. Please try again later.',
                                customClass: {
                                    popup: 'custom-error-popup', // Custom class for error popup
                                    confirmButton: 'custom-error-confirm' // Custom class for error confirm button
                                }
                            });
                        });
                    }
                });
            }

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
        </script>
    </body>
</html>