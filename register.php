<?php
require_once 'db/db.php'; 

$message = ""; 
$message_type = ""; // To distinguish between error and success styling

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $role     = $_POST['role']; 

    if (empty($username) || empty($email) || empty($password) || empty($role)) {
    $message = "Error: All fields are required.";
    $message_type = "error";

    } elseif (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/', $password)) {

        $message = "Password must be at least 8 characters long and include at least one letter and one number.";
        $message_type = "error";

    } else {
        try {
            // 1. Fetch user
            $checkStmt = $pdo->prepare("SELECT id, role FROM users WHERE username = ? OR email = ?");
            $checkStmt->execute([$username, $email]);
            $existingUser = $checkStmt->fetch();

            if ($existingUser) {

                if ($existingUser['role'] === 'Attendee') {

                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                    $updateSql = "UPDATE users SET username = ?, password = ? WHERE id = ?";
                    $updateStmt = $pdo->prepare($updateSql);
                    $updateStmt->execute([$username, $hashed_password, $existingUser['id']]);

                    $message = "Welcome! Your invitation has been activated. <a href='login.php'>Login here</a>";
                    $message_type = "success";

                } else {
                    $message = "Error: Username or Email is already registered.";
                    $message_type = "error";
                }

            } else {

                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                $sql = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$username, $email, $hashed_password, $role]);

                $message = "Registration successful! <a href='login.php'>Click here to login</a>";
                $message_type = "success";
            }

        } catch (PDOException $e) {
            error_log($e->getMessage());
            $message = "A system error occurred. Please try again later.";
            $message_type = "error";
        }
    }
}

include 'includes/header.php'; 
?>

<main class="container register-page fade-in">

    <section class="feature-card register-card">

        <h2 class="register-title">Create an Account</h2>

        <?php if (!empty($message)): ?>
            <div class="message <?= $message_type ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="register.php">

            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required>
            </div>

            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input     type="password" 
                            name="password" 
                            required
                            pattern="^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$"
                            title="Minimum 8 characters, at least one letter and one number">
            </div>

            <div class="form-group">
                <label>I am a:</label>
                <select name="role" required>
                    <option value="">-- Select Role --</option>
                    <option value="Organiser">Organiser (Planning a Wedding)</option>
                    <option value="Attendee">Attendee (Guest)</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary w-100">
                Register Now
            </button>

        </form>

        <p class="register-footer">
            Already have an account?
            <a href="login.php">Login here</a>
        </p>

    </section>

</main>

<?php include 'includes/footer.php'; ?>