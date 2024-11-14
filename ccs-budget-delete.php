<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "budget_utilization"; // Change to your actual database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => "Connection failed: " . $conn->connect_error]));
}

// Load Composer's autoloader for PHPMailer
require 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Get details_id and action/event_title from the AJAX request
$data = json_decode(file_get_contents('php://input'), true);
$detailsId = isset($data['id']) ? $data['id'] : null;
$action = isset($data['action']) ? $data['action'] : null;
$eventTitle = isset($data['event_title']) ? $data['event_title'] : null;

$response = ['success' => false];

// Connect to mov database for notifications
$dbname_mov = "mov";
$conn_mov = new mysqli($servername, $username, $password, $dbname_mov);
if ($conn_mov->connect_error) {
    die("Connection to 'mov' database failed: " . $conn_mov->connect_error);
}

// Handle notification insertion and email sending function
function sendNotificationAndEmail($projectName, $notificationMessage, $conn_mov) {
    // Insert notification into mov database
    $notificationSql = "INSERT INTO notifications (project_name, notification_message) VALUES (?, ?)";
    $stmtNotification = $conn_mov->prepare($notificationSql);
    $stmtNotification->bind_param("ss", $projectName, $notificationMessage);
    $stmtNotification->execute();
    $stmtNotification->close();

    // Fetch admin email from user_registration database
    $user_dbname = "user_registration";
    $conn_users = new mysqli("localhost", "root", "", $user_dbname);
    if ($conn_users->connect_error) {
        die("Connection to 'user_registration' database failed: " . $conn_users->connect_error);
    }
    $user_sql = "SELECT email FROM users WHERE roles = 'Head Coordinator' LIMIT 1";
    $result = $conn_users->query($user_sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $recipientEmail = $row['email'];

        // Send email using PHPMailer
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'communityextensionservices1@gmail.com';
            $mail->Password = 'ctpy rvsc tsiv fwix';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('communityextensionservices1@gmail.com', 'PLP CES');
            $mail->addAddress($recipientEmail);
            $mail->isHTML(true);
            $mail->Subject = 'Notification of Action';
            $mail->Body = "The project <strong>$projectName</strong> has been $notificationMessage.<br><br>Best regards,<br>PLP CES";
            $mail->send();
        } catch (Exception $e) {
            $_SESSION['error'] = "Mailer Error: {$mail->ErrorInfo}";
        }
    }
    $conn_users->close();
}

// Handle each action
if ($action === 'count') {
    // Count the events associated with the details_id
    $countSql = "SELECT COUNT(*) as eventCount FROM ccs_budget WHERE details_id = ?";
    $stmt = $conn->prepare($countSql);
    $stmt->bind_param('i', $detailsId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    echo json_encode(['count' => $result['eventCount']]);
    exit;
}

if ($action === 'all') {
    // Delete all events associated with the details_id
    $deleteBudgetSql = "DELETE FROM ccs_budget WHERE details_id = ?";
    $stmt = $conn->prepare($deleteBudgetSql);
    $stmt->bind_param('i', $detailsId);
    $response['success'] = $stmt->execute();

    if ($response['success']) {
        $deleteDetailsSql = "DELETE FROM ccs_details WHERE id = ?";
        $stmt = $conn->prepare($deleteDetailsSql);
        $stmt->bind_param('i', $detailsId);
        $stmt->execute();

        // Send notification and email
        sendNotificationAndEmail("Project ID $detailsId", "deleted completely", $conn_mov);
    }
} elseif ($action === 'specific' && $eventTitle) {
    // Delete a specific event by title
    $deleteBudgetSql = "DELETE FROM ccs_budget WHERE details_id = ? AND event_title = ?";
    $stmt = $conn->prepare($deleteBudgetSql);
    $stmt->bind_param('is', $detailsId, $eventTitle);
    $response['success'] = $stmt->execute();

    $remainingEventsSql = "SELECT COUNT(*) as remainingCount FROM ccs_budget WHERE details_id = ?";
    $stmt = $conn->prepare($remainingEventsSql);
    $stmt->bind_param('i', $detailsId);
    $stmt->execute();
    $remainingResult = $stmt->get_result()->fetch_assoc();

    if ($remainingResult['remainingCount'] === 0) {
        $deleteDetailsSql = "DELETE FROM ccs_details WHERE id = ?";
        $stmt = $conn->prepare($deleteDetailsSql);
        $stmt->bind_param('i', $detailsId);
        $stmt->execute();
    }

    // Send notification and email
    sendNotificationAndEmail("Event '$eventTitle'", "deleted", $conn_mov);
} elseif ($action === 'single') {
    // Delete a single event if only one exists
    $countSql = "SELECT COUNT(*) as eventCount FROM ccs_budget WHERE details_id = ?";
    $stmt = $conn->prepare($countSql);
    $stmt->bind_param('i', $detailsId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    if ($result['eventCount'] === 1) {
        $deleteBudgetSql = "DELETE FROM ccs_budget WHERE details_id = ?";
        $stmt = $conn->prepare($deleteBudgetSql);
        $stmt->bind_param('i', $detailsId);
        $response['success'] = $stmt->execute();

        if ($response['success']) {
            $deleteDetailsSql = "DELETE FROM ccs_details WHERE id = ?";
            $stmt = $conn->prepare($deleteDetailsSql);
            $stmt->bind_param('i', $detailsId);
            $stmt->execute();

            // Send notification and email
            sendNotificationAndEmail("Project ID $detailsId", "deleted as the only remaining event", $conn_mov);
        }
    }
}


// Return JSON response
echo json_encode($response);

// Close the statement and connection
$stmt->close();
$conn->close();
?>
