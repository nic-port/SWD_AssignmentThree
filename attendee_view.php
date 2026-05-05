<?php
require_once 'check_roles.php';
protect_page('Attendee'); 
require_once 'db/db.php';

$user_id = $_SESSION['user_id'];
$message = "";

// 1. REPAIR THE BRIDGE: Get the user's email from the 'users' table first
$userStmt = $pdo->prepare("SELECT email FROM users WHERE id = ?");
$userStmt->execute([$user_id]);
$userData = $userStmt->fetch();
$user_email = $userData['email'] ?? '';

// 2. CONNECT TO GUEST LIST: Now find that email in the 'guests' table
$guestLookup = $pdo->prepare("
    SELECT g.id as guest_id, g.event_id 
    FROM guests g 
    WHERE g.email = ?
");
$guestLookup->execute([$user_email]);
$guestData = $guestLookup->fetch();

if (!$guestData) {
    die("<div style='margin-top:100px; text-align:center; font-family: sans-serif;'>
            <h2>Invitation Not Found</h2>
            <p>Your account email (<strong>$user_email</strong>) is not on any guest list.</p>
            <p>Please ensure the Organiser invited this exact email address.</p>
            <a href='logout.php' class='btn btn-danger'>Logout</a>
         </div>");
}

$real_guest_id = $guestData['guest_id'];
$event_id = $guestData['event_id'];

// 3. FETCH EVENT DETAILS
$eventStmt = $pdo->prepare("
    SELECT cp.bride_name, cp.groom_name, e.location, cp.wedding_date 
    FROM events e 
    JOIN couple_profiles cp ON e.couple_id = cp.user_id 
    WHERE e.id = ?
");
$eventStmt->execute([$event_id]);
$eventInfo = $eventStmt->fetch();

// 4. HANDLE GIFT CLAIM (Using $real_guest_id to satisfy the Foreign Key)
if (isset($_POST['claim_gift'])) {
    $gift_id = $_POST['gift_id'];
    $updateStmt = $pdo->prepare("UPDATE gifts SET guest_id = ?, is_taken = 1 WHERE id = ? AND is_taken = 0");
    if ($updateStmt->execute([$real_guest_id, $gift_id])) {
        $message = "Success! You have claimed this gift.";
    }
}

// 4b. HANDLE UN-CLAIMING A GIFT
if (isset($_POST['remove_gift'])) {
    $gift_id = $_POST['gift_id'];
    // We only update if the gift actually belongs to the logged-in guest
    $removeStmt = $pdo->prepare("UPDATE gifts SET guest_id = NULL, is_taken = 0 WHERE id = ? AND guest_id = ?");
    if ($removeStmt->execute([$gift_id, $real_guest_id])) {
        $message = "Gift removed from your list.";
    }
}

// 5. FETCH GIFTS
$giftStmt = $pdo->prepare("SELECT * FROM gifts WHERE event_id = ? AND is_taken = 0");
$giftStmt->execute([$event_id]);
$gifts = $giftStmt->fetchAll();
?>

<?php include 'includes/header.php'; ?>

<main class="container fade-in attendee-page">

    <?php if ($eventInfo): ?>
    <section class="event-banner glass">
        <h2>You're invited to the wedding of</h2>
        <h1>
            <?php echo htmlspecialchars($eventInfo['bride_name'] . " & " . $eventInfo['groom_name']); ?>
        </h1>

        <p>
            <strong>When:</strong> <?php echo date('F j, Y', strtotime($eventInfo['wedding_date'])); ?><br>
            <strong>Where:</strong> <?php echo htmlspecialchars($eventInfo['location']); ?>
        </p>
    </section>
    <?php endif; ?>

    <header class="page-header">
        <h1>Hello, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
        <p>Browse the gift registry below.</p>
    </header>

    <?php if ($message): ?>
        <div class="alert-info"><?php echo $message; ?></div>
    <?php endif; ?>

    <section class="features-grid">
        <?php foreach ($gifts as $gift): ?>
            <div class="feature-card glass">
                <h3><?php echo htmlspecialchars($gift['item_name']); ?></h3>

                <form method="POST">
                    <input type="hidden" name="gift_id" value="<?php echo $gift['id']; ?>">
                    <button type="submit" name="claim_gift" class="btn btn-primary w-100">
                        I'll bring this
                    </button>
                </form>
            </div>
        <?php endforeach; ?>
    </section>

    <section class="feature-card glass my-gifts">
        <h2>Your Contributions</h2>

        <ul class="list-unstyled">
            <?php
            $myGifts = $pdo->prepare("SELECT id, item_name FROM gifts WHERE guest_id = ?");
            $myGifts->execute([$real_guest_id]);
            $contributions = $myGifts->fetchAll();

            if (empty($contributions)): ?>
                <li class="muted">No gifts selected yet.</li>
            <?php else:
                foreach ($contributions as $row): ?>
                    <li class="gift-item">
                        <span>✔ <?php echo htmlspecialchars($row['item_name']); ?></span>

                        <form method="POST" onsubmit="return confirm('Remove this gift?');">
                            <input type="hidden" name="gift_id" value="<?php echo $row['id']; ?>">
                            <button type="submit" name="remove_gift" class="btn btn-outline-danger btn-sm">
                                Remove
                            </button>
                        </form>
                    </li>
                <?php endforeach;
            endif; ?>
        </ul>
    </section>

</main>

<?php include 'includes/footer.php'; ?>