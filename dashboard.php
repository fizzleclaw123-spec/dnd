<?php
session_start();
require 'db.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION["user_id"];

// Check if adventurer exists AND is complete
$stmt = $pdo->prepare("SELECT * FROM adventurers WHERE user_id = ?");
$stmt->execute([$user_id]);
$adventurer = $stmt->fetch();

if (!$adventurer || $adventurer['is_complete'] == 0) {
    if (!$adventurer) {
        header("Location: setup_adventurer.php");
        exit;
    }
    
    if (empty($adventurer['name'])) {
        header("Location: setup_adventurer.php");
        exit;
    } elseif (empty($adventurer['class'])) {
        header("Location: setup_adventurer_class.php");
        exit;
    } elseif ($adventurer['strength'] == 0 || $adventurer['strength'] == null) {
        header("Location: setup_adventurer_stats.php");
        exit;
    } else {
        header("Location: setup_adventurer_skills.php");
        exit;
    }
}

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
        body { background: #1a1a1a; color: #d4af37; font-family: "Georgia", serif; min-height: 100vh; }
        .card { background: #2d2d2d; border: 3px solid #d4af37; border-radius: 15px; padding: 1.5rem; width: 100%; max-width: 400px; color: #d4af37; }
        .btn-dnd { background: #8b0000; color: white; border: 2px solid #5a0000; padding: 0.75rem; }
        .btn-dnd:hover { background: #8b0000 !important; color: white !important; border: 2px solid #5a0000 !important; filter: brightness(1.2); }
        .prog-row { font-size: 1.2rem; margin-bottom: 0.5rem; }
    </style>
</head>
<body class="bg-dark text-light d-flex justify-content-center align-items-center py-4">
    <div class="container text-center">
        <h1 class="text-warning mb-4">Welcome, Adventurer!</h1>
        <div class="card mx-auto">
            <h3 class="mb-3 text-warning">Progression</h3>
            <div class="d-flex flex-column mb-4">
                <p class="prog-row"><strong>Level:</strong> <?= htmlspecialchars($prog['level']) ?></p>
                <p class="prog-row"><strong>XP:</strong> <?= htmlspecialchars($prog['xp']) ?></p>
                <p class="prog-row"><strong>Skill Points:</strong> <?= htmlspecialchars($prog['skill_points']) ?></p>
            </div>
            <div class="d-grid gap-2">
                <a href="character_sheet.php" class="btn btn-dnd">View Character Sheet</a>
                <a href="create_adventure.php" class="btn btn-dnd">Create Adventure</a>
                <h4 class="text-warning mt-4">My Adventures</h4>
                <?php
                $stmt = $pdo->prepare("SELECT a.* FROM adventures a 
                                       JOIN adventure_members am ON a.id = am.adventure_id 
                                       WHERE am.user_id = ?");
                $stmt->execute([$user_id]);
                $my_adventures = $stmt->fetchAll();
                
                if (count($my_adventures) > 0) {
                    foreach ($my_adventures as $adv) {
                        echo '<div class="card mb-2" style="background: #3d3d3d; padding: 10px;">';
                        echo '<strong>' . htmlspecialchars($adv['name']) . '</strong> (' . htmlspecialchars($adv['type']) . ')<br>';
                        echo '<small>Status: ' . htmlspecialchars($adv['status']) . '</small><br>';
                        
                        if ($adv['status'] === 'lobby') {
                            echo '<a href="lobby.php?id=' . $adv['id'] . '" class="btn btn-sm btn-outline-warning mt-2">Enter Lobby</a> ';
                        } else {
                            echo '<a href="adventure.php?id=' . $adv['id'] . '" class="btn btn-sm btn-outline-success mt-2">Resume Adventure</a> ';
                        }
                        
                        echo '<a href="delete_adventure.php?id=' . $adv['id'] . '" class="btn btn-sm btn-outline-danger mt-2" onclick="return confirm(\'Are you sure you want to delete this adventure?\');">Delete</a>';
                        echo '</div>';
                    }
                } else {
                    echo '<p>No adventures found.</p>';
                }
                ?>
                <a href="logout.php" class="btn btn-outline-secondary mt-3">Logout</a>
            </div>
        </div>
    </div>
</body>
</html>
