<?php
session_start();
require 'db.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION["user_id"];

// Fetch character details
$stmt = $pdo->prepare("SELECT * FROM adventurers WHERE user_id = ?");
$stmt->execute([$user_id]);
$adv = $stmt->fetch();

// Fetch skills
$stmt = $pdo->prepare("SELECT * FROM character_skills WHERE user_id = ?");
$stmt->execute([$user_id]);
$skills = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Character Sheet</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #1a1a1a; color: #d4af37; font-family: "Georgia", serif; min-height: 100vh; }
        .sheet-card { background: #2d2d2d; border: 3px solid #d4af37; border-radius: 15px; padding: 20px; width: 100%; max-width: 600px; margin: 20px auto; }
        .stat-box { background: #3d3d3d; border-radius: 10px; padding: 10px; margin-bottom: 10px; }
        .btn-dnd { background: #8b0000; color: white; border: 2px solid #5a0000; display: block; width: 100%; padding: 10px; text-align: center; }
    </style>
</head>
<body class="bg-dark text-light p-3">
    <div class="sheet-card">
        <h2 class="text-center text-warning"><?= htmlspecialchars($adv['name']) ?></h2>
        <p class="text-center text-secondary mb-4"><?= htmlspecialchars($adv['class']) ?></p>
        
        <div class="row">
            <div class="col-12">
                <h4 class="text-warning">S.P.E.C.I.A.L. Stats</h4>
                <div class="row row-cols-2 g-2">
                    <div class="col"><div class="stat-box">Str: <?= $adv['strength'] ?></div></div>
                    <div class="col"><div class="stat-box">Per: <?= $adv['perception'] ?></div></div>
                    <div class="col"><div class="stat-box">End: <?= $adv['endurance'] ?></div></div>
                    <div class="col"><div class="stat-box">Cha: <?= $adv['charisma'] ?></div></div>
                    <div class="col"><div class="stat-box">Int: <?= $adv['intelligence'] ?></div></div>
                    <div class="col"><div class="stat-box">Agi: <?= $adv['agility'] ?></div></div>
                    <div class="col"><div class="stat-box">Lck: <?= $adv['luck'] ?></div></div>
                </div>
            </div>
            
            <div class="col-12 mt-4">
                <h4 class="text-warning">Skills</h4>
                <div class="stat-box">
                    <?php foreach($skills as $skill): ?>
                        <div class="d-flex justify-content-between border-bottom border-secondary py-1">
                            <span><?= htmlspecialchars($skill['skill_name']) ?></span>
                            <span class="text-white"><?= $skill['level'] ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <a href="dashboard.php" class="btn btn-dnd mt-4">Back to Dashboard</a>
    </div>
</body>
</html>
