<?php
session_start();
require_once 'auth_check.php';
protect_page('Admin');

require_once 'db/db.php';

if (!isset($_GET['id'])) {
    header("Location: admin_dashboard.php");
    exit;
}

$event_id = $_GET['id'];

$stmt = $pdo->prepare("
    UPDATE events 
    SET event_status = 'Rejected' 
    WHERE id = ?
");
$stmt->execute([$event_id]);

header("Location: admin_dashboard.php");
exit;