<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Assign SPECIAL Stats</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #1a1a1a; color: #d4af37; font-family: "Georgia", serif; }
        .setup-card { background: #2d2d2d; border: 3px solid #d4af37; border-radius: 15px; }
        .btn-dnd { background: #8b0000; color: white; border: 2px solid #5a0000; }
        .stat-input { width: 60px; }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="setup-card p-4">
                    <h2 class="text-center text-warning">Assign S.P.E.C.I.A.L. Stats</h2>
                    <p class="text-center text-light">Points remaining: <span id="remaining" style="color: red;">12</span> (Total: 40). Min 3, Max 10.</p>
                    <form action="setup_adventurer_stats_action.php" method="POST" id="statsForm">
                        <?php 
                        $stats = ['Strength', 'Perception', 'Endurance', 'Charisma', 'Intelligence', 'Agility', 'Luck'];
                        foreach ($stats as $stat): ?>
                            <div class="mb-3 d-flex justify-content-between align-items-center">
                                <label class="form-label mb-0"><?= $stat ?></label>
                                <input type="number" name="stats[<?= $stat ?>]" class="form-control stat-input bg-dark text-white border-secondary stat-field" value="4" min="3" max="10" required>
                            </div>
                        <?php endforeach; ?>
                        <button type="submit" class="btn btn-dnd w-100 mt-3" id="submitBtn" disabled>Finalize Character</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        const fields = document.querySelectorAll('.stat-field');
        const remainingSpan = document.getElementById('remaining');
        const submitBtn = document.getElementById('submitBtn');
        const TOTAL_POINTS = 40;

        let lastValidValues = Array.from(fields).map(f => parseInt(f.value));

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

        fields.forEach((f, index) => {
            f.addEventListener('input', () => {
                let current = 0;
                fields.forEach(field => current += parseInt(field.value) || 0);
                
                if (current > TOTAL_POINTS) {
                    f.value = lastValidValues[index];
                } else {
                    lastValidValues[index] = parseInt(f.value);
                }
                update();
            });
        });
        update();
    </script>
</body>
</html>
