<?php
session_start();
date_default_timezone_set('Asia/Manila'); // Change to your timezone

// Initialize variables
$adminId = isset($_SESSION['adminId']) ? $_SESSION['adminId'] : '';
$error = '';
$loginAttempts = isset($_SESSION['login_attempts']) ? $_SESSION['login_attempts'] : 0;
$lockoutTime = 10; // Lockout time in seconds

// Calculate remaining lockout time
$currentTime = time();
$remainingLockout = isset($_SESSION['lockout_time']) ? $_SESSION['lockout_time'] - $currentTime : 0;

// If lockout time has expired, reset login attempts and remove lockout
if ($remainingLockout <= 0 && isset($_SESSION['lockout_time'])) {
    $_SESSION['login_attempts'] = 0;
    unset($_SESSION['lockout_time']); // Remove lockout session
    $remainingLockout = 0;
}

// Calculate remaining attempts
$remainingAttempts = 5 - $loginAttempts;

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_SESSION['lockout_time']) && $currentTime < $_SESSION['lockout_time']) {
        $error = 'Too many failed attempts. Try again after 1 minute.';
    } else {
        // Reset login attempts after the lockout period expires
        if (isset($_SESSION['lockout_time']) && $currentTime >= $_SESSION['lockout_time']) {
            $_SESSION['login_attempts'] = 0;
            unset($_SESSION['lockout_time']);
        }

        // Database connection details
        $servername = "l3855uft9zao23e2.cbetxkdyhwsb.us-east-1.rds.amazonaws.com";
        $username = "equ6v8i5llo3uhjm"; // replace with your database username
        $password = "vkfaxm2are5bjc3q"; // replace with your database password
        $dbname = "ylwrjgaks3fw5sdj";

        // Create connection
        $conn = new mysqli($servername, $username, $password, $dbname);

        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $email = $_POST['email'];
        $password = $_POST['password'];

        // Prepare and execute the query
        $sql = "SELECT id, username, password FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        // Check if a user with this email exists
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($userId, $username, $storedPassword);
            $stmt->fetch();

            // Verify password
            if ($password === $storedPassword) {
                if ($adminId === $userId) {
                    // Successful login, reset attempts
                    $_SESSION['username'] = $username;
                    $_SESSION['adminId'] = $userId;
                    $_SESSION['login_success'] = true;
                    $_SESSION['login_attempts'] = 0; // Reset attempts

                    if (isset($_SESSION['username'])) {
                        $_SESSION['username'] = $username; // Store username in session upon successful login
                        $loginTime = date('Y-m-d H:i:s'); // Get the current timestamp
                    
                        // Prepare the SQL statement to insert the login timestamp into the 'adhistory' table
                        $insertTimestampSql = "INSERT INTO adhistory (username, ts, logout_ts) VALUES (?, ?, NULL)";
                        $insertStmt = $conn->prepare($insertTimestampSql);
                    
                        if ($insertStmt === false) {
                            die("MySQL prepare failed: " . $conn->error);
                        }
                    
                        // Bind parameters: first is the username, second is the login timestamp
                        $insertStmt->bind_param("ss", $username, $loginTime);
                    
                        // Execute the insert
                        if ($insertStmt->execute()) {
                            // Optionally, you can set a success message here
                            // echo "Login timestamp inserted successfully.";
                        } else {
                            // Handle the error here if needed
                            // echo "Error inserting login timestamp: " . $insertStmt->error;
                        }
                    
                        // Close the insert statement
                        $insertStmt->close();
                    }

                    header("Location: loginpage.php");
                    exit;
                } else {
                    $error = 'ID does not match with the email and password!';
                }
            } else {
                $error = 'Invalid email or password!';
            }
        } else {
            $error = 'Invalid email or password!';
        }

        // Increment login attempts after a failed login
        $_SESSION['login_attempts'] = isset($_SESSION['login_attempts']) ? $_SESSION['login_attempts'] + 1 : 1;

        // If login attempts reach 5, set a lockout time
        if ($_SESSION['login_attempts'] >= 5) {
            $_SESSION['lockout_time'] = time() + $lockoutTime;
            $error = 'Too many failed attempts. Try again later.';
        }

        $stmt->close();
        $conn->close();
    }

    // Store error message in session to display in the HTML form
    $_SESSION['error'] = $error;
}
?>


