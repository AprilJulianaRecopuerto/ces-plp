<?php
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['uname'])) {
    // Redirect to login page if the session variable is not set
    header("Location: collegelogin.php");
    exit;
}

// Database credentials
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "proj_list";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $date_of_sub = $_POST['date_of_sub'];
    $semester = $_POST['semester'];
    $lead_person = $_POST['lead_person'];
    $dept = $_POST['dept'];
    $implementor = $_POST['implementor'];
    $proj_title = $_POST['proj_title'];
    $classification = $_POST['classification'];
    $specific_activity = $_POST['specific_activity'];
    $dateof_imple = $_POST['dateof_imple'];
    $time_from = $_POST['time_from'];
    $time_to = $_POST['time_to'];
    $district = $_POST['district'];
    $barangay = $_POST['barangay'];
    $beneficiary = $_POST['beneficiary'];
    $duration = $_POST['duration'];
    $status = $_POST['status'];

    // Prepare and bind the SQL statement
    $stmt = $conn->prepare("INSERT INTO coed (date_of_sub, semester, lead_person, dept, implementor, proj_title, classification, specific_activity, dateof_imple, time_from, time_to, district, barangay, beneficiary, duration, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    if ($stmt === false) {
        $_SESSION['error'] = "Prepare statement failed: " . $conn->error;
        $redirect_url = 'coed-addproj.php';
    } else {
        // Bind parameters
        $stmt->bind_param("ssssssssssssssss", $date_of_sub, $semester, $lead_person, $dept, $implementor, $proj_title, $classification, $specific_activity, $dateof_imple, $time_from, $time_to, $district, $barangay, $beneficiary, $duration, $status);

        // Execute the statement
        if ($stmt->execute()) {
            $_SESSION['add_success'] = true; // Set session variable for success
            $redirect_url = 'coed-projlist.php'; // Redirect URL
        } else {
            $_SESSION['error'] = "Error: " . $stmt->error; // Set session variable for error
            $redirect_url = 'coed-addproj.php'; // Redirect URL
        }

        // Close the statement
        $stmt->close();
    }

    // Close the connection
    $conn->close();
}
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

        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
        <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>


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

            .custom-swal-popup {
                font-family: "Poppins", sans-serif !important;
                width: 400px;
            }

            .custom-swal-confirm {
                font-family: "Poppins", sans-serif !important;
            }

            .custom-swal-cancel {
                font-family: "Poppins", sans-serif !important;
            }

        </style>
    </head>

    <body>
        <nav class="navbar">
            <h2>Add Project</h2> 

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
                <li><a href="coed-projlist.php" class="active"><img src="images/project-list.png">Project List</a></li>
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
        
        <div class="content-projectlist">
            <div class="form-container">
                <h3>Project Details</h3>

                <form action="" method="post">

                    <div class="form-group">
                        <label for="date_of_sub">Date of Submission:</label>
                        <input type="date" id="date_of_sub" name="date_of_sub" placeholder="Enter Date of Submission" required>
                    </div>

                    <div class="form-group">
                        <label for="semester">Semester:</label>
                        <select id="semester" name="semester" required>
                            <option value="" disabled selected>Select Semester</option>
                            <option value="1st">1st Semester</option>
                            <option value="2nd">2nd Semester</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="lead_person">Lead Person:</label>
                        <input type="text" id="lead_person" name="lead_person" placeholder="Enter Lead Person" required>
                    </div>

                    <div class="form-group">
                        <label for="dept">Department:</label>
                        <input type="text" id="dept" name="dept" value="College of Education" readonly>
                    </div>

                    <div class="form-group">
                        <label for="implementor">Implementor:</label>
                        <select id="implementor" name="implementor" required>
                            <option value="" disabled selected>Select Implementor</option>
                            <option value="Students">Students</option>
                            <option value="Faculty">Faculty</option>
                            <option value="Faculty and Students">Faculty and Students</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="proj_title">Project Title:</label>
                        <input type="text" id="proj_title" name="proj_title" placeholder="Enter Project Title" required>
                    </div>

                    <div class="form-group">
                        <label for="classification">Classification:</label>
                        <input type="text" id="classification" name="classification" placeholder="Enter Classification" required>
                    </div>

                    <div class="form-group">
                        <label for="specific_activity">Specific Activity:</label>
                        <input type="text" id="specific_activity" name="specific_activity" placeholder="Enter Activity" required>
                    </div>

                    <div class="form-group">
                        <label for="dateof_imple_from">Date of Implementation:</label>
                        <input type="date" id="dateof_imple" name="dateof_imple" required onchange="checkImplementationDate()">
                    </div>

                    <div class="form-group">
                        <label for="time_from">Time From:</label>
                        <input type="text" id="time_from" name="time_from" placeholder="hh:mm AM/PM" required>
                    </div>

                    <div class="form-group">
                        <label for="time_to">Time To:</label>
                        <input type="text" id="time_to" name="time_to" placeholder="hh:mm AM/PM" required>
                    </div>

                    <div class="form-group">
                        <label for="district">District:</label>
                        <select id="district" name="district" onchange="updateBarangays()" required>
                            <option value="" disabled selected>Select District</option>
                            <option value="District 1">District 1</option>
                            <option value="District 2">District 2</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="barangay">Barangay:</label>
                        <select id="barangay" name="barangay" required>
                            <option value="" disabled selected>Select Barangay</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="beneficiary">Beneficiary:</label>
                        <input type="text" id="beneficiary" name="beneficiary" placeholder="Enter Beneficiary" required>
                    </div>

                    <div class="form-group">
                        <label for="duration">Duration of Project:</label>
                        <select id="duration" name="duration" required>
                            <option value="" disabled selected>Select Duration</option>
                            <option value="One Day">One day</option>
                            <option value="Sustained">Sustained</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="status">Status:</label>
                        <input type="text" id="status" name="status" value="Proposed" readonly>
                    </div>  

                    <div class="button-container">
                        <button type="submit">Submit</button>
                        <button type="reset">Reset</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            function updateBarangays() {
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

            // Add new options
            barangays.forEach(barangay => {
                const option = document.createElement('option');
                option.value = barangay;
                option.textContent = barangay;
                barangaySelect.appendChild(option);
            });

            // Add default option
            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.disabled = true;
            defaultOption.selected = true;
            defaultOption.textContent = 'Select Barangay';
            barangaySelect.insertBefore(defaultOption, barangaySelect.firstChild);
        }

        // Initialize barangay options based on the default district (if necessary)
        document.addEventListener('DOMContentLoaded', () => {
            updateBarangays();
        });

                document.addEventListener('DOMContentLoaded', () => {
                <?php if (isset($_SESSION['add_success']) && $_SESSION['add_success'] === true) : ?>
                    showSuccessAlert();
                    <?php unset($_SESSION['add_success']); // Unset the session variable ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])) : ?>
                    showErrorAlert('<?php echo $_SESSION['error']; ?>');
                    <?php unset($_SESSION['error']); // Unset the session variable ?>
                <?php endif; ?>
            });

            // Function to show success SweetAlert
            function showSuccessAlert() {
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: 'New record created successfully',
                    confirmButtonColor: '#089451',
                    confirmButtonText: 'Continue',
                    customClass: {
                        popup: 'custom-swal-popup',
                        title: 'custom-swal-title',
                        confirmButton: 'custom-swal-confirm'
                    }
                }).then(() => {
                    window.location.href = "coed-projlist.php"; // Redirect to the dashboard or desired page
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

            function checkImplementationDate() {
            const dateOfSub = new Date(document.getElementById('date_of_sub').value);
            const dateOfImple = new Date(document.getElementById('dateof_imple').value);

            if (dateOfImple < dateOfSub) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Date',
                    text: 'Your project should not be earlier than the date of submission.',
                    confirmButtonColor: '#e74c3c',
                    confirmButtonText: 'Okay',
                    customClass: {
                        popup: 'custom-error-popup',
                        title: 'custom-error-title',
                        confirmButton: 'custom-error-confirm'
                    }
                });
                
                // Optionally, you can reset the dateof_imple field
                document.getElementById('dateof_imple').value = '';
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
