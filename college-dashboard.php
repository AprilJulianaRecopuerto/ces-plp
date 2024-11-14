<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: loginpage.php"); // Redirect to login page if not logged in
    exit();
}

$username = $_SESSION['username'];
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title> CES PLP </title>  

    <!-- The FavIcon of our Website -->
    <link rel="icon" href="images/logoicon.png">

    <style>
      @import url('https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,400;0,600;1,500&display=swap');
      @import url('https://fonts.cdnfonts.com/css/glacial-indifference-2');
      @import url('https://fonts.googleapis.com/css2?family=Saira+Condensed:wght@500&display=swap');

      body {
        margin: 0;
        padding: 0;
        background-color: white;
        overflow-y: scroll;
      }

      body, html {
            margin: 0;
            padding: 0;
            transition: background-color 0.3s, color 0.3s; /* for dark mode */
        }

        /* Dark mode styles */
        body.dark-mode {
            background-color: #333333;
            color: #ffffff;
        }

        body.dark-mode .option {
            background-color: #444444;
        }

        body.dark-mode .toggle-button {
            background-color: #dc3545;
        }

        /* Dark mode styles for the sidebar */
        body.dark-mode .sidebar-col {
            background-color: #444444;
            color: #ffffff;
        }

        body.dark-mode .menu-item-col {
            background-color: #444444;
            color: #ffffff;
        }

        body.dark-mode .menu-item-col:hover {
            background-color: #555555;
        }

        body.dark-mode .project-updates-col  {
            background-color: #444444;
            color: #ffffff;
        }

        body.dark-mode .upcoming-activities-container-col {
            background-color: #444444;
            color: #ffffff;
        }
.sidebar-col {
    width: 300px;
    background-color: #FFF8A5;
    padding: 20px 0;
    box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
    position: fixed;
    left: 10px;
    top: 10px; /* Adjust the top position */
    bottom: 10px; /* Adjust the bottom position */
    border-radius: 23px;
}

.logo-container-col {
    display: flex;
    align-items: center;
    margin-top: 10px;
}

.logo-img-col {
    width: 40%; /* Adjust the size as needed */
    height: 40%;
    margin-right: 20px; /* Space between the logo and text */
    margin-left: 30px;
    margin-top: -27px;
}

.logo-text-col {
    font-family: "Saira Condensed", sans-serif;
    font-size: 45px;
    margin-top: 10px;
    line-height: 45px;
}

.menu-col {
    display: flex;
    flex-direction: column;
    margin-top: -5px;
}

.menu-item-col {
    font-family: "Poppins", sans-serif;
    display: flex;
    align-items: center;
    padding: 15px 20px;
    cursor: pointer;
}

.menu-item-col img {
    width: 29px;
    height: 27px;
    margin-left: 23px;
}

.menu-item-col .text {
    margin-left: 10px;
    margin-top: -4px;
}

.menu-item-col:hover {
    width: 240px;
    margin-left: 10px;
    border-radius: 10px;
    background-color: #22901C;
    transition: 0.3s;
    color: white; /* Ensure the text color is white when hovered */
}


.menu-item-col.active {
    width: 240px;
    margin-left: 10px;
    border-radius: 10px;
    background-color: #22901C !important;
    color: white;
}

#profile-col {
    margin-top: 25px;
}

.sign-out-col {
    font-family: "Poppins", sans-serif;
    display: flex;
    align-items: center;
    padding: 15px 20px;
    cursor: pointer;
}

.sign-out-col img {
    width: 29px;
    height: 27px;
    margin-left: 23px;
}

.sign-out-col .text {
    align-items: top;
    margin-left: 10px !important; /* Space between image and text */
}

.sign-out-col:hover {
    width: 240px;
    margin-left: 10px;
    border-radius: 10px;
    background-color: #22901C;
    transition: 0.3s;
    color: white; /* Ensure the text color is white when hovered */
}

/*Dashboard*/
.content-dashboard-col {
    margin-left: 250px;
    padding: 22px;
}

.over-col {
    font-family: "Poppins", sans-serif;
    font-size: 20px;
    margin-left: 60px;
}

.dashboard-header-col {
    font-family: "Poppins", sans-serif;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 22px;
    margin-left: 65px;
    font-size: 22px;
}

.welcome-container-col {
    display: flex;
    flex-direction: column;
}


.dashboard-header-col h3 {
    margin: 0;
    font-size: 32px;
    margin-top: 13px;
    margin-bottom: 15px;
}