<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title> CES PLP</title>

        <!-- The FavIcon of our Website -->
        <link rel="icon" href="images/logoicon.png">
        
        <link rel="stylesheet" href="https://unpkg.com/swiper@8/swiper-bundle.min.css" />

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,400;0,600;1,500&display=swap');
        @import url('https://fonts.cdnfonts.com/css/glacial-indifference-2');
        @import url('https://fonts.googleapis.com/css2?family=Saira+Condensed:wght@500&display=swap');

             html {
                font-size: 62.5%;
                scroll-behavior: smooth;
            }

            body {
                font-size: 1.6rem;
                overflow: hidden;
                margin:0;
            }

            /*LoadingPage*/
            /* Header styles */
            .header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 10px 20px;
                background-color: #fff;
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

            .home-button {
                font-family: 'Glacial Indifference', sans-serif;
                background-color: #089451;
                font-size: 18.9px;
                color: white;
                border: none;
                border-radius: 12px;
                width: 130.7px;
                height: 45.7px;
                margin-top: 15px;
                margin-right: 50px;
                cursor: pointer;
            }

            .home-button:hover {
                background-color: #218838;
            }

            /* Banner styles*/
            .banner3 {  
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
            .banner3 img {
                position: absolute;
                height: 150%;
                width: 100%;
                margin-top: -280px;
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

            /*LogInPage*/
            .login-container {
                background-color: #ffc107;
                padding: 23px;
                border-radius: 8px;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                width: 340px;
                height: auto;
                margin-top: -50px;
            }

            .login-form {
                display: flex;
                flex-direction: column;
            }

            .login-form h2 {
                font-family: 'Glacial Indifference', sans-serif;
                margin-bottom: -5px;
                margin-top: -10px;
                font-size: 45px;
                color: black;
                text-align: left;
                margin-left: 11px;
            }

            .sign-in-link {
                font-family: 'Glacial Indifference', sans-serif;
                font-size: 17px;
                background-color: #089451;
                color: #fff;
                border: 0.5px solid black;
                padding: 10px 20px;
                border-radius: 10px;
                width: 95%;
                cursor: pointer;
                margin-top: 20px !important;
                margin-bottom: 22px;
                margin-left: 10px;
            }

            .sign-in-link:hover {
                background-color: #218838;
            }

            /* Default style for the link */
            .default-style {
                font-family: 'Glacial Indifference', sans-serif;
                color: black !important;
                font-weight: normal;
                text-decoration: none;
            }

            /* Highlight style for username and password */
            .highlight {
                font-family: 'Glacial Indifference', sans-serif;
                font-weight: 300;
                color: #007bff;
                text-decoration: none;
            }

            .links {
                text-align: right !important;
                margin-right: 11px;
                margin-top: -20px;
            }

            .links a {
                text-decoration: none; /* Remove default underline */
                color: #007bff; /* Set link color */
            }

            .links a:hover {
                text-decoration: underline; /* Underline on hover */
                color: #0056b3; /* Adjust link color on hover */
            }

            .signin-text {
                font-family: 'Glacial Indifference', sans-serif;
                color: #3b3b3b;
                font-weight: 400;
                font-size: 16px;
                text-align: left;
                margin-bottom: 23px;
                margin-left: 11px;
            }

            .showID {
                color:black;
                font-family:'Poppins', sans-serif;
                font-size: 18px;
                margin-bottom: 15px;
            }

            .error {
                color: red;
                font-family:'Poppins', sans-serif;
                font-size: 15px;
                text-align: center;
            }
            /*LogInPage*/

            /*Slider*/
            .container {
                max-width: 89rem;
                padding: 0 1rem;
                margin: auto;
            }

            .text-center {
                text-align: center;
            }

            .section-heading {
                font-size: 3rem;
                padding: 2rem 0;
            }

            .input-wrap {
                position: relative;
                margin-bottom: 31px;
                height: 23px;
                margin-top: 10px;
                margin-bottom: 41px;
            }

            .input-field {
                font-family: 'Poppins', sans-serif;
                background-color: white;
                margin: 8px 0;
                padding: 10px 15px;
                font-size: 15px;
                border-radius: 8px;
                width: 86%;
                height: 23px;
                border: 0.5px solid black;
                margin-top: -10px;
            }

            label {
                font-family: 'Glacial Indifference', sans-serif;
                font-size: 15px;
                position: absolute;
                left: 5%;
                top: 50%;
                margin-top:-2px;
                text-align: center;
                transform: translateY(-50%);
                color: #252525;
                pointer-events: none;
                transition: 0.4s;
            }

            .input-field.active {
                border-bottom-color: #151111;
            }

            .input-field.active + label {
                font-size: 12px;
                top: -18px;
            }

            /* Eye icon styling */
            .password-toggle {
                position: absolute;
                right: 10px;
                top: 50%;
                transform: translateY(-50%);
                cursor: pointer;
            }

            .eye-icon {
                position: absolute;
                top: 55%;
                right: 25px;
                transform: translateY(-50%);
                cursor: pointer;
                z-index: 1;
                color: #999;
            }

            .close-btn {
                color: #aaa;
                position: absolute;
                top: -20px;
                right: -140px;
                font-size: 28px;
                font-weight: bold;
                cursor: pointer;
                border: none; /* Remove border */
                background-color: transparent;
                margin-right: 150px;
                margin-top: 32px;   
            }

            .close-btn:hover,
            .close-btn:focus {
                color: black;
                text-decoration: none;
                cursor: pointer;
                background-color: transparent;
            }

            .feedback-content {
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                background-color: #fff;
                width: 300px;
                height: 350px;
                padding: 20px;
                text-align: center;
                border-radius: 10px;
                box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            }

            .feedback-content h1 {
                font-family: 'Glacial Indifference', sans-serif;
                font-weight: bold;
                font-size: 45px;
                margin-top: 5px !important;
            }

            .feedback-content p {
                font-family: 'Poppins', sans-serif;
                font-size: 16px;
                margin-top: -25px !important;
            }

            #feedback-container2 {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.5);
                z-index: 9999;
            }

            .feedback-content img {
                width: 120px;
                margin-top: 25px;
            }

            #btn-feed2 {
                font-family: 'Glacial Indifference', sans-serif;
                font-size: 17px;
                background-color: #089451;
                color: #fff;
                border: 0.5px solid black;
                padding: 10px 20px;
                border-radius: 10px;
                width: 95%;
                cursor: pointer;
                margin-top: -15px !important;
            }

            #btn-feed2:hover {
                background-color: #218838;
            }

            .custom-swal-popup {
                font-family: 'Poppins', sans-serif;
                font-size: 1.6rem; /* Increase the font size */
                width: 400px !important; /* Set a larger width */
            }

            .custom-swal-title {
                font-family: 'Poppins', sans-serif;
                color: #3085d6; /* Custom title color */
            }

            .custom-confirm {
                font-family: 'Poppins', sans-serif;
                font-size: 17px;
                background-color: #089451;
                border: 0.5px #089451 !important;
                color: #fff;
                border-radius: 10px;
                cursor: pointer;
                outline: none !important; /* Remove default focus outline */
            }

            /* Custom styles for SweetAlert error popup */
            .custom-error-popup {
                font-family: 'Poppins', sans-serif;
                font-size: 1.6rem; /* Increase the font size */
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

            .input-wrap {
                position: relative;
            }

            .remaining-attempts {
                margin-top: 10px;
                padding: 15px; /* Space inside the box */
                background-color: #f8d7da; /* Light red background */
                color: #721c24; /* Darker red text */
                border-radius: 5px; /* Rounded corners */
                border: 1px solid #f5c6cb; /* Optional: subtle border */
                display: inline-block; /* Adjusts width to content */
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); /* Optional: adds a shadow */
            }

            .error-message {
                font-family: 'Poppins', sans-serif;
                margin-top: 10px;
                padding: 15px; /* Space inside the box */
                background-color: #f8d7da; /* Light red background */
                color: #721c24; /* Darker red text */
                border-radius: 5px; /* Rounded corners */
                border: 1px solid #f5c6cb; /* Optional: subtle border */
                display: inline-block; /* Adjusts width to content */
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); /* Optional: adds a shadow */
            }

            #capsLockWarning {
                font-family: 'Poppins', sans-serif;
                font-size: 12px;
                color: #053F5E; /* Warning message color */
                font-style: italic; /* Italic style */
                font-weight: bold; /* Make text bold */
                display: none; /* Hidden by default */
                margin-top: -1px; /* Space above the warning */
                text-align: left;
                margin-left: 13px;
            }
        </style>
    </head>

 <body>
        <header class="header">
            <div class="logo">
               <a href="loadingpage.php"> <img src="images/logo.png" alt="Logo"></a>
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
        
            <button class="home-button">Home</button>
        </header>
    
        <div class="banner3">
            <img src="images/Admin (1).png">
    
                <div class="login-container">
				<form class="login-form" method="POST" onsubmit="return validateEmail()">
					<h2>Sign In</h2>
					<p class="signin-text">Enter your details to Sign In</p>

					<div class="actual-form">
						<div class="input-wrap">
							<input
								type="email"
								id="email"
								name="email"
								class="input-field"
								autocomplete="off"
								required
							/>
							<label>Email</label>
						</div>

						<div class="input-wrap">
							<input
								type="password"
								id="password"
								name="password"
								minlength="4"
								class="input-field password-input"
								autocomplete="off"
								required
							/>
							<label>Password</label>
							<i class="eye-icon fas fa-eye-slash password-toggle"></i>

                            <div id="capsLockWarning">Caps Lock is ON</div> <!-- Caps Lock Warning -->

						</div>
					</div>

					<div class="links">
						<span class="default-style">Forgot <a href="forgot-password.php" class="highlight">password</a>?</span>
					</div>

					<button type="submit" class="sign-in-link" id="loginButton">Sign In</button>

                    <?php if ($error): ?>
                        <div class="remaining-attempts" id="remainingAttemptsDiv">
                            <?php if ($remainingLockout > 0): ?>
                                No remaining attempts. You are temporarily locked out.
                            <?php elseif ($remainingAttempts > 0): ?>
                                You have <?php echo $remainingAttempts; ?> remaining attempt(s).
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <div id="domainError" class="error-message" style="color: red; display: none;">
                        Please use an email address ending with @plpasig.edu.ph.
                    </div>
				</form>
			</div>
        </div>
    
            
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            <!-- Swiper Script -->
            <script src="https://unpkg.com/swiper@8/swiper-bundle.min.js"></script>     
    
            <!-- Initialize Swiper -->
            <script>
                // Wait for the DOM to be fully loaded
            document.addEventListener('DOMContentLoaded', function() {
                // Get a reference to the login button by class name
                var loginButton = document.querySelector('.home-button'); // Corrected selector to target the login button inside .header
    
                // Add click event listener to the login button
                loginButton.addEventListener('click', function() {
                    // Redirect to roleaccount.html when the button is clicked
                    window.location.href = 'loadingpage.php';
                });
            });

			document.addEventListener("DOMContentLoaded", function() {
            const inputs = document.querySelectorAll(".input-field");
            inputs.forEach((inp) => {
                inp.addEventListener("focus", () => {
                    inp.classList.add("active");
                });
                inp.addEventListener("blur", () => {
                    if (inp.value != "") return;
                    inp.classList.remove("active");
                });
            });

            // Add event listener to toggle password visibility
            const passwordInputs = document.querySelectorAll(".password-input");
            const eyeIcons = document.querySelectorAll(".eye-icon");

            passwordInputs.forEach((input, index) => {
                const eyeIcon = eyeIcons[index];
                input.addEventListener("input", function () {
                    eyeIcon.style.display = "block"; // Always show the eye icon on input change
                });

                eyeIcon.addEventListener("click", function () {
                    const type = input.getAttribute("type") === "password" ? "text" : "password";
                    input.setAttribute("type", type);
                    eyeIcon.classList.toggle("fa-eye");
                    eyeIcon.classList.toggle("fa-eye-slash");
                });
            });

            // Check for login success and show SweetAlert
                <?php if (
                    isset($_SESSION["login_success"]) &&
                    $_SESSION["login_success"] === true
                ): ?>
                    showSuccessAlert();
                    <?php unset($_SESSION["login_success"]);
                    // Unset the session variable
                    ?>
                <?php endif; ?>

                // Check for error message and show SweetAlert
                <?php if (isset($_SESSION["error"])): ?>
                    showErrorAlert('<?php echo $_SESSION["error"]; ?>');
                    <?php unset($_SESSION["error"]);
                    // Unset the session variable
                    ?>
                <?php endif; ?>
            });

            // Function to show success SweetAlert
            function showSuccessAlert() {
                // Clear the loggedOut flag from sessionStorage when user successfully signs in
                sessionStorage.removeItem('loggedOut');
                
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: "You have successfully signed in.",
                    confirmButtonText: 'Continue',
                    customClass: {
                        popup: 'custom-swal-popup',
                        title: 'custom-swal-title',
                        confirmButton: 'custom-confirm'
                    }
                }).then(() => {
                    // Redirect to the dashboard or desired page after the user confirms
                    window.location.href = "admin-dash.php"; // Change this to the appropriate dashboard URL
                });
            }


            // Function to show error SweetAlert
            function showErrorAlert(message) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: message,
                    confirmButtonText: 'Try Again',
                    customClass: {
                        popup: 'custom-error-popup',
                        title: 'custom-error-title',
                        confirmButton: 'custom-error-confirm'
                    }
                });
            }
			

             // Check for Caps Lock
             const passwordInput = document.getElementById("password");
            const capsLockWarning = document.getElementById("capsLockWarning");

            passwordInput.addEventListener("keydown", function(event) {
                if (event.getModifierState("CapsLock")) {
                    capsLockWarning.style.display = "block"; // Show warning
                }
            });

            passwordInput.addEventListener("keyup", function(event) {
                if (!event.getModifierState("CapsLock")) {
                    capsLockWarning.style.display = "none"; // Hide warning
                }
            });

           // Get remaining lockout time from PHP
           let remainingLockoutTime = <?php echo $remainingLockout; ?>;

            document.addEventListener("DOMContentLoaded", function() {
                const loginButton = document.getElementById("loginButton");
                const lockoutDuration = remainingLockoutTime * 1000; // Convert to milliseconds
                const remainingAttemptsDiv = document.getElementById("remainingAttemptsDiv");

                // If the user is locked out, disable the button and show the countdown
                if (remainingLockoutTime > 0) {
                    disableLoginButton(lockoutDuration);
                }

                function disableLoginButton(duration) {
                    loginButton.disabled = true;
                    loginButton.innerText = `Try again in ${Math.ceil(duration / 1000)} seconds`;

                    // Update the button text with a countdown
                    const interval = setInterval(function() {
                        duration -= 1000;
                        if (duration > 0) {
                            loginButton.innerText = `Try again in ${Math.ceil(duration / 1000)} seconds`;
                        } else {
                            clearInterval(interval);
                            enableLoginButton(); // Enable the button after the countdown
                        }
                    }, 1000);
                }

                function enableLoginButton() {
                    loginButton.disabled = false;
                    loginButton.innerText = "Sign In";

                    // Hide the remaining attempts message after lockout ends
                    if (remainingAttemptsDiv) {
                        remainingAttemptsDiv.style.display = 'none';
                    }
                }
            });

            function validateEmail() {
                const emailInput = document.getElementById('email');
                const domainError = document.getElementById('domainError');
                const remainingAttemptsDiv = document.getElementById('remainingAttemptsDiv'); // Grab the remaining attempts div
                const emailValue = emailInput.value;

                // Check if the email ends with @plpasig.edu.ph
                if (!emailValue.endsWith('@plpasig.edu.ph')) {
                    domainError.style.display = 'block'; // Show domain error message
                    if (remainingAttemptsDiv) {
                        remainingAttemptsDiv.style.display = 'none'; // Hide remaining attempts message
                    }
                    return false; // Prevent form submission
                } else {
                    domainError.style.display = 'none'; // Hide domain error message
                    if (remainingAttemptsDiv) {
                        remainingAttemptsDiv.style.display = 'block'; // Show remaining attempts (if applicable)
                    }
                    return true; // Allow form submission
                }
            }
        </script>
    </body>
</html>