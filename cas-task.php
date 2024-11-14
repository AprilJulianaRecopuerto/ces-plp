<?php
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['uname'])) {
    // Redirect to login page if the session variable is not set
    header("Location: roleaccount.php");
    exit;
}

// Database credentials for mov
$servername = "localhost";
$username_db = "root";
$password_db = "";
$dbname_mov = "task_management";
$dbname_user_registration = "user_registration";

// Create connection to mov database
$conn_mov = new mysqli($servername, $username_db, $password_db, $dbname_mov);

// Check connection
if ($conn_mov->connect_error) {
    die("Connection failed: " . $conn_mov->connect_error);
}

// Handle deletion if project ID is provided
if (isset($_POST['delete_id'])) {
    $project_id = intval($_POST['delete_id']);
    $sql = "DELETE FROM cas WHERE id = ?";
    $stmt = $conn_mov->prepare($sql);
    $stmt->bind_param("i", $project_id);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Data deleted successfully";
    } else {
        $_SESSION['message'] = "Error deleting data";
    }

    $stmt->close();
    header("Location: cas-task.php"); // Redirect back to project list page
    exit();
}

// Handle the download request
if (isset($_GET['download']) && isset($_GET['name'])) {
    $filePath = $_GET['download'];
    $newFileName = $_GET['name'];

    // Validate file path and existence
    if (file_exists($filePath)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . basename($newFileName) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    } else {
        echo "File not found.";
        exit;
    }
}


// Fetch projects
$sql = "SELECT * FROM cas";
$result = $conn_mov->query($sql);

$message = isset($_SESSION['message']) ? $_SESSION['message'] : '';
unset($_SESSION['message']); // Clear the message after displaying it

$conn_mov->close();

// Create connection to user_registration database
$conn_user_registration = new mysqli($servername, $username_db, $password_db, $dbname_user_registration);

// Check connection
if ($conn_user_registration->connect_error) {
    die("Connection failed: " . $conn_user_registration->connect_error);
}

// Fetch the department name from user_registration
$username = $_SESSION['uname'];
$sql = "SELECT department FROM colleges WHERE uname = ?";
$stmt = $conn_user_registration->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->bind_result($departmentName);
$stmt->fetch();
$stmt->close();
$conn_user_registration->close();

$departmentName = htmlspecialchars($departmentName);

