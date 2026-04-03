<?php
session_start();
require 'db.php';

if (!isset($_SESSION["user_id"]) || !isset($_POST["stats"])) {
    header("Location: dashboard.php");
    exit;
}

$stats = $_POST["stats"];
// Basic total validation
if (array_sum($stats) > 40) {
    $_SESSION["message"] = "Stat points exceed the limit of 40.";
    header("Location: setup_adventurer_stats.php");
    exit;
}

$stmt = $pdo->prepare("UPDATE adventurers SET 
    strength = ?, 
    perception = ?, 
    endurance = ?, 
    charisma = ?, 
    intelligence = ?, 
    agility = ?, 
    luck = ? 
    WHERE user_id = ?");

$stmt->execute([
    $stats['Strength'], 
    $stats['Perception'], 
    $stats['Endurance'], 
    $stats['Charisma'], 
    $stats['Intelligence'], 
    $stats['Agility'], 
    $stats['Luck'],
    $_SESSION["user_id"]
]);

header("Location: setup_adventurer_skills.php");
exit;
?>
