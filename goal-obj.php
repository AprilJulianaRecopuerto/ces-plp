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

            /*Goal styles*/
            .content {
                padding: 20px;
                display: grid;
                grid-template-columns: 1fr 2fr;
                gap: 20px;
                margin-left: 210px;
            }

            .goal-section h2 {
                font-family: 'Glacial Indifference', sans-serif;
                font-size: 100px;
                margin-top: 100px;
                margin-bottom: 20px;
            }

            .goal-section p {
                line-height: 1.6;
                font-family: 'Poppins', sans-serif;
                font-size: 19px;
                text-align: justify;
                margin-left: 30px;
                width: 70%;
            }

            .gallery {
                display: grid;
                grid-template-columns: 1fr 1fr;
                grid-template-rows: auto auto;
                gap: 15px;
                margin-top: 40px;
            }

            .gallery img {
                max-width: 100%;
                margin-left: -60px;
                border-radius: 10px;
                cursor: pointer;
            }

            .img-large {
                grid-column: span 2;
            }

            /* Modal styles */
            .modal {
                display: none;
                position: fixed;
                z-index: 1;
                padding-top: 70px;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                overflow: auto;
                background-color: rgba(0,0,0,0.9);
            }

            .modal-content {
                margin: auto;
                display: block;
                width: 80%;
                max-width: 700px;
            }

            .modal-content, #caption {
                animation-name: zoom;
                animation-duration: 0.6s;
            }

            @keyframes zoom {
                from {transform:scale(0)}
                to {transform:scale(1)}
            }

            .close {
                position: absolute;
                top: 15px;
                right: 35px;
                color: #fff;
                font-size: 30px;
                font-weight: bold;
                transition: 0.3s;
            }

            .close:hover,
            .close:focus {
                color: #bbb;
                text-decoration: none;
                cursor: pointer;
            }
            /*Goal styles*/
            
            /*Obj styles*/
            .obj-section {
                margin: 40px 200px;
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 15px; /* Adjust the gap between cards as needed */
            }

            .obj-section h2 {
                font-family: 'Glacial Indifference', sans-serif;
                font-size: 70px;
                margin-bottom: 40px;
                grid-column: span 3; /* Make sure the heading spans across all columns */
            }

            .obj-container {
                background: #fff;
                padding: 40px;
                box-shadow: 0 5px 20px rgba(0,0,0,.1);
                border-radius: 4px;
                box-sizing: border-box;
                text-align: justify;
                position: relative; /* Ensure position is relative for proper pseudo-element effect */
                overflow: hidden; /* Hide any overflow during the transition */
                transition: box-shadow 0.5s ease;
                z-index: 1; /* Ensure the content is above the pseudo-element */
            }

            .obj-container::before {
                content: "";
                position: absolute;
                top: 0;
                left: -100%;
                width: 100%;
                height: 100%;
                background: #f1f875; /* Color you want to slide in */
                transition: left 0.5s ease;
                z-index: -1; /* Place the pseudo-element behind the content */
            }

            .obj-container:hover {
                transform: scale(1.05); /* Zoom effect */
                box-shadow: 0 10px 30px rgba(0,0,0,.2); /* Enhanced shadow on hover */
            }

            .obj-container:hover::before {
                left: 0;
            }

            .obj-container .icon {
                font-family: 'Glacial Indifference', sans-serif;
                font-size: 35px;
                font-weight: 600;
                width: 60px;
                height: 60px;
                margin-bottom: 15px;
                color: #000000;
                background: #fffda3;
                display: flex;
                justify-content: center;
                align-items: center;
                border-radius: 50%;
                transition: 2s;
            }

            .obj-container p { 
                font-family: 'Poppins', sans-serif;
                font-size: 18px;
                line-height: 1.5;
                margin: 0;
                padding: 0;  
            }
            /*Obj styles*/
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

        <div class="content">
            <div class="gallery">
                <img src="images/Goal.jpg" class="img-large" onclick="expandImage(this);">
                <img src="images/goal1.jpg" onclick="expandImage(this);">
                <img src="images/goal2.jpg" onclick="expandImage(this);">
            </div>

            <div class="goal-section">
                <h2>Goal</h2>
                <p> The PLP CES aims at attaining progress in 
                    the individual and its community partners. 
                    The office endeavors to achieve an enhanced sense 
                    of civic and social responsibility among its students, 
                    faculty members, and personnel. </p>
            </div>
        </div>

        <div class="obj-section">
            <h2> Objectives </h2>
        
            <div class="obj-container">
                <div class="icon">01</div> <br>
                <div class="obj-content">
                    <p> To strengthen the relationship between the University 
                        and the community through collaborative extension programs 
                        and services.</p>
                </div>
            </div>
        
            <div class="obj-container">
                <div class="icon">02</div> <br>
                <div class="obj-content">
                    <p> To share the University expertise and resources for 
                        the improvement of the quality of lives of the community.</p>
                </div>
            </div>
        
            <div class="obj-container">
                <div class="icon">03</div> <br>
                <div class="obj-content">
                    <p> To organize the entire PLP community for significant 
                        and responsive community service.</p>
                </div>
            </div>

            <div class="obj-container">
                <div class="icon">04</div> <br>
                <div class="obj-content">
                    <p> To provide alternative solutions to community problems of the 
                        marginalized as well as the less-privileged people by assisting 
                        them to become productive citizens of the   community.</p>
                </div>
            </div>

            <div class="obj-container">
                <div class="icon">05</div> <br>
                <div class="obj-content">
                    <p> To provide services within the capability of the University 
                        to different institutions and organizations. </p>
                </div>
            </div>
        </div>

        <!-- The Modal -->
        <div id="myModal" class="modal">
            <span class="close" onclick="closeModal()">
                <i class="far fa-times-circle"></i>
            </span>
            <img class="modal-content" id="img01">
            <div id="caption"></div>
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

            function expandImage(img) {
                var modal = document.getElementById("myModal");
                var modalImg = document.getElementById("img01");
                var captionText = document.getElementById("caption");
                modal.style.display = "block";
                modalImg.src = img.src;
                captionText.innerHTML = img.alt;
            }

            function closeModal() {
                var modal = document.getElementById("myModal");
                modal.style.display = "none";
            }
        </script>
    </body>
</html>
