<?php
// Start the session
session_start();

// Include PHPMailer autoloader
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Initialize message variables
$message = '';
$alertType = ''; // No default alert type

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize the email input
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    // Validate email format
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Create a new PHPMailer instance
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'communityextensionservices1@gmail.com';
            $mail->Password = 'ctpy rvsc tsiv fwix'; // Your Gmail app password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Sender and recipient settings
            $mail->setFrom('ommunityextensionservices1@gmail.com', 'PLP CES');
            $mail->addAddress($email);

            // Generate a confirmation code
            $confirmationCode = rand(100000, 999999); // Random 6-digit code

            // Store OTP in session for later verification
            $_SESSION['otp'] = $confirmationCode;

            // Email content (confirmation code)
            $mail->isHTML(true);
            $mail->Subject = 'Confirmation Code';
            $mail->Body = 'Your confirmation code is: <strong>' . $confirmationCode . '</strong>';
            $mail->AltBody = 'Your confirmation code is: ' . $confirmationCode;

            // Send the email
            if ($mail->send()) {
                $message = 'Confirmation code has been sent to your email.';
                $alertType = 'success';
            } else {
                $message = "Mailer Error: Unable to send the email.";
                $alertType = 'error';
            }
        } catch (Exception $e) {
            $message = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            $alertType = 'error';
        }
    } else {
        $message = "Invalid email format.";
        $alertType = 'error';
    }

    // Store message and alert type in session variables
    $_SESSION['message'] = $message;
    $_SESSION['alertType'] = $alertType;

    // Redirect to the same page to prevent resubmission on reload
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CES PLP</title>

    <!-- The FavIcon of our Website -->
    <link rel="icon" href="images/logoicon.png">

    <!-- SweetAlert CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <!-- SweetAlert JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,400;0,600;1,500&display=swap');
        body {
            background-image: url('css/plpmain.png');
            font-family: 'Poppins', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f9f9f9;
            margin: 0;
            overflow: hidden;  CES01
        }

         /*Banner Shadow*/
         .banner1 img {
            position: absolute;
            top: -250px;
            left: 0;
            height: 150%;
            width: 100%;
            object-fit: cover;
            z-index: -1;
        }
        
        .forgot-password-box {
            background-color: #ffc107;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 25px;
            width: 350px;
            text-align: center;
            position: fixed; /* Fix the box in place */
            top: 50%; /* Center vertically */
            left: 50%; /* Center horizontally */
            transform: translate(-50%, -50%); /* Shift the box to the exact center */
        }
        h2 {
            margin-bottom: 15px;
        }
        input[type="email"] {
            font-family: 'Poppins', sans-serif;
            height: 23px;
            width: 93%;
            padding: 10px;
            margin-bottom: 15px;
            border: 0.5px solid black;
            border-radius: 4px;
        }
        button {
            padding: 10px;
            font-family: 'Glacial Indifference', sans-serif;
            font-size: 17px;
            background-color: #089451;
            color: #fff;
            border: 0.5px solid black;
            padding: 10px 20px;
            border-radius: 10px;
            cursor: pointer;
            width: 100%;
            margin-bottom: 25px;
        }
        button:hover {
            background-color: #218838;
        }

        .custom-swal-popup {
                font-family: 'Poppins', sans-serif;
                font-size: 16px; /* Increase the font size */
                width: 400px !important; /* Set a larger width */
            }

            .custom-swal-title {
                font-family: 'Poppins', sans-serif;
                color: #3085d6; /* Custom title color */
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

            .custom-error-title {
                font-family: 'Poppins', sans-serif;
                color: #e74c3c; /* Custom title color for error */
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
    <div class="banner1">
        <img src="images/Admin (1).png">
    </div>

    <div class="forgot-password-box">
        <h2>Forgot Password</h2>
        <p>Enter your email address to receive the OTP code</p>
        <form method="POST" action="">
            <input type="email" name="email" placeholder="Your email address" required>
            <button type="submit">Send</button>
        </form>
    </div>

    <script>
        // Show SweetAlert only after form submission
        <?php if (isset($_SESSION['message'])): ?>
            Swal.fire({
            icon: '<?php echo $_SESSION['alertType']; ?>',
            title: '<?php echo $_SESSION['alertType'] === 'success' ? 'Success!' : 'Error!'; ?>',
            text: '<?php echo $_SESSION['message']; ?>',
            customClass: {
                popup: '<?php echo $_SESSION['alertType'] === 'success' ? 'custom-swal-popup' : 'custom-error-popup'; ?>',
                title: '<?php echo $_SESSION['alertType'] === 'success' ? 'custom-swal-title' : 'custom-error-title'; ?>',
                confirmButton: '<?php echo $_SESSION['alertType'] === 'success' ? 'custom-swal-confirm' : 'custom-error-confirm'; ?>'
            }
        }).then(() => {
            // Redirect to 'college-otp-send.php' if the alert was successful
            <?php if ($_SESSION['alertType'] === 'success'): ?>
                window.location.href = 'otp-send.php';
            <?php endif; ?>
        });

        <?php 
        // Clear session data after showing SweetAlert
        unset($_SESSION['message']);
        unset($_SESSION['alertType']);
        ?>
        <?php endif; ?>
    </script>
</body>
</html>
