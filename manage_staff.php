<?php
require_once 'check_roles.php';
protect_page('Organiser');

require_once 'db/db.php';

/* =================================================
   GET EVENT ID
================================================= */
$event_id = $_GET['event_id'] ?? null;

if (!$event_id) {
    die("No event selected. Please go back and choose an event.");
}

/* =================================================
   HANDLE ASSIGN STAFF
================================================= */
if (isset($_POST['assign_staff'])) {

    $staff_id = $_POST['staff_id'];

    try {
        // optional: avoid duplicate assignment
        $check = $pdo->prepare("
            SELECT COUNT(*) 
            FROM staff_assignments 
            WHERE event_id = ? AND staff_id = ?
        ");
        $check->execute([$event_id, $staff_id]);

        if ($check->fetchColumn() == 0) {

            $stmt = $pdo->prepare("
                INSERT INTO staff_assignments (event_id, staff_id, task_category, job_title)
                VALUES (?, ?, 'General', 'Assigned Staff')
            ");

            $stmt->execute([$event_id, $staff_id]);
        }

    } catch (Exception $e) {
        die("Error assigning staff: " . $e->getMessage());
    }

    header("Location: manage_staff.php?event_id=" . $event_id);
    exit;
}

/* =================================================
   FETCH STAFF LIST
================================================= */
$stmt = $pdo->query("
    SELECT 
        s.id,
        s.staff_name,
        s.specialty,
        s.phone_number
    FROM staff s
");

$staff = $stmt->fetchAll();

/* =================================================
   FETCH ASSIGNED STAFF (for UI feedback)
================================================= */
$stmt2 = $pdo->prepare("
    SELECT staff_id 
    FROM staff_assignments 
    WHERE event_id = ?
");

$stmt2->execute([$event_id]);
$assigned = $stmt2->fetchAll(PDO::FETCH_COLUMN);
?>

<?php include 'includes/header.php'; ?>

<main class="container fade-in page-spacing">

    <section class="feature-card glass staff-assign-container">

        <div class="section-title text-center">
            <h2>Assign Staff to Event #<?= htmlspecialchars($event_id) ?></h2>
            <p>Select professionals to assign to this wedding event.</p>
        </div>

        <?php if (!empty($staff)): ?>

            <div class="features-grid staff-grid">

                <?php foreach ($staff as $member): ?>

                    <div class="feature-card staff-card">

                        <h4><?= htmlspecialchars($member['staff_name']) ?></h4>

                        <p><strong>Specialty:</strong> <?= htmlspecialchars($member['specialty']) ?></p>
                        <p><strong>Phone:</strong> <?= htmlspecialchars($member['phone_number']) ?></p>

                        <?php if (in_array($member['id'], $assigned)): ?>

                            <button class="btn btn-secondary" disabled>
                                Already Assigned
                            </button>

                        <?php else: ?>

                            <form method="POST" class="staff-form">
                                <input type="hidden" name="staff_id" value="<?= $member['id'] ?>">

                                <a href="organiser_dashboard.php" class="btn btn-primary">
                                    Hire New Vendor
                                </a>
                            </form>

                        <?php endif; ?>

                    </div>

                <?php endforeach; ?>

            </div>

            <?php else: ?>

                <p class="text-center">No staff available.</p>

                <div class="text-center mt-3">
                    <a href="organiser_dashboard.php" class="btn btn-primary">
                        Go to Dashboard
                    </a>
                </div>

            <?php endif; ?>

        <div class="text-center mt-4">
            <a href="event_overview.php" class="btn btn-outline-dark">
                Back
            </a>
        </div>

    </section>

</main>

<?php include 'includes/footer.php'; ?>