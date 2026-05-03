<?php
session_start();
require_once 'auth_check.php';
protect_page('Admin');

require_once 'db/db.php';

/* Fetch events */
$stmt = $pdo->query("
    SELECT e.*, u.username 
    FROM events e
    JOIN users u ON e.couple_id = u.id
    ORDER BY e.id DESC
");
$events = $stmt->fetchAll();

/* Approve event */
if (isset($_POST['approve_event'])) {
    $event_id = $_POST['event_id'];

    $update = $pdo->prepare("UPDATE events SET event_status = 'Approved' WHERE id = ?");
    $update->execute([$event_id]);

    header("Location: admin_dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<?php include 'includes/header.php'; ?>

<section class="about-bg">

    <div class="container about-section fade-in">
        <h1>Admin Dashboard</h1>
        <p class="about-intro">
            Manage wedding events, approve requests and monitor all system activity.
        </p>
    </div>

</section>

<!-- DASHBOARD -->
<section class="features container fade-in">

    <h2>Wedding Requests</h2>

    <div class="admin-grid">

        <?php foreach ($events as $event): ?>

        <div class="admin-card">

            <h3>Event #<?php echo $event['id']; ?></h3>

            <p><strong>Couple:</strong> <?php echo htmlspecialchars($event['username']); ?></p>
            <p><strong>Location:</strong> <?php echo htmlspecialchars($event['location']); ?></p>
            <p><strong>Food:</strong> <?php echo htmlspecialchars($event['food_choice']); ?></p>
            <p><strong>Decoration:</strong> <?php echo htmlspecialchars($event['decoration_style']); ?></p>

            <p class="status">
                Status:
                <span class="<?php echo strtolower($event['event_status']); ?>">
                    <?php echo $event['event_status']; ?>
                </span>
            </p>

            <?php if ($event['event_status'] == 'Pending'): ?>
                <form method="POST">
                    <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                    <button class="btn btn-primary" name="approve_event">
                        Approve Event
                    </button>
                </form>
            <?php else: ?>
                <button class="btn" disabled>Already Approved</button>
            <?php endif; ?>

        </div>

        <?php endforeach; ?>

    </div>

</section>

<?php include 'includes/footer.php'; ?>

</body>
</html>