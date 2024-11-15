<?php

date_default_timezone_set('Asia/Manila');

session_start(); // Start the session at the very top of the file
    // Database connection details
$servername = "d6ybckq58s9ru745.cbetxkdyhwsb.us-east-1.rds.amazonaws.com";
$username_db = "t9riamok80kmok3h";
$password_db = "lzh13ihy0axfny6d";
$$dbname_proj_list = "g8ri1hhtsfx77ptb";  // Database for tasks

// Create connection to proj_list database
$conn_task= new mysqli($servername, $username_db, $password_db, $dbname_proj_list);

// Check for a successful connection
if ($conn_task->connect_error) {
    die("Connection failed: " . $conn_task->connect_error);
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader
require 'vendor/autoload.php';


$sql = "SELECT id, task_description, created_at, cas_status FROM cas_notifications ORDER BY created_at DESC";
$result = $conn->query($sql);

$notifications = [];
if ($result->num_rows > 0) {
    // Fetch all notifications
    while($row = $result->fetch_assoc()) {
        $notifications[] = [
            'id' => $row['id'],
            'task_description' => $row['task_description'],
            'created_at' => $row['created_at'],
            'cas_status' => $row['cas_status'],
        ];
    }
} else {
    $notifications[] = ['message' => 'No notifications found.'];
}

$today = date('Y-m-d');
$oneDayLater = date('Y-m-d', strtotime('+1 day', strtotime($today)));
$oneWeekLater = date('Y-m-d', strtotime('+1 week', strtotime($today)));

// Fetch tasks with their due dates
$sql_due_date = "
    SELECT id, task_description, due_date
    FROM cas_tasks
    WHERE status = 'pending'
    AND (
        due_date = ? OR  -- Task due today
        due_date = ? OR  -- Task due in 1 day
        due_date = ?     -- Task due in 1 week
    )
    ORDER BY due_date ASC
";

$stmt = $conn->prepare($sql_due_date);
$stmt->bind_param("sss", $today, $oneDayLater, $oneWeekLater); 
$stmt->execute();
$result_due_date = $stmt->get_result();

$notifiedTasks = [];

// Check if session for notified tasks is set, if not, initialize it
if (!isset($_SESSION['notified_tasks'])) {
    $_SESSION['notified_tasks'] = [];
}

if ($result_due_date->num_rows > 0) {
    while ($row = $result_due_date->fetch_assoc()) {
        $taskId = $row['id'];
        $taskDescription = $row['task_description'];
        $dueDate = date('Y-m-d', strtotime($row['due_date']));

        // Check if the task has already been notified
        if (!in_array($taskId, $_SESSION['notified_tasks'])) {
            // Check due date and set the notification message accordingly
            if ($dueDate == $today) {
                $notificationMessage = "The task '$taskDescription' is due today.";
            } elseif ($dueDate == $oneDayLater) {
                $notificationMessage = "The task '$taskDescription' is due in 1 day.";
            } elseif ($dueDate == $oneWeekLater) {
                $notificationMessage = "The task '$taskDescription' is due in 1 week.";
            } elseif ($dueDate != $today && $dueDate != $oneDayLater && $dueDate != $oneWeekLater) {
                // If the due date is not today, 1 day, or 1 week, it's considered a new task
                $notificationMessage = "New task '$taskDescription' has been sent.";
            } else {
                // Debug: Log if none of the conditions match (it should hit this for new tasks)
                echo "No matching condition for task '$taskDescription'. Due Date: $dueDate<br>";
            }

            if (!empty($notificationMessage)) {
                // Insert notification for the user with 'unread' status
                $insertNotification = $conn->prepare("INSERT INTO cas_notifications (department, task_description, cas_status, created_at) VALUES (?, ?, 'unread', NOW())");
                $department = 'CAS'; // Set department explicitly (or dynamically if needed)
                $insertNotification->bind_param("ss", $department, $notificationMessage);
                $insertNotification->execute();
                $insertNotification->close();
            }

                // Add the task ID to the notified tasks session array to prevent future notifications
            $_SESSION['notified_tasks'][] = $taskId;

            $servername_ur= "l3855uft9zao23e2.cbetxkdyhwsb.us-east-1.rds.amazonaws.com";
            $username_ur = "equ6v8i5llo3uhjm";
            $password_ur = "vkfaxm2are5bjc3q";
            $dbname_user_registration = "ylwrjgaks3fw5sdj";

            $conn_users = new mysqli($servername_ur, $username_ur, $password_ur, $dbname_user_registration);
    
            if ($conn_users->connect_error) {
                die("Connection to 'user_registration' database failed: " . $conn_users->connect_error);
            }
    
            // Fetch the admin email
            $user_sql = "SELECT email FROM colleges WHERE role = 'CAS Extension Coordinator' LIMIT 1";
            $result = $conn_users->query($user_sql);

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $recipientEmail = $row['email'];

                // Send email using PHPMailer
                $mail = new PHPMailer(true);
                try {
                // Server settings
                $mail->isSMTP();                                            // Send using SMTP
                $mail->Host       = 'smtp.gmail.com';                         // Set the SMTP server to send through
                $mail->SMTPAuth   = true;                                     // Enable SMTP authentication
                $mail->Username   = 'communityextensionservices1@gmail.com'; // SMTP username
                $mail->Password   = 'ctpy rvsc tsiv fwix';                    // SMTP password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;          // Enable TLS encryption
                $mail->Port       = 587;                                     // TCP port to connect to

                // Recipients
                $mail->setFrom('communityextensionservices1@gmail.com', 'PLP CES');
                $mail->addAddress($recipientEmail); // Add the admin email fetched from the database

                // Content
                $mail->isHTML(true);                                   // Set email format to HTML
                $mail->Subject = 'Task Reminder Notification';
                $mail->Body    = "$notificationMessage. <br><br>Best regards,<br>PLP CES";

                $mail->send();
                

                    // Add task ID to notified tasks to prevent duplicate notifications
                    $_SESSION['notified_tasks'][] = $taskId;
                    $notifiedTasks[] = $taskId; // Track notified tasks
                } catch (Exception $e) {
                    $_SESSION['error'] = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                    echo 'Mailer Error: ' . $mail->ErrorInfo; // Debugging error message
                }
            }
        }
    }
} else {
    echo 'No tasks found matching due dates.'; // In case no tasks were found
}

// Return JSON response
echo json_encode(['status' => 'success', 'notified_tasks' => $notifiedTasks]);

// Close the database connection
$conn->close();
?>
