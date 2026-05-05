<?php
require_once 'check_roles.php';
protect_page('Staff');

require_once 'db/db.php';

// Ensure session username exists
if (!isset($_SESSION['username'])) {
    die("Session expired. Please log in again.");
}

$staffName = $_SESSION['username'];

// Get staff ID
$stmt = $pdo->prepare("SELECT id FROM staff WHERE staff_name = ?");
$stmt->execute([$staffName]);
$staff = $stmt->fetch();

if (!$staff) {
    die("Staff profile not found.");
}

$staffName = $_SESSION['username'];

$stmt = $pdo->prepare("
    SELECT id FROM staff WHERE staff_name = ?
");
$stmt->execute([$staffName]);
$staff = $stmt->fetch();

$staff_id = $staff['id'];

$stmt = $pdo->prepare("
    SELECT 
        e.id AS event_id,
        e.location,
        e.food_choice,
        e.decoration_style,
        e.event_status,
        sa.task_category,
        sa.job_title
    FROM staff_assignments sa
    JOIN events e ON sa.event_id = e.id
    WHERE sa.staff_id = ?
    ORDER BY e.id DESC
");

$stmt->execute([$staff_id]);
$events = $stmt->fetchAll();
?>

<?php include 'includes/header.php'; ?>

<!-- HEADER -->
<section class="about-bg">
    <div class="container about-section fade-in">
        <h1>Staff Dashboard</h1>
        <p class="about-intro">
            View and manage the events assigned to you.
        </p>
    </div>
</section>

<!-- EVENTS -->
<section class="features container fade-in">

    <h2>My Assigned Events</h2>

    <div class="admin-grid">

        <?php if (empty($events)): ?>
            <div class="admin-card text-center">
                <p>No assigned events yet.</p>
            </div>
        <?php endif; ?>

        <?php foreach ($events as $event): ?>

        <div class="admin-card">

            <h3>Event #<?= $event['event_id'] ?></h3>

            <p><strong>Location:</strong> <?= htmlspecialchars($event['location']) ?></p>
            <p><strong>Food:</strong> <?= htmlspecialchars($event['food_choice']) ?></p>
            <p><strong>Decoration:</strong> <?= htmlspecialchars($event['decoration_style']) ?></p>

            <hr class="divider">

            <p><strong>Task:</strong> <?= htmlspecialchars($event['task_category']) ?></p>
            <p><strong>Job Title:</strong> <?= htmlspecialchars($event['job_title']) ?></p>

            <p class="status">
                Status:
                <span class="<?= strtolower($event['event_status']) ?>">
                    <?= $event['event_status'] ?>
                </span>
            </p>

        </div>

        <?php endforeach; ?>

    </div>

</section>

<?php include 'includes/footer.php'; ?>