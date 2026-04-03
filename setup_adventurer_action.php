<?php
session_start();
require 'db.php';

if (!isset($_SESSION["user_id"]) || !isset($_POST["adventurer_name"])) {
    header("Location: dashboard.php");
    exit;
}

$stmt = $pdo->prepare("INSERT INTO adventurers (user_id, name) VALUES (?, ?)");
$stmt->execute([$_SESSION["user_id"], $_POST["adventurer_name"]]);

// Next step: redirect to class selection
$_SESSION["message"] = "Great name! Now, select your class.";
header("Location: setup_adventurer_class.php");
exit;
?>
