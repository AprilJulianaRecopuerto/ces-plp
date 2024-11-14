<?php
// Database connection
$servername = "localhost"; // Your database server
$username = "root"; // Your database username
$password = ""; // Your database password
$dbname = "certificate"; // Your database name

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
    // Capture form data
    $name = $_POST['name'];
    $email = $_POST['email'];
    $event = $_POST['event'];

    // Insert submission data into the database
    $stmt = $conn->prepare("INSERT INTO submissions (name, email, event) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $event); // Bind parameters

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
            font-size: 18.9px;
            color: white;
            border: none;
            border-radius: 12px;
            width: 130.7px;
            height: 45.7px;
            margin-top: 20px;
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
			position: absolute;
			top: 47%;
			left: 50%;
			transform: translate(-50%, -50%);
			width: 400px; /* Increased width for the container */
			height: 400px; /* Increased height for the container */
			background: #fff;
			padding: 10px; /* Adjusted padding */
			border-radius: 15px;
			box-shadow: 0 5px 10px rgba(0, 0, 0, 0.15);
			border: 2px solid #98fb98;
			text-align: center;
			box-sizing: border-box;
			display: flex;
			flex-direction: column;
			align-items: center;
		}

		/* Header styling */
		h2 {
			color: #4caf50;
			font-size: 24px; /* Increased font size */
			margin-bottom: 15px; /* Increased margin for better spacing */
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

		/* Form styling */
		#signupForm {
			background-color: #fffacd;
			padding: 10px; /* Adjusted padding */
			border-radius: 10px;
			width: 100%; /* Full width of container */
			height: 100%; /* Take full height of the container */
			box-sizing: border-box;
			display: flex;
			flex-direction: column;
			align-items: center;
		}

		/* Label styling */
		label {
			font-weight: bold;
			color: #4caf50; /* Green color for labels */
			margin-top: 10px;
			font-size: 18px;
			display: block;
			text-align: left;
			margin-left: -10%;
			width: 80%;
		}

		/* Adjusted for input fields and button to fit in the form without overflow */
		input[type="text"],
		input[type="email"] {
			height: 10px;
			width: 85%;
			padding: 12px; /* Adjusted padding for input */
			margin: 5px auto; /* Adjusted spacing */
			border: 2px solid #4caf50; /* Green border for inputs */
			border-radius: 10px;
			outline: none;
			font-size: 16px; /* Adjusted font size */
			background-color: #f9ffe3; /* Light yellow input background */
		}

		/* Focus state for inputs */
		input[type="text"]:focus,
		input[type="email"]:focus {
			border-color: #388e3c; /* Darker green focus border */
		}

		/* Button styling */
		.button-submit {
			padding: 12px 24px; /* Adjusted padding for button */
			margin-top: 10px; /* Increased margin */
			background-color: #4caf50; /* Green button */
			border: none;
			border-radius: 10px;
			color: white;
			cursor: pointer;
			font-size: 18px; /* Adjusted font size */
			font-weight: bold;
			transition: background-color 0.3s ease;
			width: 90%;
		}

		/* Button hover and active states */
		.button-submit:hover {
			background-color: #388e3c; /* Darker green on hover */
		}

		.button-submit:active {
			background-color: #2e7d32; /* Even darker green on click */
		}
		
		       .swal2-title {
            font-size: 1.5em;
            color: #4CAF50; /* Custom title color */
        }

        .swal2-content {
            font-size: 1.2em;
            color: #333; /* Custom content color */
        }

        .swal2-confirm {
            background-color: #4CAF50; /* Custom confirm button color */
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
        }

        .swal2-confirm:hover {
            background-color: #45a049; /* Darker green on hover */
        }
		
         .sweetalert-ok-button {
            display: inline-block; /* Make it an inline-block to apply margin */
            margin: 20px auto; /* Center horizontally with auto margin */
            padding: 10px 20px; /* Add padding for better appearance */
            background-color: #007bff; /* Button background color */
            color: white; /* Button text color */
            border: none; /* Remove border */
            border-radius: 5px; /* Rounded corners */
            cursor: pointer; /* Pointer cursor on hover */
            text-align: center; /* Center text within button */
        }

        /* Ensure the buttons are centered in the modal */
        .swal-button-container {
            display: flex; /* Use flexbox for alignment */
            justify-content: center; /* Center align the buttons */
        }

    </style>
</head>

<body>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="path/to/your/styles.css"> <!-- Link to your CSS file -->
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script> <!-- SweetAlert library -->
    <title>Event Form</title>
</head>
<body>
    <header class="header">
        <div class="logo">
            <a href="loadingpage.php"><img src="images/logo.png" alt="Logo"></a>
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
        <button class="header-button">Home</button>
    </header>

    <div class="banner1">
        <img src="images/Admin (1).png" alt="Banner Image">
        <h1 class="banner-heading">Empowering Communities <br> Through <br> Collaboration</h1>
        <div class="container">
            <h2>Event Form</h2>
            <form id="signupForm" action="" method="POST">
                <label for="name">Full Name:</label>
                <input type="text" id="name" name="name" required>
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
                <label for="event">Event:</label>
                <input type="text" id="event" name="event" required>
                <button class="button-submit" type="submit" name="signup">Sign Up</button>
            </form>
        </div>
    </div>

    <script>
        let isFormOpen = localStorage.getItem('isFormOpen') === 'true';

        // Check if the form is closed
        if (!isFormOpen) {
            // Show SweetAlert notification
            swal({
                title: "Form Closed",
                text: "The event form is currently closed. Please check back later.",
                icon: "info",
                buttons: {
                    ok: {
                        text: "OK",
                        value: true,
                        visible: true,
                        className: "sweetalert-ok-button", // Class for centering
                        closeModal: true
                    }
                },
                className: "custom-swal" // Adding custom class for additional styling
            }).then(() => {
                // Redirect to loading page after alert closes
                window.location.href = 'loadingpage-participants.php';
            });
        }

        // Add event listener for the home button
        document.addEventListener('DOMContentLoaded', function() {
            var loginButton = document.querySelector('.header-button');

            loginButton.addEventListener('click', function() {
                window.location.href = 'loadingpage.php';
            });
        });
		
		        // Check if there is a success or error message set
         <?php if ($successMessage): ?>
            Swal.fire({
                title: 'Success!',
                text: '<?php echo $successMessage; ?>',
                icon: 'success',
                confirmButtonText: 'Okay',
                customClass: {
                    title: 'swal2-title',
                    content: 'swal2-content',
                    confirmButton: 'swal2-confirm',
                },
                backdrop: true, // Optional: Enable backdrop
                allowOutsideClick: false, // Optional: Disable closing on click outside
            });
        <?php endif; ?>

    </script>
</body>
</html>
