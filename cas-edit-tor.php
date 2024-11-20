<?php
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['uname'])) {
    header("Location: loginpage.php");
    exit;
}

// Database credentials
$servername_resource = "mwgmw3rs78pvwk4e.cbetxkdyhwsb.us-east-1.rds.amazonaws.com";
$username_resource = "dnr20srzjycb99tw";
$password_resource = "ndfnpz4j74v8t0p7";
$dbname_resource = "x8uwt594q5jy7a7o";

// Create connection
$conn = new mysqli($servername_resource, $username_resource, $password_resource, $dbname_resource);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize event form data variable
$eventFormData = [];
$eventDetails = []; // Initialize details data

// Fetch event data if editing an existing entry
if (isset($_GET['id'])) {
    $eventId = intval($_GET['id']);
    
    // Fetch event form data
    $sql = "SELECT * FROM cas_tor WHERE id = $eventId";
    $result = $conn->query($sql);

    if ($result === false) {
        die("Error fetching event form: " . $conn->error); // Debugging output
    } elseif ($result->num_rows > 0) {
        $eventFormData = $result->fetch_assoc();
    } else {
        echo "No event form found for ID: $eventId<br>"; // Debugging output
    }

    // Fetch associated event details
    $detailsSql = "SELECT * FROM cas_food WHERE cas_tor_id = $eventId";
    $detailsResult = $conn->query($detailsSql);

    if ($detailsResult === false) {
        die("Error fetching event details: " . $conn->error); // Debugging output
    } else {
        while ($detailRow = $detailsResult->fetch_assoc()) {
            $eventDetails[] = $detailRow;
        }
    }
}

