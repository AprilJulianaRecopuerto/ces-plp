<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['uname'])) {
    header("Location: collegelogin.php");
    exit;
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "budget_utilization";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the project ID from the URL
$project_id = $_GET['id'];

// Fetch the current project details from the database
$query = "SELECT * FROM con_budget WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $project_id);
$stmt->execute();
$result = $stmt->get_result();
$project = $result->fetch_assoc();

// Check if project exists
if (!$project) {
    $_SESSION['error'] = "Project not found.";
    header("Location: con-edit-budget.php");
    exit();
}

// Get the total budget and expenses for the specific project
$total_budget = floatval($project['total_budget']); // Total budget of the specific project
$total_expenses = floatval($project['total_expenses']); // Total expenses of the specific project
$remaining_balance = floatval($project['remaining_balance']); // Remaining balance

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form values
    $semester = $_POST['semester'];
    $project_title = $_POST['project_title'];
    $district = $_POST['district'];
    $barangay = $_POST['barangay'];
    $new_total_expenses = floatval($_POST['total_expenses']); // New total expenses from the form

    // Check if there are changes
    if ($semester === $project['semester'] &&
        $project_title === $project['project_title'] &&
        $district === $project['district'] &&
        $barangay === $project['barangay'] &&
        $new_total_expenses === $total_expenses) {

        // No changes made
        $_SESSION['warning'] = "No changes were made to the project.";
        header("Location: con-edit-budget.php?id=" . $project_id); // Redirect with project ID
        exit();
    }

    // Calculate the new remaining balance for the current project
    $new_remaining_balance = $total_budget - $new_total_expenses;

    // Update the current project with new fields and the calculated remaining balance
    $query = "UPDATE con_budget SET semester = ?, project_title = ?, district = ?, barangay = ?, total_expenses = ?, remaining_balance = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssdii", $semester, $project_title, $district, $barangay, $new_total_expenses, $new_remaining_balance, $project_id);
    
    // Execute the statement
    if ($stmt->execute()) {
        // Update total budgets of subsequent projects based on the new remaining balance
        $query = "SELECT id, total_budget, remaining_balance FROM con_budget WHERE id > ? ORDER BY id ASC";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $project_id);
        $stmt->execute();
        $result = $stmt->get_result();

        // Update budgets of subsequent projects
        while ($subsequent_project = $result->fetch_assoc()) {
            $subsequent_id = $subsequent_project['id'];
            $subsequent_remaining_balance = floatval($subsequent_project['remaining_balance']);
            $subsequent_total_budget = floatval($subsequent_project['total_budget']);

            // Update total budget of the subsequent project to the new remaining balance
            $query = "UPDATE con_budget SET total_budget = ? WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("di", $new_remaining_balance, $subsequent_id);
            $stmt->execute();

            // Calculate the new remaining balance for the subsequent project
            $new_remaining_balance -= $subsequent_project['total_expenses'];

            // Stop updating if the remaining balance becomes zero or negative
            if ($new_remaining_balance <= 0) {
                break;
            }
        }

        // Delete subsequent projects with zero remaining balance
        $query = "DELETE FROM con_budget WHERE id > ? AND remaining_balance <= 0";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $project_id);
        $stmt->execute();

        $_SESSION['success'] = "Project updated successfully.";
        header("Location: con-edit-budget.php?id=" . $project_id);
        exit();
    } else {
        $_SESSION['error'] = "Error updating project.";
        header("Location: con-edit-budget.php?id=" . $project_id);
        exit();
    }
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        
        <title>CES PLP</title>

        <link rel="icon" href="images/logoicon.png">

        <!-- SweetAlert CSS and JavaScript -->
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

            .content-projectlist {
                margin-left: 320px; /* Align with the sidebar */
                padding: 20px;
            }

            .content-projectlist h2 {
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
            <h2>Edit Budget</h2> 

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

                <li><a href="con-budget-utilization.php" class="active"><img src="images/budget.png">Budget Allocation</a></li>

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

        <div class="content-projectlist">
            <div class="form-container">
                <h3>Project Details</h3>
                
                <form action="" method="POST" enctype="multipart/form-data">

                    <div class="form-group">
                        <label for="id">Project ID:</label>
                        <input type="text" name="id" id="id" value="<?php echo htmlspecialchars($project['id']); ?>" readonly>
                    </div>

                    <div class="form-group">
                        <label for="dept">Department:</label>
                        <input type="text" id="department" name="department" value="College of Nursing" readonly>
                    </div>

                    <div class="form-group">
                        <label for="semester">Semester:</label>
                        <select name="semester" id="semester" required>
                            <option value="1st Semester" <?php echo $project['semester'] == '1st Semester' ? 'selected' : ''; ?>>1st Semester</option>
                            <option value="2nd Semester" <?php echo $project['semester'] == '2nd Semester' ? 'selected' : ''; ?>>2nd Semester</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="project_title">Project Title:</label>
                        <input type="text" name="project_title" value="<?php echo htmlspecialchars($project['project_title']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="district">District:</label>
                        <select name="district" id="district" required>
                            <option value="District 1" <?php echo $project['district'] == 'District 1' ? 'selected' : ''; ?>>District 1</option>
                            <option value="District 2" <?php echo $project['district'] == 'District 2' ? 'selected' : ''; ?>>District 2</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="barangay">Barangay:</label>
                        <select id="barangay" name="barangay" required>
                            
                            <?php foreach ($barangay as $barangay): ?>
                                <option value="<?php echo htmlspecialchars($barangay['id']); ?>" <?php echo ($barangay['id'] == $project['barangay']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($barangay['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="total_budget">Total Budget:</label>
                        <?php 
                            // Format total budget for display
                            $formatted_total_budget = number_format($total_budget, 2);
                        ?>
                        <input type="text" id="total_budget" name="total_budget" value="<?php echo htmlspecialchars($formatted_total_budget, ENT_QUOTES, 'UTF-8'); ?>" readonly>
                        <p>You have a total budget of: <strong><?php echo htmlspecialchars($formatted_total_budget, ENT_QUOTES, 'UTF-8'); ?></strong></p>
                    </div>

                    <div class="form-group">
                        <label for="total_expenses">Total Expenses:</label>
                        <?php if ($remaining_balance <= 0): ?>
                            <p><strong>You have no remaining balance.</strong></p>
                        <?php else: ?>
                            <input type="text" id="total_expenses" name="total_expenses" 
                            value="<?php echo isset($total_expenses) ? htmlspecialchars($total_expenses, ENT_QUOTES, 'UTF-8') : ''; ?>" required>
                        <?php endif; ?>
                    </div>
                    
                    <div class="button-container">
                        <button type="submit">Update Budget</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            // Function to update barangays based on the selected district
            function updateBarangays(selectedBarangay = '') {
                const district = document.getElementById('district').value;
                const barangaySelect = document.getElementById('barangay');

                // Clear existing options
                barangaySelect.innerHTML = '';

                let barangays = [];

                if (district === 'District 1') {
                    barangays = [
                        'Bagong Ilog', 'Bagong Katipunan', 'Bambang', 'Buting', 'Caniogan',
                        'Kalawaan', 'Kapasigan', 'Kapitolyo', 'Malinao', 'Oranbo',
                        'Palatiw', 'Pineda', 'Sagad', 'San Antonio', 'San Joaquin',
                        'San Jose', 'San Nicolas', 'Sta. Cruz', 'Sta. Rosa', 'Sto. Tomas',
                        'Sumilang', 'Ugong'
                    ];
                } else if (district === 'District 2') {
                    barangays = [
                        'Dela Paz', 'Manggahan', 'Maybunga', 'Pinagbuhatan', 'Rosario',
                        'San Miguel', 'Sta. Lucia', 'Santolan'
                    ];
                }

                // Add default option
                const defaultOption = document.createElement('option');
                defaultOption.value = '';
                defaultOption.disabled = true;
                defaultOption.selected = true;
                defaultOption.textContent = 'Select Barangay';
                barangaySelect.appendChild(defaultOption);

                // Add new options and select the correct barangay
                barangays.forEach(barangay => {
                    const option = document.createElement('option');
                    option.value = barangay;
                    option.textContent = barangay;

                    // Check if this barangay is the one selected for editing
                    if (barangay === selectedBarangay) {
                        option.selected = true; // Select this option if it matches
                    }

                    barangaySelect.appendChild(option);
                });
            }

            // Function to initialize barangay options on page load, if editing
            document.addEventListener('DOMContentLoaded', () => {
                const initialDistrict = document.getElementById('district').value;
                const selectedBarangay = '<?php echo htmlspecialchars($project['barangay']); ?>'; // Get the selected barangay from PHP
                console.log('Selected Barangay:', selectedBarangay); // Debugging line
                updateBarangays(selectedBarangay); // Pass the selected barangay to the function
            });

            // Optional: Update barangays when the district changes
            document.getElementById('district').addEventListener('change', () => {
                updateBarangays(); // Call this without the selected barangay when changing district
            });

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
                    window.location.href = "con-budget-utilization.php"; // Redirect to the project list
                });
            }

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

            function showWarningAlert(message) {
                Swal.fire({
                    title: 'Warning',
                    text: message,
                    icon: 'warning',
                    showCancelButton: true, // Show cancel button
                    confirmButtonText: 'OK',
                    cancelButtonText: 'Cancel',
                    customClass: {
                        popup: 'custom-warning-popup',
                        title: 'custom-warning-title',
                        confirmButton: 'custom-warning-confirm',
                        cancelButton: 'custom-warning-cancel' 
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Redirect to con-projlist.php
                        window.location.href = 'con-budget-utilization.php';
                    }
                    // If Cancel is clicked, do nothing (stay on con-editproj.php)
                });
            }

            // Check for warning message and show alert
            <?php if (isset($_SESSION['warning'])) : ?>
                showWarningAlert('<?php echo $_SESSION['warning']; ?>');
                <?php unset($_SESSION['warning']); // Unset the session variable ?>
            <?php endif; ?>

            // Check for success message and show alert
            <?php if (isset($_SESSION['success'])) : ?>
                showSuccessAlert('<?php echo $_SESSION['success']; ?>');
                <?php unset($_SESSION['success']); // Unset the session variable ?>
            <?php endif; ?>

            // Check for error message and show alert
            <?php if (isset($_SESSION['error'])) : ?>
                showErrorAlert('<?php echo $_SESSION['error']; ?>');
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

            document.addEventListener('DOMContentLoaded', () => {
            // Add change event listeners to file inputs
            document.getElementById('budget').addEventListener('change', validateBudgetFile);
            document.getElementById('tor').addEventListener('change', validateTorFile);
            });

            function validateBudgetFile() {
                const budgetFile = this.files[0];
                const allowedTypes = /(\.docx|\.pdf|\.xls|\.xlsx)$/i;
                

                if (budgetFile) {
                    if (!allowedTypes.test(budgetFile.name)) {
                        Swal.fire({
                            icon: "error",
                            title: 'Invalid File Type',
                            text: 'Budget file must be a DOCX, PDF, XLS, or XLSX file.',
                            confirmButtonText: 'OK',
                            customClass: {
                                popup: 'custom-error-popup',
                                title: 'custom-error-title',
                                text: 'custom-error-text',
                                confirmButton: 'custom-error-confirm'
                            }
                        });
                        this.value = ''; // Clear the input
                    }
                }
            }

            function validateTorFile() {
                const torFile = this.files[0];
                const allowedTypes = /(\.docx|\.pdf|\.xls|\.xlsx)$/i;

                if (torFile) {
                    if (!allowedTypes.test(torFile.name)) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Invalid File Type',
                            text: 'TOR file must be a DOCX, PDF, XLS, or XLSX file.',
                            confirmButtonText: 'OK',
                            customClass: {
                                popup: 'custom-error-popup',
                                title: 'custom-error-title',
                                text: 'custom-error-text',
                                confirmButton: 'custom-error-confirm'
                            }
                        });
                        this.value = ''; // Clear the input
                    } 
                }
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
        </script>
    </body>
</html>
