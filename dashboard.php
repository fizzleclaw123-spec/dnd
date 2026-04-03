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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>D&D Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { 
            background: #1a1a1a; 
            color: #d4af37; 
            font-family: "Georgia", serif; 
            min-height: 100vh;
        }
        .dashboard-card { 
            background: #2d2d2d; 
            border: 3px solid #d4af37; 
            border-radius: 15px; 
            padding: 20px;
            margin-bottom: 20px;
        }
        .btn-dnd { background: #8b0000; color: white; border: 2px solid #5a0000; }
    </style>
</head>
<body class="bg-dark text-light">
    <div class="container py-4">
        <h1 class="text-center text-warning mb-4">Welcome, Adventurer!</h1>
        <div class="row justify-content-center">
            <div class="col-12 col-md-6 col-lg-4">
                <div class="dashboard-card">
                    <h3>Progression</h3>
                    <p><strong>Level:</strong> <?= htmlspecialchars($prog['level']) ?></p>
                    <p><strong>XP:</strong> <?= htmlspecialchars($prog['xp']) ?></p>
                    <p><strong>Skill Points:</strong> <?= htmlspecialchars($prog['skill_points']) ?></p>
                    <div class="d-grid gap-2">
                        <a href="character_sheet.php" class="btn btn-warning">View Character Sheet</a>
                        <a href="start_adventure.php" class="btn btn-primary">Start Adventure</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
