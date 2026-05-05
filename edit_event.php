<?php
require_once 'check_roles.php';
protect_page('Organiser');
require_once 'db/db.php';

$user_id = $_SESSION['user_id'];

/* ================================
   HANDLE POST FIRST
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $loc = $_POST['location'];
    $food = $_POST['food'];

    // check if event exists
    $stmt = $pdo->prepare("SELECT id FROM events WHERE couple_id = ?");
    $stmt->execute([$user_id]);
    $event = $stmt->fetch();

    if ($event) {
        $event_id = $event['id'];

        $update = $pdo->prepare("
            UPDATE events 
            SET location = ?, food_choice = ?
            WHERE id = ?
        ");
        $update->execute([$loc, $food, $event_id]);

    } else {
        $insert = $pdo->prepare("
            INSERT INTO events (couple_id, location, food_choice)
            VALUES (?, ?, ?)
        ");
        $insert->execute([$user_id, $loc, $food]);

        $event_id = $pdo->lastInsertId();
    }

    header("Location: manage_staff.php?event_id=" . $event_id);
    exit();
}

/* ================================
   LOAD EVENT (GET ONLY)
================================ */
$stmt = $pdo->prepare("SELECT * FROM events WHERE couple_id = ?");
$stmt->execute([$user_id]);
$event = $stmt->fetch();
?>

<?php include 'includes/header.php'; ?>

<main class="container fade-in event-page">

    <section class="event-card glass">

        <h2 class="section-title">Event Settings</h2>

        <form method="POST" class="event-form">

            <label>Location</label>
            <input type="text"
                   name="location"
                   value="<?php echo htmlspecialchars($event['location'] ?? ''); ?>"
                   placeholder="e.g. Grand Estate"
                   required>

            <label>Food Selection</label>
            <select name="food">

                <option value="Buffet"
                    <?php echo (isset($event['food_choice']) && $event['food_choice'] == 'Buffet') ? 'selected' : ''; ?>>
                    Buffet
                </option>

                <option value="Plated"
                    <?php echo (isset($event['food_choice']) && $event['food_choice'] == 'Plated') ? 'selected' : ''; ?>>
                    Plated Service
                </option>

                <option value="Cocktail"
                    <?php echo (isset($event['food_choice']) && $event['food_choice'] == 'Cocktail') ? 'selected' : ''; ?>>
                    Cocktail Style
                </option>

            </select>

            <button type="submit" class="btn btn-primary full-width mt-4">
                Save & Continue
            </button>

        </form>

    </section>

</main>

<?php include 'includes/footer.php'; ?>