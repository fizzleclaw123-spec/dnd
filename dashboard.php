<?php
session_start();
require 'db.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION["user_id"];

// Ensure exploration record exists
$pdo->prepare("INSERT OR IGNORE INTO character_progression (user_id, skill_points) VALUES (?, 0)")->execute([$user_id]);

// Fetch progression data
$stmt = $pdo->prepare("SELECT level, xp, skill_points FROM character_progression WHERE user_id = ?");
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
            margin: 0; 
            min-height: 100vh; 
        }
        .dashboard { 
            display: flex; 
            flex-direction: column; 
            align-items: center; 
            padding: 2rem; 
        }
        .card { 
            background: #2d2d2d; 
            border: 3px solid #d4af37; 
            border-radius: 15px; 
            padding: 1.5rem; 
            margin-bottom: 1.5rem; 
            width: 100%; 
            max-width: 400px; 
        }
        .btn-dnd { 
            background: #8b0000; 
            color: white; 
            border: 2px solid #5a0000; 
            padding: 0.75rem 1.5rem; 
            margin: 0.5rem 0; 
        }
    </style>
</head>
<body class="bg-dark text-light">
    <div class="dashboard">
        <h1 class="text-center text-warning mb-4 text-white">Welcome, Adventurer!</h1>

        <!-- Progression Card -->
        <div class="card">
            <div class="card-body">
                <h3 class="card-title">Progression</h3>
                <div class="d-flex flex-column mb-3">
                    <p class="text-danger mb-1"><strong>Level:</strong> <?= htmlspecialchars($prog['level']) ?></p>
                    <p class="text-danger mb-1"><strong>XP:</strong> <?= htmlspecialchars($prog['xp']) ?></p>
                    <p class="text-danger mb-1"><strong>Skill Points:</strong> <?= htmlspecialchars($prog['skill_points']) ?></p>
                </div>
                <div class="mt-3">
                    <a href="character_sheet.php" class="btn btn-warning btn-lg btn-dnd">View Character Sheet</a>
                    <a href="start_adventure.php" class="btn btn-primary btn-lg btn-dnd">Start Adventure</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
