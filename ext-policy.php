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

            .ext-policy h1 {
                font-family: 'Glacial Indifference', sans-serif;
                font-size: 50px;
                text-align: center;
                margin-bottom: 40px;
            }

            .policy-tab {
                background-color: #fff9c4;
                height: 370px;
                margin-left: 200px;
                margin-right: 200px;
                border-radius: 15px;
                padding: 15px;
                margin-bottom: 50px;
                max-height: 500px; /* Fixed height */
                overflow-y: auto; /* Scrollable when content exceeds height */
            }

            /* Style the tab */
            .tab {
                overflow: hidden;
                margin-left: 100px;
                margin-right: 100px;
                margin-top: 25px;
            }

            /* Style the buttons that are used to open the tab content */
            .tab button {
                font-family: 'Glacial Indifference', sans-serif;
                font-size: 23px;
                background-color: inherit;
                float: left;
                border: none;
                outline: none;
                cursor: pointer;
                padding: 14px 16px;
                transition: 0.3s;
            }

            /* Change background color of buttons on hover */
            .tab button:hover {
                background-color: #a1cca5;
            }

            /* Create an active/current tablink class */
            .tab button.active {
                background-color: #415d43;
                color:white;
            }

            /* Style the tab content */
            .tabcontent {
                font-family: 'Poppins', sans-serif;
                font-size: 17px;
                display: none;
                padding: 6px 12px;
                border-top: none;
                margin-left: 100px;
                margin-right: 100px;
                text-align: justify;
            }

            .tabcontent {
                animation: fadeEffect 1s; /* Fading effect takes 1 second */
            }

            /* Go from zero to full opacity */
            @keyframes fadeEffect {
                from {opacity: 0;}
                to {opacity: 1;}
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

        <div class="ext-policy">
            <h1>University Extension Service Policy</h1>

            <div class="policy-tab">
                <!-- Tab links -->
                <div class="tab">
                    <button class="tablinks" onclick="openArticle(event, '329')" id="defaultOpen">Article 329</button>
                    <button class="tablinks" onclick="openArticle(event, '330')">Article 330</button>
                    <button class="tablinks" onclick="openArticle(event, '331')">Article 331</button>
                    <button class="tablinks" onclick="openArticle(event, '332')">Article 332</button>
                    <button class="tablinks" onclick="openArticle(event, '333')">Article 333</button>
                    <button class="tablinks" onclick="openArticle(event, '334')">Article 334</button>
                </div>
                
                <!-- Tab content -->
                <div id="329" class="tabcontent">
                    <h2>Article 329 Theoretical Constructs</h2>
                    <p> In the trilogy of higher academic institutions, one
                        of which is the need to get immersed in the process
                        of building human infrastructures and exert efforts 
                        to reach the communities where the institution is located;
                        touching people's lives and the environment they leave 
                        in for social transformation.</p>
                </div>
                
                <div id="330" class="tabcontent">
                    <h2>Article 330 Frame of Reference</h2>
                    <p> As mandated by CHED, higher education institutions must respond to the call of sharing and service and address the
                        challenges of the marginalized sector of Pasig City through
                        PLP's extension service program involving the faculty members,
                        administrative personnel, and the students aimed at transferring 
                        knowledge or technology, and provide services to the community in 
                        consonance with the programs offered by the academe.</p>
                </div>
                
                <div id="331" class="tabcontent">
                    <h2>Article 331 Nature and Scope </h2>
                    <p> Extension is a program of PLP which attempts to
                        realize its commitment to a culture of sharing
                        and service. Educational institutions are expected
                        to be committed to the total human development
                        in an integrated manner through participatory
                        approach which is comprehensive in nature and
                        sustainable in character. The extension service
                        projects may be done according to management
                        hosting namely, Faculty Members-Initiated
                        Projects/Programs, Students-Initiated
                        Projects/Programs, Administrative PersonnelInitiated Projects/Programs, Joint Faculty
                        Members and Students-Initiated
                        Projects/Programs, College/Department-Initiated
                        Projects/Programs, Faculty Association-Initiated
                        Projects/Programs, Administrative Personnel
                        Association-Initiated Projects/Programs, and
                        University-Wide Projects/Programs.</p>
                </div>

                <div id="332" class="tabcontent">
                    <h2>Article 332 Advocacies of Sustainable Development </h2>
                    <p> PLP's extension service includes but
                        not exclusive to issues on Safety,
                        Health and Wellness, Poverty
                        Alleviation, Law Enforcement,
                        Environment, Sports and Recreation,
                        Culture and the Arts, Inter-Faith
                        Dialogue, and Culture of Peace.
                        </p>
                </div>

                <div id="333" class="tabcontent">
                    <h2>Article 333 Project Development</h2>
                    <p> This presupposes the emergence of a need
                        program that requires a response solution
                        from the Pamantasan ng Lungsod ng Pasig.
                        The major elements would indicate clearly
                        defined objectives, project proponents, their
                        corresponding roles, responsibilities and
                        accountabilities, the project beneficiaries,
                        project duration, localities, funding or
                        budget support, documentation and
                        evaluation and publication of project
                        outcomes. All colleges are expected to
                        include all of these elements in making their
                        extension project proposals for approval of
                        the Academic Council and ultimately for
                        concurrence of the Board of Regents.</p>
                </div>

                <div id="334" class="tabcontent">
                    <h2> Article 334 Academic Linkages </h2>
                    <p> PLP has to link with other universities,
                        government and non-government agencies,
                        private and public institutions both local
                        and abroad which requires the execution of
                        Memorandum of Agreements: render
                        extension service to fellow institutions,
                        exchange ideas, share expertise, coordinate,
                        collaborate and map out plans for future
                        developments. These interlacing endeavors,
                        networking, and linkages among
                        universities will help institutions sustain and
                        enhance their corporate image on social
                        responsibility.</p>
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

            function openArticle(evt, articleName) {
            var i, tabcontent, tablinks;
            tabcontent = document.getElementsByClassName("tabcontent");
            for (i = 0; i < tabcontent.length; i++) {
                tabcontent[i].style.display = "none";
            }
            tablinks = document.getElementsByClassName("tablinks");
            for (i = 0; i < tablinks.length; i++) {
                tablinks[i].className = tablinks[i].className.replace(" active", "");
            }
            document.getElementById(articleName).style.display = "block";
            evt.currentTarget.className += " active";
            }
            
            // Get the element with id="defaultOpen" and click on it
            document.getElementById("defaultOpen").click();
        </script>
    </body>
</html>