// Fetch the profile picture from the colleges table in user_registration
$conn_profile = new mysqli($servername, $username_db, $password_db, $dbname_user_registration);
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

            .button-container {
                display: flex;
                justify-content: flex-end; /* Align buttons to the right */
                margin-bottom: 20px; /* Space below the buttons */
                margin-right: 20px;
            }

            .button-container button {
                background-color: #4CAF50;
                border: none;
                color: white;
                padding: 10px 20px;
                margin-left: 10px;
                margin-top: 110px;
                border-radius: 5px;
                font-size: 16px;
                cursor: pointer;
                transition: background-color 0.3s;
                font-family: 'Poppins', sans-serif;
            }

            .button-container button:hover {
                background-color: #45a049; /* Darker green on hover */
            }

            .table-container {
                width: 100%;
                margin-left: -12px;
                overflow-x: auto;
                margin-top: 20px; /* Space above the table */
            }

            .crud-table {
                width: 100%;
                border-collapse: collapse;
                font-family: 'Poppins', sans-serif;
                background-color: #ffffff;
                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
                overflow: hidden;
            }

            .crud-table th, .crud-table td {
                border: 1px solid #ddd;
                padding: 10px;
                text-align: left;
                white-space: nowrap; /* Prevent text from wrapping */
            }

            .crud-table th {
                text-align: center; 
                background-color: #4CAF50;
                color: white;
                height: 40px;
                width: 14px; /* Set a fixed width for table headers */
            }

            .crud-table td {
                height: 50px;
                background-color: #fafafa;
            }

            .crud-table tr:hover {
                background-color: #f1f1f1;
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

            .edit a {
                font-family: 'Poppins', sans-serif;
                background-color: #4CAF50;
                border: none;
                color: white;
                padding: 10px 20px;
                margin-left: 10px;
                border-radius: 5px;
                font-size: 16px;
                cursor: pointer;
                text-align: center;
                transition: background-color 0.3s;
                text-decoration: none;
            }

            .edit a:hover {
                background-color: #45a049; /* Darker green on hover */
            }

            .delete-project-button {
                font-family: 'Poppins', sans-serif;
                background-color: #e74c3c;
                border: none;
                color: white;
                padding: 10px 20px;
                margin-left: 10px;
                border-radius: 5px;
                font-size: 16px;
                cursor: pointer;
                transition: background-color 0.3s;
            }

            .delete-project-button:hover {
                background-color: #ef233c; /* Darker green on hover */
            }

            .pagination-info {
                font-family: 'Poppins', sans-serif;
                display: flex; 
                justify-content: space-between; 
                margin-top: 10px;"
            }

            .pagination-link {
                font-family: 'Poppins', sans-serif;
                background-color: #4CAF50;
                border: none;
                color: white;
                padding: 10px 20px;
                margin-left: 10px;
                border-radius: 5px;
                font-size: 14px;
                cursor: pointer;
                transition: background-color 0.3s;
                text-decoration: none;
            }

            .pagination-link:hover {
                background-color: #45a049; /* Darker green on hover */
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
        </style>
    </head>

    <body>
    <nav class="navbar">
            <h2>
                <span style="color: purple;  font-size: 28px;"><?php echo htmlspecialchars($departmentName); ?></span> Task
            </h2>

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
                <button class="dropdown-btn">
                    <img src="images/task.png">Task Management
                    <i class="fas fa-chevron-down"></i> <!-- Dropdown icon -->
                </button>
                <div class="dropdown-container">
                    <a href="cas-task.php">Upload Files</a>
                    <a href="cas-mov.php">Mode of Verification</a>
                </div>

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
                <button onclick="logAndRedirect('Add Task', 'cas-addtask.php')">Add</button>
            </div>

            <div class="table-container">
                <table class="crud-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Project Name</th>
                            <th>Department</th>
                            <th>Date of Implementation</th>
                            <th>Files</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
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

                        // Pagination variables
                        $limit = 5; // Number of records per page
                        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Current page
                        $offset = ($page - 1) * $limit; // Offset for SQL query

                        // Count total records
                        $countSql = "SELECT COUNT(*) as total FROM cas";
                        $countResult = $conn->query($countSql);
                        $totalRecords = $countResult->fetch_assoc()['total'];
                        $totalPages = ceil($totalRecords / $limit); // Calculate total pages

                        // Fetch projects
                        $sql = "SELECT * FROM cas";
                        $result = $conn->query($sql);

                        if ($result->num_rows > 0) {
                            // Output data of each row
                            while ($row = $result->fetch_assoc()) {
                                // Fetch multiple files; assuming 'files' column has multiple file names separated by commas
                                $fileNames = [
                                    'Letter Request:' => htmlspecialchars($row['letter_request']),
                                    'Activity Design:' => htmlspecialchars($row['act_plan']),
                                    'Terms of Reference:' => htmlspecialchars($row['termof_ref']),
                                    'Requisition Form:' => htmlspecialchars($row['requi_form']),
                                    'Venue Reservation:' => htmlspecialchars($row['venue_reserve']),
                                    'Budget Expenditure Plan:' => htmlspecialchars($row['budget_plan'])
                                ];
                                
                                // Create links for each file with indication
                                $fileLinks = '';
                                foreach ($fileNames as $label => $fileName) {
                                    if (!empty($fileName)) { // Ensure the file name is not empty
                                        // Get the file extension
                                        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                                        
                                        // Add label and link with space
                                        $fileLinks .= "<div style='margin-bottom: 5px;'><strong>{$label}</strong></div>"; // Display the label
                                        if ($fileExtension === 'pdf') {
                                            // For PDFs, open in a new tab
                                            $fileLinks .= "<div style='margin-bottom: 10px;'><a href='cas-task-fileviewer.php?file=" . urlencode($fileName) . "' target='_blank'>" . basename($fileName) . "</a></div>";
                                        } else {
                                            // For other file types, use JavaScript to handle downloads
                                            $fileLinks .= "<div style='margin-bottom: 10px;'><a href='cas-task-fileviewer.php?file=" . urlencode($fileName) . "' class='download-file' data-filename='" . htmlspecialchars($fileName) . "'>" . basename($fileName) . "</a></div>";
                                        }
                                    }
                                }

                                echo "<tr>
                                        <td>" . htmlspecialchars($row["id"]) . "</td>
                                        <td>" . htmlspecialchars($row["project_name"]) . "</td>
                                        <td>" . htmlspecialchars($row["department"]) . "</td>
                                        <td>" . htmlspecialchars($row["due_date"]) . "</td>
                                        <td>
                                            {$fileLinks}
                                        </td>

                                        <td class='edit'>
                                    <a href='cas-edittask.php?id=" . htmlspecialchars($row["id"]) . "'>EDIT</a>
                                    <button class='delete-project-button' data-id='" . htmlspecialchars($row["id"]) . "'>DELETE</button>
                                </td>
                            </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6'>No records found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <div class="pagination-info">
                <div>
                    <p><?php echo "$totalRecords RECORDS FOUND"; ?></p>
                </div>

                <div class="page">
                    <p>
                        <?php if ($page > 1): ?>
                            <a class="pagination-link" href="?page=<?php echo $page - 1; ?>">PREV</a>
                        <?php endif; ?>

                        <span class="pagination-text">Page <?php echo $page; ?> of <?php echo $totalPages; ?></span>
                        
                        <?php if ($page < $totalPages): ?>
                            <a class="pagination-link" href="?page=<?php echo $page + 1; ?>">NEXT</a>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>

        <form id="delete-form" method="post" style="display:none;">
            <input type="hidden" name="delete_id" id="delete_id">
        </form>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                <?php if ($message): ?>
                    Swal.fire({
                        title: 'Success',
                        text: "<?php echo $message; ?>",
                        icon: 'success',
                        confirmButtonColor: '#089451',
                        confirmButtonText: 'OK',
                        customClass: {
                            popup: 'custom-swal-popup'
                        }
                    });
                <?php endif; ?>

            // Attach click event listener to all delete buttons
            document.querySelectorAll('.delete-project-button').forEach(button => {
                button.addEventListener('click', function() {
                    const projectId = this.getAttribute('data-id');
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "You won't be able to restore this!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#e74c3c',
                        cancelButtonColor: '#089451',
                        confirmButtonText: 'Yes, delete it!',
                        customClass: {
                            popup: 'custom-swal-popup',
                            confirmButton: 'custom-swal-confirm',
                            cancelButton: 'custom-swal-cancel'
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            document.getElementById('delete_id').value = projectId;
                            document.getElementById('delete-form').submit();
                        }
                    });
                });
                });
            });

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
            // Additional custom styles via CSS can be added here
        }).then((result) => {
            if (result.isConfirmed) {
                // Pass action in the fetch call
                fetch('college-logout.php?action=logout')
                    .then(response => response.text())
                    .then(data => {
                        console.log(data); // Log response for debugging
                        window.location.href = 'roleaccount.php';
                    })
                    .catch(error => console.error('Error:', error));
            }
        });
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

                document.querySelectorAll("td.edit a").forEach(function(link) {
                link.addEventListener("click", function(event) {
                    event.preventDefault(); // Prevent the default action
                    var url = this.href; // Get the URL from the href attribute
                    logAndRedirect("Edit Task", url); // Log the action and redirect
                });
            });

            // Log clicks on action buttons (Delete)
            document.querySelectorAll(".delete-button").forEach(function(button) {
                button.addEventListener("click", function() {
                    logAction("Delete Task"); // Log deletion action
                    // Additional logic for deletion can be added here if needed
                });
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