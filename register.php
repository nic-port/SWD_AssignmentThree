<?php
// 1. Establish the connection using your folder structure from image_11723a.png
require_once 'db/db.php'; 

$message = ""; // Variable to hold feedback for the user

// 2. Process only when the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect and sanitize (Server-side Validation)
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $role     = $_POST['role']; // 'Organiser' or 'Attendee'

    // 3. Validation Check
    if (empty($username) || empty($email) || empty($password) || empty($role)) {
        $message = "Error: All fields are required.";
    } else {
        try {
            // 4. Check if Username or Email already exists (Avoid Duplicates)
            $checkStmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $checkStmt->execute([$username, $email]);
            
            if ($checkStmt->rowCount() > 0) {
                $message = "Error: Username or Email is already registered.";
            } else {
                // 5. Hashing for security
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // 6. Execute the INSERT using a Prepared Statement
                $sql = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$username, $email, $hashed_password, $role]);
                
                $message = "Registration successful! <a href='login.php'>Click here to login</a>";
            }
        } catch (PDOException $e) {
            // Log error for the dev, show generic message for the user
            error_log($e->getMessage());
            $message = "A system error occurred. Please try again later.";
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

    <!-- Display the feedback message to the user -->
    <?php if (!empty($message)): ?>
        <div style="padding: 10px; margin-bottom: 15px; border: 1px solid #ccc;">
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
            <option value="Organiser">Organiser (Planning a Wedding)</option>
            <option value="Attendee">Attendee (Guest)</option>
        </select><br><br>

        <button type="submit">Register</button>
    </form>

    <p>Already have an account? <a href="login.php">Login here</a></p>
</body>
</html>