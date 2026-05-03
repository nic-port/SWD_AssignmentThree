<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <title>Wedding Management System</title>

    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600&family=Lora:wght@400;500&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="assets/style.css">
</head>

<body>

<nav class="navbar">
    <div class="logo">Wedding App</div>

    <ul class="nav-links">

        <li><a href="index.php">Home</a></li>

        <?php if (isset($_SESSION['user_id'])): ?>

            <!-- logged in -->
            <?php if ($_SESSION['role'] == 'Admin'): ?>
                <li><a href="admin_dashboard.php">Admin</a></li>
            <?php elseif ($_SESSION['role'] == 'Organiser'): ?>
                <li><a href="organiser_dashboard.php">Dashboard</a></li>
            <?php else: ?>
                <li><a href="attendee_view.php">My Event</a></li>
            <?php endif; ?>

            <li><a href="logout.php">Logout</a></li>

        <?php else: ?>

            <!-- not logged in -->
            <li><a href="login.php">Login</a></li>
            <li><a href="register.php">Register</a></li>

        <?php endif; ?>

    </ul>
</nav>