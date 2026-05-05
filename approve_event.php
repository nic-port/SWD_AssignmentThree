<?php
session_start();
require_once 'auth_check.php';
protect_page('Admin');

require_once 'db/db.php';

/* Check if event ID exists */
if (!isset($_GET['id'])) {
    header("Location: admin_dashboard.php");
    exit;
}

$event_id = $_GET['id'];

/* Update event status to Approved */
$stmt = $pdo->prepare("
    UPDATE events 
    SET event_status = 'Approved' 
    WHERE id = ?
");
$stmt->execute([$event_id]);

/* Redirect back to dashboard */
header("Location: admin_dashboard.php");
exit;