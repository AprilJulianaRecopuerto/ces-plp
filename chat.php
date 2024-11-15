<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: roleaccount.php");
    exit;
}

// Database connection to `messages` database
$servername = "uoa25ublaow4obx5.cbetxkdyhwsb.us-east-1.rds.amazonaws.com";
$username = "lcq4zy2vi4302d1q";
$password = "xswigco0cdxdi5dd";
$dbname = "kup80a8cc3mqs4ao"; // Changed database to `messages`

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch user role
$userRoleSql = "SELECT roles FROM user_registration.users WHERE username = ?";
$roleStmt = $conn->prepare($userRoleSql);
$roleStmt->bind_param("s", $_SESSION['username']);
$roleStmt->execute();
$roleResult = $roleStmt->get_result();
$userRole = $roleResult->fetch_assoc()['roles'];
$roleStmt->close();

// Handle new message submission to `sent_messages` table
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['message'])) {
    $sender = $_SESSION['username'];
    $message = $_POST['message'];

    $insertSql = "INSERT INTO sent_messages (sender, role, message, timestamp) VALUES (?, ?, ?, NOW())"; // Adjusted to include role
    $insertStmt = $conn->prepare($insertSql);
    $insertStmt->bind_param("sss", $sender, $userRole, $message); // Binding role

    if (!$insertStmt->execute()) {
        echo "Error preparing insert statement: " . $insertStmt->error;
    }
    $insertStmt->close();
}

// Handle delete message request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_message_id'])) {
    $messageId = $_POST['delete_message_id'];

    // Delete the message if it belongs to the current user
    $deleteSql = "DELETE FROM sent_messages WHERE id = ? AND sender = ?";
    $deleteStmt = $conn->prepare($deleteSql);
    $deleteStmt->bind_param("is", $messageId, $_SESSION['username']);
    $deleteStmt->execute();
    $deleteStmt->close();
}

// Fetch all messages from `sent_messages`
$chatMessages = [];
$fetchSql = "
    SELECT sent_messages.*, 
           IF(users.roles IS NOT NULL, users.roles, colleges.role) AS role,
           IF(sent_messages.sender = ?, 'user', 'other') AS message_type
    FROM sent_messages
    LEFT JOIN user_registration.colleges ON sent_messages.sender = colleges.uname
    LEFT JOIN user_registration.users ON sent_messages.sender = users.username
    ORDER BY sent_messages.timestamp";

$fetchStmt = $conn->prepare($fetchSql);
$fetchStmt->bind_param("s", $_SESSION['username']);
$fetchStmt->execute();
$messageResult = $fetchStmt->get_result();

while ($msgRow = $messageResult->fetch_assoc()) {
    $chatMessages[] = $msgRow;
}
$fetchStmt->close();

