<?php
    session_start();
    if (!isset($_SESSION['uname'])) {
        // Redirect to login page if the session variable is not set
        header("Location: collegelogin.php");
        exit;
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

            .see-all-link {
                font-size: 14px;
                color: #8A8A8A;
                cursor: pointer;
                text-decoration: none;
                margin-top: -65px;
                margin-left: 1070px !important;
            }

            .see-all-link:hover {
                color: black;
                text-decoration: underline;
            }


            /* Additional styles for the table */
            table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 20px;
            }

            th, td {
                border: 1px solid #ddd;
                padding: 8px;
                text-align: left;
            }

            th {
                text-align: center; 
                background-color: #4CAF50;
                color:white;
                height: 40px;
                width: 14px; /* Set a fixed width for table headers */
            }

            td {
                height: 50px;
            }

            tr:nth-child(even) {
                background-color: #FFF8A5;
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
                border-radius: 10px;           /* Rounded corners with a radius of 10 pixels */
                box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); /* Add shadow effect */
                text-align: center;            /* Center align the text inside */
            }
        </style>
    </head>

    <body>
        <nav class="navbar">
            <h2>Dashboard</h2> 

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
                <li><a href="con-dash.php" class="active"><img src="images/home.png">Dashboard</a></li>
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
        
        <div class="content">
            <div class="activities-container">
                <div class="total-activities">
                    <a href="con-projlist.php">
                        <h2>Total Activities</h2>
                        <div class="activities-details">
                            <div class="total-activities-count">
                                <?php
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

                                // SQL query to count total activities
                                $sql = "SELECT COUNT(*) as total FROM con";
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
                <div class="pending-activities">
                    <h2>Pending Activities</h2>
                    <div class="activities-details">
                        <div class="pending-activities-count">7</div>
                        <img src="images/pending.png" alt="Down Icon">
                    </div>
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
                $host = 'localhost';  // or your host
                $db = 'proj_list';     // database name
                $user = 'root'; // database username
                $pass = ''; // database password

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
