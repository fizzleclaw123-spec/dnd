<?php
session_start();
require 'db.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION["user_id"];

// Check if user has a fully created adventurer (with stats)
$stmt = $pdo->prepare("SELECT id FROM adventurers WHERE user_id = ? AND name IS NOT NULL AND class IS NOT NULL AND strength IS NOT NULL");
$stmt->execute([$user_id]);
$adventurer = $stmt->fetch();

if (!$adventurer) {
    header("Location: setup_adventurer.php");
    exit;
}

// Ensure progression row exists
$pdo->prepare("INSERT OR IGNORE INTO character_progression (user_id, skill_points) VALUES (?, 0)")->execute([$user_id]);

$stmt = $pdo->prepare("SELECT * FROM character_progression WHERE user_id = ?");
$stmt->execute([$user_id]);
$prog = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>D&D Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark text-light">
    <div class="container mt-5">
        <h1>Welcome, Adventurer!</h1>
        <div class="row">
            <div class="col-md-4">
                <div class="card bg-secondary text-light p-3">
                    <h3>Progression</h3>
                    <p>Level: <?= $prog['level'] ?></p>
                    <p>XP: <?= $prog['xp'] ?></p>
                    <p>Skill Points: <?= $prog['skill_points'] ?></p>
                    <a href="character_sheet.php" class="btn btn-warning mt-2">View Character Sheet</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
