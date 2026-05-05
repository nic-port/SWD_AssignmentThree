<?php
session_start();
require_once 'db/db.php';

/* Get event ID from URL */
if (!isset($_GET['event_id'])) {
    die("Event not found.");
}

$event_id = $_GET['event_id'];

/* Fetch event */
$stmt = $pdo->prepare("
    SELECT e.*, u.username 
    FROM events e
    JOIN users u ON e.couple_id = u.id
    WHERE e.id = ?
");
$stmt->execute([$event_id]);
$event = $stmt->fetch();

if (!$event) {
    die("Invalid event.");
}

/* Fetch gifts */
$stmt = $pdo->prepare("
    SELECT * FROM gifts 
    WHERE event_id = ?
");
$stmt->execute([$event_id]);
$gifts = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Wedding Invitation</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<?php include 'includes/header.php'; ?>

<section class="about-bg">

    <div class="container about-section">
        <h1>You are Invited 💍</h1>
        <p>Wedding hosted by <?= htmlspecialchars($event['username']) ?></p>
    </div>

</section>

<section class="features container">

    <div class="admin-card">

        <h2>Event Details</h2>

        <p><strong>Location:</strong> <?= htmlspecialchars($event['location']) ?></p>
        <p><strong>Food:</strong> <?= htmlspecialchars($event['food_choice']) ?></p>
        <p><strong>Decoration:</strong> <?= htmlspecialchars($event['decoration_style']) ?></p>
        <p><strong>Status:</strong> <?= $event['event_status'] ?></p>

    </div>

</section>

<section class="features container">

    <h2>Gift List </h2>

    <div class="admin-grid">

        <?php foreach ($gifts as $gift): ?>

            <div class="admin-card">

                <h3><?= htmlspecialchars($gift['item_name']) ?></h3>

                <?php if ($gift['is_taken'] == 0): ?>
                    <a href="take_gift.php?id=<?= $gift['id'] ?>" class="btn btn-primary">
                        Choose Gift
                    </a>
                <?php else: ?>
                    <button class="btn" disabled>Already Taken</button>
                <?php endif; ?>

            </div>

        <?php endforeach; ?>

    </div>

</section>

<?php include 'includes/footer.php'; ?>

</body>
</html>