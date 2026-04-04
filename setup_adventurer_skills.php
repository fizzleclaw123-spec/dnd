<?php
session_start();
require 'db.php';

$user_id = $_SESSION["user_id"];
// Load skills: session first, then database
$existing_skills = [];
if (isset($_SESSION["skills"])) {
    $existing_skills = $_SESSION["skills"];
} else {
    $stmt = $pdo->prepare("SELECT * FROM character_skills WHERE user_id = ?");
    $stmt->execute([$user_id]);
    while ($row = $stmt->fetch()) {
        $existing_skills[$row['skill_name']] = $row['level'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Skills</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #1a1a1a; color: #d4af37; font-family: "Georgia", serif; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .setup-card { background: #2d2d2d; border: 3px solid #d4af37; border-radius: 15px; padding: 2rem; width: 100%; max-width: 450px; }
        .btn-dnd { background: #8b0000; color: white; border: 2px solid #5a0000; padding: 0.75rem; }
        .skill-field { width: 60px; background: #1a1a1a !important; color: white !important; border-color: #d4af37 !important; text-align: center; font-size: 1.2rem; border-radius: 5px; }
        .btn-plus-minus { background: #d4af37; color: #1a1a1a; border: none; width: 35px; height: 35px; border-radius: 5px; font-weight: bold; font-size: 1.2rem; }
    </style>
</head>
<body>
    <div class="setup-card shadow-lg">
        <h2 class="text-center text-warning mb-3">Assign Skills</h2>
        <p class="text-center text-light mb-4 small">Points remaining: <span id="remaining" style="color: green;">40</span>.</p>
        <form action="setup_adventurer_skills_action.php" method="POST" id="skillsForm">
            <?php 
            $skill_list = [
                'Athletics', 'Intimidation', 'Tracking', 'Notice', 'Resilience',
                'Persuasion', 'Deception', 'Arcana', 'Medicine', 'Investigation',
                'Stealth', 'Acrobatics', 'Lockpicking', 'Pickpocketing', 'Gambling', 'Appraisal'
            ];
            foreach ($skill_list as $skill): 
                $val = $existing_skills[$skill] ?? 0;
            ?>
                <div class="mb-2 d-flex justify-content-between align-items-center">
                    <label class="form-label mb-0 small fw-bold"><?= $skill ?></label>
                    <div class="d-flex align-items-center">
                        <button type="button" class="btn-plus-minus me-2" onclick="changeSkill('<?= $skill ?>', -1)">-</button>
                        <input type="text" name="skills[<?= $skill ?>]" id="skill-<?= $skill ?>" class="form-control skill-field" value="<?= $val ?>" readonly required>
                        <button type="button" class="btn-plus-minus ms-2" onclick="changeSkill('<?= $skill ?>', 1)">+</button>
                    </div>
                </div>
            <?php endforeach; ?>
            <button type="submit" class="btn btn-dnd w-100 fw-bold mt-4" id="submitBtn">Proceed to Review</button>
            <a href="setup_adventurer_stats.php" class="btn btn-outline-secondary w-100 mt-2">Back</a>
        </form>
    </div>
    <script>
        function changeSkill(skill, delta) {
            const input = document.getElementById('skill-' + skill);
            let val = parseInt(input.value) + delta;
            if (val >= 0 && val <= 10) {
                input.value = val;
                update();
            }
        }

        const fields = document.querySelectorAll('.skill-field');
        const remainingSpan = document.getElementById('remaining');
        const submitBtn = document.getElementById('submitBtn');
        const TOTAL_POINTS = 40;

        function update() {
            let current = 0;
            fields.forEach(f => current += parseInt(f.value) || 0);
            
            const remaining = TOTAL_POINTS - current;
            remainingSpan.innerText = remaining;
            
            if (remaining === 0) {
                remainingSpan.style.color = 'green';
                submitBtn.disabled = false;
            } else {
                remainingSpan.style.color = 'red';
                submitBtn.disabled = true;
            }
        }
        update();
    </script>
</body>
</html>