<?php
require_once 'check_roles.php';
protect_page('Organiser');
require_once 'db/db.php';

$message = "";

// 1. Identify if we are in "Edit Mode" for a specific guest
$edit_id = isset($_GET['edit_id']) ? $_GET['edit_id'] : null;

// 2. Handle Actions (Add, Update, Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // ADD GUEST (Creating a new User)
    // ADD GUEST (Creating a new User AND a Guest record)
    if (isset($_POST['add_guest'])) {
        $name = trim($_POST['username']);
        $email = trim($_POST['email']);
        
        // We need to know WHICH event this Organiser is managing.
        // Assuming you store the organiser's user_id in the session:
        $organiser_id = $_SESSION['user_id'];
        
        // First, find the event_id owned by this organiser
        $stmtEvent = $pdo->prepare("SELECT id FROM events WHERE couple_id = ?");
        $stmtEvent->execute([$organiser_id]);
        $event = $stmtEvent->fetch();
        $event_id = $event['id'] ?? null;

        if (!empty($name) && !empty($email) && $event_id) {
            try {
                $pdo->beginTransaction();

                // 1. Create the User Account (for Login)
                $password = password_hash('guest123', PASSWORD_DEFAULT);
                $stmtUser = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'Attendee')");
                $stmtUser->execute([$name, $email, $password]);

                // 2. Create the Guest Invitation (The Bridge for gifts/details)
                $stmtGuest = $pdo->prepare("INSERT INTO guests (name, email, event_id) VALUES (?, ?, ?)");
                $stmtGuest->execute([$name, $email, $event_id]);

                $pdo->commit();
                $message = "Guest invited successfully to both lists!";
            } catch (Exception $e) {
                $pdo->rollBack();
                $message = "Error: Could not add guest. " . $e->getMessage();
            }
        } else {
            $message = "Error: Name, Email, or Event ID missing.";
        }
    }
}

// 3. Fetch all Attendees
$guestStmt = $pdo->query("SELECT id, username, email FROM users WHERE role = 'Attendee' ORDER BY username ASC");
$guests = $guestStmt->fetchAll();
?>

<?php include 'includes/header.php'; ?>

<main class="container fade-in page-spacing">

    <section class="feature-card glass guest-card-container">

        <div class="text-center mb-5">
            <h2 class="guest-title">Guest List Management</h2>
            <p class="guest-subtitle">View and manage everyone invited to the big day.</p>
        </div>

        <?php if ($message): ?>
            <div class="alert glass guest-alert">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <!-- ADD GUEST FORM -->
        <form method="POST" class="guest-form">

            <input type="text" name="username" placeholder="Guest Name" required class="guest-input">
            <input type="email" name="email" placeholder="Email Address" required class="guest-input">

            <button type="submit" name="add_guest" class="btn btn-primary guest-btn">
                Invite
            </button>

        </form>

        <!-- TABLE -->
        <div class="table-responsive">

            <table class="guest-table">

                <thead>
                    <tr class="guest-table-head">
                        <th>Guest Name</th>
                        <th>Email</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>

                <tbody>

                <?php if (empty($guests)): ?>
                    <tr>
                        <td colspan="3" class="guest-empty">
                            No guests have been added yet.
                        </td>
                    </tr>
                <?php else: ?>

                    <?php foreach ($guests as $guest): ?>

                        <tr class="guest-row">

                            <?php if ($edit_id == $guest['id']): ?>

                                <form method="POST">
                                    <input type="hidden" name="guest_id" value="<?= $guest['id'] ?>">

                                    <td>
                                        <input type="text" name="new_username"
                                            value="<?= htmlspecialchars($guest['username']) ?>"
                                            class="guest-edit-input">
                                    </td>

                                    <td>
                                        <input type="email" name="new_email"
                                            value="<?= htmlspecialchars($guest['email']) ?>"
                                            class="guest-edit-input">
                                    </td>

                                    <td class="text-right">
                                        <button type="submit" name="save_guest" class="btn btn-sm btn-success">
                                            Save
                                        </button>

                                        <a href="manage_guests.php" class="btn btn-sm btn-outline-dark">
                                            Cancel
                                        </a>
                                    </td>
                                </form>

                            <?php else: ?>

                                <td class="guest-name">
                                    <?= htmlspecialchars($guest['username']) ?>
                                </td>

                                <td class="guest-email">
                                    <?= htmlspecialchars($guest['email']) ?>
                                </td>

                                <td class="text-right">

                                    <a href="?edit_id=<?= $guest['id'] ?>"
                                       class="btn btn-sm btn-outline-primary guest-action-btn">
                                        Edit
                                    </a>

                                    <form method="POST"
                                          class="guest-delete-form"
                                          onsubmit="return confirm('Remove this guest?');">

                                        <input type="hidden" name="guest_id" value="<?= $guest['id'] ?>">

                                        <button type="submit"
                                                name="delete_guest"
                                                class="btn btn-sm btn-outline-danger guest-action-btn">
                                            Remove
                                        </button>

                                    </form>

                                </td>

                            <?php endif; ?>

                        </tr>

                    <?php endforeach; ?>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

        <div class="text-center mt-5">
            <a href="organiser_dashboard.php" class="btn btn-dark w-100 guest-back-btn">
                Back to Dashboard
            </a>
        </div>

    </section>

</main>

<?php include 'includes/footer.php'; ?>