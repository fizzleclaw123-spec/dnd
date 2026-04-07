<?php
session_start();
require 'db.php';

if (!isset($_SESSION["user_id"]) || !isset($_POST["skills"])) {
    header("Location: setup_adventurer_skills.php");
    exit;
}

// Optional: Validate total points sum if needed
$total_spent = array_sum($_POST["skills"]);
if ($total_spent != 40) {
    $_SESSION["message"] = "You must spend exactly 40 skill points.";
    header("Location: setup_adventurer_skills.php");
    exit;
}

$stmt = $pdo->prepare("SELECT id FROM adventurers WHERE user_id = ?");
$stmt->execute([$_SESSION["user_id"]]);
$adv = $stmt->fetch();
$_SESSION['adventurer_id'] = $adv['id'];
$_SESSION["skills"] = $_POST["skills"];
header("Location: setup_adventurer_review.php");
exit;
?>
