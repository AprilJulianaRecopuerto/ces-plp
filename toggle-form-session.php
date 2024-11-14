<?php
session_start(); // Start the session

if (isset($_POST['toggleForm'])) {
    // Toggle the visibility of the form
    $_SESSION['show_event_form'] = !isset($_SESSION['show_event_form']) || $_SESSION['show_event_form'] === false;

    // Set a session message if the form is closed
    if (!$_SESSION['show_event_form']) {
        $_SESSION['form_closed_message'] = "The form is currently closed. Please try again later.";
    } else {
        unset($_SESSION['form_closed_message']); // Clear the message if the form is open
    }
}
?>
