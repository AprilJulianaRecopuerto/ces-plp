<?php
// Start the session
session_start();

// Initialize message variables
$message = '';
$alertType = '';

// Generate OTP and store in session if not already set
if (!isset($_SESSION['otp'])) {
    $_SESSION['otp'] = rand(100000, 999999); // Generate a random 6-digit OTP
    // Example: Send the email with the OTP (uncomment and set your email parameters)
    // $to = 'recipient@example.com'; // Replace with the recipient's email address
    // $subject = 'Your OTP Code';
    // mail($to, $subject, "Your OTP is: " . $_SESSION['otp']); // Send the email
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the OTP entered by the user
    $enteredOtp = '';
    for ($i = 0; $i < 6; $i++) {
        $enteredOtp .= filter_var($_POST['otp'][$i], FILTER_SANITIZE_STRING);
    }

    // Debug: Log the stored and entered OTPs
    error_log("Entered OTP: " . $enteredOtp); // Log the entered OTP
    error_log("Stored OTP: " . $_SESSION['otp']); // Log the stored OTP

    // Compare with the OTP stored in session
    if ($enteredOtp === (string)$_SESSION['otp']) { // Make sure to cast to string for comparison
        $message = 'OTP verified successfully. Proceed with your next steps.';
        $alertType = 'success';
        // Optionally, you can redirect to another page or do additional processing here
    } else {
        $message = 'Invalid OTP. Please try again.';
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
            background-color: #f4f4f4;
			overflow: hidden;
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

        .otp-container {
            font-family: 'Poppins', sans-serif;
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

        .otp-input {
            font-family: 'Poppins', sans-serif;
            width: 45px;
            height: 45px;
            text-align: center;
            font-size: 24px;
            margin: 0 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
            outline: none;
            transition: border-color 0.3s;
        }

        .otp-input:focus {
            border-color: #007bff;
        }

        .submit-button {
            padding: 10px 20px;
            font-family: 'Poppins', sans-serif;
            font-size: 17px;
            background-color: #089451;
            color: #fff;
            border: 0.5px solid black;
            border-radius: 10px;
            cursor: pointer;
            width: 100%;
            margin-top: 20px;
            margin-bottom: 20px;
        }

        .submit-button:hover {
            background-color: #218838;
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

    <div class="otp-container">
        <h2>OTP CODE</h2>
        <p>Please type the OTP Code sent to your email:</p>
        
        <form method="POST" action="">
            <div style="display: flex; justify-content: center;">
                <input type="text" class="otp-input" name="otp[]" maxlength="1" oninput="moveFocus(this)">
                <input type="text" class="otp-input" name="otp[]" maxlength="1" oninput="moveFocus(this)">
                <input type="text" class="otp-input" name="otp[]" maxlength="1" oninput="moveFocus(this)">
                <input type="text" class="otp-input" name="otp[]" maxlength="1" oninput="moveFocus(this)">
                <input type="text" class="otp-input" name="otp[]" maxlength="1" oninput="moveFocus(this)">
                <input type="text" class="otp-input" name="otp[]" maxlength="1" oninput="moveFocus(this)">
            </div>
            <button type="submit" class="submit-button">Submit</button>
        </form>
    </div>

    <script>
         function moveFocus(current) {
        // Restrict input to numbers only
        current.value = current.value.replace(/[^0-9]/g, '');

        // Move focus to the next input if current input is filled
        if (current.value.length === current.maxLength) {
            const nextInput = current.nextElementSibling;
            if (nextInput) {
                nextInput.focus();
            }
        }
    }

    // Show SweetAlert for messages if they exist

        <?php if (isset($_SESSION['message'])): ?>
            Swal.fire({
                icon: '<?php echo $_SESSION['alertType']; ?>',
                title: '<?php echo $_SESSION['alertType'] === 'success' ? 'Success!' : 'Error!'; ?>',
                text: '<?php echo $_SESSION['message']; ?>',
                allowOutsideClick: false, // Prevent clicking outside to close
                confirmButtonText: 'OK',
                customClass: {
                    popup: '<?php echo $_SESSION['alertType'] === 'success' ? 'custom-swal-popup' : 'custom-error-popup'; ?>',
                    title: '<?php echo $_SESSION['alertType'] === 'success' ? 'custom-swal-title' : 'custom-error-title'; ?>',
                    confirmButton: '<?php echo $_SESSION['alertType'] === 'success' ? 'custom-swal-confirm' : 'custom-error-confirm'; ?>'
                }
            }).then((result) => {
                if (result.isConfirmed && '<?php echo $_SESSION['alertType']; ?>' === 'success') {
                    // Redirect to college-reset-password.php on confirmation for success
                    window.location.href = 'college-reset-password.php';
                }
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
