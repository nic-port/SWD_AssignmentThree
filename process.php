<?php
//start session if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

//connects to local MySQL using root and empty password
function getDBConnection() {
    $host = 'localhost';
    $user = 'root';        
    $pass = '';            
    $dbname = 'wedding_management';
    $conn = new mysqli($host, $user, $pass, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}

//destroys session

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header("Location: index.php");
    exit();
}

//all form submissions go through here

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    //authenticates existing couple

    if ($action === 'login') {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header("Location: index.php");
        } else {
            echo "Invalid email or password. <a href='index.php'>Try again</a>";
        }
        $stmt->close();
        $conn->close();
        exit();

    }

    //registers new couple

    if ($action === 'create_account') {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        if (empty($username) || empty($email) || empty($password)) {
            echo "All fields required. <a href='index.php'>Back</a>";
            exit();
        }

        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $role = 'Organiser';
        $conn = getDBConnection();

        //check if the email exists

        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();

        if ($check->get_result()->num_rows > 0) {
            echo "Email already registered. <a href='index.php'>Back</a>";
            $check->close();
            $conn->close();
            exit();

        }
        $check->close();
        $stmt = $conn->prepare("INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username, $hashed, $email, $role);
        if ($stmt->execute()) {
            $_SESSION['user_id'] = $stmt->insert_id;
            $_SESSION['username'] = $username;
            header("Location: index.php");
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
        $conn->close();
        exit();

    }

    //all actions to a logged‑in couple

    if (!isset($_SESSION['user_id'])) {
        echo "You must be logged in. <a href='index.php'>Login</a>";
        exit();
    }

    $user_id = $_SESSION['user_id'];
    $conn = getDBConnection();

    //stores or updates food, decor, location

    if ($action === 'event_request') {
        $food = isset($_POST['food']) ? implode(', ', $_POST['food']) : '';
        $decor = $_POST['decor'] ?? '';
        $location = $_POST['location'] ?? '';
        
        //checks if the event exists

        $check = $conn->prepare("SELECT id FROM events WHERE couple_id = ?");
        $check->bind_param("i", $user_id);
        $check->execute();
        $exists = $check->get_result()->num_rows > 0;
        $check->close();
        
        if ($exists) {

            $stmt = $conn->prepare("UPDATE events SET location = ?, food_choice = ?, decoration_style = ? WHERE couple_id = ?");
            $stmt->bind_param("sssi", $location, $food, $decor, $user_id);
        } else {
            $stmt = $conn->prepare("INSERT INTO events (couple_id, location, food_choice, decoration_style, event_status) VALUES (?, ?, ?, ?, 'Pending')");
            $stmt->bind_param("isss", $user_id, $location, $food, $decor);
        }
        if ($stmt->execute()) {
            echo "Event request saved. <a href='index.php'>Go back</a>";
        } else {
            echo "Error: " . $stmt->error;

        }
        $stmt->close();
        $conn->close();
        exit();

    }

    // Manager/guest section : Adds a new guest to the list

    if ($action === 'add_guest') {
        $guestname = trim($_POST['guestname'] ?? '');
        if (empty($guestname)) {
            echo "Guest name required. <a href='index.php'>Back</a>";
            exit();

        }

        // Gets event id based on the user

        $event_query = $conn->prepare("SELECT id FROM events WHERE couple_id = ?");
        $event_query->bind_param("i", $user_id);
        $event_query->execute();
        $event_result = $event_query->get_result();
        $event_row = $event_result->fetch_assoc();
        if (!$event_row) {
            echo "No event found. Please submit an event request first. <a href='index.php'>Back</a>";
            $conn->close();
            exit();

        }

        $event_id = $event_row['id'];
        $stmt = $conn->prepare("INSERT INTO guests (event_id, name, rsvp_status) VALUES (?, ?, 'Pending')");
        $stmt->bind_param("is", $event_id, $guestname);
        if ($stmt->execute()) {
            header("Location: index.php");
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
        $conn->close();
        exit();

    }

    //reservations changes a guest's status pending/confirmed/declined)
    if ($action === 'update_rsvp') {
        $guest_id = (int)($_POST['guest_id'] ?? 0);
        $new_status = $_POST['rsvp_status'] ?? 'Pending';
        $stmt = $conn->prepare("UPDATE guests SET rsvp_status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $guest_id);
        $stmt->execute();
        $stmt->close();
        $conn->close();
        header("Location: index.php");
        exit();
    }
} 
?>