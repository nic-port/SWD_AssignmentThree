<?php
require_once 'check_roles.php';
protect_page('Organiser');
require_once 'db/db.php';

$user_id = $_SESSION['user_id'];

// 1. Fetch Profile (Names and Date)
$profileStmt = $pdo->prepare("SELECT bride_name, groom_name, wedding_date FROM couple_profiles WHERE user_id = ?");
$profileStmt->execute([$user_id]);
$profile = $profileStmt->fetch();

// 2. Fetch Event (Location, Food, Style) - Matching your CREATE TABLE schema
$eventStmt = $pdo->prepare("SELECT location, food_choice, decoration_style, event_status FROM events WHERE couple_id = ?");
$eventStmt->execute([$user_id]);
$event = $eventStmt->fetch();

// 3. Count Staff
$staffCount = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'Staff'")->fetchColumn();
?>

<?php include 'includes/header.php'; ?>
<main class="container fade-in overview-page">

    <section class="feature-card glass overview-card">

        <h2 class="text-center mb-5 overview-title">
            Event Overview
        </h2>

        <div class="row g-4">

            <!-- CORE INFO -->
            <div class="col-md-6">
                <div class="overview-box core-box border-start border-primary border-3 bg-light">

                    <h4 class="h6 text-uppercase text-muted">
                        Core Details
                    </h4>

                    <p class="mb-1">
                        <strong>Couple:</strong>
                        <?php echo htmlspecialchars($profile['bride_name'] . " & " . $profile['groom_name']); ?>
                    </p>

                    <p class="mb-1">
                        <strong>Date:</strong>
                        <?php echo date('F j, Y', strtotime($profile['wedding_date'])); ?>
                    </p>

                    <p class="mb-0">
                        <strong>Location:</strong>
                        <?php echo htmlspecialchars($event['location'] ?? 'Not specified'); ?>
                    </p>

                </div>
            </div>

            <!-- LOGISTICS -->
            <div class="col-md-6">
                <div class="overview-box logistics-box border-start border-success border-3 bg-light">

                    <h4 class="h6 text-uppercase text-muted">
                        Logistics & Style
                    </h4>

                    <p class="mb-1">
                        <strong>Style:</strong>
                        <?php echo htmlspecialchars($event['decoration_style'] ?? 'Not set'); ?>
                    </p>

                    <p class="mb-1">
                        <strong>Catering:</strong>
                        <?php echo htmlspecialchars($event['food_choice'] ?? 'Menu pending'); ?>
                    </p>

                    <p class="mb-0">
                        <strong>Status:</strong>

                        <span class="badge status-badge <?php echo ($event['event_status'] ?? 'Pending') === 'Approved' ? 'bg-success' : 'bg-warning'; ?>">
                            <?php echo $event['event_status'] ?? 'Pending'; ?>
                        </span>

                    </p>

                </div>
            </div>

        </div>

        <!-- STAFF -->
        <div class="staff-summary text-center mt-4">
            <p class="text-muted">
                Currently, you have
                <strong><?php echo $staffCount; ?></strong>
                staff members assigned to this event.
            </p>
        </div>

        <!-- ACTIONS -->
        <div class="overview-actions mt-5 pt-3 border-top d-flex gap-3">

            <a href="edit_event.php" class="btn btn-primary flex-grow-1 overview-btn">
                Edit Event Information
            </a>

            <a href="organiser_dashboard.php" class="btn btn-outline-secondary overview-btn">
                Back to Hub
            </a>

        </div>

    </section>

</main>

<?php include 'includes/footer.php'; ?>