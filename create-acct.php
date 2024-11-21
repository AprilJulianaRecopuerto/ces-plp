<?php
session_start();

// Database connection details
$servername = "l3855uft9zao23e2.cbetxkdyhwsb.us-east-1.rds.amazonaws.com";
$username = "equ6v8i5llo3uhjm"; // replace with your database username
$password = "vkfaxm2are5bjc3q"; // replace with your database password
$dbname = "ylwrjgaks3fw5sdj";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize response
$response = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $id = $_POST['id'];
    $username = $_POST['username'];
    $department = $_POST['department'];
    $role = $_POST['role'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Check for empty fields
    if (empty($id) || empty($username) || empty($department) || empty($role) || empty($email) || empty($password) || empty($confirm_password)) {
        $response['error'] = "All fields are required!";
    } elseif ($password !== $confirm_password) {
        $response['error'] = "Passwords do not match!";
    } else {
        // Insert logic based on role
        if ($role === "Head Coordinator") {
            $stmt = $conn->prepare("INSERT INTO users (id, username, email, password, role, department) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $id, $username, $email, $password, $role, $department);
        } else {
            $stmt = $conn->prepare("INSERT INTO colleges (id, uname, department, email, password, role) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isssss", $id, $username, $department, $email, $password, $role);
        }

        // Execute and check for errors
        if ($stmt->execute()) {
            $response['success'] = "Account created successfully as " . ($role === "Head Coordinator" ? "Head Coordinator" : $role) . "!";
        } else {
            $response['error'] = "Error creating account: " . $stmt->error;
        }
        
        // Close statement
        $stmt->close();
    }

    // Close connection
    $conn->close();

    // Return the response
    echo json_encode($response);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>CES PLP</title>

    <link rel="icon" href="images/logoicon.png">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> <!-- Ensure SweetAlert is included -->
    

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
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

            .login-container {
                display: flex;
                flex-direction: column;
            }

            .login-container h4 {
                font-family: "Poppins", sans-serif;
                margin-bottom: -10px;
                font-size: 25px;
                margin-top: 5px;
                color: black;
                text-align: left;
                margin-left: 11px;
            }

            /* Default style for the link */
            .default-style {
                font-family: 'Poppins', sans-serif;
                color: black !important;
                font-weight: normal;
                text-decoration: none;
            }

            /* Highlight style for username and password */
            .highlight {
                font-family: 'Poppins', sans-serif;
                font-size: 15px;
                font-weight: 300;
                color: #007bff;
                text-decoration: none;
            }

            .links {
                text-align: right !important;
                font-size: 15px;
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
                margin-bottom:-20px;
                margin-left: 11px;
            }
            /*LogInPage*/
            /* Eye icon styling */
            .password-toggle {
                position: absolute;
                right: 10px; /* Adjust to fit your layout */
                top: 50%;
                transform: translateY(-50%);
                cursor: pointer;
                z-index: 1; /* Ensure it's above the input */
            }

            .eye-icon {
                position: absolute; /* Position the icon absolutely */
                margin-top:100px;
                margin-right: 572px;
                transform: translateY(-50%); /* Center the icon */
                cursor: pointer; /* Change cursor to pointer on hover */
                color: black; /* Change the color as needed */
                z-index: 1000; /* Make sure it appears above the input field */
            }
            
           /* Custom styles for the SweetAlert popup */
            .custom-swal-popup {
                font-family: 'Poppins', sans-serif;
                width: 400px !important; /* Set a larger width */
            }

            /* Custom title style */
            .custom-swal-title {
                font-family: 'Poppins', sans-serif;
                color: #3085d6; /* Custom title color */
                font-size: 22px !important; /* Adjust title font size */
                margin-bottom: 10px; /* Space below the title */
            }

            /* Custom text style */
            .custom-swal-text {
                font-family: 'Poppins', sans-serif;
                color: #555; /* Text color */
                font-size: 17px !important; /* Font size for the text */
                margin-bottom: 15px; /* Space below the text */
            }

            /* Custom confirm button style */
            .custom-swal-confirm {
                font-family: 'Poppins', sans-serif;
                font-size: 17px !important; /* Button font size */
                background-color: #3085d6; /* Custom button color */
                border: none; /* Remove border */
                color: #fff; /* Button text color */
                border-radius: 5px; /* Rounded corners */
                padding: 10px 20px; /* Padding around button text */
                cursor: pointer; /* Change cursor to pointer */
                transition: background-color 0.3s; /* Transition effect for hover */
            }

            .custom-swal-confirm:hover {
                background-color: #267bb5; /* Darker shade on hover */
            }

            /* Custom error popup styles */
            .custom-error-popup {
                font-family: 'Poppins', sans-serif;
                width: 400px !important; /* Set a larger width for error popup */
            }

            .custom-error-title {
                font-family: 'Poppins', sans-serif;
                color: #e74c3c; /* Custom title color for error */
                font-size: 22px !important; /* Adjust title font size */
            }

            .custom-error-icon {
                font-size: 19px !important; /* Adjust title font size */
            }

            .custom-error-text {
                font-family: 'Poppins', sans-serif;
                color: #555; /* Text color for error message */
                font-size: 17px !important; /* Font size for the text */
            }

            /* Custom error confirm button style */
            .custom-error-confirm {
                font-family: 'Poppins', sans-serif;
                font-size: 17px; /* Button font size */
                background-color: #e74c3c; /* Custom button color for error */
                color: #fff; /* Button text color */
                border-radius: 5px; /* Rounded corners */
                padding: 10px 20px; /* Padding around button text */
                cursor: pointer; /* Change cursor to pointer */
                transition: background-color 0.3s; /* Transition effect for hover */
            }

            .custom-error-confirm:hover {
                background-color: #c0392b; /* Darker shade on hover */
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
            }

            .signup-link span, a {
                font-family: 'Poppins', sans-serif;
                font-size: 13px;
                color: #3b3b3b;
            }

            input[type="text"], input[type="email"], input[type="password"], select {
                font-family: 'Poppins', sans-serif;
                width: 93.5%;
                padding: 8px;
                margin: 5px 0;
                border: 1px solid #ccc;
                border-radius: 4px;
            }

            .dept,.role {
                width: 100%;
                padding: 10px;
                margin: 5px 0;
                border: 1px solid #ccc;
                border-radius: 4px;
            }

            button {
                font-family: "Poppins", sans-serif;
                font-size: 16px;
                background-color: #089451;
                color: #fff;
                border: 0.5px solid black;
                padding: 10px;
                border-radius: 10px;
                width: 98%;
                cursor: pointer;
                margin-top:10px;
                margin-bottom: 15px; 
            }

            button:hover {
                background-color: #45a049;
            }

            .password-container {
                position: relative;
                display: inline-block;
                width: 100%;
            }

            .eye-icon {
                cursor: pointer;
                position: absolute;
                right: 10px;
                left: 310px;
                top: -160%;
                transform: translateY(-50%);
                color: black;
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
                <h4> Create Account </h4>
                <p class="signin-text">Enter your details to create account<p>

                <form method="POST" id="signupForm">
                    <input type="text" name="id" placeholder="ID" required><br>

                    <input type="text" name="username" placeholder="Name" required><br>

                    <select name="department" required class="dept">
                        <option value="" disabled selected>Department</option>
                        <option value="College of Arts and Sciences">CAS</option>
                        <option value="College of Business Administration">CBA</option>
                        <option value="College of Computer Studies">CCS</option>
                        <option value="College of Education">COED</option>
                        <option value="College of Engineering">COE</option>
                        <option value="College of International Hospitality Management">CIHM</option>
                        <option value="College of Nursing">CON</option>
                    </select><br>

                    <select name="role" required class="role">
                        <option value="" disabled selected>Role</option>
                        <option value="Head Coordinator">Head Coordinator</option>
                        <option value="CAS Extension Coordinator">CAS Extension Coordinator</option>
                        <option value="CBA Extension Coordinator">CBA Extension Coordinator</option>
                        <option value="CCS Extension Coordinator">CCS Extension Coordinator</option>
                        <option value="COED Extension Coordinator">COED Extension Coordinator</option>
                        <option value="COE Extension Coordinator">COE Extension Coordinator</option>
                        <option value="CIHM Extension Coordinator">CIHM Extension Coordinator</option>
                        <option value="CON Extension Coordinator">CON Extension Coordinator</option>
                    </select><br>

                    <input type="email" name="email" placeholder="Email" required><br>
                   
                    <div class="password-container">
                        <input type="password" id="password" name="password" placeholder="Password" required>
                        <i id="togglePassword" class="eye-icon fa fa-eye-slash" style="cursor: pointer;"></i>
                    </div>

                    <div class="password-container">
                        <input type="password" id="confirmPassword" name="confirm_password" placeholder="Confirm Password" required>
                        <i id="toggleConfirmPassword" class="eye-icon fa fa-eye-slash" style="cursor: pointer;"></i>
                    </div>

                    <!-- Caps Lock warning message -->
                    <div id="capsLockWarning" style="display: none; color: #721c24; margin-top: 5px;">
                        Caps Lock is ON
                    </div>

                    <button type="submit">Create Account</button>

                    <div class="signup-link">
                        <span>Already have an account? <a href="roleaccount.php" class="highlight">Sign In</a></span>
                    </div>
                </form>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> <!-- Ensure SweetAlert is included -->
    
        <script>
             document.querySelectorAll('.eye-icon').forEach(icon => {
                icon.addEventListener('click', function() {
                    const input = this.previousElementSibling;
                    if (input.type === 'password') {
                        input.type = 'text';
                        this.classList.add('fa-eye');
                        this.classList.remove('fa-eye-slash');
                    } else {
                        input.type = 'password';
                        this.classList.add('fa-eye-slash');
                        this.classList.remove('fa-eye');
                    }
                });
            });

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

            document.getElementById('signupForm').addEventListener('submit', function(event) {
                event.preventDefault(); // Prevent the default form submission

                const form = this;
                const formData = {
                    id: form.querySelector('input[name="id"]').value,
                    username: form.querySelector('input[name="username"]').value,
                    email: form.querySelector('input[name="email"]').value,
                    password: form.querySelector('input[name="password"]').value,
                    confirmPassword: form.querySelector('input[name="confirm_password"]').value,
                    department: form.querySelector('select[name="department"]').value,
                    role: form.querySelector('select[name="role"]').value
                };

                // Validate email domain (@plpasig.edu.ph)
                const emailPattern = /^[a-zA-Z0-9._%+-]+@plpasig\.edu\.ph$/;
                if (!emailPattern.test(formData.email)) {
                    Swal.fire({
                        title: 'Error!',
                        html: `<div class="custom-error-text">Email must end with @plpasig.edu.ph.</div>`,
                        icon: 'error',
                        confirmButtonColor: '#e74c3c',
                        confirmButtonText: 'OK',
                        customClass: {
                            popup: 'custom-error-popup',
                            title: 'custom-error-title',
                            icon: 'custom-error-icon',
                            text: 'custom-error-text',
                            confirmButton: 'custom-error-confirm'
                        }
                    });
                    return; // Stop the form submission
                }

                // Check if passwords match
                if (formData.password !== formData.confirmPassword) {
                    Swal.fire({
                        title: 'Error!',
                        html: `<div class="custom-error-text">Passwords do not match. Please try again.</div>`,
                        icon: 'error',
                        confirmButtonColor: '#e74c3c',
                        confirmButtonText: 'OK',
                        customClass: {
                            popup: 'custom-error-popup',
                            title: 'custom-error-title',
                            icon: 'custom-error-icon',
                            text: 'custom-error-text',
                            confirmButton: 'custom-error-confirm'
                        }
                    });
                    return; // Stop the form submission
                }

                // Create a formatted summary of the form data
                const formSummary = `
                    <strong>Review Your Details:</strong><br><br>
                    <strong>ID:</strong> ${formData.id}<br>
                    <strong>Username:</strong> ${formData.username}<br>
                    <strong>Email:</strong> ${formData.email}<br>
                    <strong>Department:</strong> ${formData.department}<br>
                    <strong>Role:</strong> ${formData.role}<br>
                    <strong>Password:</strong> ${formData.password}<br><br>
                    Please confirm the details before creating your account.
                `;

                // Show SweetAlert for confirmation
                Swal.fire({
                    title: 'Confirm Your Details',
                    html: formSummary,
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#e74c3c',
                    confirmButtonText: 'Confirm',
                    cancelButtonText: 'Cancel',
                    customClass: {
                        popup: 'custom-swal-popup',
                        title: 'custom-swal-title',
                        icon: 'custom-error-icon',
                        text: 'custom-swal-text',
                        confirmButton: 'custom-swal-confirm',
                        cancelButton: 'custom-error-confirm'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Prepare the form data for submission
                        const requestData = new FormData(form);

                        // Submit the form data using Fetch API
                        fetch('<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>', {
                            method: 'POST',
                            body: requestData,
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    title: 'Success!',
                                    html: `<div class="custom-swal-text">${data.success}</div>`,
                                    icon: 'success',
                                    confirmButtonColor: '#3085d6',
                                    confirmButtonText: 'OK',
                                    customClass: {
                                        popup: 'custom-swal-popup',
                                        title: 'custom-swal-title',
                                        icon: 'custom-error-icon',
                                        text: 'custom-swal-text',
                                        confirmButton: 'custom-swal-confirm'
                                    }
                                }).then(() => {
                                    form.reset(); // Reset the form after success
                                    window.location.href = 'collegelogin.php'; // Redirect to collegelogin.php
                                });
                            } else {
                                Swal.fire({
                                    title: 'Error!',
                                    html: `<div class="custom-error-text">${data.error}</div>`,
                                    icon: 'error',
                                    confirmButtonColor: '#e74c3c',
                                    confirmButtonText: 'OK',
                                    customClass: {
                                        popup: 'custom-error-popup',
                                        title: 'custom-error-title',
                                        icon: 'custom-error-icon',
                                        text: 'custom-error-text',
                                        confirmButton: 'custom-error-confirm'
                                    }
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire({
                                title: 'Error!',
                                html: `<div class="custom-error-text">An error occurred while submitting the form.</div>`,
                                icon: 'error',
                                confirmButtonColor: '#e74c3c',
                                confirmButtonText: 'OK',
                                customClass: {
                                    popup: 'custom-error-popup',
                                    title: 'custom-error-title',
                                    icon: 'custom-error-icon',
                                    text: 'custom-error-text',
                                    confirmButton: 'custom-error-confirm'
                                }
                            });
                        });
                    }
                });
            });

            // Check for Caps Lock
            const passwordInput = document.querySelector('input[name="password"]');
            const confirmpasswordInput = document.querySelector('input[name="confirm_password"]'); // Ensure to select by the correct attribute
            const capsLockWarning = document.getElementById("capsLockWarning");

            // Show warning when Caps Lock is on
            passwordInput.addEventListener("keydown", function(event) {
                if (event.getModifierState("CapsLock")) {
                    capsLockWarning.style.display = "block"; // Show warning
                }
            });

            // Hide warning when Caps Lock is off
            passwordInput.addEventListener("keyup", function(event) {
                if (!event.getModifierState("CapsLock")) {
                    capsLockWarning.style.display = "none"; // Hide warning
                }
            });

            // Show warning when Caps Lock is on
            confirmpasswordInput.addEventListener("keydown", function(event) {
                if (event.getModifierState("CapsLock")) {
                    capsLockWarning.style.display = "block"; // Show warning
                }
            });

            // Hide warning when Caps Lock is off
            confirmpasswordInput.addEventListener("keyup", function(event) {
                if (!event.getModifierState("CapsLock")) {
                    capsLockWarning.style.display = "none"; // Hide warning
                }
            });
        </script>
    </body>
</html>