<?php
session_start();
require_once 'process.php'; //contains getDBConnection() and other functions

$logged_in = isset($_SESSION['user_id']);
$event_id = null;
$user_id = null;

if ($logged_in) {

    $user_id = $_SESSION['user_id'];
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT id FROM events WHERE couple_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $event = $result->fetch_assoc();
    $event_id = $event ? $event['id'] : null;
    $stmt->close();
    $conn->close();

}
?>
<!DOCTYPE html>
<html>
<head>

    <link rel="stylesheet" href="style.css">
    <title>Member B - Wedding Management</title>

</head>
<body>

    <div class="container">

        <?php if (!$logged_in): ?>
            
            <?php  //form for existing couples to log in ?>
            <h2>Login</h2>

            <form action="process.php" method="post">

                <label>Email:</label> <input type="email" name="email" required><br>
                <label>Password:</label> <input type="password" name="password" required><br>
                <button type="submit" name="action" value="login">Login</button>

            </form>

            <?php?>

            <?php //new couple registration ?>
            <h2>Or Create New Account</h2>

            <form action="process.php" method="post">

                <label>Username:</label> <input type="text" name="username" required><br>
                <label>Email:</label> <input type="email" name="email" required><br>
                <label>Password:</label> <input type="password" name="password" required><br>
                <button type="submit" name="action" value="create_account">Create Account</button>

            </form>
            <?php ?>

        <?php else: ?>
            
            <?php ?>

            <h2>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></h2>
            <p><a href="process.php?action=logout">Logout</a></p>

            <?php ?>

            <?php //Food, Decor, Location forms ?>

            <h2>Event Request (Food/Decor/Location)</h2>
            <form action="process.php" method="post">
                <fieldset>

                    <legend>Food</legend>

                    <label><input type="checkbox" name="food[]" value="buffet"> Buffet</label>
                    <label><input type="checkbox" name="food[]" value="plated"> Plated dinner</label>

                </fieldset>

                <!-- Decoration Options -->

                <fieldset>
                   <fieldset>
                        <legend>Decor</legend>

                        <label><input type="radio" name="decor" value="minimal"> Minimal</label>
                        <label><input type="radio" name="decor" value="rustic"> Rustic</label>
                        <label><input type="radio" name="decor" value="bohemian"> Bohemian</label>
                        <label><input type="radio" name="decor" value="classic"> Classic</label>
                        <label><input type="radio" name="decor" value="modern"> Modern</label>
                        <label><input type="radio" name="decor" value="vintage"> Vintage</label>
                        <label><input type="radio" name="decor" value="glam"> Glamorous</label>

                    </fieldset>
                </fieldset>

                <!-- Location Options -->

                <fieldset>
                    <legend>Location</legend>
                   <select name="location" size="5">

                        <option>Indoor hall</option>
                        <option>Outdoor garden</option>
                        <option>Beach Outdoors</option>
                        <option>Casino</option>
                        <option>Cruise Ship</option>
                        <option>Mountain side</option>

                    </select>
                </fieldset>

                <button type="submit" name="action" value="event_request">Submit Request</button>
            </form>
            <?php?>

            <?php //display guests, update reservation , add new guest ?>

            <h2>Guest List Management</h2>
            <?php if ($event_id): ?>

                <table border="1">
                    <thead>
                        <tr><th>Name</th><th>Reservation Status</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php
                        $conn = getDBConnection();
                        $stmt = $conn->prepare("SELECT id, name, rsvp_status FROM guests WHERE event_id = ?");
                        $stmt->bind_param("i", $event_id);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['rsvp_status']) . "</td>";
                            echo "<td>
                                    <form action='process.php' method='post' style='display:inline;'>
                                        <input type='hidden' name='guest_id' value='{$row['id']}'>
                                        <select name='rsvp_status'>
                                            <option value='Pending'>Pending</option>
                                            <option value='Confirmed'>Confirmed</option>
                                            <option value='Declined'>Declined</option>
                                        </select>
                                        <button type='submit' name='action' value='update_rsvp'>Update</button>
                                    </form>
                                  </td>";
                            echo "</tr>";
                        }
                        $stmt->close();
                        $conn->close();
                        ?>
                    </tbody>

                <tr>

            <?php else: ?>

                <p>No event found. Please submit an event request first.</p>

            <?php endif; ?>
            
            <?php?>
            <h3>Add New Guest</h3>

            <form action="process.php" method="post">

                <label>Guest name:</label> <input type="text" name="guestname" required>
                <button type="submit" name="action" value="add_guest">Add Guest</button>

            </form>
            <?php ?>

        <?php endif; ?>
    </div>
</body>
</html>