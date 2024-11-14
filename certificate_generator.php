<?php
require 'vendor/autoload.php'; // Composer's autoload file
use Dompdf\Dompdf;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Database connection
$servername = "localhost";
$username = "root"; 
$password = ""; 
$dbname = "certificate";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['signup'])) {
    // Capture form data for sign-up
    $name = $_POST['name'];
    $email = $_POST['email'];

    // Insert submission data into the database
    $stmt = $conn->prepare("INSERT INTO submissions (name, email) VALUES (?, ?)");
    $stmt->bind_param("ss", $name, $email);
    $stmt->execute();
    $stmt->close();

    echo "Thank you for signing up!";
}

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
    <title>Certificate Management</title>
</head>
<body>
    <h2>Certificate Sign-Up Form</h2>
    <form action="" method="POST">
        <label for="name">Full Name:</label>
        <input type="text" id="name" name="name" required><br><br>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required><br><br>

        <button type="submit" name="signup">Sign Up</button>
    </form>

    <h2>Send Certificates</h2>
    <form action="" method="POST">
        <button type="submit" name="send_certificates">Send Certificates to All Participants</button>
    </form>
</body>
</html>
