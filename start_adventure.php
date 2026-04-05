<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$adventure_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Set adventure to active
$stmt = $pdo->prepare("UPDATE adventures SET status = 'active' WHERE id = ?");
$stmt->execute([$adventure_id]);

header("Location: adventure.php?id=" . $adventure_id);
exit();
