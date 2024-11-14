<?php
session_start(); // Start the session

// Database connection
$servername = "localhost"; // Your database server
$username = "root"; // Your database username
$password = ""; // Your database password
$dbname = "certificate"; // Your database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch submissions from the database
$sql = "SELECT name, email, event FROM submissions";
$result = $conn->query($sql);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_certificates'])) {
    // Fetch all participants from the database
    $result = $conn->query("SELECT name, email FROM submissions");
    
    while ($row = $result->fetch_assoc()) {
        $name = $row['name'];
        $email = $row['email'];

        // Generate PDF for each participant
        $date = date("F j, Y");
        $html = "
        <html>
        <head>
            <style>
                body { text-align: center; font-family: Arial, sans-serif; }
                .certificate {
                    border: 10px solid #000;
                    padding: 20px;
                    width: 800px;
                    margin: auto;
                }
                h1 { font-size: 48px; margin-bottom: 0; }
                p { font-size: 24px; }
                .name { font-size: 32px; font-weight: bold; }
            </style>
        </head>
        <body>
            <div class='certificate'>
                <h1>Certificate of Participation</h1>
                <p>This certificate is awarded to</p>
                <p class='name'>" . htmlspecialchars($name) . "</p>
                <p>for their participation in our event.</p>
                <p>Date: $date</p>
            </div>
        </body>
        </html>
        ";

        // Generate PDF
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $pdfFilePath = "certificates/certificate_$name.pdf"; // Ensure this directory exists
        file_put_contents($pdfFilePath, $dompdf->output());

        // Send Email with the certificate attached
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // Gmail SMTP server
            $mail->SMTPAuth = true;
            $mail->Username = 'communityextensionservices1@gmail.com'; // Your Gmail address
            $mail->Password = 'ctpy rvsc tsiv fwix'; // Your Gmail app password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption
            $mail->Port = 587; // TCP port to connect to

            $mail->setFrom('communityextensionservices1@gmail.com', 'Community Extension Services'); // Your name
            $mail->addAddress($email);
            $mail->Subject = 'Your Certificate of Participation';
            $mail->Body = 'Attached is your certificate of participation.';
            $mail->addAttachment($pdfFilePath); // Attach the PDF file

            $mail->send();
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }

        // Optional: Remove the PDF file after sending
        unlink($pdfFilePath);
    }

    echo "Certificates have been sent to all participants.";
}

?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        
        <title>CES PLP</title>

        <link rel="icon" href="images/logoicon.png">
    
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
            width: calc(96.2% - 250px); /* Adjusted width considering the sidebar */
            height: 80px;
            margin-left: 290px; /* Align with the sidebar */
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
            margin-right: 10px; /* Space between profile picture and name */
        }

        span {
            font-family: "Poppins", sans-serif;
            font-size: 17px;
            color: black; /* Set text color */
        }

        .dropdown-icon {
            width:22px !important; /* Adjust size of the down-arrow icon */
            height: 15px !important;
            margin-left: 5px; /* Space between name and icon */
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
            width: 250px;
            background-color: #FFF8A5; /* Light yellow */
            color: black;
            padding: 20px;
            z-index: 1000;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.2); /* Added box shadow */
        }

        .logo {
            display: flex;
            align-items: center;
            margin-bottom: 30px; /* Increased margin bottom */
        }

        .logo img {
            height: 100px; /* Increased logo size */
            margin-right: 15px; /* Adjusted margin */
        }

        .logo span {
            font-size: 30px; /* Increased font size */
            margin-left:-15px;
            font-family: 'Glacial Indifference', sans-serif;
            font-weight: bold;
        }

        .menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .menu li {
            margin: 6px 0; /* Increased margin for spacing between items */
            display: flex;
            align-items: center;
        }

        .menu a {
            color: black;
            text-decoration: none;
            display: flex;
            align-items: center;
            padding: 15px; /* Increased padding for better click area */
            border-radius: 5px; /* Increased border-radius for rounded corners */
            width: 100%;
            font-size: 17px; /* Increased font size */
            font-family: 'Poppins', sans-serif;
        }

        .menu a:hover {
            background-color: #22901C;
            transition: 0.3s;
            color: white; /* Ensure the text color is white when hovered */
        }

        .menu img {
            height: 30px; /* Increased icon size */
            margin-right: 15px; /* Adjusted space between icon and text */
        }

        .menu .signout {
            margin-top: 35px; /* Pushes Sign Out to the bottom of the sidebar */
        }
		
		.content-settings {
            margin-left: 250px;
            padding: 22px;
        }

        .settings-header {
            font-family: "Poppins", sans-serif;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: -24px;
            margin-left: 65px;
            font-size: 22px;
        }
		.table-container {
    width: 100%;
    margin-left: -12px;
    overflow-x: auto;
    margin-top: 40px; /* Space above the table */
}