.overview-col {
    display: flex;
    margin-bottom: 5px;
    height: 25%;
    margin-left: 18px;
}

.total-activities-col, .pending-activities-col {
    width: 250px; /* Adjust width as needed */
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    background-color: #ffffff;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    margin-left: 20px; /* Add margin to separate elements */
}

.total-activities-col {
    margin-left: 50px; /* Add margin to separate elements */
}

.total-activities-col img, .pending-activities-col img {
    width: 20%;
    height: 20%;
    margin-top: -20px;
    margin-right: 15px;
}

.total-activities-col h2, .pending-activities-col h2 {
    font-family: "Poppins", sans-serif;
    font-size: 18px;
    margin-left: 15px;
    margin-top: 5px;
    margin-bottom: 3px;
}

.activities-details-col {
    display: flex;
    justify-content: space-between;
}


.total-activities-count-col, .pending-activities-count-col {
    font-family: "Poppins", sans-serif;
    font-size: 25px;
    margin-left: 15px;
    margin-top: 5px;
    margin-bottom: 3px;
}

/* project updates */
.proj-col {
    font-family: "Poppins", sans-serif;
    font-size: 20px;
    margin-left: 60px;
    margin-top: 25px;
}

.project-updates-col {
    font-family: "Poppins", sans-serif;
    background-color: #FAFAFA;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    border: 1px solid #ddd;
    padding: 20px;
    margin: 0 auto;
    width: 520px;
    margin-left: 67px;
    border-radius: 10px;
    height: 380px;
}

.project-updates-col ul {
    list-style: none;
    padding: 0;
    margin: 0;
    margin-top: 15px;
}

