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

            *{
                box-sizing: border-box;
            }

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

            .programs-act h1 {
                font-family: 'Glacial Indifference', sans-serif;
                font-size: 50px;
                text-align: center;
                position: relative;
                margin-bottom:40px;
            }

            .wrapper{
                margin: 100px auto;
                max-width: 1100px;
                margin-top: 10px !important;
            }

            .wrapper nav{
                display: flex;
                justify-content: center;
                margin-right:15px;
            }

            .wrapper .items{
                display: flex;
                max-width: 720px;
                width: 100%;
                justify-content: space-between;
            }

            .items span{
                margin-right:5px;
                padding: 7px 25px;
                font-size: 18px;
                font-weight: 500;
                cursor: pointer;
                color: black;
                border-radius: 50px;
                border: 2px solid #b7e4c7;
                transition: all 0.3s ease;
            }

            .items span.active,
            .items span:hover{
                color: #fff;
                background: #089451;
            }

            .gallery{
                display: flex;
                flex-wrap: wrap;
                margin-top: 30px;
            }

            .gallery .image{
                width: calc(100% / 3);
                padding: 2px;
            }

            .gallery .image span {
                display: flex;
                width: 100%;
                overflow: hidden;
                position: relative;
                padding-top: 70%; /* Aspect ratio 1:1 (square) */
            }

            .gallery .image img {
                position: absolute;
                border-radius:10px;
                top: 0;
                left: 0;
                width: 95%;
                height: 95%;
                object-fit: cover; /* Ensures image covers the entire container */
                vertical-align: middle;
                transition: all 0.3s ease;
            }

            .gallery .image:hover img{
                transform: scale(1.1);
            }

            .gallery .image.hide{
                display: none;
            }

            .gallery .image.show{
                animation: animate 0.4s ease;
            }

            @keyframes animate {
            0%{
                transform: scale(0.5);
            }
            100%{
                transform: scale(1);
            }
            }

            .preview-box{
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%) scale(0.9);
                background: #fff;
                max-width: 600px;
                width: 100%;
                z-index: 5;
                opacity: 0;
                pointer-events: none;
                border-radius: 3px;
                padding: 0 5px 5px 5px;
                box-shadow: 0px 0px 15px rgba(0,0,0,0.2);
            }

            .preview-box.show{
                opacity: 1;
                pointer-events: auto;
                transform: translate(-50%, -50%) scale(1);
                transition: all 0.3s ease;
            }

            .preview-box .details{
                padding: 13px 15px 13px 10px;
                display: flex;
                align-items: center;
                justify-content: space-between;
            }

            .details .title{
                display: flex;
                font-size: 18px;
                font-weight: 400;
            }

            .details .title p{
                font-weight: 500;
                margin-left: 5px;
            }

            .details .icon{
                color: #007bff;
                font-style: 22px;
                cursor: pointer;
            }
            .preview-box .image-box{
                width: 100%;
                display: flex;
            }

            .image-box img{
                width: 100%;
                border-radius: 0 0 3px 3px;
            }

            .shadow{
                position: fixed;
                left: 0;
                top: 0;
                height: 100%;
                width: 100%;
                z-index: 2;
                display: none;
                background: rgba(0,0,0,0.4);
            }

            .shadow.show{
                display: block;
            }

            @media (max-width: 1000px) {
                .gallery .image {
                    width: calc(100% / 3);
                }
            }

            @media (max-width: 800px) {
                .gallery .image {
                    width: calc(100% / 2);
                }
            }

            @media (max-width: 700px) {
            .wrapper nav .items{
                max-width: 600px;
            }

            nav .items span{
                padding: 7px 15px;
            }
            }

            @media (max-width: 600px) {
            .wrapper{
                margin: 30px auto;
            }

            .wrapper nav .items{
                flex-wrap: wrap;
                justify-content: center;
            }

            nav .items span{
                margin: 5px;
            }

            .gallery .image{
                width: 50%;
            }
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

        <div class="programs-act">
            <h1> Programs and Activities </h1>
            <div class="wrapper">
                <!-- filter Items -->
                <nav>
                    <div class="items">
                        <span class="item active" data-name="all">All</span>
                        <span class="item" data-name="CAS">CAS</span>
                        <span class="item" data-name="CBA">CBA</span>
                        <span class="item" data-name="CCS">CCS</span>
                        <span class="item" data-name="COED">COED</span>
                        <span class="item" data-name="COE">COE</span>
                        <span class="item" data-name="CIHM">CIHM</span>
                        <span class="item" data-name="CON">CON</span>
                    </div>
                </nav>
                <!-- filter Images -->
                <div class="gallery">
                <div class="image" data-name="CAS"><span><img src="images/cas.jpg" alt=""></span></div>
                <div class="image" data-name="CAS"><span><img src="images/cas2.jpg" alt=""></span></div>
                <div class="image" data-name="CAS"><span><img src="images/cas3.jpg" alt=""></span></div>
                <div class="image" data-name="CAS"><span><img src="images/cas4.jpg" alt=""></span></div>

                <div class="image" data-name="CBA"><span><img src="images/cba.jpg" alt=""></span></div>
                <div class="image" data-name="CBA"><span><img src="images/cba2.jpg" alt=""></span></div>
                <div class="image" data-name="CBA"><span><img src="images/cba3.jpg" alt=""></span></div>
                <div class="image" data-name="CBA"><span><img src="images/cba4.jpg" alt=""></span></div>
                <div class="image" data-name="CBA"><span><img src="images/cba5.jpg" alt=""></span></div>

                <div class="image" data-name="CCS"><span><img src="images/ccs.jpg" alt=""></span></div>
                <div class="image" data-name="CCS"><span><img src="images/ccs2.jpg" alt=""></span></div>
                <div class="image" data-name="CCS"><span><img src="images/ccs3.jpg" alt=""></span></div>
                <div class="image" data-name="CCS"><span><img src="images/ccs4.jpg" alt=""></span></div>
                <div class="image" data-name="CCS"><span><img src="images/ccs5.jpg" alt=""></span></div>
                <div class="image" data-name="CCS"><span><img src="images/ccs6.jpg" alt=""></span></div>
                <div class="image" data-name="CCS"><span><img src="images/ccs7.jpg" alt=""></span></div>

                <div class="image" data-name="COED"><span><img src="images/coed.jpg" alt=""></span></div>

                <div class="image" data-name="COE"><span><img src="images/ccs.jpg" alt=""></span></div>

                <div class="image" data-name="CIHM"><span><img src="images/cihm.jpg" alt=""></span></div>
                <div class="image" data-name="CIHM"><span><img src="images/cihm2.jpg" alt=""></span></div>
                <div class="image" data-name="CIHM"><span><img src="images/cihm3.jpg" alt=""></span></div>
                <div class="image" data-name="CIHM"><span><img src="images/cihm4.jpg" alt=""></span></div>
                <div class="image" data-name="CIHM"><span><img src="images/cihm5.jpg" alt=""></span></div>
                <div class="image" data-name="CIHM"><span><img src="images/cihm6.jpg" alt=""></span></div>
                <div class="image" data-name="CIHM"><span><img src="images/cihm7.jpg" alt=""></span></div>

                <div class="image" data-name="CON"><span><img src="images/nursing.jpg" alt=""></span></div>
                <div class="image" data-name="CON"><span><img src="images/nursing2.jpg" alt=""></span></div>
                <div class="image" data-name="CON"><span><img src="images/nursing3.jpg" alt=""></span></div>
                <div class="image" data-name="CON"><span><img src="images/nursing4.jpg" alt=""></span></div>
                <div class="image" data-name="CON"><span><img src="images/nursing5.jpg" alt=""></span></div>
                <div class="image" data-name="CON"><span><img src="images/nursing6.jpg" alt=""></span></div>
            </div>
        </div>
        <!-- fullscreen img preview box -->
        <div class="preview-box">
            <div class="details">
            <span class="title">Category: <p></p></span>
            <span class="icon fas fa-times"></span>
            </div>
            <div class="image-box"><img src="" alt=""></div>
        </div>
        <div class="shadow"></div>

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

            //selecting all required elements
            const filterItem = document.querySelector(".items");
            const filterImg = document.querySelectorAll(".gallery .image");

            window.onload = ()=>{ //after window loaded
            filterItem.onclick = (selectedItem)=>{ //if user click on filterItem div
                if(selectedItem.target.classList.contains("item")){ //if user selected item has .item class
                filterItem.querySelector(".active").classList.remove("active"); //remove the active class which is in first item
                selectedItem.target.classList.add("active"); //add that active class on user selected item
                let filterName = selectedItem.target.getAttribute("data-name"); //getting data-name value of user selected item and store in a filtername variable
                filterImg.forEach((image) => {
                    let filterImges = image.getAttribute("data-name"); //getting image data-name value
                    //if user selected item data-name value is equal to images data-name value
                    //or user selected item data-name value is equal to "all"
                    if((filterImges == filterName) || (filterName == "all")){
                    image.classList.remove("hide"); //first remove the hide class from the image
                    image.classList.add("show"); //add show class in image
                    }else{
                    image.classList.add("hide"); //add hide class in image
                    image.classList.remove("show"); //remove show class from the image
                    }
                });
                }
            }
            for (let i = 0; i < filterImg.length; i++) {
                filterImg[i].setAttribute("onclick", "preview(this)"); //adding onclick attribute in all available images
            }
            }

            //fullscreen image preview function
            //selecting all required elements
            const previewBox = document.querySelector(".preview-box"),
            categoryName = previewBox.querySelector(".title p"),
            previewImg = previewBox.querySelector("img"),
            closeIcon = previewBox.querySelector(".icon"),
            shadow = document.querySelector(".shadow");

            function preview(element){
            //once user click on any image then remove the scroll bar of the body, so user cant scroll up or down
            document.querySelector("body").style.overflow = "hidden";
            let selectedPrevImg = element.querySelector("img").src; //getting user clicked image source link and stored in a variable
            let selectedImgCategory = element.getAttribute("data-name"); //getting user clicked image data-name value
            previewImg.src = selectedPrevImg; //passing the user clicked image source in preview image source
            categoryName.textContent = selectedImgCategory; //passing user clicked data-name value in category name
            previewBox.classList.add("show"); //show the preview image box
            shadow.classList.add("show"); //show the light grey background
            closeIcon.onclick = ()=>{ //if user click on close icon of preview box
                previewBox.classList.remove("show"); //hide the preview box
                shadow.classList.remove("show"); //hide the light grey background
                document.querySelector("body").style.overflow = "auto"; //show the scroll bar on body
            }
            }
        </script>
    </body>
</html>