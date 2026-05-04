<?php
// Enable error reporting (ONLY FOR DEVELOPMENT)
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'db/db.php';

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
                    exit;

                } else {
                    header("Location: attendee_view.php");
                    exit;
                }

            } else {
                $error_message = "Invalid username or password.";
            }

        } catch (PDOException $e) {
            /* Show real error during development */
            $error_message = "Database Error: " . $e->getMessage();
        }
    }
}
?>

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