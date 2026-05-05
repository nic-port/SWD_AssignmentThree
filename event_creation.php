<?php
require_once 'check_roles.php';
protect_page('Organiser'); //
require_once 'db/db.php';

$user_id = $_SESSION['user_id'];
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bride = $_POST['bride_name'];
    $groom = $_POST['groom_name'];
    $wedding_date = $_POST['wedding_date'];
    $bio = $_POST['bio'];

    try {
        $pdo->beginTransaction();

        // 1. Create Profile
        $stmt1 = $pdo->prepare("INSERT INTO couple_profiles (user_id, bride_name, groom_name, wedding_date, bio) VALUES (?, ?, ?, ?, ?)");
        $stmt1->execute([$user_id, $bride, $groom, $wedding_date, $bio]);

        $pdo->commit();

        // 3. Redirect to Event Settings to finish the setup
        header("Location: edit_event.php?status=new_profile");
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Database Error: " . $e->getMessage();
    }
}
?>

<?php include 'includes/header.php'; ?>

<main class="container fade-in profile-page">

    <section class="profile-card glass">

        <div class="section-title text-center">
            <h2>Create Your Wedding Profile</h2>
            <p>Tell us a bit about the happy couple to get started.</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="event_creation.php" class="profile-form">

            <label>Bride's Name</label>
            <input type="text" name="bride_name" placeholder="e.g. Maria" required>

            <label>Groom's Name</label>
            <input type="text" name="groom_name" placeholder="e.g. John" required>

            <label>Wedding Date</label>
            <input type="date" name="wedding_date" required>

            <label>Our Story (Short Bio)</label>
            <textarea name="bio" rows="4"
                      placeholder="A brief message for your guests..."></textarea>

            <button type="submit" class="btn btn-primary full-width mt-4">
                Save & Continue to Dashboard
            </button>

        </form>

    </section>

</main>

<?php include 'includes/footer.php'; ?>