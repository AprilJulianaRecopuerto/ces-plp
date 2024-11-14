// LoadingPage Script
// Wait for the DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Get a reference to the login button by class name
    var loginButton = document.querySelector('.header-button'); // Corrected selector to target the login button inside .header

    // Add click event listener to the login button
    loginButton.addEventListener('click', function() {
        // Redirect to roleaccount.html when the button is clicked
        window.location.href = 'roleaccount.php';
    });
});

// Wait for the DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Get a reference to the login button by class name
    var loginButton = document.querySelector('.home-button'); // Corrected selector to target the login button inside .header

    // Add click event listener to the login button
    loginButton.addEventListener('click', function() {
        // Redirect to roleaccount.html when the button is clicked
        window.location.href = 'loadingpage.php';
    });
});


// Role Account Script
document.addEventListener("DOMContentLoaded", function() {
    const adminButton = document.querySelector(".adminbutton");
    const adminFormContainer = document.getElementById("adminFormContainer");
    const overlay = document.getElementById("overlay");
    const closeButton = document.querySelector(".close-button"); // Corrected selector
    const adminForm = document.getElementById("adminForm");

    // Show the admin form container and overlay when the "Admin" button is clicked
    adminButton.addEventListener("click", function() {
        overlay.style.display = "block";
        adminFormContainer.style.display = "block";
    });

    // Hide the admin form container and overlay when the close button is clicked
    closeButton.addEventListener("click", function() {
        overlay.style.display = "none";
        adminFormContainer.style.display = "none";
    });

    // Handle form submission
    adminForm.addEventListener("submit", function(event) {
        event.preventDefault(); // Prevent the default form submission

        // Optionally, you can validate the input here

        // Reset the form inputs
        adminForm.reset();

        // Redirect to loginpage.html (replace with actual redirect logic)
        window.location.href = "loginpage.html";
    });

    // Hide the admin form container and overlay when clicking outside the form
    overlay.addEventListener("click", function() {
        overlay.style.display = "none";
        adminFormContainer.style.display = "none";
    });
});

// LogInPage Script
// JavaScript for toggling password visibility
function togglePassword() {
    var passwordInput = document.getElementById("password");
    var toggleIcon = document.getElementById("togglePasswordIcon");

    if (passwordInput.type === "password") {
        passwordInput.type = "text";
        toggleIcon.classList.remove("fa-eye");
        toggleIcon.classList.add("fa-eye-slash");
    } else {
        passwordInput.type = "password";
        toggleIcon.classList.remove("fa-eye-slash");
        toggleIcon.classList.add("fa-eye");
    }
}
