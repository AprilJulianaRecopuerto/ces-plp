<?php
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['uname'])) {
    // Redirect to login page if the session variable is not set
    header("Location: loginpage.php");
    exit;
}

// Database credentials
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "task_management";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the submitted data
    $projectId = intval($_POST['id']); // Ensure project ID is an integer

    // Retrieve existing project data
    $stmt = $conn->prepare("SELECT project_name, department, due_date, letter_request, act_plan, termof_ref, venue_reserve FROM con WHERE id = ?");
    $stmt->bind_param("i", $projectId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $existingProject = $result->fetch_assoc();
    } else {
        $_SESSION['error'] = 'Project not found.';
        header("Location: con-task.php");
        exit();
    }

    // Retrieve form data, using existing values where appropriate
    $project_name = !empty($_POST['project_name']) ? $_POST['project_name'] : $existingProject['project_name'];
    $department = !empty($_POST['department']) ? $_POST['department'] : $existingProject['department'];
    $due_date = !empty($_POST['due_date']) ? $_POST['due_date'] : $existingProject['due_date'];
    $letter_request = !empty($_POST['letter_request']) ? $_POST['letter_request'] : $existingProject['letter_request'];
    $act_plan = !empty($_POST['act_plan']) ? $_POST['act_plan'] : $existingProject['act_plan'];
    $termof_ref = !empty($_POST['termof_ref']) ? $_POST['termof_ref'] : $existingProject['termof_ref'];
    $venue_reserve = !empty($_POST['venue_reserve']) ? $_POST['venue_reserve'] : $existingProject['venue_reserve'];

    // Prepare and bind the update statement
    $stmt = $conn->prepare("UPDATE con SET 
        project_name=?, 
        department=?, 
        due_date=?, 
        letter_request=?, 
        act_plan=?, 
        termof_ref=?, 
        venue_reserve=? 
        WHERE id=?");

    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }

    // Bind parameters
    $stmt->bind_param('sssssssi', $project_name, $department, $due_date, $letter_request, $act_plan, $termof_ref, $venue_reserve, $projectId);

    // Execute the statement
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Project updated successfully.';
        header("Location: con-edittask.php?id=" . $projectId); // Redirect with project ID
        exit();
    } else {
        $_SESSION['error'] = 'Failed to update project: ' . $stmt->error;
        header("Location: con-edittask.php?id=" . $projectId); // Redirect with project ID
        exit();
    }

    $stmt->close();
}

// Get project ID from URL
if (isset($_GET['id'])) {
    $projectId = intval($_GET['id']);

    // Prepare and bind for fetching project data
    $stmt = $conn->prepare("SELECT * FROM con WHERE id = ?");
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("i", $projectId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $project = $result->fetch_assoc();
    } else {
        $_SESSION['error'] = 'Project not found.';
        header("Location: con-task.php"); // Redirect to project list if no ID is found
        exit();
    }
} else {
    $_SESSION['error'] = 'No project ID specified.';
    header("Location: con-task.php"); // Redirect to project list if no ID is specified
    exit();
}

