<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$adventure_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Check if user is already a member
$stmt = $pdo->prepare("SELECT id FROM adventurers WHERE user_id = ? LIMIT 1");
$stmt->execute([$user_id]);
$adv = $stmt->fetch();
$adventurer_id = $adv ? $adv['id'] : null;

$stmt = $pdo->prepare("SELECT * FROM adventure_members WHERE adventure_id = ? AND user_id = ?");
$stmt->execute([$adventure_id, $user_id]);

if ($stmt->rowCount() == 0) {
    $stmt = $pdo->prepare("INSERT INTO adventure_members (adventure_id, user_id, adventurer_id, is_ready) VALUES (?, ?, ?, 0)");
    $stmt->execute([$adventure_id, $user_id, $adventurer_id]);
}

header("Location: lobby.php?id=" . $adventure_id);
exit();
