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
            margin-top: 8px;
            margin-left: 16px;
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

        .header-button-dropbtn{
            font-family: 'Glacial Indifference', sans-serif;
            background-color: white;
            font-size: 17.9px;
            color: black;
            border: none;
            border-radius: 12px;
            width: 170.7px;
            height: 60px;
            margin-top: 14px;
            margin-right: 40px;
            cursor: pointer;
        }

        .header-button-dropbtn:hover {
            background-color: #218838;
            color: white;
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
		
        /* Adjust dropdown for Evaluation Form */
.header .dropdown-content {
    display: none;
    position: absolute;
    background-color: #f9f9f9;
    min-width: 200px; /* Adjust as needed */
    box-shadow: 0px 8px 16px 0px rgba(0, 0, 0, 0.1);
    border-radius: 10px;
    z-index: 1000;
    right: 50px; /* Align to the right */
    top: 45px; /* Adjust vertical placement */
    margin-top:10px;
}

.header .dropdown-content a {
    font-family: 'Poppins', sans-serif;
    color: black;
    padding: 12px 16px;
    text-decoration: none;
    display: block;
    text-align: left;
    border-radius: 5px;
}

.header .dropdown-content a:hover {
    background-color: #ddd;
}

.header .dropdown:hover .dropdown-content {
    display: block;
}

.header .dropdown:hover .dropbtn {
    background-color: #218838;
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
    
        <div class="dropdown">
        <button class="header-button-dropbtn">Evaluation Form &nbsp;
            <i class="fa fa-caret-down"></i>
        </button>

        <div class="dropdown-content" style="right: 50px; top: 45px; position: absolute;">
            <a href="cas-form-participants.php">CAS Form</a>
            <a href="coed-form-participants.php">CCS Form</a>
            <a href="ccs-form-participants.php">CCS Form</a>
            <a href="cihm-form-participants.php">CIHM Form</a>
            <a href="con-form-participants.php">CON Form</a>
            <a href="coe-form-participants.php">COE Form</a>
            <a href="cba-form-participants.php">CBA Form</a>
        </div>
    </div>
    </header>
    
    
    <div class="banner1">
        <img src="images/Admin (1).png">
        <h1 class="banner-heading">Empowering Communities <br> Through <br> Collaboration</h1>
    </div>
</body>
</html>
