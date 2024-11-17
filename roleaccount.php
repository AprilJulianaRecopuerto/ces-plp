<?php
session_start();

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

$idValid = false;
$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Handle Head Coordinator ID validation
    if (isset($_POST['adminID'])) {
        $id = $_POST['adminID'];

        if (preg_match('/^\d{4}$/', $id)) {
            // Prepare and execute the query to validate the ID
            $sql = "SELECT id FROM users WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $id);
            $stmt->execute();
            $stmt->store_result();
            
            $idValid = $stmt->num_rows > 0;
            $stmt->close();

            if ($idValid) {
                $_SESSION['adminId'] = $id;
                $message = 'Head Coordinator ID is valid.';
                header('Content-Type: application/json');
                echo json_encode(['valid' => true, 'message' => $message]);
            } else {
                $message = 'Invalid ID for Head Coordinator!';
                header('Content-Type: application/json');
                echo json_encode(['valid' => false, 'message' => $message]);
            }
        } else {
            $message = 'ID must be exactly 4 digits!';
            header('Content-Type: application/json');
            echo json_encode(['valid' => false, 'message' => $message]);
        }
        exit(); // Ensure no further code is executed after sending JSON response
    }

    // Handle College Coordinator ID validation
    if (isset($_POST['collegeID'])) {
        $id = $_POST['collegeID'];

        if (preg_match('/^\d{4}$/', $id)) {
            // Prepare and execute the query to validate the ID
            $sql = "SELECT id FROM colleges WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $id);
            $stmt->execute();
            $stmt->store_result();
            
            $idValid = $stmt->num_rows > 0;
            $stmt->close();

            if ($idValid) {
                $_SESSION['collegeID'] = $id;
                $message = 'College Coordinator ID is valid.';
                header('Content-Type: application/json');
                echo json_encode(['valid' => true, 'message' => $message]);
            } else {
                $message = 'Invalid ID for College Coordinator!';
                header('Content-Type: application/json');
                echo json_encode(['valid' => false, 'message' => $message]);
            }
        } else {
            $message = 'ID must be exactly 4 digits!';
            header('Content-Type: application/json');
            echo json_encode(['valid' => false, 'message' => $message]);
        }
        exit(); // Ensure no further code is executed after sending JSON response
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>CES PLP</title>


        <link rel="icon" href="images/logoicon.png">

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">
        <link rel="stylesheet" href="https://unpkg.com/swiper@8/swiper-bundle.min.css" />

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
                margin: 0;
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
            .banner2 {
                flex-direction: column;
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
            .banner2 img {
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
            #tranding {
                position: absolute;
                top: 180px; /* Adjust top position as needed */
                right: 30px; /* Adjust right position as needed */
                z-index: 1; /* Ensure it's above other content */
                background-color: none;
                padding: 20px;
                border-radius: 10px;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); /* Optional: Add shadow for depth */
            }
            .tranding-slide {
                position: relative;
                width: 38rem;
                height: 45rem;
                overflow: hidden; /* Ensures text doesn't overflow outside the slide */
            }
            .tranding-slide .tranding-slide-img {
                position: relative;
                overflow: hidden;
                width: 100%;
                height: 100%;
                border-radius: 15px;
                transition: transform 0.3s ease;
            }
            .tranding-slide:hover .tranding-slide-img {
                transform: scale(1.1); /* Zoom effect */
            }
            .tranding-slide .tranding-slide-img img {
                width: 100%;
                height: 100%;
                object-fit: cover;
                transition: transform 0.3s ease;
            }
            .tranding-slide:hover .tranding-slide-img img {
                transform: scale(1.1); /* Zoom effect */
                border-radius: 15px !important; /* Maintain border-radius on hover */
            }
            .tranding-slide-content {
                position: absolute;
                bottom: 0;
                left: 0;
                right: 0;
                background-color: rgba(0, 0, 0.7, 0.7); /* Transparent black background */
                color: white;
                padding: 1rem;
                transform: translateY(100%);
                transition: 0.3s ease;
            }
            .tranding-slide:hover .tranding-slide-content {
                transform: translateY(0); /* Slide content upward */
            }
            .details-name {
                font-family: 'Glacial Indifference', sans-serif;
                font-size: 30px;
                font-weight: 300;
                margin-bottom: 20px;
            }
            .details {
                font-family: "Poppins", sans-serif;
                font-size: 18.5px;
                text-align: justify;
                margin-bottom: 10px;
            }
            .swiper-slide-shadow-left,
            .swiper-slide-shadow-right {
                display: none;
            }
            .tranding-slider-control {
                position: relative;
                bottom: 2rem;
                display: flex;
                align-items: center;
                justify-content: center;
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

            .banner-content {
                display: flex;
                flex-direction: column;
                z-index: 1;
                position: relative;
                margin-left: -70%;
            }

            .adminbutton, .userbutton {
                font-family: 'Glacial Indifference', sans-serif;
                background-color: #089451;
                font-size: 30px;
                color: white;
                border: none;
                border-radius: 12px;
                width: 260px;
                height: 120px;
                margin: 10px;
                cursor: pointer;
            }

            .adminbutton:hover, .userbutton:hover {
                background-color: #218838;
            }

            .or {
                margin: 10px;
                font-family: 'Glacial Indifference', sans-serif;
                font-size: 18.9px;
                color: white;
            }

            .overlay {
                display: none; 
                position: fixed;
                top: 0;
                left: 0; 
                width: 100%; 
                height: 100%;
                background-color: rgba(0, 0, 0, 0.5); 
                z-index: 10;
            }

            .admin-form-container {
                display: none; 
                position: fixed; 
                top: 50%; 
                left: 50%;
                transform: translate(-50%, -50%);
                background-color: white;
                padding: 20px; 
                height: auto;
                width: 310px;
                border-radius: 10px;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
                z-index: 1000;
            }

            .admin-form-container form {
                display: flex;
                flex-direction: column;
            }

            .admin-form-container label {
                font-family: 'Glacial Indifference', sans-serif;
                font-size: 27px;
                font-weight: 500;
                margin-bottom: 12px;
            }

            .admin-form-container input {
                font-family: 'Glacial Indifference', sans-serif;
                font-size: 15px;   
                height: 38px;
                border-radius: 10px;
                margin-bottom: 15px;
                padding-left: 15px;
            }

            #adminID::placeholder {
                padding-left: -5px;
                color: #999; /* Adjust color as needed */
            }

            .adminb, .collegeb {
                margin-bottom: 10px;
                font-family: 'Glacial Indifference', sans-serif;
                font-size: 17px;
                background-color: #089451;
                color: #fff;
                border-radius: 10px;
                height: 38px;
                width: 100%;
                cursor: pointer;
            }

            .adminb, .collegeb:hover {
                background-color: #218838;
            }

            .close-button {
                position: absolute;
                top: 10px;
                right: 10px;
                cursor: pointer;
                border: none; 
                background-color: transparent !important; 
                padding: 0;
                margin: 0; 
            }

            .close-button img {
                width: 25px; 
                height: 25px; 
                border: none !important; 
                background-color: transparent !important;
                transition: transform 0.3s; /* Ensure smooth zoom effect */
            }

            .close-button img:hover {
                transform: scale(1.2); /* Adjust the scale value as needed for zoom effect */
            }

            /* Overlay for College Coordinator */
            #collegeOverlay {
                display: none; 
                position: fixed;
                top: 0;
                left: 0; 
                width: 100%; 
                height: 100%;
                background-color: rgba(0, 0, 0, 0.5); 
                z-index: 10;
            }

            /* Floating College Coordinator ID Form Container */
            #collegeFormContainer {
                display: none; 
                position: fixed; 
                top: 50%; 
                left: 50%;
                transform: translate(-50%, -50%);
                background-color: white;
                padding: 20px; 
                height: auto;
                width: 310px;
                border-radius: 10px;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
                z-index: 1000;
            }

            .close-college-button {
                position: absolute;
                top: 10px;
                right: 10px;
                cursor: pointer;
                border: none; 
                background-color: transparent !important; 
                padding: 0;
                margin: 0; 
            }

            .close-college-button img {
                width: 25px; 
                height: 25px; 
                border: none !important; 
                background-color: transparent !important;
                transition: transform 0.3s; /* Ensure smooth zoom effect */
            }

            .close-college-button img:hover {
                transform: scale(1.2); /* Adjust the scale value as needed for zoom effect */
            }

            .swal2-popup {
                font-family: 'Poppins', sans-serif;
                font-size: 1.6rem; /* Increase the font size */
                width: 400px !important; /* Set a larger width */
            }

            .swal2-title {
                font-family: 'Poppins', sans-serif;
                color: #3085d6; /* Custom title color */
            }

            .swal2-confirm {
                font-family: 'Poppins', sans-serif;
                font-size: 17px;
                background-color: #089451;
                color: #fff;
                border-radius: 10px;
                cursor: pointer;
            }

            .swal2-title.error {
                font-family: 'Poppins', sans-serif;
                color: #e74c3c; /* Custom title color for error */
            }

            .swal2-confirm.error {
                font-family: 'Poppins', sans-serif;
                font-size: 17px;
                background-color: #e74c3c;
                border: 0.5px #e74c3c !important;
                color: #fff;
                border-radius: 10px;
                cursor: pointer;
            }

        </style>
    </head>

    <body>
        <!-- Header Logo -->
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

        <!-- Container for Slider and Image Banner -->
        <div class="content-container">
            <!-- Slider Section -->
            <section id="tranding">
                <div class="container">
                    <div class="swiper tranding-slider">
                        <div class="swiper-wrapper">
                            <!-- Slide 1 -->
                            <div class="swiper-slide tranding-slide">
                                <div class="tranding-slide-img">
                                    <img src="images/plp slider.jpg" alt="Tranding">
                                </div>
                                <div class="tranding-slide-content">
                                    <div class="tranding-slide-content-bottom">
                                        <p class="details-name">
                                            Pamantasan ng Lungsod ng Pasig
                                        </p>
                                        <p class="details">
                                            The Pamantasan ng Lungsod ng Pasig fulfills our 
                                            longstanding aspiration for all Pasigueños. It proudly 
                                            ensures comprehensive educational opportunities from daycare 
                                            through college, courtesy of the Pasig City Government.
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <!-- Slide 2 -->
                            <div class="swiper-slide tranding-slide">
                                <div class="tranding-slide-img">
                                    <img src="images/ces.png" alt="Tranding">
                                </div>
                                <div class="tranding-slide-content">
                                    <div class="tranding-slide-content-bottom">
                                        <p class="details-name">
                                            PLP Community Extension Services
                                        </p>
                                        <p class="details">
                                            PLP Community Extension Services: Initiatives by Pamantasan 
                                            ng Lungsod ng Pasig aimed at providing educational support, 
                                            resources, and services to enhance community well-being and 
                                            development.
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <!-- Slide 3 -->
                            <div class="swiper-slide tranding-slide">
                                <div class="tranding-slide-img">
                                    <img src="images/cesprog.png" alt="Tranding">
                                </div>
                                <div class="tranding-slide-content">
                                    <div class="tranding-slide-content-bottom">
                                        <p class="details-name">
                                            Community Extension Programs
                                        </p>
                                        <p class="details">
                                            TheCommunity Extension Programs at Pamantasan 
                                            ng Lungsod ng Pasig (PLP) are initiatives designed 
                                            to support and engage with the local community through 
                                            various educational, social, and developmental activities.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <!-- Image Banner Section -->
        <div class="banner2">
            <img src="images/Admin (1).png" alt="Banner Image">
            <div class="banner-content">
                <button class="adminbutton">Head Coordinator</button>
                <p class="or"> or </p>
                <button class="userbutton">College Coordinator</button>
            </div>
        </div>

        <!-- Overlay -->
        <div id="overlay" class="overlay"></div>

        <!-- Floating Admin ID Form Container -->
        <div id="adminFormContainer" class="admin-form-container">
            <button class="close-button" id="closeAdminForm">
                <img src="images/close.png" alt="Close Button">
            </button>

            <form method="post" action="">
                <label for="adminID">Head Coordinator ID:</label>
                <input type="text" id="adminID" name="adminID" placeholder="Enter HC ID" required>
                <button type="submit" class="adminb">Submit <br></button>
            </form>
            <div class="message"><?php echo $message; ?></div>
        </div>
        
        <!-- Overlay for College Coordinator -->
        <div id="collegeOverlay" class="overlay"></div>

        <!-- Floating College Coordinator ID Form Container -->
        <div id="collegeFormContainer" class="admin-form-container">
            <button class="close-college-button">
                <img src="images/close.png" alt="Close Button">
            </button>
            <form method="post" action="">
                <label for="collegeID">College Coordinator ID:</label>
                <input type="text" id="collegeID" name="collegeID" placeholder="&nbsp;College ID" required>
                <button type="submit" class="collegeb">Submit <br></button>
            </form>
            <div class="message"><?php echo $message; ?></div>
        </div>

        <!-- Swiper Script -->
        <script src="https://unpkg.com/swiper@8/swiper-bundle.min.js"></script>

        <!-- Initialize Swiper -->
        <script>
            var TrandingSlider = new Swiper('.tranding-slider', {
                effect: 'coverflow',
                grabCursor: true,
                centeredSlides: true,
                loop: true,
                slidesPerView: 'auto',
                autoplay: {
                    delay: 2500, // Autoplay delay in milliseconds
                    disableOnInteraction: false, // Autoplay continues even when user interacts with slider
                },
                coverflowEffect: {
                    rotate: 0,
                    stretch: 0,
                    depth: 100,
                    modifier: 2.5,
                }
            });

            document.addEventListener("DOMContentLoaded", function() {
                // Handle Admin Form Submission
                document.querySelector('.admin-form-container form').addEventListener('submit', function(event) {
                    event.preventDefault(); // Prevent default form submission  

                    const formData = new FormData(this);

                    fetch('', { // URL should be the PHP file handling the form
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        const form = document.querySelector('.admin-form-container form');
                        if (data.valid) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: data.message,
                                confirmButtonText: 'OK',
                                customClass: {
                                    popup: 'swal2-popup'
                                }
                            }).then(() => {
                                window.location.href = 'loginpage.php'; // Redirect on valid ID
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data.message,
                                confirmButtonText: 'OK',
                                customClass: {
                                    popup: 'swal2-popup'
                                }
                            });
                        }

                        form.reset(); // Reset the form fields
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        document.querySelector('.message').textContent = 'Error validating ID.';
                    });
                });

                // Handle College Coordinator Form Submission
                document.querySelector('#collegeFormContainer form').addEventListener('submit', function(event) {
                    event.preventDefault(); // Prevent default form submission  

                    const formData = new FormData(this);

                    fetch('', { // URL should be the PHP file handling the form
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        const form = document.querySelector('#collegeFormContainer form');
                        
                        if (data.valid) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: data.message,
                                confirmButtonText: 'OK',
                                customClass: {
                                    popup: 'swal2-popup'
                                }
                            }).then(() => {
                                window.location.href = 'collegelogin.php'; // Redirect on valid ID
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data.message,
                                confirmButtonText: 'OK',
                                customClass: {
                                    popup: 'swal2-popup'
                                }
                            });
                        }

                        form.reset(); // Reset the form fields
                    })
                    
                    .catch(error => {
                        console.error('Error:', error);
                        document.querySelector('.message').textContent = 'Error validating ID.';
                    });
                });

                // Open Admin Form
                document.querySelector('.adminbutton').addEventListener('click', function() {
                    document.getElementById('adminFormContainer').style.display = 'block';
                    document.getElementById('overlay').style.display = 'block';
                });

                // Close Admin Form
                document.getElementById('closeAdminForm').addEventListener('click', function() {
                    document.getElementById('adminFormContainer').style.display = 'none';
                    document.getElementById('overlay').style.display = 'none';
                });

                // Open College Coordinator Form
                document.querySelector('.userbutton').addEventListener('click', function() {
                    document.getElementById('collegeFormContainer').style.display = 'block';
                    document.getElementById('collegeOverlay').style.display = 'block';
                });

                // Close College Coordinator Form
                document.querySelector('.close-college-button').addEventListener('click', function() {
                    document.getElementById('collegeFormContainer').style.display = 'none';
                    document.getElementById('collegeOverlay').style.display = 'none';
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
        </script>
    </body>
</html>