<?php
session_start(); // Start session to manage SweetAlert success messages

// Connect to the database
$servername = "localhost"; // Update as needed
$username = "root"; // Update as needed
$password = ""; // Update as needed
$dbname = "user_registration"; // Your database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $email = $_POST['email-field'];
    $newPassword = $_POST['new-password-field'];
    $confirmPassword = $_POST['confirm-password-field'];

    // Check if the new password and confirm password match
    if ($newPassword !== $confirmPassword) {
        $_SESSION['error'] = "Passwords do not match!";
        header("Location: " . $_SERVER['PHP_SELF']); // Reload the page
        exit;
    }

    // Prepare and execute the SQL query to update the password (no hashing)
    $query = "UPDATE users SET password = ? WHERE email = ?";
    $stmt = $conn->prepare($query); // Use $conn here, not $pdo
    $stmt->bind_param("ss", $newPassword, $email); // Bind parameters for the query

    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $_SESSION['success'] = "Password updated successfully!";
    } else {
        $_SESSION['error'] = "Error: Email not found or password update failed.";
    }

    // Redirect after form submission
    header("Location: " . $_SERVER['PHP_SELF']); 
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - CES PLP</title>
    <link rel="icon" href="images/logoicon.png">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> <!-- SweetAlert2 CDN -->

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

        .reset-password-box {
            background-color: #ffc107;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            width: 350px;
            text-align: left;
        }

        h2 {
            margin-bottom: 15px;
            text-align: center;
        }

        input[type="email"],
        input[type="password"] {
            font-family: 'Poppins', sans-serif;
            height: 23px;
            width: 93.2%;
            padding: 10px;
            margin-bottom: 15px;
            border: 0.5px solid black;
            border-radius: 4px;
        }

        button {
            background-color: #4CAF50;
                border: none;
                color: black;
                padding: 10px 20px;
                margin-left: 10px;
                border-radius: 5px;
                font-size: 16px;
                cursor: pointer;
                transition: background-color 0.3s;
                font-family: 'Poppins', sans-serif;
            }
        
        button:hover {
            background-color: #45a049; /* Darker green on hover */
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

<div class="reset-password-box">
    <h2>Reset Password</h2>

    <form autocomplete="off" method="POST" action="">
        <label for="email">Email</label>
        <input type="email" id="email" placeholder="Your email address" required name="email-field" autocomplete="new-email">

        <label for="new-password">New Password</label>
        <input type="password" id="new-password" placeholder="New password" required name="new-password-field" autocomplete="new-password">

        <label for="confirm-password">Re-Enter Password</label>
        <input type="password" id="confirm-password" placeholder="Re-enter password" required name="confirm-password-field" autocomplete="new-password">

        <button type="submit">Change</button>
    </form>
</div>

<?php if (isset($_SESSION['success'])): ?>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: '<?php echo $_SESSION['success']; ?>',
            customClass: {
                popup: 'custom-swal-popup',
                title: 'custom-swal-title',
                confirmButton: 'custom-swal-confirm'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Redirect to 'roleaccount' page
                window.location.href = 'roleaccount.php';
            }
        });
    </script>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: '<?php echo $_SESSION['error']; ?>',
            customClass: {
                popup: 'custom-error-popup',
                title: 'custom-error-title',
                confirmButton: 'custom-error-confirm'
            }
        });
    </script>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>


</body>
</html>
