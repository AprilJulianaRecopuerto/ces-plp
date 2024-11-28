<?php
session_start();
$isFormClosed = isset($_SESSION['show_event_form']) && $_SESSION['show_event_form'] === false;
    
// Database connection
$servername = "iwqrvsv8e5fz4uni.cbetxkdyhwsb.us-east-1.rds.amazonaws.com";
$username = "sh9sgtg12c8vyqoa";
$password = "s3jzz232brki4nnv";
$dbname = "szk9kdwhvpxy2g77";
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize message variables
$successMessage = "";
$errorMessage = "";

// Check if the local form data is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['signup'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $event = $_POST['event'];
    $department = $_POST['department'];  // Capture the department value
    $rate = $_POST['rating'];  //

    
    // Insert submission data into the database
    $stmt = $conn->prepare("INSERT INTO submissions (name, email, event, department, rate) VALUES (?, ?, ?, ?, ?)");
    
    // Bind parameters
    $stmt->bind_param("sssss", $name, $email, $event, $department, $rate);  // "sssss" means all are strings
    

    if ($stmt->execute()) {
        // Success response
        $successMessage = "Thank you for signing up for the event!";
    } else {
        // Error response
        $errorMessage = "There was an error signing up. Please try again.";
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>CES PLP</title>

    <!-- The FavIcon of our Website -->
    <link rel="icon" href="images/logoicon.png">
    
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,400;0,600;1,500&display=swap');
    @import url('https://fonts.cdnfonts.com/css/glacial-indifference-2');
    @import url('https://fonts.googleapis.com/css2?family=Saira+Condensed:wght@500&display=swap');
    
        body, html {
            margin: 0;
            padding: 0;
            overflow:hidden;
        }

        .logo {
            display: flex;
            align-items: center;
        }

        /*LoadingPage*/
        /* Header styles */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 20px;
            background-color: #fff;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); /* Optional: Adds a shadow for better visibility */
        }

        .logo img {
            max-height: 85px;
            width: auto;
        }

        .navbar {
            display: flex;
            align-items: center;
            margin-top: 13px;
        }

        .nav-item {
            font-family: 'Glacial Indifference', sans-serif;
            font-size: 18.9px;
            padding: 14px 16px;
            color: black;
            text-decoration: none;
            margin: 0 10px;
        }

        .nav-item:hover {
            background-color: #089451;
            border-radius: 10px;
            color: white;
        }

        .dropdown {
            position: relative;
            display: inline-block;
        }

        .dropdown .dropbtn {
            font-family: 'Glacial Indifference', sans-serif;
            font-size: 18.9px;  
            border: none;
            border-radius: 12px;
            outline: none;
            color: black;
            padding: 14px 16px;
            background-color: inherit;
            margin: 0 8px;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            border-radius: 10px;
            background-color: #f9f9f9;
            min-width: 180px;
            box-shadow: 0px 8px 16px 0px rgba(0, 0, 0, 0.1);
            z-index: 15;
            margin-left: 13px;
        }

        .dropdown-content a {
            font-family: 'Poppins', sans-serif;
            color: black;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
            text-align: left;
        }

        .dropdown-content a:hover {
            background-color: #ddd;
            border-radius: 10px;
        }

        .dropdown:hover .dropbtn {
            background-color: #089451;
            color: white;
        }

        .dropdown:hover .dropdown-content {
            display: block;
        }

        .header-button {
            font-family: 'Glacial Indifference', sans-serif;
            background-color: #089451;
            font-size: 17.9px;
            color: white;
            border: none;
            border-radius: 12px;
            width: 145.7px;
            height: 45.7px;
            margin-top: 14px;
            margin-right: 50px;
            cursor: pointer;
        }

        .header-button:hover {
            background-color: #218838;
        }

        /* Banner styles*/
        .banner1 {
            position: relative;
            background-image: url('css/plpmain.png');
            background-size: cover;
            background-position: center;
            height: 92.8vh;
            color: white;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            z-index: 0;
            border-radius: 60px;
        }

        /*Banner Shadow*/
        .banner1 img {
            position: absolute;
            top: -110px;
            left: 0;
            height: 110%;
            width: 100%;
            object-fit: cover;
            z-index: -1;
        }

        /*text h1 design */
        .banner-heading {
            font-size: 100px; /* Adjust the font size */
            color: #ffffff; 
            text-align: left;
            margin-top: 20px;
            margin-left: 80px;
            margin-right: 350px;
            font-family: "Poppins", sans-serif;
            font-weight: 600;
            padding: 0; /* Remove padding */
            line-height: 1.2;
        }
        /*LoadingPage*/
		
	    /* Centering the container on the page */
		.container {
            font-family: 'Poppins', sans-serif;
			background-color: #ffc107;
            padding: 23px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 490px;
            height: auto;
            margin-top: -50px;
		}

        .signupForm {
            display: flex;
            flex-direction: column;
        }

		/* Header styling */
		h2 {
            font-family: 'Poppins', sans-serif;
            margin-bottom: 15px;
            margin-top: -10px;
            font-size: 27px;
            color: black;
            text-align: center;
            margin-left: 11px;
        }

        label {
            font-weight: bold;
			color: black;
			margin-top: 10px;
			font-size: 17px;
			display: block;
			text-align: left;
			margin-left: 1%;
			width: 80%;
        }

		/* Success and error message styling */
		.message, .error {
			font-weight: bold;
			font-size: 18px; /* Adjusted font size */
			margin-bottom: 10px; /* Spacing below messages */
		}

		.message {
			color: #4caf50;
		}

		.error {
			color: #f44336;
		}

        .rating {
            margin-top: 15  px;
        }

		/* Adjusted for input fields and button to fit in the form without overflow */
		input[type="text"],
		input[type="email"] {
            font-family: 'Poppins', sans-serif;
			height: 17px;
			width: 270%;
			padding: 12px; /* Adjusted padding for input */
			margin: 5px auto; /* Adjusted spacing */
            margin-top: 12px;
			border-radius: 10px;
			outline: none;
			font-size: 16px; /* Adjusted font size */
            border: 1px solid #ccc;
            border-radius: 4px;
		}

		/* Focus state for inputs */
		input[type="text"]:focus,
		input[type="email"]:focus {
			border-color: black; /* Darker green focus border */
		}

        .dept {
            font-family: 'Poppins', sans-serif;
            height: 47px; /* Adjusted height */
            width: 240%; /* Ensures the select element takes full width */
            padding: 12px; /* Adjusted padding */
            margin: 5px auto;
            margin-top: 15px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        /* Style for the placeholder text (first option) */
        .dept option:disabled {
            color: #aaa; /* Light gray for placeholder text */
        }

		/* Button styling */
		.button-submit {
			font-family: "Poppins", sans-serif;
            font-size: 16px;
            background-color: #089451;
            color: #fff;
            border: 0.5px solid black;
            padding: 10px;
            border-radius: 10px;
            width: 98%;
            cursor: pointer;
            margin-top: 25px;
            margin-bottom: 15px; 
		}

		/* Button hover and active states */
		.button-submit:hover {
			background-color: #45a049;
		}

		.custom-swal-popup {
                font-family: 'Poppins', sans-serif;
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

            .custom-swal-input {
                font-family: 'Poppins', sans-serif;
                font-size: 17px;
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

    </style>
</head>

<body>
    <header class="header">
        <div class="logo">
            <img src="images/logo.png" alt="Logo"></a>
        </div>
        <nav class="navbar">
            <div class="dropdown">
                <button class="dropbtn">About us &nbsp; 
                    <i class="fa fa-caret-down"></i>
                </button>
                <div class="dropdown-content">
                    <a href="mis-vis.php">Mission and Vision</a>
                    <a href="goal-obj.php">Goal and Objectives</a>
                    <a href="org-struc.php">Organizational Structure</a>
                    <a href="ext-policy.php">University Extension Service Policy</a>
                </div>
            </div>
            <a href="operation-ces.php" class="nav-item">Major Operations of CES</a>
            <div class="dropdown">
                <button class="dropbtn">Programs &nbsp; 
                    <i class="fa fa-caret-down"></i>
                </button>
                <div class="dropdown-content">
                    <a href="uni-wide.php">University Wide</a>
                    <a href="collegedept.php">College/ Department</a>
                </div>
            </div>
            <a href="linkages-parts.php" class="nav-item">Linkages and Partners</a>
            <a href="programs-act.php" class="nav-item">Programs and Activities</a>
        </nav>
        <button class="header-button">Evaluation Form</button>
    </header>

    <div class="banner1">
        <img src="images/Admin (1).png" alt="Banner Image">

        <div class="container">
            <h2>Evaluation Form</h2>

        <form id="signupForm" action="" method="POST">

            <div style="display: flex; align-items: center; margin-bottom: 5px;">
                <label for="name">Full Name:</label>
                <input type="text" id="name" name="name" required>
            </div>
            
            <div style="display: flex; align-items: center; margin-bottom: 5px;">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div style="display: flex; align-items: center; margin-bottom: 5px;">
                <label for="department">Department:</label>
                <input type="text" id="department" name="department" value="College of Arts and Sciences" readonly class="dept">
            </div>

            
            <?php
// Database connection
$servername = "ryvdxs57afyjk41z.cbetxkdyhwsb.us-east-1.rds.amazonaws.com";
$username_db = "zf8r3n4qqjyrfx7o";
$password_db = "su6qmqa0gxuerg98";
$dbname_proj_list = "hpvs3ggjc4qfg9jp";

$conn_proj = new mysqli($servername, $username_db, $password_db, $dbname_proj_list);

// Check connection
if ($conn_proj->connect_error) {
    die("Connection failed: " . $conn_proj->connect_error);
}

// Fetch project titles from the cas table
$sql = "SELECT proj_title FROM cas";
$result = $conn_proj->query($sql);
?>

<div style="display: flex; align-items: center; margin-bottom: 10px;">
    <label for="event">Event:</label>
    <select id="event" name="event" required>
        <option value="" disabled selected>Select Event</option>
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo '<option value="' . htmlspecialchars($row['proj_title']) . '">' . htmlspecialchars($row['proj_title']) . '</option>';
            }
        } else {
            echo '<option value="" disabled>No Events Available</option>';
        }
        ?>
    </select>
</div>

<?php
$conn_proj->close();
?>
            
            <label for="rating" class= "rating">How satisfied are you with the event?</label><br>

            <a href=https://forms.gle/CshKcCeExNbusNeD9>Clich here to give ratings.</a>
            
            
            <button class="button-submit" type="submit" name="signup">Submit</button>
        </form>
    </div>

    <script>
		// Check if there is a success or error message set
         <?php if ($successMessage): ?>
            Swal.fire({
                title: 'Success!',
                text: '<?php echo $successMessage; ?>',
                icon: 'success',
                confirmButtonText: 'Okay',
                customClass: {
                    popup: 'custom-swal-popup',
                    title: 'custom-swal-title',
                    confirmButton: 'custom-swal-confirm'
                },
                backdrop: true, // Optional: Enable backdrop
                allowOutsideClick: false, // Optional: Disable closing on click outside
            });
        <?php endif; ?>
        document.addEventListener('DOMContentLoaded', function() {
        // PHP variable passed to JavaScript
        var isFormClosed = <?php echo json_encode($isFormClosed); ?>;

        // Check if the form is closed and display SweetAlert
        if (isFormClosed) {
            Swal.fire({
                icon: 'info',
                title: 'Form Closed',
                text: 'The form is currently closed. Please try again later.',
                confirmButtonText: 'OK',
                customClass: {
                    popup: 'custom-swal-popup',
                    title: 'custom-swal-title',
                    confirmButton: 'custom-swal-confirm'
                },
            }).then((result) => {
                if (result.isConfirmed) {
                    // Redirect to loading page
                    window.location.href = 'loadingpage-participants.php';
                }
            });
            }
        });

        // LoadingPage Script
        // Wait for the DOM to be fully loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Get a reference to the login button by class name
            var loginButton = document.querySelector('.header-button'); // Corrected selector to target the login button inside .header

            // Add click event listener to the login button
            loginButton.addEventListener('click', function() {
                // Redirect to roleaccount.html when the button is clicked
                window.location.href = 'loadingpage-participants.php';
            });
        });
    </script>
</body>
</html>
