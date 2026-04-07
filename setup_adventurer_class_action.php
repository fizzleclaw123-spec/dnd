<?php
session_start();
require 'db.php';

if (!isset($_SESSION["user_id"]) || !isset($_POST["adventurer_class"])) {
    header("Location: dashboard.php");
    exit;
}

$class = $_POST["adventurer_class"];
$stmt = $pdo->prepare("SELECT id FROM class_library WHERE name = ?");
$stmt->execute([$class]);
$class_id = $stmt->fetchColumn();

$stmt = $pdo->prepare("UPDATE adventurers SET class_id = ? WHERE user_id = ?");
$stmt->execute([$class_id, $_SESSION["user_id"]]);

// Next step: redirect to stats selection
header("Location: setup_adventurer_stats.php");
exit;
?>
