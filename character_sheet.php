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
    <title>Character Sheet</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #1a1a1a; color: #d4af37; font-family: "Georgia", serif; }
        .sheet-card { background: #2d2d2d; border: 3px solid #d4af37; border-radius: 15px; }
    </style>
</head>
<body class="bg-dark text-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="sheet-card p-4">
                    <h2 class="text-center text-warning"><?= htmlspecialchars($adv['name']) ?></h2>
                    <p class="text-center text-secondary">Level: <?= htmlspecialchars($adv['class']) ?></p>
                    <hr class="border-warning">
                    <div class="row">
                        <div class="col-md-6">
                            <h4>S.P.E.C.I.A.L.</h4>
                            <p>Strength: <?= $adv['strength'] ?></p>
                            <p>Perception: <?= $adv['perception'] ?></p>
                            <p>Endurance: <?= $adv['endurance'] ?></p>
                            <p>Charisma: <?= $adv['charisma'] ?></p>
                            <p>Intelligence: <?= $adv['intelligence'] ?></p>
                            <p>Agility: <?= $adv['agility'] ?></p>
                            <p>Luck: <?= $adv['luck'] ?></p>
                        </div>
                        <div class="col-md-6">
                            <h4>Skills</h4>
                            <?php foreach($skills as $skill): ?>
                                <p><?= htmlspecialchars($skill['skill_name']) ?>: <?= $skill['level'] ?></p>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <a href="dashboard.php" class="btn btn-secondary mt-3">Back to Dashboard</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
