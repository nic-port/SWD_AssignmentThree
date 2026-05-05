<?php
require_once 'check_roles.php';
protect_page('Admin');

require_once 'db/db.php';

/* =================================================
   HELPER: USERNAME GENERATOR
================================================= */
function generateUsername($name, $pdo) {

    $base = strtolower(trim($name));
    $base = preg_replace('/\s+/', '.', $base);

    $username = $base;
    $i = 1;

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");

    while (true) {
        $stmt->execute([$username]);
        $exists = $stmt->fetchColumn();

        if ($exists == 0) break;

        $username = $base . $i;
        $i++;
    }

    return $username;
}

/* =================================================
   POST ACTIONS
================================================= */

/* -------------------------
   CREATE USER
------------------------- */
if (isset($_POST['create_user'])) {

    $name = trim($_POST['new_username']);
    $email = trim($_POST['new_email']);
    $role = $_POST['new_role'];
    $password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);

    $specialty = $_POST['specialty'] ?? 'General';
    $phone = $_POST['phone'] ?? '';

    if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/', $password)) {

        $message = "Password must be at least 8 characters long and include at least one letter and one number.";
        $message_type = "error";
    }

    if (!empty($name) && !empty($email)) {

        try {
            $pdo->beginTransaction();

            // username logic
            $username = ($role === 'Staff')
                ? generateUsername($name, $pdo)
                : $name;

            // insert user
            $stmt = $pdo->prepare("
                INSERT INTO users (username, email, password, role)
                VALUES (?, ?, ?, ?)
            ");

            $stmt->execute([$username, $email, $password, $role]);

            // insert staff
            if ($role === 'Staff') {

                $stmt2 = $pdo->prepare("
                    INSERT INTO staff (staff_name, specialty, phone_number)
                    VALUES (?, ?, ?)
                ");

                $stmt2->execute([$name, $specialty, $phone]);
            }

            $pdo->commit();
            $message = "User created successfully: $username";

        } catch (Exception $e) {
            $pdo->rollBack();
            $message = $e->getMessage();
        }
    }
}

/* -------------------------
   LOAD USER FOR EDIT
------------------------- */
$editUser = null;

if (isset($_POST['load_user'])) {

    $id = $_POST['edit_user_id'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);

    $editUser = $stmt->fetch();

    if ($editUser && $editUser['role'] === 'Staff') {

        $stmt2 = $pdo->prepare("
            SELECT * FROM staff WHERE staff_name = ?
        ");

        $stmt2->execute([$editUser['username']]);

        $staff = $stmt2->fetch();

        if ($staff) {
            $editUser['specialty'] = $staff['specialty'];
            $editUser['phone'] = $staff['phone_number'];
        }
    }
}

/* -------------------------
   UPDATE USER
------------------------- */
if (isset($_POST['update_user'])) {

    $id = $_POST['user_id'];
    $name = $_POST['edit_username'];
    $email = $_POST['edit_email'];
    $role = $_POST['edit_role'];

    $specialty = $_POST['edit_specialty'] ?? '';
    $phone = $_POST['edit_phone'] ?? '';

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("
            UPDATE users
            SET username = ?, email = ?, role = ?
            WHERE id = ?
        ");

        $stmt->execute([$name, $email, $role, $id]);

        if ($role === 'Staff') {

            $stmt2 = $pdo->prepare("
                UPDATE staff
                SET specialty = ?, phone_number = ?
                WHERE staff_name = ?
            ");

            $stmt2->execute([$specialty, $phone, $name]);
        }

        $pdo->commit();
        $message = "User updated successfully";

    } catch (Exception $e) {
        $pdo->rollBack();
        $message = $e->getMessage();
    }
}

/* -------------------------
   APPROVE EVENT
------------------------- */
if (isset($_POST['approve_event'])) {

    $event_id = $_POST['event_id'];

    $stmt = $pdo->prepare("
        UPDATE events
        SET event_status = 'Approved'
        WHERE id = ?
    ");

    $stmt->execute([$event_id]);

    header("Location: admin_dashboard.php");
    exit;
}

/* =================================================
   DATA FETCHING
================================================= */

$events = $pdo->query("
    SELECT e.*, u.username
    FROM events e
    JOIN users u ON e.couple_id = u.id
    ORDER BY e.id DESC
")->fetchAll();

$users = $pdo->query("
    SELECT id, username, email, role
    FROM users
    ORDER BY id DESC
")->fetchAll();
?>

<?php include 'includes/header.php'; ?>

<section class="about-bg">
    <div class="container about-section fade-in">
        <h1>Admin Dashboard</h1>
        <p class="about-intro">
            Manage wedding events, users and system activity.
        </p>
    </div>
</section>

<!-- EVENTS -->
<section class="features container fade-in">
    <h2>Wedding Requests</h2>

    <div class="admin-grid">
        <?php foreach ($events as $event): ?>
        <div class="admin-card">

            <h3>Event #<?= $event['id'] ?></h3>

            <p><strong>Couple:</strong> <?= htmlspecialchars($event['username']) ?></p>
            <p><strong>Location:</strong> <?= htmlspecialchars($event['location']) ?></p>
            <p><strong>Food:</strong> <?= htmlspecialchars($event['food_choice']) ?></p>
            <p><strong>Decoration:</strong> <?= htmlspecialchars($event['decoration_style']) ?></p>

            <p class="status">
                Status:
                <span class="<?= strtolower($event['event_status']) ?>">
                    <?= $event['event_status'] ?>
                </span>
            </p>

            <?php if ($event['event_status'] === 'Pending'): ?>
                <form method="POST">
                    <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                    <button class="btn btn-primary" name="approve_event">Approve</button>
                </form>
            <?php else: ?>
                <button class="btn" disabled>Approved</button>
            <?php endif; ?>

        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- CREATE USER -->
<section class="container fade-in mb-5">
    <div class="form-card glass">
        <h3>Create Account</h3>

        <form method="POST" class="form-grid">

            <input type="text" name="new_username" placeholder="Name" required>
            <input type="email" name="new_email" placeholder="Email" required>
            <input type="password" name="new_password" placeholder="Password" required>

            <select name="new_role" id="roleSelect" onchange="toggleStaffFields()">
                <option value="Organiser">Organiser</option>
                <option value="Staff">Staff</option>
            </select>

            <select class="staff-fields hidden">
                <option value="Photography" name="specialty" placeholder="Specialty">Photography</option>
                <option value="Catering" name="specialty" placeholder="Specialty">Catering</option>
                <option value="Decor" name="specialty" placeholder="Specialty">Decor</option>
                <option value="Music" name="specialty" placeholder="Specialty">Music</option>
                <option value="Lighting" name="specialty" placeholder="Specialty">Lighting</option>
                <option value="General" name="specialty" placeholder="Specialty">General</option>
            </select>

            <button class="btn btn-primary" name="create_user">Add</button>
        </form>
    </div>
</section>

<!-- STAFF LIST -->
<section class="features container fade-in">
    <h2>Manage Staff</h2>

    <?php
    $stmt = $pdo->prepare("SELECT * FROM users WHERE role = 'Staff'");
    $stmt->execute();
    $staff = $stmt->fetchAll();
    ?>

    <div class="admin-grid">

        <?php foreach ($staff as $st): ?>
            <div class="admin-card">

                <h3><?= htmlspecialchars($st['username']) ?></h3>

                <p><strong>ID:</strong> <?= $st['id'] ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($st['email']) ?></p>
                <p><strong>Role:</strong> <?= $st['role'] ?></p>

                <button class="btn btn-primary"
                    onclick="openEdit(
                        <?= $st['id'] ?>,
                        '<?= htmlspecialchars($st['username']) ?>',
                        '<?= htmlspecialchars($st['email']) ?>',
                        '<?= $st['role'] ?>'
                    )">
                    Edit
                </button>

            </div>
        <?php endforeach; ?>

    </div>
</section>

<!-- EDIT USER -->
<section id="editUserSection" class="container fade-in mb-5 hidden-section">

    <div class="form-card glass">

        <h3>Edit User</h3>

        <form method="POST" class="form-grid">

            <input type="hidden" name="user_id" id="edit_user_id">

            <input type="text" name="edit_username" id="edit_username">
            <input type="email" name="edit_email" id="edit_email">

            <select name="edit_role" id="edit_role">
                <option value="Organiser">Organiser</option>
                <option value="Staff">Staff</option>
            </select>

            <label for="edit_specialty">Specialty</label>
            <select name="edit_specialty" id="edit_specialty">
                <option value="Photography">Photography</option>
                <option value="Catering">Catering</option>
                <option value="Decor">Decor</option>
                <option value="Music">Music</option>
                <option value="Lighting">Lighting</option>
                <option value="General">General</option>
            </select>

            <input type="text" name="edit_phone" id="edit_phone" placeholder="Phone number">

            <button class="btn btn-primary" name="update_user">
                Save
            </button>

        </form>

    </div>

</section>

<?php include 'includes/footer.php'; ?>
<script src="js/script.js"></script>
</body>