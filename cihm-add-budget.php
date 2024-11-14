<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['uname'])) {
    header("Location: collegelogin.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form inputs
    $semester = $_POST['semester'];
    $department = $_POST['department'];
    $project_title = $_POST['project_title'];
    $district = $_POST['district'];
    $barangay = $_POST['barangay'];

    // Directory for uploads
    $target_dir = "uploadsfile/";

    // Allowed file types for both budget and TOR
    $allowed_types = array("docx", "pdf", "xls", "xlsx");
    $image_types = array("jpeg", "jpg", "png", "jfif", "gif");

    // Handling budget file upload
    if (isset($_FILES["budget"]) && $_FILES["budget"]["error"] == 0) {
        $budget_full_path = $target_dir . basename($_FILES["budget"]["name"]);
        $budget_file_type = strtolower(pathinfo($budget_full_path, PATHINFO_EXTENSION));


        // Move the uploaded budget file
        if (!move_uploaded_file($_FILES["budget"]["tmp_name"], $budget_full_path)) {
            $_SESSION['error'] = "There was an error uploading the budget file.";
            header("Location: cihm-add-budget.php");
            exit;
        }

        // Get only the file name, not the full path
        $budget = basename($_FILES["budget"]["name"]);
    } else {
        $_SESSION['error'] = "No budget file uploaded.";
        header("Location: cihm-add-budget.php");
        exit;
    }

    // Handling TOR file upload
    if (isset($_FILES["tor"]) && $_FILES["tor"]["error"] == 0) {
        $tor_full_path = $target_dir . basename($_FILES["tor"]["name"]);
        $tor_file_type = strtolower(pathinfo($tor_full_path, PATHINFO_EXTENSION));

        // Move the uploaded TOR file
        if (!move_uploaded_file($_FILES["tor"]["tmp_name"], $tor_full_path)) {
            $_SESSION['error'] = "There was an error uploading the TOR file.";
            header("Location: cihm-add-budget.php");
            exit;
        }

        // Get only the file name, not the full path
        $tor = basename($_FILES["tor"]["name"]);
    } else {
        $_SESSION['error'] = "No TOR file uploaded.";
        header("Location: cihm-add-budget.php");
        exit;
    }

    // Database connection
    $db_host = "localhost";
    $db_user = "root";
    $db_pass = "";
    $db_name = "budget-utili";

    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Insert the form and file data into the database, now only storing the file name
    $sql = "INSERT INTO cihm_budget (semester, department, project_title, district, barangay, budget, tor) 
            VALUES ('$semester', '$department', '$project_title', '$district', '$barangay', '$budget', '$tor')";

    if ($conn->query($sql) === TRUE) {
        $_SESSION['success'] = "New record successfully added!";
    } else {
        $_SESSION['error'] = "Error saving your project data: " . $conn->error;
    }

    $conn->close();

    header("Location: cihm-add-budget.php");
    exit;
}

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
                width: calc(96.2% - 250px); /* Adjusted width considering the sidebar */
                height: 80px;
                margin-left: 290px; /* Align with the sidebar */
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
                width: 250px;
                background-color: #FFF8A5; /* Light yellow */
                color: black;
                padding: 20px;
                z-index: 1000;
                box-shadow: 2px 0 5px rgba(0, 0, 0, 0.2); /* Added box shadow */
            }

            .logo {
                display: flex;
                align-items: center;
                margin-bottom: 30px; /* Increased margin bottom */
            }

            .logo img {
                height: 100px; /* Increased logo size */
                margin-right: 15px; /* Adjusted margin */
            }

            .logo span {
                font-size: 30px; /* Increased font size */
                margin-left:-15px;
                font-family: 'Glacial Indifference', sans-serif;
                font-weight: bold;
            }

            .menu {
                list-style: none;
                padding: 0;
                margin: 0;
            }

            .menu li {
                margin: 6px 0; /* Increased margin for spacing between items */
                display: flex;
                align-items: center;
            }

            .menu a {
                color: black;
                text-decoration: none;
                display: flex;
                align-items: center;
                padding: 15px; /* Increased padding for better click area */
                border-radius: 5px; /* Increased border-radius for rounded corners */
                width: 100%;
                font-size: 17px; /* Increased font size */
                font-family: 'Poppins', sans-serif;
            }

            .menu a:hover {
                background-color: #22901C;
                transition: 0.3s;
                color: white; /* Ensure the text color is white when hovered */
            }

            .menu img {
                height: 30px; /* Increased icon size */
                margin-right: 15px; /* Adjusted space between icon and text */
            }

            .menu .signout {
                margin-top: 35px; /* Pushes Sign Out to the bottom of the sidebar */
            }

            .content-projectlist {
                margin-left: 310px; /* Align with the sidebar */
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
                font-size: 16px; /* Increase the font size */
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
        </style>
    </head>

    <body>
        <nav class="navbar">
            <h2>Add New Budget Plan</h2> 

            <div class="profile" id="profileDropdown">
                <?php
                    // Check if a profile picture is set in the session
                    if (!empty($_SESSION['picture'])) {
                        echo '<img src="' . $_SESSION['picture'] . '" alt="Profile Picture">';
                    } else {
                        // Get the first letter of the username for the placeholder
                        $firstLetter = strtoupper(substr($_SESSION['uname'], 0, 1));
                        echo '<div class="profile-placeholder">' . $firstLetter . '</div>';
                    }
                ?>
                <span><?php echo htmlspecialchars($_SESSION['uname']); ?></span>

                <i class="fa fa-chevron-down dropdown-icon"></i>
                <div class="dropdown-menu">
                    <a href="cihm-your-profile.php">Profile</a>
                </div>
            </div>
        </nav>

        <div class="sidebar">
            <div class="logo">
                <img src="images/logo.png" alt="Logo">
            </div>

            <ul class="menu">
                <li><a href="cihm-dash.php"><img src="images/home.png">Dashboard</a></li>
                <li><a href="cihm-projlist.php"><img src="images/project-list.png">Project List</a></li>
                <li><a href="cihm-calendar.php"><img src="images/calendar.png">Event Calendar</a></li>
                <li><a href="cihm-budget-utilization.php"><img src="images/resource.png">Budget Utilization</a></li>
                <li><a href="#task-management"><img src="images/task.png">Task Management</a></li>
                <li><a href="#task-management"><img src="images/report.png">Progress Report</a></li>
                <li><a href="settings.php"><img src="images/setting.png">Settings</a></li>
                <li class="signout">
                    <a href="#" onclick="confirmLogout(event)">
                        <img src="images/log-out.png">Sign Out
                    </a>
                </li>
            </ul>
        </div>

        <div class="content-projectlist">
            <div class="form-container">
                <h3>Project Details</h3>

                <form action="" method="post" enctype="multipart/form-data">

                    <div class="form-group">
                        <label for="semester">Semester:</label>
                        <select id="semester" name="semester">
                            <option value="" disabled selected>Select Semester</option>
                            <option value="1st">1st Semester</option>
                            <option value="2nd">2nd Semester</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="department">Department:</label>
                        <input type="text" id="department" name="department" value="College of International Hospitality Management" readonly>
                    </div>

                    <div class="form-group">
                        <label for="project_title">Project Title:</label>
                        <input type="text" id="project_title" name="project_title" placeholder="Enter Project Title" required>
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
                        <label for="budget">Budget Plan:</label>
                        <input type="file" id="budget" name="budget" class="file-input" required>
                        <small class="form-text text-muted">File must be a DOCX, PDF, XLS, or XLSX.</small>
                    </div>

                    <div class="form-group">
                        <label for="tor">Term of Reference:</label>
                        <input type="file" id="tor" name="tor" class="file-input" required>
                        <small class="form-text text-muted">File must be a DOCX, PDF, XLS, or XLSX.</small>
                    </div>

                    <div class="button-container">
                        <button type="submit">Submit</button>
                        <button type="reset" id="resetButton">Reset</button>
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

            // Check if there is a success or error message
            <?php if (isset($_SESSION['success'])): ?>
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: '<?php echo $_SESSION['success']; ?>',
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'cihm-budget-utilization.php';
                    }
                });
            <?php unset($_SESSION['success']); endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: '<?php echo $_SESSION['error']; ?>',
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'cihm-add-budget.php';
                    }
                });
            <?php unset($_SESSION['error']); endif; ?>

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

        document.getElementById('resetButton').addEventListener('click', function(event) {
            event.preventDefault(); // Prevent default reset behavior
            
            // Reset the barangay dropdown back to the first option (Select Barangay)
            document.getElementById('barangay').selectedIndex = 0;


            // Reload the page after resetting
            setTimeout(function() {
                location.reload(); // Reload the page to refresh it
            }, 100); // Slight delay to allow the reset action to complete
        });         
        </script>
    </body>
</html>
