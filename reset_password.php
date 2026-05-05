<?php
session_start();
require_once 'db/db.php';

$message = "";

/*
=================================================
RESET PASSWORD LOGIC
=================================================
*/
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST['username']);
    $new_password = $_POST['new_password'];

    if (empty($username) || empty($new_password)) {
        $message = "Please fill in all fields.";
    } else {
        try {
            // Check if user exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user) {

                // Hash new password
                $hashed = password_hash($new_password, PASSWORD_DEFAULT);

                // Update password
                $update = $pdo->prepare("
                    UPDATE users 
                    SET password = ? 
                    WHERE username = ?
                ");

                $update->execute([$hashed, $username]);

                $message = "Password reset successfully. You can now log in.";

            } else {
                $message = "User not found.";
            }

        } catch (Exception $e) {
            $message = "Error: " . $e->getMessage();
        }
    }
}
?>

<?php include 'includes/header.php'; ?>

<main class="container reset-page fade-in">

    <section class="feature-card reset-card">

        <h2 class="reset-title">Reset Password</h2>

        <?php if (!empty($message)): ?>
            <div class="message-box">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required>
            </div>

            <div class="form-group">
                <label>New Password</label>
                <input type="password" name="new_password" required>
            </div>

            <button type="submit" class="btn btn-primary w-100">
                Reset Password
            </button>

        </form>

        <div class="back-login">
            <a href="login.php">Back to Login</a>
        </div>

    </section>

</main>

<?php include 'includes/footer.php'; ?>