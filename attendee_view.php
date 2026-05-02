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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Guest Area - Wedding Management</title>
</head>
<body>
    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
    <p><a href="logout.php">Logout</a></p>

    <?php if ($message): ?>
        <p style="color: green;"><strong><?php echo $message; ?></strong></p>
    <?php endif; ?>

    <h2>Wedding Gift List</h2>
    <p>Please select an item you would like to bring to the wedding:</p>

    <table border="1" cellpadding="10">
        <thead>
            <tr>
                <th>Gift Item</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($gifts) > 0): ?>
                <?php foreach ($gifts as $gift): ?>
                <tr>
                    <td><?php echo htmlspecialchars($gift['item_name']); ?></td>
                    <td>
                        <form method="POST" action="attendee_view.php">
                            <input type="hidden" name="gift_id" value="<?php echo $gift['id']; ?>">
                            <button type="submit" name="claim_gift">I'll bring this</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="2">No available gifts at the moment. Check back later!</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <hr>
    <h3>My Commitments</h3>
    <ul>
        <?php
        // Show what this specific guest has already promised
        $myGifts = $pdo->prepare("SELECT item_name FROM gifts WHERE guest_id = ?");
        $myGifts->execute([$user_id]);
        while ($row = $myGifts->fetch()) {
            echo "<li>" . htmlspecialchars($row['item_name']) . "</li>";
        }
        ?>
    </ul>
</body>
</html>