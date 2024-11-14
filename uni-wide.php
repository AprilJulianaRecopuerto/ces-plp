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

            .uni-container h1 {
                font-family: 'Glacial Indifference', sans-serif;
                font-size: 50px;
                text-align: center;
                margin-bottom: 40px;
            }

            .box {
                display: flex;
                align-items: center;
                margin-bottom: 20px; /* Add margin to space out boxes */
            }

            .image {
                width: 450px; /* Adjust the width as needed */
                height: 350px;
                margin-left: 100px;
            }

            .text {
                font-family: 'Poppins', sans-serif;
                font-size: 18px;
                width: 50%; /* Adjust the width as needed */
                padding: 0 20px; /* Add padding for spacing */
                margin-left: 25px;
                margin-right: -25px;
                text-align: justify;
                box-sizing: border-box; /* Ensure padding does not affect total width */
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

                <a href="#" class="nav-item">Programs and Activities</a>
            </nav>

            <button class="home-button">Home</button>
        </header>

        <div class="uni-container">
            <h1> University Wide </h1>
            <div class="box">
                <img class="image" src="images/univ-wide1.jpg" alt="University Wide 1">
                <div class="text">
                    University-wide programs involve the participation of the entire university in Outreach or Community Extension Services (CES). These programs are designed to leverage the collective resources and expertise of the university to engage with and support the broader community. This involvement encompasses a range of initiatives and partnerships, including collaborations with external organizations such as the United States Agency for International Development (USAID). Through such partnerships, the university aims to extend its impact beyond the campus, contributing to community development, capacity building, and the enhancement of societal well-being.
                </div>

            </div>
            <div class="box">
                <img class="image" src="images/univ-wide.jpg" alt="University Wide">
                <div class="text">
                University-wide programs involve the participation of the entire university in Outreach or Community Extension Services (CES). These programs are designed to leverage the collective resources and expertise of the university to engage with and support the broader community. This involvement encompasses a range of initiatives and partnerships, including collaborations with external organizations such as the Philippine National Volunteer Service Coordinating Agency (PNVSCA). Through such partnerships, the university aims to extend its impact beyond the campus, contributing to community development, capacity building, and the enhancement of societal well-being.
                </div>
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
