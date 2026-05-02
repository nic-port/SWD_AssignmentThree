<?php
session_start();

/**
 * The Security Gatekeeper Function
 * This guarantees that only the allowed role can view the page.
 */
function protect_page($allowed_role) {
    // 1. Check if the user is even logged in
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    // 2. Check if their role matches the requirement
    // If a Guest tries to enter an Admin page, or vice versa, redirect them.
    if ($_SESSION['role'] !== $allowed_role) {
        // Option A: Send them to their actual home dashboard based on their real role
        if ($_SESSION['role'] === 'Admin') {
            header("Location: admin_dashboard.php");
        } elseif ($_SESSION['role'] === 'Organiser') {
            header("Location: organiser_dashboard.php");
        } else {
            header("Location: guest_view.php");
        }
        exit();
    }
}
?>