// Ensure $eventFormData['details'] exists and is assigned
$eventFormData['details'] = $eventDetails;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get posted data for cas_event_form
    $college_name = $conn->real_escape_string($_POST['college_name']);
    $procurement_title = $conn->real_escape_string($_POST['procurement_title']);
    $agency = $conn->real_escape_string($_POST['agency']);
    $date_of_delivery = $_POST['date_of_delivery'];

    // Update the main event form
    if (isset($_GET['id'])) {
        // Update the event form
        $updateEventSql = "UPDATE cas_tor SET 
            college_name='$college_name',
            procurement_title='$procurement_title',
            agency='$agency',
            date_of_delivery='$date_of_delivery'
            WHERE id = $eventId";

        if ($conn->query($updateEventSql) === TRUE) {
            // Clear existing event details
            $deleteDetailsSql = "DELETE FROM cas_food WHERE cas_tor_id = $eventId";
            $conn->query($deleteDetailsSql);
            
            // Insert updated details
            $event_dates = $_POST['event_date'];
            $event_titles = $_POST['event_title'];
            $meal_types = $_POST['meal_type'];
            $menus = $_POST['menu'];
            $total_meals = $_POST['total_meals'];
            $total_usage = $_POST['total_usage'];

            foreach ($event_dates as $index => $date) {
                $title = $conn->real_escape_string($event_titles[$index]);
                $mealType = $conn->real_escape_string($meal_types[$index]);
                $menuItem = $conn->real_escape_string($menus[$index]);
                $meals = intval($total_meals[$index]);
                $usage = intval($total_usage[$index]);

                $insertDetailsSql = "INSERT INTO cas_food (cas_tor_id, event_date, event_title, meal_type, menu, total_meals, total_usage) VALUES ($eventId, '$date', '$title', '$mealType', '$menuItem', $meals, $usage)";
                if (!$conn->query($insertDetailsSql)) {
                    $_SESSION['error'] = 'Error inserting event detail: ' . $conn->error;
                    header("Location: cas-edit-tor.php?id=$eventId");
                    exit;
                }
            }

            // Set success message in session
            $_SESSION['success'] = 'Event details updated successfully.';
            header("Location: cas-edit-tor.php?id=$eventId"); // Redirect back to the edit page
            exit;
        } else {
            // Set error message in session
            $_SESSION['error'] = 'Error updating event details: ' . $conn->error;
            header("Location: cas-edit-tor.php?id=$eventId");
            exit;
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
                border-radius: 5px;
                font-size: 16px;
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
                font-size: 16px;
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
            <h2>Edit Term of Reference</h2> 

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
                    <a href="cas-your-profile.php">Profile</a>
                    <a class="signout" href="roleaccount.php" onclick="confirmLogout(event)">Sign out</a>
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

                <li><a href="responses.php"><img src="images/setting.png">Responses</a></li>

                <!-- Dropdown for Audit Trails -->
                <button class="dropdown-btn">
                    <img src="images/resource.png"> Audit Trails
                    <i class="fas fa-chevron-down"></i> <!-- Dropdown icon -->
                </button>
                <div class="dropdown-container">
                    <a href="cas-login.php">Log In History</a>
                    <a href="cas-activitylogs.php">Activity Logs</a>
                </div>
            </ul>
        </div>
        
        <div class="content-editor">
            <div class="form-container">
                <h2>Edit Event Submission Form</h2>
                
                <form id="eventForm" action="" method="POST"> <!-- Change action as needed -->
                    <div class="form-group">
                        <label for="college_name">College Name:</label>
                        <input type="text" id="college_name" name="college_name" value="<?php echo htmlspecialchars($eventFormData['college_name'] ?? ''); ?>" placeholder="Enter College Name" >
                    </div>

                    <div class="form-group">
                        <label for="procurement_title">Procurement Title:</label>
                        <input type="text" id="procurement_title" name="procurement_title" value="<?php echo htmlspecialchars($eventFormData['procurement_title'] ?? ''); ?>" placeholder="Enter Procurement Title" >
                    </div>

                    <div class="form-group">
                        <label for="agency">Proponent and Implementing Agency:</label>
                        <input type="text" id="agency" name="agency" value="<?php echo htmlspecialchars($eventFormData['agency'] ?? ''); ?>" placeholder="Enter Proponent and Implementing Agency" >
                    </div>

                    <div class="form-group">
                        <label for="date_of_delivery">Date of Delivery:</label>
                        <input type="date" id="date_of_delivery" name="date_of_delivery" value="<?php echo htmlspecialchars($eventFormData['date_of_delivery'] ?? ''); ?>" >
                    </div>

                    <h2>Event Details</h2>
                    <div id="eventContainer">
                        <?php
                        // Display existing events if they exist
                        $eventCount = 0;
                        if (!empty($eventFormData['details'])) {
                            foreach ($eventFormData['details'] as $detail) {
                                $eventCount++;
                                ?>
                                <div class="event-section">
                                    <h3>Event <?php echo $eventCount; ?></h3>
                                    <div class="form-group">
                                        <label for="event_date_<?php echo $eventCount; ?>">Event Date:</label>
                                        <input type="date" id="event_date_<?php echo $eventCount; ?>" name="event_date[]" 
                                            value="<?php echo htmlspecialchars($detail['event_date'] ?? ''); ?>" 
                                            onblur="checkEventDate(<?php echo $eventCount; ?>)" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="event_title_<?php echo $eventCount; ?>">Event Title:</label>
                                        <input type="text" id="event_title_<?php echo $eventCount; ?>" name="event_title[]" value="<?php echo htmlspecialchars($detail['event_title'] ?? ''); ?>" placeholder="Enter Event Title" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="meal_type_<?php echo $eventCount; ?>">Food Category:</label>
                                        <select id="meal_type_<?php echo $eventCount; ?>" name="meal_type[]" required>
                                            <option value="" disabled>Select Meal Type</option>
                                            <option value="Packed Meals" <?php if ($detail['meal_type'] == 'Packed Meals') echo 'selected'; ?>>Packed Meals</option>
                                            <option value="Catering Services" <?php if ($detail['meal_type'] == 'Catering Services') echo 'selected'; ?>>Catering Services</option>
                                            <option value="Boxed Meals" <?php if ($detail['meal_type'] == 'Boxed Meals') echo 'selected'; ?>>Boxed Meals</option>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="menu_<?php echo $eventCount; ?>">Menu:</label>
                                        <textarea id="menu_<?php echo $eventCount; ?>" name="menu[]" required><?php echo htmlspecialchars($detail['menu'] ?? ''); ?></textarea>
                                    </div>

                                    <div class="form-group">
                                        <label for="total_meals_<?php echo $eventCount; ?>">Total of Meals:</label>
                                        <input type="text" id="total_meals_<?php echo $eventCount; ?>" name="total_meals[]" value="<?php echo htmlspecialchars($detail['total_meals'] ?? ''); ?>" placeholder="Enter Total of Meals (e.g 500 meals)" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="total_usage_<?php echo $eventCount; ?>">Total Usage:</label>
                                        <input type="text" id="total_usage_<?php echo $eventCount; ?>" name="total_usage[]" value="<?php echo htmlspecialchars($detail['total_usage'] ?? ''); ?>" placeholder="Enter Total Usage (e.g 450 meals)" required>
                                    </div>

                                    <button type="button" class="remove-btn" onclick="removeEvent(this)">Remove Event</button>
                                </div>
                            <?php
                            }
                        } else {
                            echo "<p>No event details to display.</p>"; // Message if no details found
                        }
                        ?>
                    </div>

                    <button type="button" class="add-btn" onclick="addEvent()">Add Another Event</button><br><br>

                    <div class="button-container">
                        <button type="submit">Update Event</button>
                    </div>
                </form>
            </div>
        </div>

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

           // Set initial event count based on existing event sections
            let eventCount = <?php echo $eventCount; ?>;

            function addEvent() {
                eventCount++;
                const eventContainer = document.getElementById('eventContainer');
                const newEventSection = document.createElement('div');
                newEventSection.className = 'event-section';
                newEventSection.innerHTML = `
                    <h3>Event ${eventCount}</h3>
                    <div class="form-group">
                        <label for="event_date_${eventCount}">Event Date:</label>
                        <input type="date" id="event_date_${eventCount}" name="event_date[]" required>
                    </div>

                    <div class="form-group">
                        <label for="event_title_${eventCount}">Event Title:</label>
                        <input type="text" id="event_title_${eventCount}" name="event_title[]" placeholder="Enter Event Title" required>
                    </div>

                    <div class="form-group">
                        <label for="meal_type_${eventCount}">Meal Type:</label>
                        <select id="meal_type_${eventCount}" name="meal_type[]" required>
                            <option value="" disabled selected>Select Meal Type</option>
                            <option value="Packed Meals">Packed Meals</option>
                            <option value="Catering Services">Catering Services</option>
                            <option value="Boxed Meals">Boxed Meals</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="menu_${eventCount}">Menu:</label>
                        <textarea id="menu_${eventCount}" name="menu[]" required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="total_meals_${eventCount}">Total of Meals:</label>
                        <input type="text" id="total_meals_${eventCount}" name="total_meals[]" placeholder="Enter Total of Meals (e.g 500 meals)" required>
                    </div>

                    <div class="form-group">
                        <label for="total_usage_${eventCount}">Total Usage:</label>
                        <input type="text" id="total_usage_${eventCount}" name="total_usage[]" placeholder="Enter Total Usage (e.g 450 meals)" required>
                    </div>

                    <button type="button" class="remove-btn" onclick="removeEvent(this)">Remove Event</button>
                `;
                eventContainer.appendChild(newEventSection);
            }

            function removeEvent(element) {
                element.parentElement.remove();
                // Update event count when an event is removed
                eventCount = document.querySelectorAll('.event-section').length;
            }
           
             // Function to show success SweetAlert
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
                    window.location.href = "cas-tor.php"; // Redirect to cas-tor.php
                });
            }

            // Function to show error SweetAlert
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

            // Check for success message in session and show alert
            <?php if (isset($_SESSION['success'])): ?>
                showSuccessAlert('<?php echo addslashes($_SESSION['success']); ?>');
                <?php unset($_SESSION['success']); // Clear the message after displaying ?>
            <?php endif; ?>

            // Check for error message in session and show alert
            <?php if (isset($_SESSION['error'])): ?>
                showErrorAlert('<?php echo addslashes($_SESSION['error']); ?>');
                <?php unset($_SESSION['error']); // Clear the message after displaying ?>
            <?php endif; ?>

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
