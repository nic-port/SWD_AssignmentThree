<?php
session_start();
require_once 'auth_check.php';
protect_page('Organiser');

require_once 'db/db.php';

/* =========================
   CREATE EVENT (INSERT)
========================= */
if (isset($_POST['create_event'])) {

    $location = $_POST['location'];
    $food = $_POST['food_choice'];
    $decoration = $_POST['decoration_style'];

    try {
        $stmt = $pdo->prepare("
            INSERT INTO events 
            (couple_id, location, food_choice, decoration_style, event_status)
            VALUES (?, ?, ?, ?, 'Pending')
        ");

        $stmt->execute([
            $_SESSION['user_id'],
            $location,
            $food,
            $decoration
        ]);

        header("Location: organiser_dashboard.php");
        exit;

    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
}

/* =========================
   FETCH LATEST EVENT
========================= */
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT * FROM events 
    WHERE couple_id = ?
    ORDER BY id DESC
    LIMIT 1
");
$stmt->execute([$user_id]);
$event = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Couple Dashboard</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<?php include 'includes/header.php'; ?>

<section class="about-bg">

    <div class="container about-section">
        <h1>Welcome to Your Wedding Dashboard</h1>
        <p>Manage and track your wedding request in real time.</p>
    </div>

</section>

<section class="features container">

<!-- =========================
     CREATE EVENT FORM
========================= -->
<h2>Create Wedding Request</h2>

<form method="POST">

    <label>Location:</label><br>
    <input type="text" name="location" required><br><br>

    <label>Food Choice:</label><br>
    <input type="text" name="food_choice" required><br><br>

    <label>Decoration Style:</label><br>
    <input type="text" name="decoration_style" required><br><br>

    <button type="submit" name="create_event">
        Submit Request
    </button>
</form>

<br><hr><br>

<!-- =========================
     DISPLAY EVENT
========================= -->

<?php if ($event): ?>

    <div class="admin-card">

        <h2>Your Wedding Request</h2>

        <p><strong>Location:</strong> <?= htmlspecialchars($event['location']) ?></p>
        <p><strong>Food:</strong> <?= htmlspecialchars($event['food_choice']) ?></p>
        <p><strong>Decoration:</strong> <?= htmlspecialchars($event['decoration_style']) ?></p>

        <p class="status">
            Status:
            <span class="<?= strtolower($event['event_status']) ?>">
                <?= $event['event_status'] ?>
            </span>
        </p>

        <?php if ($event['event_status'] == 'Rejected'): ?>
            <p style="color:red;">
                Your request was rejected. Please contact admin.
            </p>

        <?php elseif ($event['event_status'] == 'Approved'): ?>
            <p style="color:green;">
                Your wedding has been approved! 🎉
            </p>

        <?php else: ?>
            <p style="color:orange;">
                Your request is pending approval.
            </p>
        <?php endif; ?>

    </div>

<?php else: ?>

    <p>No wedding request found. Please create one above.</p>

<?php endif; ?>

</section>

<?php include 'includes/footer.php'; ?>

</body>
</html>