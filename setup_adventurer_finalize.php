<?php
session_start();
require 'db.php';

$user_id = $_SESSION["user_id"];

if (!isset($_POST["action"]) || $_POST["action"] !== "finalize") {
    header("Location: dashboard.php"); 
    exit;
}

// Update adventurer status to complete
$stmt = $pdo->prepare("UPDATE adventurers SET is_complete = 1 WHERE id = ?");
$stmt->execute([$_SESSION['adventurer_id']]);
error_log("Attempting finalize for adventurer_id: " . $_SESSION['adventurer_id']);

// Save skills
$stmt = $pdo->prepare("DELETE FROM adventurer_skills WHERE adventurer_id = ?");
$stmt->execute([$_SESSION['adventurer_id']]);

foreach ($_SESSION["skills"] as $name => $level) {
    if ($level > 0) {
        // Insert into library if not exists
        $stmt = $pdo->prepare("INSERT OR IGNORE INTO skill_library (name) VALUES (?)");
        $stmt->execute([$name]);
        // Get id
        $stmt = $pdo->prepare("SELECT id FROM skill_library WHERE name = ?");
        $stmt->execute([$name]);
        $skill_id = $stmt->fetchColumn();
        
        $stmt = $pdo->prepare("INSERT INTO adventurer_skills (adventurer_id, skill_id, level) VALUES (?, ?, ?)");
        $stmt->execute([$_SESSION['adventurer_id'], $skill_id, $level]);
    }
}

// Clean up session and redirect
unset($_SESSION["skills"]);
header("Location: dashboard.php");
exit;
?>
