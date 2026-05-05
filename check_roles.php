<?php
session_start();

function protect_page($allowed_role) {

    // 1. Validate session integrity
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        session_unset();
        session_destroy();
        header("Location: login.php");
        exit();
    }

    // 2. Allow access if role matches
    if ($_SESSION['role'] === $allowed_role) {
        return;
    }

    // 3. Redirect based on role
    if ($_SESSION['role'] === 'Admin') {
        header("Location: admin_dashboard.php");
    } elseif ($_SESSION['role'] === 'Organiser') {
        header("Location: organiser_dashboard.php");
    } elseif ($_SESSION['role'] === 'Attendee') {
        header("Location: attendee_view.php");
    } elseif ($_SESSION['role'] === 'Staff') {
        header("Location: staff_dashboard.php"); // 👈 ADD THIS
    } else {
        // Unknown role → force logout
        session_unset();
        session_destroy();
        header("Location: login.php?error=invalid_role");
        exit();
    }
}
?>