// Close connections
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>CES PLP</title>

        <link rel="icon" href="images/icoon.png">

        <!-- SweetAlert CSS and JavaScript -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

        <style>
            @import url('https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,400;0,600;1,500&display=swap');
            @import url('https://fonts.cdnfonts.com/css/glacial-indifference-2');
            @import url('https://fonts.googleapis.com/css2?family=Saira+Condensed:wght@500&display=swap');

            body {
                margin: 0;
                background-color: #F6F5F5; /* Light gray background color */
            }

            .navbar {
                background-color: #E7F0DC; /* Dirty white color */
                color: black;
                padding: 10px;
                display: flex;
                justify-content: space-between; /* Space between heading and profile */
                align-items: center;
                position: fixed;
                width: calc(96.2% - 270px); /* Adjusted width considering the sidebar */
                height: 80px;
                margin-left: 320px; /* Align with the sidebar */
                border-radius: 10px;
                z-index: 5;
                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2); /* Added box shadow */
            }

            .navbar h2 {
                font-family: "Glacial Indifference", sans-serif;
                margin: 0; /* Remove default margin */
                font-size: 32px; /* Adjust font size if needed */
                color: black; /* Set text color */
                margin-left: 20px;
            }

            .profile {
                position: relative;
                display: flex;
                align-items: center;
                cursor: pointer;
                margin-right: 20px; /* Space from the right edge */
            }

            .profile img, .profile-placeholder {
                width: 50px;
                height: 50px;
                border-radius: 50%;
                margin-right: 10px;
            }

            .profile-placeholder {
                font-family: "Poppins", sans-serif;
                width: 50px; /* Adjust as needed */
                height: 50px;
                border-radius: 50%;
                background-color: #ccc; /* Placeholder background color */
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 24px; /* Adjust text size */
                color: green;
                font-weight: bold;
                margin-right: 20px; /* Space between profile picture and name */
            }

            span {
                font-family: "Poppins", sans-serif;
                font-size: 17px;
                color: black; /* Set text color */
                white-space: nowrap; /* Prevent line breaks */
                overflow: hidden; /* Hide overflow */
                text-overflow: ellipsis; /* Show ellipsis if the text overflows */
                flex-grow: 1; /* Allow the username to take available space */
            }

            .dropdown-icon {
                width:22px !important; /* Adjust size of the down-arrow icon */
                height: 15px !important;
                margin-left: 10px; /* Space between name and icon */
            }

            .dropdown-menu {
                font-family: "Poppins", sans-serif;
                display: none; /* Hidden by default */
                position: absolute;
                width: 198px;
                top: 60px; /* Adjust based on the profile's height */
                right: 0;
                background-color: white;
                border: 1px solid #ccc;
                border-radius: 10px;
                padding: 10px;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                z-index: 1;
            }

            .dropdown-menu a {
                text-decoration: none;
                border-radius: 10px;
                color: black;
                display: block;
                padding: 10px;
            }

            .dropdown-menu a:hover {
                background-color: #218838;
                color: white;
            }

            .sidebar {
                position: fixed;
                top: 0;
                left: 0;
                height: 100%;
                width: 278px;
                background-color: #FFF8A5; /* Light yellow */
                color: black;
                padding: 20px;
                z-index: 1000;
                box-shadow: 2px 0 5px rgba(0, 0, 0, 0.2); /* Added box shadow */
            }

            .logo {
                display: flex;
                align-items: center;
                margin-bottom: 25px; /* Increased margin bottom */
            }

            .logo img {
                height: 80px; /* Increased logo size */
                margin-left: 25px; /* Adjusted margin */
            }

            .logo span {
                font-size: 30px; /* Increased font size */
                margin-left: -15px;
                font-family: 'Glacial Indifference', sans-serif;
                font-weight: bold;
            }

            .menu {
                list-style: none;
                padding: 0;
                margin: 0;
            }

            .menu li {
                margin: 6px; /* Increased margin for spacing between items */
                display: flex;
                align-items: center;
            }

            .menu a {
                color: black;
                text-decoration: none;
                display: flex;
                align-items: center;
                padding: 10.5px; /* Increased padding for better click area */
                border-radius: 5px; /* Increased border-radius for rounded corners */
                width: 94%;
                font-size: 17px; /* Increased font size */
                font-family: 'Poppins', sans-serif;
            }

            .menu a:hover {
                background-color: #22901C;
                transition: 0.3s;
                color: white; /* Ensure the text color is white when hovered */
            }

            /* Style the sidenav links and the dropdown button */
            .menu .dropdown-btn {
                list-style: none;
                padding: 0;
                margin: 0;
                text-decoration: none !important;
                display: flex;
                align-items: center;
                padding: 10.5px; /* Increased padding for better click area */
                border-radius: 5px; /* Increased border-radius for rounded corners */
                width: 100%;
                font-size: 17px; /* Increased font size */
                font-family: 'Poppins', sans-serif;
                background-color: transparent; /* Set background to transparent */
                border: none; /* Remove border */
                cursor: pointer; /* Change cursor to pointer */
            }

            /* On mouse-over */
            .menu .dropdown-btn:hover {
                background-color: #22901C;
                transition: 0.3s;
                color: white;
            }

            .dropdown-btn img {
                height: 30px; /* Increased icon size */
                margin-left: 6px; /* Adjusted space between icon and text */
            }

            /* Dropdown container (hidden by default). Optional: add a lighter background color and some left padding to change the design of the dropdown content */
            .dropdown-container {
                display: none;
                padding-left: 8px;
                margin-top: -2px;
                width: 85%;
                margin-left: 25px;
                margin-bottom: -5px;
            }

            /* Optional: Style the caret down icon */
            .fa-chevron-down {
                float: right;
                margin-left: 15px;
            }
            .menu img {
                height: 30px; /* Increased icon size */
                margin-right: 15px; /* Adjusted space between icon and text */
            }

            .menu li a.active {
                background-color: green; /* Change background color */
                color: white; /* Change text color */
            }

            .content {
                margin-left: 320px;
                padding: 20px;
                overflow-y: hidden !important;
            }

            .chat-window {
                font-family: "Poppins", sans-serif;
                border: 1px solid #ccc;
                border-radius: 10px;
                padding: 10px;
                height: 350px; /* Height of the chat window */
                overflow-y: auto; /* Allow scrolling */
                background-color: #fff;
                margin-top: 120px;
                margin-bottom: 20px; /* Space between chat and input */
            }

            .message-input {
                font-family: "Poppins", sans-serif;
                gap: 10px; /* Optional: Adjust spacing between textarea and button */
                padding: 10px;
                background-color: #f1f1f1; /* Optional background color */
                border-radius: 8px;
            }

            .message-input textarea {
                font-family: "Poppins", sans-serif;
                width: 100%;
                height: 100px;
                padding: 8px;
                resize: none;
                border: 1px solid #ccc;
                border-radius: 4px;
                box-sizing: border-box;
                font-size: 14px;
            }

            .message-input button {
                font-family: "Poppins", sans-serif;
                padding: 8px 16px;
                background-color: #4CAF50;
                color: white;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                font-size: 14px;
            }

            .message-input button:hover {
                background-color: #45a049;
            }

            .message {
                margin: 5px 0;
                padding: 10px;
                border-radius: 5px;
                background-color: #f1f1f1; /* Light gray for message background */
            }

            .message.user {
                background-color: #d1e7dd; /* Light green for user's messages */
                text-align: right; /* Align user's messages to the right */
            }

            .message.other {
                background-color: #f1f1f1; /* Gray for other messages */
                text-align: left; /* Align other messages to the left */
            }

            .chat-window small {
                margin-right: 9px;
            }
            
            /* Chat styles */
            .navbar .profile-container {
                display: flex;
                align-items: center;
            }

            .chat-icon {
                margin-right: 15px;
                font-size: 20px;
                color: #333;
                text-decoration: none;
                position: relative; /* To position the badge correctly */
            }

            .notification-badge {
                display: inline-block;
                background-color: red; /* Change this to your preferred color */
                color: white;
                border-radius: 50%;
                width: 20px; /* Width of the badge */
                height: 20px; /* Height of the badge */
                text-align: center;
                font-weight: bold;
                position: absolute; /* Position it relative to the chat icon */
                top: -5px; /* Adjust as needed */
                right: -10px; /* Adjust as needed */
                font-size: 14px; /* Size of the exclamation point */
            }

            .delete-btn {
                font-family: "Poppins", sans-serif;
                background-color: #ff4d4d;
                color: white;
                padding: 8px 12px;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                transition: background-color 0.3s;
            }

            .delete-btn:hover {
                background-color: #ff3333;
            }

            .custom-swal-popup {
                font-family: 'Poppins', sans-serif;
                font-size: 16px; /* Increase the font size */
                width: 400px !important; /* Set a larger width */
            }

            .custom-swal-confirm {
                font-family: 'Poppins', sans-serif;
                font-size: 17px;
                background-color: #089451;
                border: 0.5px #089451 !important;
                color: #fff;
                border-radius: 10px;
                cursor: pointer;
                outline: none !important; /* Remove default focus outline */
            }

            .custom-swal-cancel {
                font-family: 'Poppins', sans-serif;
                font-size: 17px;
                background-color: #e74c3c;
                color: #fff;
                border-radius: 10px;
                cursor: pointer;
                outline: none; /* Remove default focus outline */
            }

            .custom-error-popup {
                font-family: 'Poppins', sans-serif;
                width: 400px !important; /* Set a larger width */
            }

            .custom-error-confirm {
                font-family: 'Poppins', sans-serif;
                font-size: 17px;
                background-color: #e74c3c;
                color: #fff;
                border-radius: 10px;
                cursor: pointer;
                outline: none; /* Remove default focus outline */
            }
        </style>
    </head>

    <body>
        <nav class="navbar">
            <h2>Chat</h2> 

            <div class="profile" id="profileDropdown">
                <?php
                    // Check if a profile picture is set in the session
                    if (!empty($_SESSION['pictures'])) {
                        echo '<img src="' . $_SESSION['pictures'] . '" alt="Profile Picture">';
                    } else {
                        // Get the first letter of the username for the placeholder
                        $firstLetter = strtoupper(substr($_SESSION['username'], 0, 1));
                        echo '<div class="profile-placeholder">' . $firstLetter . '</div>';
                    }
                ?>

                <span class="username"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
            
                <i class="fa fa-chevron-down dropdown-icon"></i>
                <div class="dropdown-menu">
                    <a href="your-profile.php">Profile</a>
                    <a class="signout" href="roleaccount.php" onclick="confirmLogout(event)">Sign out</a>
                </div>
            </div>
        </nav>
    
        <div class="sidebar">
            <div class="logo">
                <img src="images/logo.png" alt="Logo">
            </div>

            <ul class="menu">
                <li><a href="admin-dash.php"><img src="images/home.png">Dashboard</a></li>
                <li><a href="admin-projlist.php"><img src="images/project-list.png">Project List</a></li>
                <li><a href="admin-calendar.php"><img src="images/calendar.png">Event Calendar</a></li>

                <!-- Dropdown for Resource Utilization -->
                <button class="dropdown-btn">
                    <img src="images/resource.png"> Resource Utilization
                    <i class="fas fa-chevron-down"></i> <!-- Dropdown icon -->
                </button>
                <div class="dropdown-container">
                    <a href="admin-tor.php">Term of Reference</a>
                    <a href="admin-requi.php">Requisition</a>
                    <a href="admin-venue.php">Venue</a>
                </div>

                <li><a href="admin-budget-utilization.php"><img src="images/budget.png">Budget Allocation</a></li>

                <!-- Dropdown for Task Management -->
                <button class="dropdown-btn">
                    <img src="images/task.png">Task Management
                    <i class="fas fa-chevron-down"></i> <!-- Dropdown icon -->
                </button>
                <div class="dropdown-container">
                    <a href="admin-task.php">Upload Files</a>
                    <a href="admin-mov.php">Mode of Verification</a>
                </div>

                <li><a href="responses.php"><img src="images/feedback.png">Responses</a></li>

                <!-- Dropdown for Audit Trails -->
                <button class="dropdown-btn">
                    <img src="images/logs.png"> Audit Trails
                    <i class="fas fa-chevron-down"></i> <!-- Dropdown icon -->
                </button>
                <div class="dropdown-container">
                    <a href="admin-login.php">Log In History</a>
                    <a href="admin-activitylogs.php">Activity Logs</a>
                </div>
            </ul>
        </div>
    
        <div class="content">
            <div class="chat-window" id="chatWindow">
                <?php foreach ($chatMessages as $chatMessage): ?>
                    <div class="message <?php echo ($chatMessage['message_type'] == 'user') ? 'user' : 'other'; ?>" 
                        style="text-align: <?php echo ($chatMessage['message_type'] == 'user') ? 'right' : 'left'; ?>;">
                        <strong><?php echo htmlspecialchars($chatMessage['role']); ?>:</strong> <!-- Display the sender's role -->
                        <p><?php echo htmlspecialchars($chatMessage['message']); ?></p>
                        <small><?php echo date('F j, Y || h:i A', strtotime($chatMessage['timestamp'])); ?></small>
                        
                <?php if ($chatMessage['sender'] == $_SESSION['username']): ?>
                    <!-- Delete button -->
                    <form method="POST" action="chat.php" style="display:inline;" id="deleteForm_<?php echo $chatMessage['id']; ?>">
                        <input type="hidden" name="delete_message_id" value="<?php echo $chatMessage['id']; ?>">
                        <button type="button" class="delete-btn">Delete</button>
                    </form>

                <?php endif; ?>
            </div>

                <?php endforeach; ?>
            </div>

            <div class="message-input">
                <form method="POST" action="chat.php" style="width: 100%;">
                    <textarea name="message" placeholder="Type your message..." required></textarea>
                    <button type="submit">Send</button>
                </form>
            </div>
        </div>

        <script>
             // Function to confirm the deletion of a message
            function confirmDelete(chatMessageId) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "Do you really want to delete this message?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it!',
                    customClass: {
                        popup: 'custom-swal-popup',
                        confirmButton: 'custom-swal-confirm',
                        cancelButton: 'custom-swal-cancel'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Submit the form for deleting the message
                        document.getElementById('deleteForm_' + chatMessageId).submit();
                    }
                });
            }

            // Dropdown Menu Toggle
            document.getElementById('profileDropdown').addEventListener('click', function() {
                var dropdownMenu = document.querySelector('.dropdown-menu');
                dropdownMenu.style.display = dropdownMenu.style.display === 'block' ? 'none' : 'block';
            });
            
            // Optional: Close the dropdown if clicking outside the profile area
            window.onclick = function(event) {
                if (!event.target.closest('#profileDropdown')) {
                    var dropdownMenu = document.querySelector('.dropdown-menu');
                    if (dropdownMenu.style.display === 'block') {
                        dropdownMenu.style.display = 'none';
                    }
                }
            };

            var dropdowns = document.getElementsByClassName("dropdown-btn");

            for (let i = 0; i < dropdowns.length; i++) {
                dropdowns[i].addEventListener("click", function () {
                    // Close all dropdowns first
                    let dropdownContents = document.getElementsByClassName("dropdown-container");
                    for (let j = 0; j < dropdownContents.length; j++) {
                        dropdownContents[j].style.display = "none";
                    }

                    // Toggle the clicked dropdown's visibility
                    let dropdownContent = this.nextElementSibling;
                    if (dropdownContent.style.display === "block") {
                        dropdownContent.style.display = "none";
                    } else {
                        dropdownContent.style.display = "block";
                    }
                });
            }

            // Confirmation for logout
            function confirmLogout(event) {
                event.preventDefault(); // Prevent the default link behavior
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'Do you really want to log out?',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, log me out',
                    customClass: {
                        popup: 'custom-swal-popup',
                        confirmButton: 'custom-swal-confirm',
                        cancelButton: 'custom-swal-cancel'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'roleaccount.php'; // Redirect to the logout page
                    }
                });
            }
            
           // Auto-scroll to the bottom of the chat window when the page loads or chat updates
        const chatWindow = document.getElementById('chatWindow');
        chatWindow.scrollTop = chatWindow.scrollHeight; // Scroll to the bottom

        // Function to fetch and update messages
