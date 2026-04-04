<?php
session_start();
require 'db.php';

$user_id = $_SESSION["user_id"];

// Update adventurer status to complete
$stmt = $pdo->prepare("UPDATE adventurers SET is_complete = 1 WHERE user_id = ?");
$stmt->execute([$user_id]);

// Save skills
$stmt = $pdo->prepare("DELETE FROM character_skills WHERE user_id = ?");
$stmt->execute([$user_id]);

foreach ($_SESSION["skills"] as $name => $level) {
    if ($level > 0) {
        $stmt = $pdo->prepare("INSERT INTO character_skills (user_id, skill_name, level) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $name, $level]);
    }
}

// Clean up session and redirect
unset($_SESSION["skills"]);
header("Location: dashboard.php");
exit;
?>
