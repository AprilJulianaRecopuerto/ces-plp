<?php
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['uname'])) {
    header("Location: loginpage.php");
    exit;
}

// Database connection parameters
$servername = "localhost";
$username = "root"; // replace with your database username
$password = ""; // replace with your database password
$dbname = "resource_utilization"; // replace with your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Initialize and validate form data
    $date = $_POST['date'] ?? '';
    $name = $_POST['name'] ?? '';
    $position = $_POST['position'] ?? '';
    $college_name = $_POST['college_name'] ?? 'College of Arts and Science';

    // Prepare to insert requisition data
    $stmt = $conn->prepare("INSERT INTO coed_requisition (date, name, position, college_name) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $date, $name, $position, $college_name);

    // Execute the statement for requisition
    if ($stmt->execute()) {
        // Get the last inserted requisition ID
        $requisition_id = $conn->insert_id;

        // Prepare to insert items associated with this requisition
        $itemStmt = $conn->prepare("INSERT INTO coed_items (requisition_id, item_name, total_items, total_usage) VALUES (?, ?, ?, ?)");
        $itemStmt->bind_param("isii", $requisition_id, $item_name, $total_items, $total_usage);

        // Check if item data exists and loop through each item
        if (isset($_POST['item']) && is_array($_POST['item'])) {
            for ($i = 0; $i < count($_POST['item']); $i++) {
                $item_name = $_POST['item'][$i] ?? '';
                $total_meals = $_POST['total_items'][$i] ?? 0;
                $total_usage = $_POST['total_usage'][$i] ?? 0;

                // Check if item data is valid
                if ($item_name) {
                    // Execute the item insertion statement
                    $itemStmt->execute();
                }
            }
        }

        // Close the item statement
        $itemStmt->close();

        $_SESSION['success'] = "Requisition submitted successfully!";
    } else {
        $_SESSION['error'] = "Error submitting requisition.";
    }

    // Close the requisition statement and connection
    $stmt->close();
    $conn->close();
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

            .content-requisition {
                margin-left: 340px; /* Align with the sidebar */
                padding: 20px;
            }

            .content-requisition h2 {
                font-family: 'Poppins', sans-serif;
                font-size: 28px; /* Adjust the font size as needed */
                margin-bottom: 20px; /* Space below the heading */
                color: black; /* Adjust text color */
                margin-top: 110px;
            }

            .content-requisition p {
                font-family: 'Poppins', sans-serif;
                font-size: 16px;
                font-style: Italic;
                margin-top: 110px;
                margin-left: 620px;
                margin-bottom: 20px;
            }

            .form-container {
                font-family: 'Poppins', sans-serif;
                margin-left: -20px;
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
                font-family: 'Poppins', sans-serif;
                display: block;
                font-weight: bold;
                margin-bottom: 5px;
            }

            .form-group select, .form-group input[type="text"], .form-group input[type="date"], .form-group input[type="number"] {
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
                justify-content: flex-end; /* Align buttons to the right */
                margin-bottom: 20px; /* Space below the buttons */
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
                font-family: "Poppins", sans-serif !important;
                width: 400px;
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

            .item-section {
                border: 1px solid #ccc;
                padding: 15px;
                margin: 10px 0;
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
        </style>
    </head>

    <body>
    <nav class="navbar">
            <h2>Requisition (for Materials)</h2> 

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
                    <a href="coed-your-profile.php">Profile</a>
                    <a class="signout" href="roleaccount.php" onclick="confirmLogout(event)">Sign out</a>
                </div>
            </div>
        </nav>
        
        <div class="sidebar">
            <div class="logo">
                <img src="images/logo.png" alt="Logo">
            </div>

            <ul class="menu">
                <li><a href="coed-dash.php"><img src="images/home.png">Dashboard</a></li>
                <li><a href="coed-projlist.php"><img src="images/project-list.png">Project List</a></li>
                <li><a href="coed-calendar.php"><img src="images/calendar.png">Event Calendar</a></li>

                <!-- Dropdown for Resource Utilization -->
                <button class="dropdown-btn">
                    <img src="images/resource.png"> Resource Utilization
                    <i class="fas fa-chevron-down"></i> <!-- Dropdown icon -->
                </button>
                <div class="dropdown-container">
                    <a href="coed-tor.php">Term of Reference</a>
                    <a href="coed-requi.php">Requisition</a>
                    <a href="coed-venue.php">Venue</a>
                </div>

                <li><a href="coed-budget-utilization.php"><img src="images/budget.png">Budget Allocation</a></li>

                <!-- Dropdown for Task Management -->
                <button class="dropdown-btn">
                    <img src="images/task.png">Task Management
                    <i class="fas fa-chevron-down"></i> <!-- Dropdown icon -->
                </button>
                <div class="dropdown-container">
                    <a href="coed-task.php">Upload Files</a>
                    <a href="coed-mov.php">Mode of Verification</a>
                </div>

                <li><a href="responses.php"><img src="images/setting.png">Responses</a></li>

                <!-- Dropdown for Audit Trails -->
                <button class="dropdown-btn">
                    <img src="images/resource.png"> Audit Trails
                    <i class="fas fa-chevron-down"></i> <!-- Dropdown icon -->
                </button>
                <div class="dropdown-container">
                    <a href="coed-login.php">Log In History</a>
                    <a href="coed-activitylogs.php">Activity Logs</a>
                </div>
            </ul>
        </div>

        <div class="content-requisition">
            <div class="form-container">

                <h2>Requisition Submission Form</h2>
                
                <form id="requisitionForm" action="" method="POST">

                    <div class="form-group">
                        <label for="date">Date:</label>
                        <input type="date" id="date" name="date" required>
                    </div>

                    <div class="form-group">
                        <label for="name">Name:</label>
                        <input type="text" id="name" name="name" placeholder="Enter your Name" required>
                    </div>

                    <div class="form-group">
                        <label for="position">Designation/Position:</label>
                        <input type="text" id="position" name="position" placeholder="Enter Designation or Position" required>
                    </div>

                    <div class="form-group">
                        <label for="college_name">Office/College:</label>
                        <input type="text" id="college_name" name="college_name" value="College of Education" placeholder="Enter College Name" required>
                    </div>

                    <h2>Item/s Requested</h2>
                    <div id="itemContainer">
                        <div class="item-section">
                            <h3>Item 1</h3>

                            <div class="form-group">
                                <label for="item_1">Item Name:</label>
                                <input type="text" id="item_1" name="item[]" placeholder="Enter Name of Item" required>
                            </div>

                            <div class="form-group">
                                <label for="total_items_1">Total Item Requested:</label>
                                <input type="text" id="total_items_1" name="total_items[]" placeholder="Enter Total of Meals (e.g 500)" required>
                            </div>

                            <div class="form-group">
                                <label for="total_usage_1">Total Usage:</label>
                                <input type="text" id="total_usage_1" name="total_usage[]" placeholder="Enter Total Usage (e.g 450)" required>
                            </div>

                            <button type="button" class="remove-btn" onclick="removeEvent(this)">Remove Item</button>
                        </div>
                    </div>

                    <button type="button" class="add-btn" onclick="addItem()">Add Another Item</button><br><br>

                    <div class="button-container">
                        <button type="submit">Submit</button>
                        <button type="reset">Reset</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
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

            let itemCount = 1;

            function addItem() {
                itemCount++;
                const itemContainer = document.getElementById('itemContainer');
                const newItemSection = document.createElement('div');
                newItemSection.className = 'item-section';
                newItemSection.innerHTML = `
                    <h3>Item ${itemCount}</h3>

                    <div class="form-group">
                        <label for="item_${itemCount}">Item Name:</label>
                        <input type="text" id="item_${itemCount}" name="item[]" placeholder="Enter Name of Item" required>
                    </div>

                    <div class="form-group">
                        <label for="total_items_${itemCount}">Total Item Requested:</label>
                        <input type="text" id="total_items_${itemCount}" name="total_items[]" placeholder="Enter Total of Meals (e.g 500 meals)" >
                    </div>

                    <div class="form-group">
                        <label for="total_usage_${itemCount}">Total Usage:</label>
                        <input type="text" id="total_usage_${itemCount}" name="total_usage[]" placeholder="Enter Total Usage (e.g 450 meals)" >
                    </div>

                    <button type="button" class="remove-btn" onclick="removeItem(this)">Remove Item</button>
                `;
                itemContainer.appendChild(newItemSection);
            }

            function removeItem(element) {
                element.parentElement.remove();
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
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = "coed-requi.php"; // Redirect to the same page or desired page
                    }
                });
            }

            // Check for success message in session and show alert
            <?php if (isset($_SESSION['success'])): ?>
                showSuccessAlert('<?php echo addslashes($_SESSION['success']); ?>');
                <?php unset($_SESSION['success']); ?> // Clear the message after displaying
            <?php endif; ?>

            var dropdown = document.getElementsByClassName("dropdown-btn");
                var i;

                for (i = 0; i < dropdown.length; i++) {
                dropdown[i].addEventListener("click", function() {
                    var dropdownContent = this.nextElementSibling;
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