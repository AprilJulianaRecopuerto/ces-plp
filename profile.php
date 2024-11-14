<?php
session_start();
// Database connection details
$servername = "localhost";
$username = "root"; // replace with your database username
$password = ""; // replace with your database password
$dbname = "user_registration";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$userId = isset($_SESSION['userId']) ? $_SESSION['userId'] : null;

if ($userId) {
    // Fetch user details from the database
    $sql = "SELECT username, department, role FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $userId);
    $stmt->execute();
    $stmt->bind_result($name, $department, $role);
    $stmt->fetch();
    $stmt->close();
} else {
    $username = $department = $role = "Not available";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
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
.sidebar {
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

      .logo-container {
          display: flex;
          align-items: center;
          margin-top: 10px;
      }

        .logo-img {
          width: 40%; /* Adjust the size as needed */
          height: 40%;
          margin-right: 20px; /* Space between the logo and text */
          margin-left:30px;
          margin-top: -27px;
        }

        .logo-text {
            font-family: "Saira Condensed", sans-serif;
            font-size: 45px;
            margin-top: 10px;
            line-height: 45px;
        }

        .menu {
            display: flex;
            flex-direction: column;
            margin-top: -5px;
        }

        .menu-item {
            font-family: "Poppins", sans-serif;
            display: flex;
            align-items: center;
            padding: 15px 20px;
            cursor: pointer;
        }

        .menu-item img {
          width: 29px;
          height:27px;
          margin-left: 23px;
        }

        .menu-item .text {
          margin-left:10px;
          margin-top: -4px;
        }

        .menu-item:hover {
            width: 240px;
            margin-left: 10px;
            border-radius: 10px;
            background-color: #22901C;
            transition: 0.3s;
            color: white; /* Ensure the text color is white when hovered */
        }

        .menu-item.active {
          width: 240px;
          margin-left: 10px;
          border-radius: 10px;
          background-color: #22901C !important;
          color: white;
        }

        #profile {
          margin-top: 25px;
        }

        .sign-out {
            font-family: "Poppins", sans-serif;
            display: flex;
            align-items: center;
            padding: 15px 20px;
            cursor: pointer;
        }

        .sign-out img {
            width: 29px;
          height:27px;
            margin-left: 23px;
        }

        .sign-out .text {
            align-items: top;
            margin-left: 10px !important; /* Space between image and text */
        }

        .sign-out:hover {
          width: 240px;
          margin-left: 10px;
          border-radius: 10px;
          background-color: #22901C;
          transition: 0.3s;
          color: white; /* Ensure the text color is white when hovered */
        }

        .content-projectlist {
            margin-left: 250px;
          padding: 22px;
        }

        .projectlist-header{
          font-family: "Poppins", sans-serif;
          display: flex;
          justify-content: space-between;
          align-items: center;
          margin-bottom: 22px;
          margin-left: 65px;
          font-size: 22px;
        }
        .projectlist-header h3 {
          margin: 0;
          font-size: 32px;
          margin-top: 13px;
          margin-bottom: 15px;
        }

        .project-budget-table {
            width: 100%;
            border-collapse: collapse;
            background-color: white; /* Set table background to white */
        }

        .project-budget-table th, .project-budget-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        .project-budget-table th {
            background-color: #4CAF50;
            color: white;
        }

        .project-budget-table tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .project-budget-table tr:hover {
            background-color: #ddd;
        }

        .project-budget-table th, .project-budget-table td {
            padding: 12px 15px;
        }

        #addProjectButton {
            background-color: #4CAF50;
            border: none;
            color: white;
            padding: 10px 10px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin: 4px 2px;
            cursor: pointer;
            border-radius: 4px;
        }

        #addProjectButton:hover {
            background-color: #45a049;
        }

        .expenses-list {
            list-style-type: disc;
            padding-left: 20px;
        }

        .profile-container {
            display: flex;
            align-items: flex-start;
            margin: 20px;
            gap: 20px; /* Space between the items */
        }

        .profile-picture-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            flex: 1;
            max-width: 300px;
        }

        .profile-picture {
            width: 300px;
            height: 300px;
            border-radius: 50%;
            overflow: hidden;
            position: relative;
            margin-left:200%;
            margin-top: 200px;
        }

        .profile-picture img {
            width: 100%;
            height: auto;

        }

        .profile-id {
            margin-top: 10px;
            font-size: 24px; /* Increased font size */
            font-weight: bold;
            text-align: center; /* Center-align ID */
        }

        .profile-info {
            flex: 2;
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 10px;
            width: 100%;
        }

        .profile-info div {
            margin-bottom: 15px;
            font-size: 20px; /* Adjusted font size */
        }

        .profile-info div span {
            font-weight: bold;
        }
    </style>
</head>
<body>
<div class="sidebar">
        <div class="logo-container">
          <img src="images/icoon.png" alt="Logo" class="logo-img">
          <h2 class="logo-text"> CES <br> PLP</h2>
        </div>
        <div class="menu">
          <div class="menu-item" id="dashboard" data-url="dashboard.php">
            <img src="images/home.png">
            <span class="text">Dashboard</span>
          </div>
          <div class="menu-item" id="projectlist" data-url="projectlist.php">
            <img src="images/project-list.png">
            <span class="text">Project List</span>
          </div>
          <div class="menu-item" id="calendar" data-url="calendar.html">
            <img src="images/calendar.png">
            <span class="text">Event Calendar</span>
          </div>
          <div class="menu-item" id="resource" data-url="resource.html">
            <img src="images/resource.png">
            <span class="text">Resource Allocation</span>
          </div>
          <div class="menu-item" id="report" data-url="report.html">
            <img src="images/report.png">
            <span class="text">Report</span>
          </div>
          <div class="menu-item" id="task" data-url="task.php">
            <img src="images/task.png">
            <span class="text">Task Management</span>
          </div>
          <div class="menu-item" id="settings" data-url="settings.html">
            <img src="images/Settings.png">
            <span class="text">Settings</span>
          </div>
          <div class="menu-item" id="profile" data-url="profile.html">
            <img src="images/user.png">
            <span class="text">Profile</span>
          </div>
          <div class="menu-item sign-out" id="logout" data-url="loadingpage.html">
            <img src="images/logout.png">
            <span class="text">Sign Out</span>
          </div>
        </div>
      </div>
    
    <div class="content-projectlist">
        <div class="projectlist-header">
            <h3>Profile</h3>
        </div>
    </div>

    <div class="list-container">    
        <div class="yellow-container">
            <div class="profile-container">
                <div class="profile-picture-container">
                    <div class="profile-picture">
                        <img src="images/user.png" alt="User Picture">
                    </div>
                    <div class="profile-id">ID: <?php echo htmlspecialchars($userId); ?></div>
                </div>
                <div class="profile-info">
                    <div><span>Name:</span> <?php echo htmlspecialchars($username); ?></div>
                    <div><span>Department:</span> <?php echo htmlspecialchars($department); ?></div>
                    <div><span>Role:</span> <?php echo htmlspecialchars($role); ?></div>
                </div>
            </div>
        </div>
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
    </script>

</body>
</html>
