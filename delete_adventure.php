<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$adventure_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Check if user is the creator
$stmt = $pdo->prepare("SELECT created_by FROM adventures WHERE id = ?");
$stmt->execute([$adventure_id]);
$adventure = $stmt->fetch();

if ($adventure && $adventure['created_by'] == $user_id) {
    // Delete related logs and members first
    $pdo->prepare("DELETE FROM adventure_logs WHERE adventure_id = ?")->execute([$adventure_id]);
    $pdo->prepare("DELETE FROM adventure_members WHERE adventure_id = ?")->execute([$adventure_id]);
    // Delete the adventure
    $pdo->prepare("DELETE FROM adventures WHERE id = ?")->execute([$adventure_id]);
}

header("Location: dashboard.php");
exit();
