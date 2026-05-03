<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<nav class="navbar">

    <div class="logo">
        WeddingSystem
    </div>

    <ul class="nav-links">

    <link rel="stylesheet" href="assets/style.css">

        <li><a href="index.php">Home</a></li>
        <li><a href="about.php">About</a></li>
        <li><a href="gallery.php">Gallery</a></li>

        <?php if (!isset($_SESSION['user_id'])): ?>

            <li><a href="login.php">Login</a></li>
            <li><a href="register.php">Register</a></li>

        <?php else: ?>

            <?php if ($_SESSION['role'] === 'Admin'): ?>
                <li><a href="admin_dashboard.php">Admin</a></li>
            <?php endif; ?>

            <?php if ($_SESSION['role'] === 'Organiser'): ?>
                <li><a href="organiser_dashboard.php">Dashboard</a></li>
            <?php endif; ?>

            <?php if ($_SESSION['role'] === 'Attendee'): ?>
                <li><a href="attendee_view.php">My Area</a></li>
            <?php endif; ?>

            <li><a href="logout.php">Logout</a></li>

        <?php endif; ?>

    </ul>

</nav>