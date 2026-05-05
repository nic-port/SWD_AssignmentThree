<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* Protect page based on role */
function protect_page($role_required) {

    if (!isset($_SESSION['role'])) {
        header("Location: login.php");
        exit;
    }

    if ($_SESSION['role'] !== $role_required) {
        header("Location: login.php");
        exit;
    }
}
?>