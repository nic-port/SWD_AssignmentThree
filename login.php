<?php
// 1. Start the session at the very top
session_start();

// 2. Include your database connection (using your folder structure)
require_once 'db/db.php';

// Initialize a variable for error messages
$error_message = "";

// 3. Process the form only when it is submitted via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Sanitize inputs to prevent basic XSS (Server-side Validation)
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Basic Validation: Ensure fields aren't empty
    if (empty($username) || empty($password)) {
        $error_message = "Please enter both username and password.";
    } else {
        try {
            // 4. Use a Prepared Statement to find the user
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            // 5. Verify the password hash stored in the DB
            if ($user && password_verify($password, $user['password'])) {
                
                // Regenerate session ID to prevent Session Fixation (Security best practice)
                session_regenerate_id(true);

                // Store user data in the session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role']    = $user['role'];

                // 6. Complete Role-Based Routing
                // This ensures the user lands on the correct "Home"
                if ($_SESSION['role'] === 'Admin') {
                    header("Location: admin_dashboard.php");
                } elseif ($_SESSION['role'] === 'Organiser') {
                    header("Location: organiser_dashboard.php");
                } else {
                    header("Location: attendee_view.php");
                }
                exit(); // Always exit after a header redirect
            } else {
                $error_message = "Invalid username or password.";
            }
        } catch (PDOException $e) {
            // Log the error and show a generic message for security
            error_log($e->getMessage());
            $error_message = "A system error occurred. Please try again later.";
        }
    }
}
?>

<!-- 7. The HTML Form Part (In the same file or a separate one) -->
<!DOCTYPE html>
<html>
<head>
    <title>Login - Wedding Management</title>

        <link rel="stylesheet" href="assets/style.css">

</head>
<body>
    <h2>Login</h2>

    <?php if (!empty($error_message)): ?>
        <p style="color: red;"><?php echo $error_message; ?></p>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <label>Username:</label><br>
        <input type="text" name="username" required><br><br>
        
        <label>Password:</label><br>
        <input type="password" name="password" required><br><br>
        
        <button type="submit">Login</button>
    </form>
</body>
</html>