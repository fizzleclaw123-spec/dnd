<?php
session_start();
require 'db.php';

$user_id = $_SESSION["user_id"];
$stmt = $pdo->prepare("SELECT * FROM adventurers WHERE user_id = ?");
$stmt->execute([$user_id]);
$adv = $stmt->fetch();

// Skills data from session
$skills = $_SESSION["skills"];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Review Character</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #1a1a1a; color: #d4af37; font-family: "Georgia", serif; }
        .setup-card { background: #2d2d2d; border: 3px solid #d4af37; border-radius: 15px; }
        .btn-dnd { background: #8b0000; color: white; border: 2px solid #5a0000; }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="setup-card p-4 text-light">
                    <h2 class="text-center text-warning">Review Your Adventurer</h2>
                    <p><strong>Name:</strong> <?= htmlspecialchars($adv['name']) ?></p>
                    <p><strong>Class:</strong> <?= htmlspecialchars($adv['class']) ?></p>
                    <p><strong>Stats:</strong> Str: <?= $adv['strength'] ?>, Per: <?= $adv['perception'] ?>, End: <?= $adv['endurance'] ?>, Cha: <?= $adv['charisma'] ?>, Int: <?= $adv['intelligence'] ?>, Agi: <?= $adv['agility'] ?>, Lck: <?= $adv['luck'] ?></p>
                    
                    <p><strong>Skills:</strong></p>
                    <ul>
                        <?php foreach($skills as $name => $val): if($val > 0): ?>
                            <li><?= htmlspecialchars($name) ?>: <?= htmlspecialchars($val) ?></li>
                        <?php endif; endforeach; ?>
                    </ul>
                    
                    <form action="setup_adventurer_finalize.php" method="POST">
                        <button type="submit" class="btn btn-dnd w-100 mt-3">Confirm and Start Adventure!</button>
                        <a href="setup_adventurer.php" class="btn btn-secondary w-100 mt-2">Restart</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
