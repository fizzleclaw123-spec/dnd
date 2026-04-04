<?php
session_start();
require 'db.php';

if (!isset($_SESSION["user_id"]) || !isset($_POST["adventurer_name"])) {
    header("Location: dashboard.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM adventurers WHERE user_id = ?");
$stmt->execute([$_SESSION["user_id"]]);
$adv = $stmt->fetch();

if ($adv) {
    $stmt = $pdo->prepare("UPDATE adventurers SET name = ? WHERE user_id = ?");
    $stmt->execute([$_POST["adventurer_name"], $_SESSION["user_id"]]);
} else {
    $stmt = $pdo->prepare("INSERT INTO adventurers (user_id, name) VALUES (?, ?)");
    $stmt->execute([$_SESSION["user_id"], $_POST["adventurer_name"]]);
}

// Next step: redirect to class selection
$_SESSION["message"] = "Great name! Now, select your class.";
header("Location: setup_adventurer_class.php");
exit;
?>
