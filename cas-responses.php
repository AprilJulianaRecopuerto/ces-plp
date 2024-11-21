<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['uname'])) {
    header("Location: roleaccount.php");
    exit;
}

$currentDepartment = $_SESSION['department']; // Get the current department
$showForm = isset($_SESSION['show_event_form']) && $_SESSION['show_event_form'];

require 'vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Database connection
$servername = "iwqrvsv8e5fz4uni.cbetxkdyhwsb.us-east-1.rds.amazonaws.com";
$username = "sh9sgtg12c8vyqoa";
$password = "s3jzz232brki4nnv";
$dbname = "szk9kdwhvpxy2g77";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT name, email, event, rate, department FROM submissions WHERE department = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $currentDepartment);
$stmt->execute();
$result = $stmt->get_result();

// Handle form submission for sending certificates
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_certificates'])) {
    $all_sent = true;
    while ($row = $result->fetch_assoc()) {
        $name = $row['name'];
        $email = $row['email'];
        $department = $row['department'];
        $event = $row['event'];

        // Hosted image URLs
        $bgImageURL = 'https://ces-plp-d5e378ca4d4d.herokuapp.com/images/cert-bg.png';
        $logoImageURL = 'https://ces-plp-d5e378ca4d4d.herokuapp.com/images/logoicon.png';
        
        // Generate PDF for each participant
        $date = date("l, F j, Y");
        
        $html = "
        <html>
        <head>
        
            <!-- Link Google Fonts directly -->
            <link href='https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,400;0,600;1,500&display=swap' rel='stylesheet'>
            <link href='https://fonts.googleapis.com/css2?family=Lilita+One&display=swap' rel='stylesheet'>
        
            <style>
                body { 
                    text-align: center;
                    margin: 0; 
                    padding: 0; 
                    font-family: 'Poppins', sans-serif;
                }
        
                .certificate img {
                    position: absolute;
                    margin-top: -45px;
                    width: 109%;
                    margin-left: -45px;
                    object-fit: cover;
                    z-index: -1;
                }
        
                .subheading {
                    font-family: 'Poppins', sans-serif;
                    font-size: 20px;
                    color: #666;
                    margin: 20px 0;
                    margin-top: 240px;
                    margin-left: -195px;
                    letter-spacing: 0.5px;
                }
        
                .name { 
                    font-family: 'Lilita One', sans-serif;
                    font-size: 80px;
                    font-weight: bold;
                    color: #333;
                    margin: 20px 0;
                    text-decoration: underline;
                    font-style: italic;  /* Make it italic if cursive is not working */
                    text-transform: uppercase; /* Convert text to uppercase */
                    margin-top: 30px;
                }
        
                .details {
                    font-family: 'Poppins', sans-serif;
                    font-size: 22px; 
                    color: black;
                    line-height: 1.5;
                    margin-top: 20px;
                }
        
                .date {
                    font-family: 'Poppins', sans-serif;
                    font-size: 20px; 
                    color: #888;
                    margin-top: 30px;
                }
        
                .footer {
                    font-family: 'Poppins', sans-serif;
                    font-size: 18px;
                    color: black;
                    text-align: center;
                    margin-top: 50px;
                }
        
                .footer-content {
                    display: flex;
                    justify-content: center;  /* Centers items horizontally */
                
                }
        
                .footer-content img {
                    margin-left: 340px;
                    max-width: 80px;  /* Adjust the size of the logo */
                    height: auto;
                    margin-top: -3px;
                }
        
                .footer-text {
                    font-size: 20px;
                    margin-left: 110px;
                    font-weight: normal;
                }
            </style>
        </head>
        <body>
            <div class='certificate'>
                <img src='$bgImageURL' alt='Background'>
                <p class='subheading'>This certificate is proudly presented to</p>
                <p class='name'>" . htmlspecialchars($name) . "</p>
                <p class='details'>Who have participated in <strong>&quot;$event&quot;</strong> hosted by <strong>$department</strong><br> on <strong>$date</strong>.</p>
                <div class='footer'>
                    <div class='footer-content'>
                        <img src='$logoImageURL' alt='Logo'>
                        <p class='footer-text'>Community Extension Services</p>
                    </div>
                </div>
            </div>
        </body>
        </html>
        ";
        try {
            // Generate the PDF
            $options = new Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isPhpEnabled', true); // Ensure this is enabled for PHP functionality
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isCssFloatEnabled', true); // Ensure floating is enabled
            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();

            // Save PDF to a temporary directory
            $pdfFilePath = '/tmp/certificate_' . urlencode($name) . '.pdf';
            file_put_contents($pdfFilePath, $dompdf->output());
        } catch (Exception $e) {
            error_log("PDF generation failed: " . $e->getMessage());
            $all_sent = false;
            continue;
        }

        // Send Email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'communityextensionservices1@gmail.com';
            $mail->Password = 'ctpy rvsc tsiv fwix';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('communityextensionservices1@gmail.com', 'Community Extension Services');
            $mail->addAddress($email);
            $mail->Subject = 'Your Certificate of Participation';
            $mail->Body = 'Attached is your certificate of participation.';
            $mail->addAttachment($pdfFilePath);

            $mail->send();
        } catch (Exception $e) {
            $all_sent = false;
            break;
        }

        // Cleanup PDF file
        unlink($pdfFilePath);
    }

    // Return response
    echo $all_sent ? 'success' : 'error';
    exit;
}

$sn = "l3855uft9zao23e2.cbetxkdyhwsb.us-east-1.rds.amazonaws.com";
$un = "equ6v8i5llo3uhjm";
$psd = "vkfaxm2are5bjc3q";
$dbname_user_registration = "ylwrjgaks3fw5sdj";

// Fetch the profile picture from the colleges table in user_registration
$conn_profile = new mysqli($sn, $un, $psd, $dbname_user_registration);
if ($conn_profile->connect_error) {
    die("Connection failed: " . $conn_profile->connect_error);
}

$uname = $_SESSION['uname'];
$sql_profile = "SELECT picture FROM colleges WHERE uname = ?"; // Adjust 'username' to your matching column
$stmt = $conn_profile->prepare($sql_profile);
$stmt->bind_param("s", $uname);
$stmt->execute();
$result_profile = $stmt->get_result();

$profilePicture = null;
if ($result_profile && $row_profile = $result_profile->fetch_assoc()) {
    $profilePicture = $row_profile['picture'];
}

?>