.crud-table {
    width: 100%;
    border-collapse: collapse;
    font-family: 'Poppins', sans-serif;
    background-color: #ffffff;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    border-radius: 10px;
    overflow: hidden;
}

.crud-table th, .crud-table td {
    border: 1px solid #ddd;
    padding: 10px;
    text-align: left;
    white-space: nowrap; /* Prevent text from wrapping */
}

.crud-table th {
    background-color: #4CAF50;
    color: white;
    height: 40px;
}

.crud-table td {
    height: 50px;
    background-color: #fafafa;
}

.crud-table tr:hover {
    background-color: #f1f1f1;
}



</style>
<body>
<nav class="navbar">
        <h2>Responses</h2> 

        <div class="profile">
            <img src="images/jen.png" alt="Profile Picture">
            <span><?php echo $_SESSION['username']; ?></span>
        </div>
    </nav>
    
    <div class="sidebar">
        <div class="logo">
            <img src="images/logo.png" alt="Logo">
        </div>
        <ul class="menu">
            <li><a href="admin-dash.php"><img src="images/home.png">Dashboard</a></li>
            <li><a href="admin-projlist.php"><img src="images/project-list.png">Project List</a></li>
            <li><a href="calendar.php"><img src="images/project-list.png">Event Calendar</a></li>
            <li><a href="admin-resource-allocation.php"><img src="images/resource.png">Resource Allocation</a></li>
            <li><a href="task.php"><img src="images/task.png">Task Management</a></li>
            <li><a href="#task-management"><img src="images/task.png">Progress Report</a></li>
            <li><a href="settings.php"><img src="images/settings.png">Settings</a></li>
            <li class="signout">
                <a href="#" onclick="confirmLogout(event)">
                    <img src="images/logout.png">Sign Out
                </a>
            </li>
        </ul>
    </div>
	
 
		<div class="table-container">
			<table class="crud-table">
				<thead>
					<tr>
						<th>ID</th>
						<th>Name</th>
						<th>Email</th>
						<th>Event</th>
					</tr>
				</thead>
				<tbody id="table-body">
					<?php
					if ($result->num_rows > 0) {
						// Output data of each row
						while ($row = $result->fetch_assoc()) {
							echo "<tr>
									<td>" . htmlspecialchars($row["id"]) . "</td>
									<td>" . htmlspecialchars($row["name"]) . "</td>
									<td>" . htmlspecialchars($row["email"]) . "</td>
									<td>" . htmlspecialchars($row["event"]) . "</td>
								</tr>";
						}
					} else {
						echo "<tr><td colspan='4'>No records found</td></tr>";
					}
					$conn->close();
					?>
				</tbody>
			</table>
		</div>
		
        <script>
		//darkmode
			document.addEventListener('DOMContentLoaded', () => {
            const toggleButton = document.getElementById('toggle-dark-mode');

            // Check local storage for the current mode
            if (localStorage.getItem('darkMode') === 'enabled') {
                document.body.classList.add('dark-mode');
                toggleButton.textContent = 'OFF';
                toggleButton.style.backgroundColor = '#dc3545';
            } else {
                document.body.classList.remove('dark-mode');
                toggleButton.textContent = 'ON';
                toggleButton.style.backgroundColor = '#28a745';
            }

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
        });

		document.addEventListener('DOMContentLoaded', () => {
            // Check local storage for the current mode
            if (localStorage.getItem('darkMode') === 'enabled') {
                document.body.classList.add('dark-mode');
            }
        });
		
		 // Toggle notification buttons
    function toggleNotification(type) {
        const button = document.getElementById(`toggle-${type}-notifications`);
        if (button.innerText === 'ON') {
            button.innerText = 'OFF';
        } else {
            button.innerText = 'ON';
        }
        // Here you can add additional logic to handle the actual notification settings, like saving preferences to the server.
    }

    // Update notification sound
    function updateNotificationSound() {
        const soundSelect = document.getElementById('notification-sound');
        const soundSelected = soundSelect.options[soundSelect.selectedIndex].text;
        document.getElementById('sound-selected').innerText = soundSelected;
        // You can add logic here to apply the selected sound preference.
    }
		</script>
    </div>
</body>
</html>
