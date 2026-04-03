<?php
session_start();
require 'db.php';

if (!isset($_SESSION["user_id"]) || !isset($_POST["skills"])) {
    header("Location: setup_adventurer_skills.php");
    exit;
}

// Optional: Validate total points sum if needed
$total_spent = array_sum($_POST["skills"]);
if ($total_spent > 40) {
    $_SESSION["message"] = "Cannot spend more than 40 skill points.";
    header("Location: setup_adventurer_skills.php");
    exit;
}

$_SESSION["skills"] = $_POST["skills"];

header("Location: setup_adventurer_review.php");
exit;
?>
