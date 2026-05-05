<?php
require_once 'check_roles.php';
protect_page('Organiser');
require_once 'db/db.php';

$user_id = $_SESSION['user_id'];
$message = "";

// 1. Locate the event
$eventStmt = $pdo->prepare("SELECT id FROM events WHERE couple_id = ? LIMIT 1");
$eventStmt->execute([$user_id]);
$event = $eventStmt->fetch();

if (!$event) {
    header("Location: event_creation.php");
    exit();
}

$current_event_id = $event['id'];

// 2. Identify if we are in "Edit Mode" for a specific gift
$edit_id = isset($_GET['edit_id']) ? $_GET['edit_id'] : null;

// 3. Handle Actions (Add, Update, Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ADD
    if (isset($_POST['add_gift'])) {
        $item = trim($_POST['item_name']);
        if (!empty($item)) {
            $stmt = $pdo->prepare("INSERT INTO gifts (item_name, is_taken, event_id) VALUES (?, 0, ?)");
            $stmt->execute([$item, $current_event_id]);
            $message = "Gift added!";
        }
    }
    // UPDATE
    if (isset($_POST['save_gift'])) {
        $new_name = trim($_POST['new_item_name']);
        $gift_id = $_POST['gift_id'];
        $stmt = $pdo->prepare("UPDATE gifts SET item_name = ? WHERE id = ? AND event_id = ?");
        $stmt->execute([$new_name, $gift_id, $current_event_id]);
        header("Location: manage_gifts.php"); // Clear the edit_id from URL
        exit();
    }
    // DELETE
    if (isset($_POST['delete_gift'])) {
        $gift_id = $_POST['gift_id'];
        $stmt = $pdo->prepare("DELETE FROM gifts WHERE id = ? AND event_id = ?");
        $stmt->execute([$gift_id, $current_event_id]);
        $message = "Item removed.";
    }
}

// 4. Fetch the list
$giftsStmt = $pdo->prepare("SELECT * FROM gifts WHERE event_id = ? ORDER BY id DESC");
$giftsStmt->execute([$current_event_id]);
$gifts = $giftsStmt->fetchAll();
?>

<?php include 'includes/header.php'; ?>

<main class="container fade-in mt-5">

    <section class="feature-card glass gift-container">

        <div class="text-center mb-5">
            <h2 class="display-6 text-primary">Gift Registry</h2>
            <p class="text-muted">Refine your list for the perfect celebration.</p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-info text-center mb-4">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <!-- ADD FORM -->
        <form method="POST" class="d-flex gap-2 mb-5 gift-form">
            <input type="text" name="item_name" placeholder="New Gift Idea..." required class="form-control gift-input">
            <button type="submit" name="add_gift" class="btn btn-primary gift-btn">Add</button>
        </form>

        <!-- LIST -->
        <div class="gift-list">

            <?php if (empty($gifts)): ?>
                <p class="text-center text-muted">Your registry is currently empty.</p>
            <?php endif; ?>

            <?php foreach ($gifts as $g): ?>

                <div class="gift-item glass d-flex justify-content-between align-items-center mb-3">

                    <?php if ($edit_id == $g['id']): ?>

                        <form method="POST" class="d-flex gap-2 w-100 m-0 gift-edit-form">
                            <input type="hidden" name="gift_id" value="<?php echo $g['id']; ?>">

                            <input type="text"
                                   name="new_item_name"
                                   value="<?php echo htmlspecialchars($g['item_name']); ?>"
                                   required
                                   class="form-control">

                            <button type="submit" name="save_gift" class="btn btn-success btn-sm">
                                Save
                            </button>

                            <a href="manage_gifts.php" class="btn btn-outline-dark btn-sm">
                                Cancel
                            </a>
                        </form>

                    <?php else: ?>

                        <div class="d-flex align-items-center gap-3">
                            <span class="gift-icon">🎁</span>
                            <span class="gift-name">
                                <?php echo htmlspecialchars($g['item_name']); ?>
                            </span>
                        </div>

                        <div class="d-flex gap-2">
                            <a href="?edit_id=<?php echo $g['id']; ?>"
                               class="btn btn-outline-primary btn-sm">
                                Edit
                            </a>

                            <form method="POST" class="m-0"
                                  onsubmit="return confirm('Remove this item?');">

                                <input type="hidden" name="gift_id" value="<?php echo $g['id']; ?>">

                                <button type="submit"
                                        name="delete_gift"
                                        class="btn btn-outline-danger btn-sm">
                                    Remove
                                </button>
                            </form>
                        </div>

                    <?php endif; ?>

                </div>

            <?php endforeach; ?>

        </div>

        <div class="text-center mt-5 pt-4 border-top">
            <a href="organiser_dashboard.php"
               class="btn btn-dark w-100 gift-finish-btn">
                Finish & Go to Dashboard
            </a>
        </div>

    </section>

</main>

<?php include 'includes/footer.php'; ?>