<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>CES PLP</title>

        <!-- The FavIcon of our Website -->
        <link rel="icon" href="images/logoicon.png">

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
        
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,400;0,600;1,500&display=swap');
            @import url('https://fonts.cdnfonts.com/css/glacial-indifference-2');
            @import url('https://fonts.googleapis.com/css2?family=Saira+Condensed:wght@500&display=swap');

            /*LoadingPage*/
            /* Header styles */
            body, html {
                margin: 0;
                padding: 0;
                overflow: auto;
            }

            .logo {
                display: flex;
                align-items: center;
            }

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
            /*LoadingPage*/
            /*Header styles*/

            /*Mis-Vis styles*/
            .mis-container {
                background-color: #fff9c4; /* Pastel yellow */
                margin: 20px auto;
                padding: 20px;
                width: 95%;
                height: 420px;
                display: flex;
                align-items: center;
                justify-content: center;
                box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
                border-radius: 10px;
            }

            .mis-content {
                flex: 1;
                margin-right: 10px;
            }

            .mis-content h1 {
                margin-top: 0;
                font-family: 'Glacial Indifference', sans-serif;
                font-size: 50px;
                width: 50%;
                margin-left: 25%;
            }

            .mis-content p {
                margin-bottom: 0;
                font-family: 'Poppins', sans-serif;
                font-size: 19px;
                text-align: justify;
                width: 50%;
                margin-left: 25%;
            }

            .mis-container img {
                width: 40%;
                height: auto;
                margin-right: 10%;
            }

            .mis-container2 {
                background-color: #fff9c4; /* Pastel yellow */
                margin: 20px auto;
                padding: 20px;
                width: 95%;
                height: 420px;
                display: flex;
                align-items: center;
                justify-content: center;
                box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
                border-radius: 10px;
            }

            .mis-content2 {
                flex: 1;
                margin-right: 10px;
            }

            .mis-content2 h1 {
                margin-top: 0;
                font-family: 'Glacial Indifference', sans-serif;
                font-size: 50px;
                width: 70%;
                margin-left: 35%;
            }

            .mis-content2 p {
                margin-bottom: 0;
                font-family: 'Poppins', sans-serif;
                font-size: 19px;
                text-align: justify;
                width: 75%;
                margin-left: 2%;
            }

            .mis-container2 img {
                width: 40%;
                height: auto;
                margin-right: 10%;
                margin-left: -10%;
            }
            /*Mis-Vis styles*/
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

        <div class="mis-container">
            
                <div class="mis-content">
                    <h1>Our Mission</h1>
                    <p>To build partnerships and instill capacity building with the 
                        communities in the city and the nation by providing support-mechanism 
                        in the implementation of programs and services, shared expertise 
                        and academic resources that promote fundamental human development.</p>
                </div>

            <img src="images/logo.png" alt="Logo">
        </div>

        <div class="mis-container2" style="background-color: white;">
            <img src="images/logo.png" alt="Logo" style="margin-left: 10%;">
            
            <div class="mis-content2">
                <h1>Our Vision</h1>
                <p>The Pamantasan ng Lungsod ng Pasig Community Extension Service 
                    Office envisions itself as anoffice that works in partnership 
                    with communities and with other organizations dedicated to local and 
                    national development through integrated programs through learning, 
                    building, and access to opportunities towards growth of people and 
                    realization of positive life options.</p>
            </div>
        </div>

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
        </script>

    </body>
</html>
