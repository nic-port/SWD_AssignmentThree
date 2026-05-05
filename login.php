<?php
// Enable error reporting (ONLY FOR DEVELOPMENT)
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'db/db.php';
// Initialize a variable for error messages
$error_message = "";

/* Process login only when form is submitted */
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Sanitize inputs
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    /* Validate inputs */
    if (empty($username) || empty($password)) {
        $error_message = "Please enter both username and password.";
    } else {

        try {

            /* Find user by username */
            $stmt = $pdo->prepare("
                SELECT * 
                FROM users 
                WHERE username = ?
            ");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            /* Verify user and password */
            if ($user && password_verify($password, $user['password'])) {

                // Prevent session fixation
                session_regenerate_id(true);

                // Store session data
                $_SESSION['user_id']  = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role']     = $user['role'];

                /* Role-based redirect */
                if ($user['role'] === 'Admin') {
                    header("Location: admin_dashboard.php");
                    exit;

                } elseif ($user['role'] === 'Organiser') {
                    header("Location: organiser_dashboard.php");
<<<<<<< HEAD
                }  elseif ($_SESSION['role'] === 'Staff') {
                    header("Location: staff_dashboard.php");
                }else {
=======
                    exit;

                } else {
>>>>>>> 62a2ab8c549ac27163a59908f622d8451a852ef4
                    header("Location: attendee_view.php");
                    exit;
                }

            } else {
                $error_message = "Invalid username or password.";
            }
<<<<<<< HEAD
            
=======

>>>>>>> 62a2ab8c549ac27163a59908f622d8451a852ef4
        } catch (PDOException $e) {
            /* Show real error during development */
            $error_message = "Database Error: " . $e->getMessage();
        }
    }
}
?>

<<<<<<< HEAD
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
=======
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Wedding Management</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<h2>Login</h2>

<?php if (!empty($error_message)): ?>
    <div style="color:red; margin-bottom:15px;">
        <?php echo $error_message; ?>
    </div>
<?php endif; ?>

<!-- Optional success message after register -->
<?php if (isset($_GET['registered'])): ?>
    <div style="color:green; margin-bottom:15px;">
        Account created successfully! Please login.
    </div>
<?php endif; ?>

<form method="POST" action="login.php">

    <label>Username:</label><br>
    <input type="text" name="username" required><br><br>

    <label>Password:</label><br>
    <input type="password" name="password" required><br><br>

    <button type="submit">Login</button>

</form>

<p>Don't have an account? <a href="register.php">Register here</a></p>

</body>
</html>
>>>>>>> 62a2ab8c549ac27163a59908f622d8451a852ef4
