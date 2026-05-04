<?php
// Enable error reporting (ONLY FOR DEVELOPMENT - remove later if needed)
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db/db.php';

$message = "";

/* Process form only when submitted */
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Sanitize inputs
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $role     = $_POST['role'];

    /* Validate required fields */
    if (empty($username) || empty($email) || empty($password) || empty($role)) {
        $message = "Error: All fields are required.";

    } else {

        try {

            /* Check duplicate user */
            $checkStmt = $pdo->prepare("
                SELECT id 
                FROM users 
                WHERE username = ? OR email = ?
            ");
            $checkStmt->execute([$username, $email]);

            if ($checkStmt->rowCount() > 0) {

                $message = "Error: Username or Email is already registered.";

            } else {

                /* Hash password securely */
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                /* Insert user */
                $stmt = $pdo->prepare("
                    INSERT INTO users (username, email, password, role)
                    VALUES (?, ?, ?, ?)
                ");

                $stmt->execute([
                    $username,
                    $email,
                    $hashed_password,
                    $role
                ]);

                /* Redirect after success (IMPORTANT - avoids resubmit error) */
                header("Location: login.php?registered=1");
                exit;
            }

        } catch (PDOException $e) {

            /* Show real error during development */
            $message = "Database Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - Wedding Management</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<h2>Create an Account</h2>

<?php if (!empty($message)): ?>
    <div style="padding:10px; margin-bottom:15px; border:1px solid #ccc;">
        <?php echo $message; ?>
    </div>
<?php endif; ?>

<form method="POST" action="register.php">

    <label>Username:</label><br>
    <input type="text" name="username" required><br><br>

    <label>Email:</label><br>
    <input type="email" name="email" required><br><br>

    <label>Password:</label><br>
    <input type="password" name="password" required><br><br>

    <label>I am a:</label><br>
    <select name="role" required>
        <option value="">-- Select Role --</option>
        <option value="Organiser">Organiser (Wedding Couple)</option>
        <option value="Attendee">Attendee (Guest)</option>
    </select><br><br>

    <button type="submit">Register</button>
</form>

<p>Already have an account? <a href="login.php">Login here</a></p>

</body>
</html>