.project-updates-col ul li {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.project-updates-col ul li img {
    width: 80px;
    height: 50px;
    border-radius: 50%;
    margin-right: 10px;
}

.project-number-col {
    text-align: center;
}

.project-details-col {
    display: flex;
    flex-direction: column;
    flex-grow: 1;
    margin-left: 10px;
}

.project-name-col {
    font-weight: bold;
    margin-top: 5px;
    margin-left: 10px;
}

.stat-col {
    font-weight: bold;
    margin-bottom: 2px;
    margin-left: 100px;
}

.delayed-col .project-status-col {
    color: orange;
    margin-bottom: 30px;
}

.cancelled-col .project-status-col {
    color: red;
}

.successful-col .project-status-col {
    color: green;
}

.update-col {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 5px;
    border: 1px solid #ddd;
    border-radius: 5px;
    margin-bottom: 10px;
    height: 60px;
}

.update-col.delayed-col {
    background-color: #fff3cd;
    border-color: #ffeeba;
}

.update-col.cancelled-col {
    background-color: #f8d7da;
    border-color: #f5c6cb;
}

.update-col.successful-col {
    background-color: #d4edda;
    border-color: #c3e6cb;
}

.see-all-link-col {
    color: #8A8A8A;
    margin-left: 463px;
    cursor: pointer;
    text-decoration: none;
}

.see-all-link-col:hover {
    color: black;
    text-decoration: underline;
}

        /*upcoming activities*/
        .content-col {
    margin-left: 250px;
    padding: 20px;
    position: relative; /* Ensure the container is positioned relative */
}

.upco-col {
    font-family: "Poppins", sans-serif;
    font-size: 20px;
    margin-left: 925px;
    margin-top: -665px;
}

.upcoming-activities-container-col {
    font-family: "Poppins", sans-serif;
    position: absolute;
    top: 115px;
    right: 20px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    border: 1px solid #ddd;
    border-radius: 5px;
    padding: 30px;
    width: 500px;
    margin-right: 15px;
    margin-top: 50px;
    height: 535px;
    background-color: #FFF8A5;
    overflow-y: auto; /* Add vertical scroll */
}

.upcoming-activities-container-col h2 {
    margin-bottom: 10px;
}

.upcoming-activities-list-col {
    list-style: none;
    padding: 0;
    margin-top: 10px;
}

.upcoming-activities-list-col li {
    padding: 10px;
    border-radius: 5px;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
}

.upcoming-activities-list-col li img {
    width: 80px;
    height: 50px;
    border-radius: 50%;
    margin-right: 10px;
    margin-top: 10px;
}

.upcoming-activities-list-col li .activity-details {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.upcoming-activities-list-col li .activity-details .activity-name {
    font-size: 20px;
    font-weight: 500;
    margin-top: 10px;
    margin-left: -3px;
}

.upcoming-activities-list-col li .activity-details .activity-eta {
    color: #777;
    margin-top: 4px;
    margin-left: -3px;
}

.money-col {
    font-size: 18px;
    color: #089451;
    font-weight: 600;
    margin-top: -12px;
    margin-left: 140px;
}

.see-all-link-act-col {
    color: #8A8A8A;
    margin-left: 430px;
    cursor: pointer;
    text-decoration: none;
}

.see-all-link-act-col:hover {
    color: black;
    text-decoration: underline;
}

.upcoming-activities-container-col::-webkit-scrollbar {
    width: 12px; /* Width of the scrollbar */
    height: 12px; /* Height of the scrollbar (for horizontal scroll) */
}

.upcoming-activities-container-col::-webkit-scrollbar-track {
    background: #f1f1f1; /* Background color of the scrollbar track */
    border-radius: 10px;
}

.upcoming-activities-container-col::-webkit-scrollbar-thumb {
    background-color: #888; /* Color of the scrollbar thumb */
    border-radius: 10px;
    border: 2px solid #f1f1f1; /* Adds padding around the thumb */
}

.upcoming-activities-container-col::-webkit-scrollbar-thumb:hover {
    background-color: green; /* Color of the scrollbar thumb when hovered */
}

    </style>
  </head>

  <body>
      <div class="sidebar-col">
        <div class="logo-container-col">
          <img src="images/icoon.png" alt="Logo" class="logo-img-col">
          <h2 class="logo-text-col"> CES <br> PLP</h2>
        </div>
        <div class="menu-col">
          <div class="menu-item-col" id="dashboard" data-url="dashboard.php">
            <img src="images/home.png">
            <span class="text">Dashboard</span>
          </div>
          <div class="menu-item-col" id="resource" data-url="resource.html">
            <img src="images/resource.png">
            <span class="text">Resource Allocation</span>
          </div>
          <div class="menu-item-col" id="task" data-url="task.php">
            <img src="images/task.png">
            <span class="text">Task Management</span>
          </div>
          <div class="menu-item-col" id="settings" data-url="settings.html">
            <img src="images/Settings.png">
            <span class="text">Settings</span>
          </div>
          <div class="menu-item-col" id="profile" data-url="profile.html">
            <img src="images/user.png">
            <span class="text">Profile</span>
          </div>
          <div class="menu-item-col sign-out" id="logout" data-url="loadingpage.html">
            <img src="images/logout.png">
            <span class="text">Sign Out</span>
          </div>
        </div>
      </div>
    
      <div class="content-dashboard-col">
          <div class="dashboard-header-col">
            <div class="welcome-container-col">
            <h3>Hello, <?php echo htmlspecialchars($username); ?></h3>
          </div>
        </div>
          
        
          
          <h2 class="proj-col"> Project Updates </h2>
          <div class="project-updates-col">
            <div class="updates-header-col">
              <span class="see-all-link-col">See all</span>
            </div>
            <ul>
              <li class="update delayed-col">
                <span class="project-number-col">1.</span>
                <img src="images/samplepic.jpg" alt="">
                <div class="project-details-col">
                  <span class="project-name-col">Health and Wellness Fair</span>
              </div>
              <div class="stat-col">
                  <span class="project-status-col">Delayed</span>
                </div>
              </li>
              <li class="update cancelled-col">
                <span class="project-number-col">2.</span>
                <img src="images/samplepic.jpg" alt="">
                <div class="project-details-col">
                  <span class="project-name-col">Health and Wellness Fair</span>
              </div>
              <div class="stat-col">
                  <span class="project-status-col">Cancelled</span>
                </div>
              </li>
              <li class="update successful-col">
                <span class="project-number-col">3.</span>
                <img src="images/samplepic.jpg" alt="">
                <div class="project-details-col">
                  <span class="project-name-col">Health and Wellness Fair</span>
              </div>
              <div class="stat-col">
                  <span class="project-status-col">Successful</span>
                </div>
              </li>
              <li class="update successful-col">
                <span class="project-number-col">4.</span>
                <img src="images/samplepic.jpg" alt="">
                <div class="project-details-col">
                  <span class="project-name-col">Health and Wellness Fair</span>
              </div>
              <div class="stat-col">
                  <span class="project-status-col">Successful</span>
                </div>
                </li>
              </ul>
          </div>
        </div>
        
        <h2 class="upco-col">Upcoming Activities</h2>
        <div class="upcoming-activities-container-col">
          <span class="see-all-link-act-col">See all</span>

            <ul class="upcoming-activities-list-col">
              <li>
                <span class="activity-number-col">1.</span>
                <img src="images/samplepic.jpg" alt="Activity Image">
                <div class="activity-details-col">
                  <span class="activity-name-col">Blood Donation</span>
                  <span class="activity-eta-col">ETA June 21, 2023</span>
                </div>
                <span class="money-col">$25,000</span>
              </li>
              <li>
                <span class="activity-number-col">2.</span>
                <img src="images/samplepic.jpg" alt="Activity Image">
                <div class="activity-details-col">
                  <span class="activity-name-col">Arts Festival</span>
                  <span class="activity-eta-col">ETA June 23, 2023</span>
                </div>
                <span class="money-col">$20,000</span>
              </li>
              <li>
                <span class="activity-number-col">3.</span>
                <img src="images/samplepic.jpg" alt="Activity Image">
                <div class="activity-details-col">
                  <span class="activity-name-col">Charity Gala</span>
                  <span class="activity-eta-col">ETA July 10, 2023</span>
                </div>
                <span class="money-col">$30,000</span>
              </li>
              <li>
                <span class="activity-number-col">4.</span>
                <img src="images/samplepic.jpg" alt="Activity Image">
                <div class="activity-details-col">
                  <span class="activity-name-col">Community Cleanup</span>
                  <span class="activity-eta-col">ETA July 15, 2023</span>
                </div>
                <span class="money-col">$15,000</span>
              </li>
              <li>
                <span class="activity-number-col">5.</span>
                <img src="images/samplepic.jpg" alt="Activity Image">
                <div class="activity-details-col">
                  <span class="activity-name-col">Educational Workshop</span>
                  <span class="activity-eta-col">ETA August 5, 2023</span>
                </div>
                <span class="money-col">$12,000</span>
              </li>
          <li>
                <span class="activity-number-col">5.</span>
                <img src="images/samplepic.jpg" alt="Activity Image">
                <div class="activity-details-col">
                  <span class="activity-name-col">Educational Workshop</span>
                  <span class="activity-eta-col">ETA August 5, 2023</span>
                </div>
                <span class="money-col">$12,000</span>
              </li>
          <li>
                <span class="activity-number-col">5.</span>
                <img src="images/samplepic.jpg" alt="Activity Image">
                <div class="activity-details-col">
                  <span class="activity-name-col">Educational Workshop</span>
                  <span class="activity-eta-col">ETA August 5, 2023</span>
                </div>
                <span class="money-col">$12,000</span>
              </li>
          <li>
                <span class="activity-number">5.</span>
                <img src="images/samplepic.jpg" alt="Activity Image">
                <div class="activity-details-col">
                  <span class="activity-name-col">Educational Workshop</span>
                  <span class="activity-eta-col">ETA August 5, 2023</span>
                </div>
                <span class="money-col">$12,000</span>
              </li>
            </ul>
      </div>
    
      <script>
        // Add this JavaScript at the end of your HTML file or in a separate JS file
        document.addEventListener("DOMContentLoaded", function() {
          const menuItems = document.querySelectorAll(".menu-item");

          menuItems.forEach(item => {
            item.addEventListener("click", function() {
              // Remove active class from all menu items
              menuItems.forEach(i => i.classList.remove("active"));
              
              // Add active class to the clicked menu item
              item.classList.add("active");

              // Optionally, you can also handle the navigation here
              const url = item.getAttribute("data-url");
              window.location.href = url;
            });
          });
        });


    document.addEventListener('DOMContentLoaded', () => {
        const toggleButton = document.getElementById('toggle-dark-mode');

        // Check local storage for the current mode
        if (localStorage.getItem('darkMode') === 'enabled') {
            document.body.classList.add('dark-mode');
            if (toggleButton) {
                toggleButton.textContent = 'OFF';
                toggleButton.style.backgroundColor = '#dc3545';
            }
        } else {
            document.body.classList.remove('dark-mode');
            if (toggleButton) {
                toggleButton.textContent = 'ON';
                toggleButton.style.backgroundColor = '#28a745';
            }
        }

        if (toggleButton) {
            toggleButton.addEventListener('click', () => {
                document.body.classList.toggle('dark-mode');
                if (document.body.classList.contains('dark-mode')) {
                    toggleButton.textContent = 'OFF';
                    toggleButton.style.backgroundColor = '#dc3545';
                    localStorage.setItem('darkMode', 'enabled');
                } else {
                    toggleButton.textContent = 'ON';
                    toggleButton.style.backgroundColor = '#28a745';
                    localStorage.setItem('darkMode', 'disabled');
                }
            });
        }
    });
</script>

  </body>
</html>

