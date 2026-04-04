<?php
session_start();
require 'db.php';

$user_id = $_SESSION["user_id"];
$stmt = $pdo->prepare("SELECT * FROM adventurers WHERE user_id = ?");
$stmt->execute([$user_id]);
$adv = $stmt->fetch();

$current_stats = [
    'Strength' => $adv['strength'] ?? 4,
    'Perception' => $adv['perception'] ?? 4,
    'Endurance' => $adv['endurance'] ?? 4,
    'Charisma' => $adv['charisma'] ?? 4,
    'Intelligence' => $adv['intelligence'] ?? 4,
    'Agility' => $adv['agility'] ?? 4,
    'Luck' => $adv['luck'] ?? 4
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign SPECIAL Stats</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #1a1a1a; color: #d4af37; font-family: "Georgia", serif; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .setup-card { background: #2d2d2d; border: 3px solid #d4af37; border-radius: 15px; padding: 2rem; width: 100%; max-width: 450px; }
        .btn-dnd { background: #8b0000 !important; color: white !important; border: 2px solid #5a0000 !important; padding: 0.75rem; }
        .btn-dnd:hover { background: #8b0000 !important; color: white !important; border: 2px solid #5a0000 !important; filter: brightness(1.2); }
        .stat-field { width: 60px; background: #1a1a1a !important; color: white !important; border-color: #d4af37 !important; text-align: center; font-size: 1.2rem; border-radius: 5px; }
        .btn-plus-minus { background: #d4af37; color: #1a1a1a; border: none; width: 40px; height: 40px; border-radius: 5px; font-weight: bold; font-size: 1.5rem; }
    </style>
</head>
<body>
    <div class="setup-card shadow-lg">
        <h2 class="text-center text-warning mb-3">Assign S.P.E.C.I.A.L. Stats</h2>
        <p class="text-center text-light mb-4 small">Points remaining: <span id="remaining" style="color: red;">0</span> (Total: 40). Min 3, Max 10.</p>
        <form action="setup_adventurer_stats_action.php" method="POST" id="statsForm">
            <?php 
            foreach ($current_stats as $stat => $val): ?>
                <div class="mb-3 d-flex justify-content-between align-items-center">
                    <label class="form-label mb-0 fw-bold"><?= $stat ?></label>
                    <div class="d-flex align-items-center">
                        <button type="button" class="btn-plus-minus me-2" onclick="changeStat('<?= $stat ?>', -1)">-</button>
                        <input type="text" name="stats[<?= $stat ?>]" id="stat-<?= $stat ?>" class="form-control stat-field" value="<?= $val ?>" readonly required>
                        <button type="button" class="btn-plus-minus ms-2" onclick="changeStat('<?= $stat ?>', 1)">+</button>
                    </div>
                </div>
            <?php endforeach; ?>
            <button type="submit" class="btn btn-dnd w-100 fw-bold mt-4" id="submitBtn">Finalize Character</button>
            <a href="setup_adventurer_class.php" class="btn btn-outline-secondary w-100 mt-2">Back</a>
        </form>
    </div>
    <script>
        function changeStat(stat, delta) {
            const input = document.getElementById('stat-' + stat);
            let val = parseInt(input.value) + delta;
            if (val >= 3 && val <= 10) {
                // Check if we have points remaining before adding
                if (delta > 0) {
                    let current = 0;
                    fields.forEach(f => current += parseInt(f.value) || 0);
                    if (current >= TOTAL_POINTS) return;
                }
                input.value = val;
                update();
            }
        }
        
        const fields = document.querySelectorAll('.stat-field');
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