$stmt->close();
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>CES PLP</title>

        <link rel="icon" href="images/logoicon.png">

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
                font-family: 'Poppins', sans-serif;
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

            .content-editor{
                margin-left: 320px; /* Align with the sidebar */
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
                display: block;
                font-weight: bold;
                margin-bottom: 5px;
            }

            .form-group select, .form-group input[type="text"], .form-group input[type="date"], .form-group input[type="time"] {
                width: 100%;
                padding: 8px;
                border: 1px solid #ddd;
                border-radius: 5px;
                font-size: 16px;
                box-sizing: border-box;
                font-family: 'Poppins', sans-serif;
            }

            .form-group input::placeholder {
                font-family: 'Poppins', sans-serif;
                color: #999;
                font-style: italic;
            }

            .form-group select {
                background: #f9f9f9;
            }

            .button-container {
                display: flex;
                justify-content: flex-end; /* Align buttons to the right */
                margin-top: 20px; /* Space above the buttons */
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

            .custom-confirm {
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
                font-size: 17px;
                background-color: #e74c3c;
                color: #fff;
                border-radius: 10px;
                cursor: pointer;
                outline: none; /* Remove default focus outline */
            }

            .form-text.text-muted {
                font-size: 0.875rem; /* Adjust the font size */
                color: #6c757d; /* Bootstrap's muted color */
                margin-top: 0.25rem; /* Space above the text */
            }

            input[type="file"] {
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

            input[type="file"]::file-selector-button:hover {
                background-color: #2579a8;
            }

            .form-group {
                margin-bottom: 15px;
            }

            .form-text {
                margin-top: 5px;
                font-size: 0.875em;
                color: #6c757d;
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
                background-color: #089451;
                color: #fff;
                border-radius: 10px;
                cursor: pointer;
                outline: none; /* Remove default focus outline */
            }

            .custom-warning-cancel {
                font-family: 'Poppins', sans-serif;
                font-size: 17px;
                background-color: #e74c3c;
                color: #fff;
                border-radius: 10px;
                cursor: pointer;
                outline: none; /* Remove default focus outline */
            }
        </style>
    </head>

    <body>
    <nav class="navbar">
            <h2>Edit Task</h2> 

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

        <div class="content-editor">
            <div class="form-container">
                <h3>Edit Project</h3>

                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="id">Project ID:</label>
                        <input type="text" name="id" id="id" value="<?php echo htmlspecialchars($project['id']); ?>" readonly>
                    </div>

                    <div class="form-group">
                        <label for="project_name">Project Title:</label>
                        <input type="text" name="project_name" value="<?php echo htmlspecialchars($project['project_name']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="dept">Department:</label>
                        <input type="text" id="department" name="department" value="College of Nursing" readonly>
                    </div>

                    <div class="form-group">
                        <label for="due_date">Due Date:</label>
                        <input type="date" name="due_date" id="due_date" value="<?php echo isset($project['due_date']) ? htmlspecialchars($project['due_date']) : ''; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="letter_request">Letter Request:</label>
                        <?php if (!empty($project['letter_request'])) : ?>
                            <p>Current File: 
                                <?php 
                                $letterFilePath = "taskuploadfiles/" . $project['letter_request'];
                                $letterFileExtension = strtolower(pathinfo($letterFilePath, PATHINFO_EXTENSION));
                                ?>
                                <a href="<?php echo $letterFileExtension === 'pdf' ? 'con-task-fileviewer.php?file=' . urlencode($project['letter_request']) : $letterFilePath; ?>" 
                                <?php echo $letterFileExtension !== 'pdf' ? 'download' : ''; ?>
                                target="<?php echo $letterFileExtension === 'pdf' ? '_blank' : ''; ?>">
                                    <?php echo $project['letter_request']; ?>
                                </a>
                            </p>
                        <?php else : ?>
                            <p>No current file available.</p>
                        <?php endif; ?>

                        <input type="file" id="letter_request" name="letter_request" class="file-input">
                        <small class="form-text text-muted">Choose a file to replace the current one.</small>
                    </div>

                    <div class="form-group">
                        <label for="act_plan">Activity Plan:</label>
                        <?php if (!empty($project['act_plan'])) : ?>
                            <p>Current File: 
                                <?php 
                                $actPlanFilePath = "taskuploadfiles/" . $project['act_plan'];
                                $actPlanFileExtension = strtolower(pathinfo($actPlanFilePath, PATHINFO_EXTENSION));
                                ?>
                                <a href="<?php echo $actPlanFileExtension === 'pdf' ? 'con-task-fileviewer.php?file=' . urlencode($project['act_plan']) : $actPlanFilePath; ?>" 
                                <?php echo $actPlanFileExtension !== 'pdf' ? 'download' : ''; ?>
                                target="<?php echo $actPlanFileExtension === 'pdf' ? '_blank' : ''; ?>">
                                    <?php echo $project['act_plan']; ?>
                                </a>
                            </p>
                        <?php else : ?>
                            <p>No current file available.</p>
                        <?php endif; ?>

                        <input type="file" id="act_plan" name="act_plan" class="file-input">
                        <small class="form-text text-muted">Choose a file to replace the current one.</small>
                    </div>

                    <div class="form-group">
                        <label for="termof_ref">Terms of Reference:</label>
                        <?php if (!empty($project['termof_ref'])) : ?>
                            <p>Current File: 
                                <?php 
                                $termOfRefFilePath = "taskuploadfiles/" . $project['termof_ref'];
                                $termOfRefFileExtension = strtolower(pathinfo($termOfRefFilePath, PATHINFO_EXTENSION));
                                ?>
                                <a href="<?php echo $termOfRefFileExtension === 'pdf' ? 'con-task-fileviewer.php?file=' . urlencode($project['termof_ref']) : $termOfRefFilePath; ?>" 
                                <?php echo $termOfRefFileExtension !== 'pdf' ? 'download' : ''; ?>
                                target="<?php echo $termOfRefFileExtension === 'pdf' ? '_blank' : ''; ?>">
                                    <?php echo $project['termof_ref']; ?>
                                </a>
                            </p>
                        <?php else : ?>
                            <p>No current file available.</p>
                        <?php endif; ?>

                        <input type="file" id="termof_ref" name="termof_ref" class="file-input">
                        <small class="form-text text-muted">Choose a file to replace the current one.</small>
                    </div>

                    <div class="form-group">
                        <label for="venue_reserve">Venue Reservation:</label>
                        <?php if (!empty($project['venue_reserve'])) : ?>
                            <p>Current File: 
                                <?php 
                                $venueReserveFilePath = "taskuploadfiles/" . $project['venue_reserve'];
                                $venueReserveFileExtension = strtolower(pathinfo($venueReserveFilePath, PATHINFO_EXTENSION));
                                ?>
                                <a href="<?php echo $venueReserveFileExtension === 'pdf' ? 'con-task-fileviewer.php?file=' . urlencode($project['venue_reserve']) : $venueReserveFilePath; ?>" 
                                <?php echo $venueReserveFileExtension !== 'pdf' ? 'download' : ''; ?>
                                target="<?php echo $venueReserveFileExtension === 'pdf' ? '_blank' : ''; ?>">
                                    <?php echo $project['venue_reserve']; ?>
                                </a>
                            </p>
                        <?php else : ?>
                            <p>No current file available.</p>
                        <?php endif; ?>

                        <input type="file" id="venue_reserve" name="venue_reserve" class="file-input">
                        <small class="form-text text-muted">Choose a file to replace the current one.</small>
                    </div>

                    <div class="button-container">
                        <button type="submit">Update Project</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            
        document.addEventListener('DOMContentLoaded', (event) => {
            // Function to show success alert
            function showSuccessAlert(message) {
                Swal.fire({
                    title: 'Success',
                    text: message,
                    icon: 'success',
                    confirmButtonColor: '#089451',
                    confirmButtonText: 'OK',
                    customClass: {
                        popup: 'custom-swal-popup',
                        title: 'custom-swal-title',
                        confirmButton: 'custom-swal-confirm'
                    }
                }).then(() => {
                    window.location.href = "con-task.php"; // Redirect to the project list
                });
            }

            // Function to show error alert
            function showErrorAlert(message) {
                Swal.fire({
                    title: 'Error',
                    text: message,
                    icon: 'error',
                    confirmButtonColor: '#e74c3c',
                    confirmButtonText: 'OK',
                    customClass: {
                        popup: 'custom-error-popup',
                        title: 'custom-error-title',
                        confirmButton: 'custom-error-confirm'
                    }
                });
            }

            // Check for success message and show alert
            <?php if (isset($_SESSION['success'])) : ?>
                showSuccessAlert('<?php echo addslashes($_SESSION['success']); ?>');
                <?php unset($_SESSION['success']); // Unset the session variable ?>
            <?php endif; ?>

            // Check for error message and show alert
            <?php if (isset($_SESSION['error'])) : ?>
                showErrorAlert('<?php echo addslashes($_SESSION['error']); ?>');
                <?php unset($_SESSION['error']); // Unset the session variable ?>
            <?php endif; ?>
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
        </script>
    </body>
</html>
