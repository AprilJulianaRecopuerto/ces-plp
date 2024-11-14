<?php
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['uname'])) {
    header("Location: loginpage.php");
    exit;
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "resource_utilization";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables
$requisitionFormData = [];
$venue_requests = [];
$additional_requests = [];
$quantity = [];

// Fetch reservation data if editing an existing entry
if (isset($_GET['id'])) {
    $reservationId = intval($_GET['id']);
    
    // Fetch reservation data
    $sql = "SELECT * FROM cba_reservation WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $reservationId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $requisitionFormData = $result->fetch_assoc();
        
        // Fetch venue requests
        $sqlVenue = "SELECT venue_name FROM cba_venue_request WHERE reservation_id = ?";
        $stmtVenue = $conn->prepare($sqlVenue);
        $stmtVenue->bind_param("i", $reservationId);
        $stmtVenue->execute();
        $resultVenue = $stmtVenue->get_result();
        while ($row = $resultVenue->fetch_assoc()) {
            $venue_requests[] = $row['venue_name'];
        }

        // Fetch additional requests
        $sqlAdditional = "SELECT additional_request, quantity FROM cba_addedrequest WHERE reservation_id = ?";
        $stmtAdditional = $conn->prepare($sqlAdditional);
        $stmtAdditional->bind_param("i", $reservationId);
        $stmtAdditional->execute();
        $resultAdditional = $stmtAdditional->get_result();
        
        while ($row = $resultAdditional->fetch_assoc()) {
            $additional_requests[] = $row['additional_request'];
            $quantity[$row['additional_request']] = $row['quantity'];
        }
    }

    // Close the statements
    $stmt->close();
    $stmtVenue->close();
    $stmtAdditional->close();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get posted data
    $date = $conn->real_escape_string($_POST['date']);
    $name = $conn->real_escape_string($_POST['name']);
    $college_name = $conn->real_escape_string($_POST['college_name']);
    $event_activity = $conn->real_escape_string($_POST['event']);
    $event_date = $conn->real_escape_string($_POST['event_date']);
    $time_of_event = $conn->real_escape_string($_POST['time_of_event']);

    // Update reservation details
    if (isset($_GET['id'])) {
        $reservationId = intval($_GET['id']);

        // Update the reservation form in the database
        $updateReservationSql = "UPDATE cba_reservation SET 
            date_of_request = '$date',
            name = '$name',
            college_name = '$college_name',
            event_activity = '$event_activity',
            event_date = '$event_date',
            time_of_event = '$time_of_event'
            WHERE id = $reservationId";

        if ($conn->query($updateReservationSql) === TRUE) {
            // Clear and insert updated venue requests
            $deleteVenuesSql = "DELETE FROM cba_venue_request WHERE reservation_id = $reservationId";
            $conn->query($deleteVenuesSql);

            $newVenueRequests = isset($_POST['venue_requests']) ? $_POST['venue_requests'] : [];
            foreach ($newVenueRequests as $venue) {
                if (!empty($venue)) {
                    $insertVenueSql = "INSERT INTO cba_venue_request (reservation_id, venue_name) 
                                       VALUES ($reservationId, '" . $conn->real_escape_string($venue) . "')";
                    $conn->query($insertVenueSql);
                }
            }

            // Clear and insert updated additional requests
            $deleteAdditionalSql = "DELETE FROM cba_addedrequest WHERE reservation_id = $reservationId";
            $conn->query($deleteAdditionalSql);

            $newAdditionalRequests = isset($_POST['additional_requests']) ? $_POST['additional_requests'] : [];
            if (!empty($newAdditionalRequests) && is_array($newAdditionalRequests)) {
                foreach ($newAdditionalRequests as $request) {
                    if (!empty($request)) {
                        $quantityValue = isset($_POST['quantity' . $request]) && is_numeric($_POST['quantity' . $request]) ? intval($_POST['quantity' . $request]) : 0;
                        $insertAdditionalSql = "INSERT INTO cba_addedrequest (reservation_id, additional_request, quantity) VALUES (?, ?, ?)";
                        $stmtInsertAdditional = $conn->prepare($insertAdditionalSql);
                        $stmtInsertAdditional->bind_param("isi", $reservationId, $request, $quantityValue);
                        $stmtInsertAdditional->execute();
                    }
                }
            }

            // Set a single success message
            $_SESSION['success'] = 'Details submitted successfully.';
        } else {
            $_SESSION['error'] = 'Error updating reservation: ' . $conn->error;
        }
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
                font-family: 'Glacial Indifference', sans-serif;
                margin-top:110px;
                background-color: #ffffff;
                padding: 20px;
                border-radius: 10px;
                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            }

            .form-container h2 {
                margin-top: 0;
                font-family: 'Poppins', sans-serif;
                font-size: 24px;
                color: black;
            }

            .form-group {
                margin-bottom: 15px;
            }

            .form-group label {
                font-family: 'Poppins', sans-serif; /* Set Poppins font for table cells */
                display: block;
                font-weight: bold;
                margin-bottom: 5px;
            }

            .form-group select, .form-group input[type="text"], .form-group input[type="date"], textarea,  .form-group input[type="number"] {
                width: 100%;
                padding: 8px;
                border: 1px solid #ddd;
                border-radius: 5px;
                font-size: 16px;
                box-sizing: border-box;
                font-family: 'Poppins', sans-serif;
                margin-bottom: 10px;
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
                justify-content: flex-end;
                margin-top: 20px;
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

            .event-section {
                border: 1px solid #ccc;
                padding: 15px;
                margin: 10px 0;
            }

            .event-section h3 {
                font-family: 'Poppins', sans-serif;
                margin-top: 5px;
            }

            .remove-btn {
                background-color: #e74c3c;
                border: none;
                color: white;
                padding: 10px 20px;
                margin-left: 10px;
                margin-bottom: 10px;
                border-radius: 5px;
                font-size: 14px;
                cursor: pointer;
                transition: background-color 0.3s;
                font-family: 'Poppins', sans-serif;
            }

            .remove-btn:hover {
                background-color: #e74c1c;
                color: white;
            }

            .add-btn {
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

            .add-btn:hover {
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
        </style>
    </head>

    <body>
    <nav class="navbar">
            <h2>Edit Venue Details</h2> 

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
                    <a href="cba-your-profile.php">Profile</a>
                    <a class="signout" href="roleaccount.php" onclick="confirmLogout(event)">Sign out</a>
                </div>
            </div>
        </nav>
        
        <div class="sidebar">
            <div class="logo">
                <img src="images/logo.png" alt="Logo">
            </div>

            <ul class="menu">
                <li><a href="cba-dash.php" class="active"><img src="images/home.png">Dashboard</a></li>
                <li><a href="cba-projlist.php"><img src="images/project-list.png">Project List</a></li>
                <li><a href="cba-calendar.php"><img src="images/calendar.png">Event Calendar</a></li>

                <!-- Dropdown for Resource Utilization -->
                <button class="dropdown-btn">
                    <img src="images/resource.png"> Resource Utilization
                    <i class="fas fa-chevron-down"></i> <!-- Dropdown icon -->
                </button>
                <div class="dropdown-container">
                    <a href="cba-tor.php">Term of Reference</a>
                    <a href="cba-requi.php">Requisition</a>
                    <a href="cba-venue.php">Venue</a>
                </div>

                <li><a href="cba-budget-utilization.php"><img src="images/budget.png">Budget Allocation</a></li>

                <!-- Dropdown for Task Management -->
                <button class="dropdown-btn">
                    <img src="images/task.png">Task Management
                    <i class="fas fa-chevron-down"></i> <!-- Dropdown icon -->
                </button>
                <div class="dropdown-container">
                    <a href="cba-task.php">Upload Files</a>
                    <a href="cba-mov.php">Mode of Verification</a>
                </div>

                <li><a href="cba-responses.php"><img src="images/feedback.png">Responses</a></li>

                <!-- Dropdown for Audit Trails -->
                <button class="dropdown-btn">
                    <img src="images/logs.png"> Audit Trails
                    <i class="fas fa-chevron-down"></i> <!-- Dropdown icon -->
                </button>
                <div class="dropdown-container">
                    <a href="cba-history.php">Log In History</a>
                    <a href="cba-activitylogs.php">Activity Logs</a>
                </div>
            </ul>
        </div>
        
        <div class="content-editor">
            <div class="form-container">
                <h2>Edit Event Submission Form</h2>
                
                <form id="venueForm" action="" method="POST">

                    <div class="form-group">
                        <label for="date">Date of Request:</label>
                        <input type="date" id="date" name="date" value="<?php echo htmlspecialchars($requisitionFormData['date_of_request'] ?? ''); ?>" >
                    </div>

                    <h3>Requested made by:</h3>
                    <div class="form-group">
                        <label for="name">Name:</label>
                        <input type="text" id="name" name="name" placeholder="Enter your Name" value="<?php echo htmlspecialchars($requisitionFormData['name'] ?? ''); ?>" >
                    </div>

                    <div class="form-group">
                        <label for="college_name">Office/College:</label>
                        <input type="text" id="college_name" name="college_name" value="<?php echo htmlspecialchars($requisitionFormData['college_name'] ?? 'College of Business Administration'); ?>" placeholder="Enter College Name" required>
                    </div>

                    <div class="form-group">
                        <label for="event">Event/Activity:</label>
                        <input type="text" id="event" name="event" placeholder="Enter your Event/Activity Name" value="<?php echo htmlspecialchars($requisitionFormData['event_activity'] ?? ''); ?>" >
                    </div>

                    <div class="form-group">
                        <label for="event_date">Date of Event:</label>
                        <input type="date" id="event_date" name="event_date" value="<?php echo htmlspecialchars($requisitionFormData['event_date'] ?? ''); ?>" >
                    </div>

                    <div class="form-group">
                        <label for="time_of_event">Time of Event:</label>
                        <input type="text" id="time_of_event" name="time_of_event" placeholder="hh:mm AM/PM" value="<?php echo htmlspecialchars($requisitionFormData['time_of_event'] ?? ''); ?>" >
                    </div>

                    <div class="form-group">
                        <h3>Facility/Venue Requested</h3>

                        <div class="checkbox-options" id="venueRequestsContainer">
                            <label>
                                <input type="checkbox" name="venue_requests[]" value="HM Function Hall" <?php echo isset($venue_requests) && in_array('HM Function Hall', $venue_requests) ? 'checked' : ''; ?>> HM Function Hall
                            </label>
                            <label>
                                <input type="checkbox" name="venue_requests[]" value="HM Banquet Hall" <?php echo isset($venue_requests) && in_array('HM Banquet Hall', $venue_requests) ? 'checked' : ''; ?>> HM Banquet Hall
                            </label>
                            <label>
                                <input type="checkbox" name="venue_requests[]" value="PLP Auditorium" <?php echo isset($venue_requests) && in_array('PLP Auditorium', $venue_requests) ? 'checked' : ''; ?>> PLP Auditorium
                            </label>
                            <label>
                                <input type="checkbox" name="venue_requests[]" value="PLP Gymnasium" <?php echo isset($venue_requests) && in_array('PLP Gymnasium', $venue_requests) ? 'checked' : ''; ?>> PLP Gymnasium
                            </label>
                            <label>
                                <input type="checkbox" name="venue_requests[]" value="AVR 1" <?php echo isset($venue_requests) && in_array('AVR 1', $venue_requests) ? 'checked' : ''; ?>> AVR 1
                            </label>
                            <label>
                                <input type="checkbox" name="venue_requests[]" value="AVR 2" <?php echo isset($venue_requests) && in_array('AVR 2', $venue_requests) ? 'checked' : ''; ?>> AVR 2
                            </label>
                            <label>
                                <input type="checkbox" name="venue_requests[]" value="AVR 3" <?php echo isset($venue_requests) && in_array('AVR 3', $venue_requests) ? 'checked' : ''; ?>> AVR 3
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <h3>Materials Requested</h3>

                        <div class="checkbox-options" id="additionalRequestsContainer">
                            <label>
                                <input type="checkbox" name="additional_requests[]" value="Projector" <?php echo isset($additional_requests) && in_array('Projector', $additional_requests) ? 'checked' : ''; ?> onclick="toggleQuantityInput(this)">
                                Projector
                                <input type="number" name="quantityProjector" class="quantity-input" placeholder="Quantity" style="display: <?php echo isset($quantity['Projector']) ? 'block' : 'none'; ?>;" value="<?php echo isset($quantity['Projector']) ? htmlspecialchars($quantity['Projector']) : ''; ?>">
                            </label>
                            <label>
                                <input type="checkbox" name="additional_requests[]" value="Widescreen" <?php echo isset($additional_requests) && in_array('Widescreen', $additional_requests) ? 'checked' : ''; ?> onclick="toggleQuantityInput(this)">
                                Widescreen
                                <input type="number" name="quantityWidescreen" class="quantity-input" placeholder="Quantity" style="display: <?php echo isset($quantity['Widescreen']) ? 'block' : 'none'; ?>;" value="<?php echo isset($quantity['Widescreen']) ? htmlspecialchars($quantity['Widescreen']) : ''; ?>">
                            </label>
                            <label>
                                <input type="checkbox" name="additional_requests[]" value="InternetConnectivityWiFi" <?php echo isset($additional_requests) && in_array('InternetConnectivityWiFi', $additional_requests) ? 'checked' : ''; ?> onclick="toggleQuantityInput(this)">
                                Internet Connectivity (WiFi)
                                <input type="number" name="quantityInternetConnectivityWiFi" class="quantity-input" placeholder="Quantity" style="display: <?php echo isset($quantity['InternetConnectivityWiFi']) ? 'block' : 'none'; ?>;" value="<?php echo isset($quantity['InternetConnectivityWiFi']) ? htmlspecialchars($quantity['InternetConnectivityWiFi']) : ''; ?>">
                            </label>
                            <label>
                                <input type="checkbox" name="additional_requests[]" value="SoundSystem" <?php echo isset($additional_requests) && in_array('SoundSystem', $additional_requests) ? 'checked' : ''; ?> onclick="toggleQuantityInput(this)">
                                Sound System
                                <input type="number" name="quantitySoundSystem" class="quantity-input" placeholder="Quantity" style="display: <?php echo isset($quantity['SoundSystem']) ? 'block' : 'none'; ?>;" value="<?php echo isset($quantity['SoundSystem']) ? htmlspecialchars($quantity['SoundSystem']) : ''; ?>">
                            </label>
                            <label>
                                <input type="checkbox" name="additional_requests[]" value="Microphone" <?php echo isset($additional_requests) && in_array('Microphone', $additional_requests) ? 'checked' : ''; ?> onclick="toggleQuantityInput(this)">
                                Microphone
                                <input type="number" name="quantityMicrophone" class="quantity-input" placeholder="Quantity" style="display: <?php echo isset($quantity['Microphone']) ? 'block' : 'none'; ?>;" value="<?php echo isset($quantity['Microphone']) ? htmlspecialchars($quantity['Microphone']) : ''; ?>">
                            </label>
                            <label>
                                <input type="checkbox" name="additional_requests[]" value="TablesRound" <?php echo isset($additional_requests) && in_array('TablesRound', $additional_requests) ? 'checked' : ''; ?> onclick="toggleQuantityInput(this)">
                                Tables (Round)
                                <input type="number" name="quantityTablesRound" class="quantity-input" placeholder="Quantity" style="display: <?php echo isset($quantity['TablesRound']) ? 'block' : 'none'; ?>;" value="<?php echo isset($quantity['TablesRound']) ? htmlspecialchars($quantity['TablesRound']) : ''; ?>">
                            </label>
                            <label>
                                <input type="checkbox" name="additional_requests[]" value="ChairsMonoblock" <?php echo isset($additional_requests) && in_array('ChairsMonoblock', $additional_requests) ? 'checked' : ''; ?> onclick="toggleQuantityInput(this)">
                                Chairs (Monoblock)
                                <input type="number" name="quantityChairsMonoblock" class="quantity-input" placeholder="Quantity" style="display: <?php echo isset($quantity['ChairsMonoblock']) ? 'block' : 'none'; ?>;" value="<?php echo isset($quantity['ChairsMonoblock']) ? htmlspecialchars($quantity['ChairsMonoblock']) : ''; ?>">
                            </label>
                            <label>
                                <input type="checkbox" name="additional_requests[]" value="ChairsTiffany" <?php echo isset($additional_requests) && in_array('ChairsTiffany', $additional_requests) ? 'checked' : ''; ?> onclick="toggleQuantityInput(this)">
                                Chairs (Tiffany)
                                <input type="number" name="quantityChairsTiffany" class="quantity-input" placeholder="Quantity" style="display: <?php echo isset($quantity['ChairsTiffany']) ? 'block' : 'none'; ?>;" value="<?php echo isset($quantity['ChairsTiffany']) ? htmlspecialchars($quantity['ChairsTiffany']) : ''; ?>">
                            </label>
                            <label>
                                <input type="checkbox" name="additional_requests[]" value="Rostrum" <?php echo isset($additional_requests) && in_array('Rostrum', $additional_requests) ? 'checked' : ''; ?> onclick="toggleQuantityInput(this)">
                                Rostrum
                                <input type="number" name="quantityRostrum" class="quantity-input" placeholder="Quantity" style="display: <?php echo isset($quantity['Rostrum']) ? 'block' : 'none'; ?>;" value="<?php echo isset($quantity['Rostrum']) ? htmlspecialchars($quantity['Rostrum']) : ''; ?>">
                            </label> 
                        </div>
                    <div class="button-container">
                        <button type="submit">Update Venue</button>
                    </div>
                </form>
            </div>
        </div>


        <!-- Include SweetAlert -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <script>
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

            function showSuccessAlert(message) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: message,
                    confirmButtonColor: '#089451',
                    confirmButtonText: 'OK',
                    customClass: {
                        popup: 'custom-swal-popup',
                        title: 'custom-swal-title',
                        confirmButton: 'custom-swal-confirm'
                    }
                }).then(() => {
                    window.location.href = "cba-venue.php"; // Redirect to cba-resource.php
                });
            }

            function showErrorAlert(message) {
                Swal.fire({
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

            <?php if (isset($_SESSION['success'])): ?>
                showSuccessAlert('<?php echo addslashes($_SESSION['success']); ?>');
                <?php unset($_SESSION['success']); ?> // Clear the message after displaying
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                showErrorAlert('<?php echo addslashes($_SESSION['error']); ?>');
                <?php unset($_SESSION['error']); ?> // Clear the message after displaying
            <?php endif; ?>

            function toggleQuantityInput(checkbox) {
            const quantityInput = checkbox.nextElementSibling;
            if (checkbox.checked) {
                quantityInput.style.display = 'block';
            } else {
                quantityInput.style.display = 'none';
                quantityInput.value = ''; // Clear the value when unchecked
            }
        }

        // Function to toggle the visibility of additional "Other" requests
        function toggleOtherInput() {
            const otherCheckbox = document.getElementById('otherCheckbox');
            const otherInputsContainer = document.getElementById('otherInputsContainer');
            const addMoreButton = document.getElementById('addMoreButton');

            if (otherCheckbox.checked) {
                otherInputsContainer.style.display = 'block'; // Show the container
                addMoreButton.style.display = 'block'; // Show the Add More button
            } else {
                otherInputsContainer.style.display = 'none'; // Hide the container
                addMoreButton.style.display = 'none'; // Hide the Add More button

                // Clear all other request inputs when unchecked
                const otherRequests = document.querySelectorAll('.other-request');
                otherRequests.forEach((request) => {
                    request.querySelector('input[type="text"]').value = ''; // Clear text input
                    request.querySelector('input[type="number"]').value = ''; // Clear quantity input
                });
            }
        }

        // Call the function on page load to display saved values if they exist
        document.addEventListener("DOMContentLoaded", function() {
            toggleOtherInput();
        });


        function addMoreRequest() {
            const addedRequestsContainer = document.getElementById('addedRequestsContainer');
            const newRequest = document.createElement('div');
            newRequest.classList.add('other-request');
            newRequest.innerHTML = `
                <input type="text" name="other_request[]" placeholder="Please specify" required>
                <input type="number" name="quantityOther[]" class="quantity-input" placeholder="Quantity" min="1" required>
                <button type="button" class="delete" onclick="deleteRequest(this)">Delete Request</button>
            `;
            addedRequestsContainer.appendChild(newRequest);
        }

        function deleteRequest(button) {
            const requestDiv = button.parentElement;
            requestDiv.remove();

            // Hide Add More button if no other requests are left
            const addedRequestsContainer = document.getElementById('addedRequestsContainer');
            if (addedRequestsContainer.querySelectorAll('.other-request').length === 0) {
                document.getElementById('addMoreButton').style.display = 'none';
            }
        }

            // Function to toggle the visibility of other venue inputs
            function toggleOtherVenueInput() {
                const otherCheckbox = document.getElementById('otherVenueCheckbox');
                const otherVenueInputsContainer = document.getElementById('otherVenueInputsContainer');
                const addMoreVenueButton = document.getElementById('addMoreVenueButton');

                if (otherCheckbox.checked) {
                    otherVenueInputsContainer.style.display = 'block'; // Show the container
                    addMoreVenueButton.style.display = 'block'; // Ensure the Add More button is shown
                } else {
                    otherVenueInputsContainer.style.display = 'none'; // Hide the container

                    // Clear all other request inputs when unchecked
                    const otherRequests = document.querySelectorAll('.other-request');
                    otherRequests.forEach((request) => {
                        request.querySelector('input[type="text"]').value = ''; // Clear input
                    });
                }
            }

            // Function to add more venue requests
            function addMoreVenueRequest() {
                const container = document.getElementById('venueRequestsContainer');
                const newRequestDiv = document.createElement('div');
                newRequestDiv.classList.add('other-request'); // Add class for styling

                newRequestDiv.innerHTML = `
                    <input type="text" name="other_venue[]" placeholder="Please specify">
                    <button type="button" class="delete" onclick="deleteVenueRequest(this)">Delete Request</button>
                `;

                // Insert the new request above the Add More button
                container.appendChild(newRequestDiv); // Append new request at the end of the container

                // Move the Add More button after the new request
                const addMoreButton = document.getElementById('addMoreVenueButton');
                container.appendChild(addMoreButton); // Place the button below the last added request
            }

            // Function to delete a venue request
            function deleteVenueRequest(button) {
                const requestDiv = button.parentElement; // Get the parent div of the button
                requestDiv.remove(); // Remove the request div

                // Check if there are no venue requests left
                const venueRequestsContainer = document.getElementById('venueRequestsContainer');

                // If no venue requests are left, hide the Add More button
                if (venueRequestsContainer.querySelectorAll('.other-request').length === 0) {
                    document.getElementById('addMoreVenueButton').style.display = 'none'; // Hide the Add More button
                }
            }
        </script>
    </body>
</html>
