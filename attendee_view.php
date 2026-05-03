<?php
session_start();
// 1. Include the modular guard. It should handle session_start() internally.
require_once 'auth_check.php';

// 2. The Guarantee: This one line replaces your manual 'if' check.
protect_page('Attendee'); 

// 3. Include the database bridge
require_once 'db/db.php';

$user_id = $_SESSION['user_id'];
$message = "";

// 2. Handle Gift Selection (The "Update" in CRUD)
if (isset($_POST['claim_gift'])) {
    $gift_id = $_POST['gift_id'];
    
    // We update the gift only if it hasn't been taken yet
    $updateStmt = $pdo->prepare("UPDATE gifts SET guest_id = ?, is_taken = 1 WHERE id = ? AND is_taken = 0");
    if ($updateStmt->execute([$user_id, $gift_id])) {
        $message = "Success! You have claimed this gift.";
    } else {
        $message = "Sorry, someone else just claimed that gift.";
    }
}

// 3. Fetch Event Details and Available Gifts
// Note: In a real app, you'd filter by a specific event_id. Here we show all active gifts.
$giftStmt = $pdo->query("SELECT * FROM gifts WHERE is_taken = 0");
$gifts = $giftStmt->fetchAll();
?>

<?php include 'includes/header.php'; ?>

<!-- PAGE HEADER -->
<section class="features container fade-in">

    <h2>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>

    <p>
        Here you can view available wedding gifts and manage your commitments.
    </p>

    <p><a href="logout.php" class="btn">Logout</a></p>

    <?php if ($message): ?>
        <div class="feature-card glass">
            <strong><?php echo $message; ?></strong>
        </div>
    <?php endif; ?>

</section>

<!-- GIFTS LIST -->
<section class="features container fade-in">

    <h2>Wedding Gift List</h2>

    <p>Please select an item you would like to bring to the wedding:</p>

    <div class="features-grid">

        <?php if (count($gifts) > 0): ?>
            <?php foreach ($gifts as $gift): ?>

                <div class="feature-card glass">

                    <h3><?php echo htmlspecialchars($gift['item_name']); ?></h3>

                    <form method="POST" action="attendee_view.php">
                        <input type="hidden" name="gift_id" value="<?php echo $gift['id']; ?>">
                        <button type="submit" name="claim_gift" class="btn btn-primary">
                            I'll bring this
                        </button>
                    </form>

                </div>

            <?php endforeach; ?>
        <?php else: ?>

            <div class="feature-card glass">
                <p>No available gifts at the moment. Check back later!</p>
            </div>

        <?php endif; ?>

    </div>

</section>

<!-- MY COMMITMENTS -->
<section class="features container fade-in">

    <h2>My Commitments</h2>

    <div class="feature-card glass">

        <ul style="list-style:none; padding:0; line-height:2;">
            <?php
            // Show what this specific guest has already promised
            $myGifts = $pdo->prepare("SELECT item_name FROM gifts WHERE guest_id = ?");
            $myGifts->execute([$user_id]);

            $hasGifts = false;

            while ($row = $myGifts->fetch()) {
                $hasGifts = true;
                echo "<li>✔ " . htmlspecialchars($row['item_name']) . "</li>";
            }

            if (!$hasGifts) {
                echo "<li>You have not selected any gifts yet.</li>";
            }
            ?>
        </ul>

    </div>

</section>

<?php include 'includes/footer.php'; ?>