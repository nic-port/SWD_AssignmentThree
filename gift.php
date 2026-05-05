<?php
session_start();
require_once 'db/db.php';

if (!isset($_GET['id'])) {
    header("Location: guest_view.php");
    exit;
}

$gift_id = $_GET['id'];

/* Mark gift as taken */
$stmt = $pdo->prepare("
    UPDATE gifts 
    SET is_taken = 1 
    WHERE id = ?
");
$stmt->execute([$gift_id]);

header("Location: guest_view.php");
exit;