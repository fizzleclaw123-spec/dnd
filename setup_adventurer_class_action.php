<?php
session_start();
require 'db.php';

if (!isset($_SESSION["user_id"]) || !isset($_POST["adventurer_class"])) {
    header("Location: dashboard.php");
    exit;
}

$stmt = $pdo->prepare("UPDATE adventurers SET class = ? WHERE user_id = ?");
$stmt->execute([$_POST["adventurer_class"], $_SESSION["user_id"]]);

// Next step: redirect to stats selection
header("Location: setup_adventurer_stats.php");
exit;
?>
