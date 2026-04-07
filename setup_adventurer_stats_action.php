<?php
session_start();
require 'db.php';

if (!isset($_SESSION["user_id"]) || !isset($_POST["stats"])) {
    header("Location: dashboard.php");
    exit;
}

$stmt = $pdo->prepare("SELECT id FROM adventurers WHERE user_id = ?");
$stmt->execute([$_SESSION["user_id"]]);
$adv = $stmt->fetch();
$_SESSION['adventurer_id'] = $adv['id'];
$stats = $_POST["stats"];
// Basic total validation
if (array_sum($stats) > 40) {
    $_SESSION["message"] = "Stat points exceed the limit of 40.";
    header("Location: setup_adventurer_stats.php");
    exit;
}

// Update adventurer_stats
$stmt = $pdo->prepare("INSERT OR REPLACE INTO adventurer_stats (adventurer_id, strength, perception, endurance, charisma, intelligence, agility, luck) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

$stmt->execute([
    $_SESSION['adventurer_id'],
    $stats['Strength'], 
    $stats['Perception'], 
    $stats['Endurance'], 
    $stats['Charisma'], 
    $stats['Intelligence'], 
    $stats['Agility'], 
    $stats['Luck']
]);

header("Location: setup_adventurer_skills.php");
exit;
?>
