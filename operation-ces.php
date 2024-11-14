<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>CES PLP</title>

        <link rel="icon" href="images/logoicon.png">
        
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

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

            .operation-sec h1 {
                font-family: 'Glacial Indifference', sans-serif;
                font-size: 50px;
                text-align: center;
                margin-bottom: 40px;
            }

            .container-wrapper {
                display: flex;
                justify-content: center;
                align-items: center;
                padding: 10px 0;
                margin-top:20px;
            }

            .green-container1 {
                background-color: #2d6a4f;
                padding: 20px;
                margin: 10px;
                text-align: justify;
                box-sizing: border-box; /* Ensure padding doesn't affect the width */
            }
            
            .green-container2 {
                background-color: #2d6a4f;
                padding: 20px;
                margin: 10px;
                text-align: justify;
                box-sizing: border-box; /* Ensure padding doesn't affect the width */
            }   

            .green-container1 {
                width: 40%; /* Adjust this value to change the width of the first container */
                height:375px;
                position: relative;
            }

            .green-container2 {
                width: 40%; /* Adjust this value to change the width of the second container */
                height:375px;
                position: relative;
            }

            .text-container1 p{
                font-family: 'Poppins', sans-serif;
                color: white;
                font-size: 18px; /* Adjust this value to change the text size */
                margin-top:70px;
            }

            .text-container2 p {
                font-family: 'Poppins', sans-serif;
                color: white;
                font-size: 17px; /* Adjust this value to change the text size */
                margin-top:70px;
            }

            .yellow-container1, .yellow-container2 {
                background-color: #FFF8A5;
                padding: 5px;
                text-align: center;
                box-sizing: border-box; /* Ensure padding doesn't affect the width */
            }

            .yellow-container1 {
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                background-color: #FFF8A5;
            }

            .yellow-container2 {
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                background-color: #FFF8A5;
            }

            .yellow-container2 h2, .yellow-container1 h2 {
                font-family: 'Glacial Indifference', sans-serif;
                font-size: 25px;
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

        <div class="operation-sec">
            <h1> Two Major Operation of CES </h1>

            <div class="container-wrapper">
                <div class="green-container1">
                    <div class="yellow-container1">
                        <h2>I. COMMUNITY ENGAGEMENT PROGRAM</h2>
                    </div>
                    <div class="text-container1">
                        <p>It covers outreach activities to adopted barangays and
                            communities in need of immediate services. These
                            outreach activities include relief drive/operation, gift giving
                            activity, blood donation, medical and dental services, and
                            dental services, and feeding programs, seminars and
                            orientation are a requirement in the conduct of these
                            activities.<br><br>
                            Ex. One Shot or Short Term Scheme like Outreach Program</p>
                    </div>
                </div>
                <div class="green-container2">
                    <div class="yellow-container2">
                        <h2>II. COMMUNITY EXTENSION PROGRAM</h2>
                    </div>
                    <div class="text-container2">
                        <p>Community extension is the process of application and utilization
                            of research to an identified community or selected recipients. The
                            research output can be utilized through the developed model or
                            prototype of a particular technology that can be used by the
                            beneficiaries in their livelihood activities.
                            The transfer of technology to the beneficiaries comes with
                            orientation-seminars and trainings. A memorandum of agreement
                            is required as part of the process to secure the university and the
                            partner-community or beneficiaries in their extension
                            undertakings.<br><br>
                            Ex. LongTerm Scheme that lasts more than a year</p>
                    </div>
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
