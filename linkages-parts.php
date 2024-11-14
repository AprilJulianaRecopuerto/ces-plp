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

            .linkages-sec h1 {
                font-family: 'Glacial Indifference', sans-serif;
                font-size: 50px;
                text-align: center;
                margin-bottom: 40px;
            }

            .gallery {
                display: grid;
                grid-template-columns: repeat(5, 1fr);
                gap: 20px;
                padding: 20px;
                margin-left: 50px;
                margin-right: 50px;
            }

            .gallery-item {
                position: relative;
                overflow: hidden;
                border: 2px solid black;
                border-radius: 10px;
                padding: 15px;
            }

            .gallery-item img {
                width: 100%;
                height: 100%;
                object-fit: cover;
                transition: transform 0.3s ease, filter 0.3s ease;
            }

            .gallery-item:hover img {
                transform: scale(1.1);
                filter: blur(2px);
            }

            .gallery-item .overlay {
                font-family: 'Poppins', sans-serif;
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.7);
                color: white;
                display: flex;
                justify-content: center;
                align-items: center;
                opacity: 0;
                transition: opacity 0.3s ease;
                text-align: center;
            }

            .gallery-item:hover .overlay {
                opacity: 1;
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

        <div class="linkages-sec">
            <h1> PLP CES Linkages and Partners </h1>
            <div class="gallery" id="gallery">
                <!-- Repeat this block for each gallery item (22 times in total) -->
                <div class="gallery-item">
                    <img src="images/usaid.png" alt="Photo 1">
                    <div class="overlay">United State Agency for International Development </div>
                </div>

                <div class="gallery-item">
                    <img src="images/edc.png" alt="Photo 2">
                    <div class="overlay">Education Development <br> Center</div>
                </div>

                <div class="gallery-item">
                    <img src="images/pnvc.png" alt="Photo 3">
                    <div class="overlay">Philippine National Volunteer Services Coordinating Agency</div>
                </div>

                <div class="gallery-item">
                    <img src="images/caritas pasig.png" alt="Photo 4">
                    <div class="overlay">CARITAS Pasig Inc.</div>
                </div>

                <div class="gallery-item">
                    <img src="images/crs.jpg" alt="Photo 5">
                    <div class="overlay">Catholic Relief Services</div>
                </div>

                <div class="gallery-item">
                    <img src="images/araling.jfif" alt="Photo 6">
                    <div class="overlay">Araling Pasig</div>
                </div>

                <div class="gallery-item">
                    <img src="images/spnp.jpg" alt="Photo 7">
                    <div class="overlay">Samahang Pangkasaysayan ng Pasig</div>
                </div>

                <div class="gallery-item">
                    <img src="images/dilglogo.jpg" alt="Photo 8">
                    <div class="overlay">Department of Interior and Local Government</div>
                </div>

                <div class="gallery-item">
                    <img src="images/pcho.jfif" alt="Photo 9">
                    <div class="overlay">Pasig City Health Office</div>
                </div>

                <div class="gallery-item">
                    <img src="images/kapasigan.jpg" alt="Photo 10">
                    <div class="overlay">Barangay Kapasigan</div>
                </div>

                <div class="gallery-item">
                    <img src="images/servings.png" alt="Photo 11">
                    <div class="overlay">Serving Hearts</div>
                </div>

                <div class="gallery-item">
                    <img src="images/pasig chap.jpg" alt="Photo 12">
                    <div class="overlay">United Architects of the Philippines (Pasig Chapter)</div>
                </div>

                <div class="gallery-item">
                    <img src="images/BIGMAC.jfif" alt="Photo 13">
                    <div class="overlay">Bigay Malasakit</div>
                </div>

                <div class="gallery-item">
                    <img src="images/CIS.png" alt="Photo 14">
                    <div class="overlay">Pasig city Institute of Science and Technology (Pasig)</div>
                </div>

                <div class="gallery-item">
                    <img src="images/PCDY.png" alt="Photo 15">
                    <div class="overlay">Pasig City Youth Development Alliance (PCYDA)</div>
                </div>

                <div class="gallery-item">
                    <img src="images/als.png" alt="Photo 16">
                    <div class="overlay">Alternative Learning System (ALS)</div>
                </div>

                <div class="gallery-item">
                    <img src="images/deped.jfif" alt="Photo 17">
                    <div class="overlay">DepEd Pasig  Division Office</div>
                </div>

                <div class="gallery-item">
                    <img src="images/QCU_Logo_2019.png" alt="Photo 18">
                    <div class="overlay">Quezon City University (QCU)</div>
                </div>

                <div class="gallery-item">
                    <img src="images/PCC.jpg" alt="Photo 19">
                    <div class="overlay">Pasig Catholic College</div>
                </div>

                <div class="gallery-item">
                    <img src="images/uap.png" alt="Photo 20">
                    <div class="overlay">University of Asia and the Pacific</div>
                </div>

                <div class="gallery-item">
                    <img src="images/zonta.jfif" alt="Photo 21">
                    <div class="overlay">Zonta Club Metropolitan Pasig</div>
                </div>

                <div class="gallery-item">
                    <img src="images/gad.jfif" alt="Photo 22">
                    <div class="overlay">Gender and Development (GAD) Pasig City</div>
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
