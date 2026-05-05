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
                }  elseif ($_SESSION['role'] === 'Staff') {
                    header("Location: staff_dashboard.php");
                }else {
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

<?php include 'includes/header.php'; ?>

<main class="container login-page fade-in">
    <section class="feature-card login-card">

        <h2 class="login-title">Login</h2>

        <?php if (!empty($error_message)): ?>
            <div class="error-message">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php">

            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>

            <button type="submit" class="btn btn-primary w-100">
                Log In
            </button>

            <div class="forgot-password">
                <a href="reset_password.php">Forgot your password?</a>
            </div>

        </form>

        <div class="register-box">
            <p>New to our platform?</p>
            <a href="register.php" class="btn">Create an Account</a>
        </div>

    </section>
</main>

<?php include 'includes/footer.php'; ?>