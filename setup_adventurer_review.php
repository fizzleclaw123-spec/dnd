<?php
session_start();
require 'db.php';

$user_id = $_SESSION["user_id"];
$stmt = $pdo->prepare("SELECT a.name, c.name as class, s.strength, s.perception, s.endurance, s.charisma, s.intelligence, s.agility, s.luck FROM adventurers a LEFT JOIN class_library c ON a.class_id = c.id LEFT JOIN adventurer_stats s ON a.id = s.adventurer_id WHERE a.user_id = ?");
$stmt->execute([$user_id]);
$adv = $stmt->fetch();

// Skills data from session (if available) or database
if (isset($_SESSION["skills"])) {
    $existing_skills = $_SESSION["skills"];
} else {
    $stmt = $pdo->prepare("SELECT sl.name as skill_name, cs.level FROM adventurer_skills cs JOIN skill_library sl ON cs.skill_id = sl.id WHERE cs.adventurer_id = ?");
    $stmt->execute([$_SESSION['adventurer_id']]);
    $existing_skills = [];
    while ($row = $stmt->fetch()) {
        $existing_skills[$row['skill_name']] = $row['level'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Character</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #1a1a1a; color: #d4af37; font-family: "Georgia", serif; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .setup-card { background: #2d2d2d; border: 3px solid #d4af37; border-radius: 15px; padding: 2rem; width: 100%; max-width: 450px; }
        .btn-dnd { background: #8b0000 !important; color: white !important; border: 2px solid #5a0000 !important; padding: 0.75rem; margin-top: 0.5rem; }
        .btn-dnd:hover { background: #8b0000 !important; color: white !important; border: 2px solid #5a0000 !important; filter: brightness(1.2); }
    </style>
</head>
<body>
    <div class="setup-card shadow-lg text-light">
        <h2 class="text-center text-warning mb-4">Review Your Adventurer</h2>
        <div class="mb-4">
            <p class="mb-1"><strong>Name:</strong> <?= htmlspecialchars($adv['name']) ?> <a href="setup_adventurer.php" class="text-warning small">(Edit)</a></p>
            <p class="mb-1"><strong>Class:</strong> <?= htmlspecialchars($adv['class']) ?> <a href="setup_adventurer_class.php" class="text-warning small">(Edit)</a></p>
        </div>

        <div class="mb-4">
            <p class="mb-2"><strong>Stats:</strong> <a href="setup_adventurer_stats.php" class="text-warning small">(Edit)</a></p>
            <div class="row row-cols-3 g-2">
                <?php 
                $stats = ['Str'=>$adv['strength'], 'Per'=>$adv['perception'], 'End'=>$adv['endurance'], 'Cha'=>$adv['charisma'], 'Int'=>$adv['intelligence'], 'Agi'=>$adv['agility'], 'Lck'=>$adv['luck']];
                foreach ($stats as $key => $val): ?>
                    <div class="col"><div class="bg-dark p-1 rounded text-center small"><?= $key ?>: <?= $val ?></div></div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <p class="mb-2"><strong>Skills:</strong> <a href="setup_adventurer_skills.php" class="text-warning small">(Edit)</a></p>
        <ul class="list-unstyled mb-4">
            <?php foreach($existing_skills as $name => $val): if($val > 0): ?>
                <li class="small"><?= htmlspecialchars($name) ?>: <?= htmlspecialchars($val) ?></li>
            <?php endif; endforeach; ?>
        </ul>
        
<form action="setup_adventurer_finalize.php" method="POST">
            <input type="hidden" name="action" value="finalize">
            <button type="submit" class="btn btn-dnd w-100 fw-bold">Confirm and Start Adventure!</button>
            <a href="setup_adventurer_skills.php" class="btn btn-outline-secondary w-100 mt-2">Back</a>
        </form>
    </div>
</body>
</html>
