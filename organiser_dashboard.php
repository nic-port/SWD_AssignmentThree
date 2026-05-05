<?php
require_once 'check_roles.php';
protect_page('Organiser');
require_once 'db/db.php';

$user_id = $_SESSION['user_id'];

// 1. Fetch Couple Profile details
$profileStmt = $pdo->prepare("SELECT * FROM couple_profiles WHERE user_id = ?");
$profileStmt->execute([$user_id]);
$profile = $profileStmt->fetch();

if (!$profile) {
    header("Location: event_creation.php");
    exit();
}

// 2. UPDATED STATS: Querying the Attendee role and the gifts table
$totalGuests = $pdo->query("
    SELECT COUNT(*) 
    FROM users 
    WHERE role = 'Attendee'
")->fetchColumn();

$claimedGifts = $pdo->query("
    SELECT COUNT(*) 
    FROM gifts 
    WHERE is_taken = 1
")->fetchColumn();

$giftCount = $pdo->query("SELECT COUNT(*) FROM gifts WHERE is_taken = 1");
$claimedGifts = $giftCount->fetchColumn();
?>

<?php include 'includes/header.php'; ?>

<main class="container fade-in page-spacing">

    <header class="text-center mb-5 dashboard-header">
        <h1 class="dashboard-title">Wedding Management Hub</h1>
        <p class="lead">
            Organiser: <?php echo htmlspecialchars($profile['bride_name'] . " & " . $profile['groom_name']); ?>
        </p>
        <a href="logout.php" class="btn btn-outline-danger btn-sm logout-btn">
            Logout
        </a>
    </header>

    <!-- STATS -->
    <div class="features-grid mb-5 stats-grid">

        <div class="feature-card glass">
            <div class="icon-box">📅</div>
            <h3>Wedding Date</h3>
            <p><?php echo $profile['wedding_date'] ?? 'Not set'; ?></p>
        </div>

        <div class="feature-card glass">
            <div class="icon-box">👥</div>
            <h3>Total Guests</h3>
            <p><?php echo $totalGuests; ?> Invited</p>
        </div>

        <div class="feature-card glass">
            <div class="icon-box">🎁</div>
            <h3>Gifts Claimed</h3>
            <p><?php echo $claimedGifts; ?> Reserved</p>
        </div>

    </div>

    <!-- ACTIONS -->
    <section class="mb-5">

        <div class="section-title text-center mb-4">
            <h2>Management Options</h2>
        </div>

        <div class="features-grid management-grid">

            <a href="manage_guests.php" class="feature-card glass text-decoration-none banner-btn">
                <div class="icon-box">✉️</div>
                <h3>Guest List</h3>
                <p>Manage your attendees and invitations.</p>
            </a>

            <a href="manage_gifts.php" class="feature-card glass text-decoration-none banner-btn">
                <div class="icon-box">🎁</div>
                <h3>Gift Registry</h3>
                <p>View and edit your desired wedding gifts.</p>
            </a>

            <a href="event_overview.php" class="feature-card glass text-decoration-none banner-btn highlight-card">
                <div class="icon-box">⚙️</div>
                <h3>Event Logistics</h3>
                <p>Settings, Staff, Vendors, and Venue Info.</p>
            </a>

        </div>

    </section>

</main>

<?php include 'includes/footer.php'; ?>