function fetchMessages() {
    fetch('fetch_messages.php')
        .then(response => response.json())
        .then(data => {
            const chatWindow = document.getElementById('chatWindow');
            chatWindow.innerHTML = ''; // Clear current messages
            data.forEach(chatMessage => {
                const messageDiv = document.createElement('div');
                messageDiv.className = `message ${chatMessage.message_type}`;
                messageDiv.style.textAlign = chatMessage.message_type === 'user' ? 'right' : 'left';

                // Format the date as "Month Day, Year || Time"
                const timestamp = new Date(chatMessage.timestamp);
                const formattedDate = timestamp.toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });
                const formattedTime = timestamp.toLocaleTimeString('en-US', {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: true
                });
                const formattedTimestamp = `${formattedDate} || ${formattedTime}`;

                // Construct the message HTML
                messageDiv.innerHTML = `
                    <strong>${chatMessage.role}:</strong>
                    <p>${chatMessage.message}</p>
                    <small>${formattedTimestamp}</small>
                `;

                // Check if the message sender is the current user, add delete button
                if (chatMessage.sender === '<?php echo $_SESSION['username']; ?>') {
                    const deleteForm = document.createElement('form');
                    deleteForm.method = 'POST';
                    deleteForm.action = 'chat.php';
                    deleteForm.style.display = 'inline';
                    deleteForm.id = 'deleteForm_' + chatMessage.id;

                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'delete_message_id';
                    input.value = chatMessage.id;

                    const button = document.createElement('button');
                    button.type = 'button';
                    button.textContent = 'Delete';
                    button.classList.add('delete-btn'); // Ensure the button retains styling

                    // Add event listener for the delete button
                    button.addEventListener('click', function() {
                        confirmDelete(chatMessage.id); // Pass the message ID
                    });

                    deleteForm.appendChild(input);
                    deleteForm.appendChild(button);
                    messageDiv.appendChild(deleteForm);
                }

                // Append the new message to the chat window
                chatWindow.appendChild(messageDiv);
            });

            // Auto-scroll to the bottom of the chat window after new messages are added
            chatWindow.scrollTop = chatWindow.scrollHeight;
        })
        .catch(error => console.error('Error fetching messages:', error));
}
        // Fetch messages every 2 seconds
        setInterval(fetchMessages, 2000);
        fetchMessages(); // Initial fetch to load messages on page load

            // Event listener to reset notifications on page load   
            document.addEventListener("DOMContentLoaded", () => {
                fetch('reset_notifications.php')
                    .then(response => response.json())
                    .then(data => {
                        if (data.status !== 'success') {
                            console.error('Error resetting notifications:', data.message);
                        }
                    })
                    .catch(error => console.error('Error resetting notifications:', error));
            });
        </script>
    </body>
</html>