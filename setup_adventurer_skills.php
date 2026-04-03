<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Assign Skills</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #1a1a1a; color: #d4af37; font-family: "Georgia", serif; }
        .setup-card { background: #2d2d2d; border: 3px solid #d4af37; border-radius: 15px; }
        .btn-dnd { background: #8b0000; color: white; border: 2px solid #5a0000; }
        .skill-input { width: 60px; }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="setup-card p-4">
                    <h2 class="text-center text-warning">Assign Skills</h2>
                    <p class="text-center text-light">Points remaining: <span id="remaining" style="color: green;">40</span>. Spend them on your skills!</p>
                    <form action="setup_adventurer_skills_action.php" method="POST" id="skillsForm">
                        <?php 
                        $skills = [
                            'Athletics', 'Intimidation', 'Tracking', 'Notice', 'Resilience',
                            'Persuasion', 'Deception', 'Arcana', 'Medicine', 'Investigation',
                            'Stealth', 'Acrobatics', 'Lockpicking', 'Pickpocketing', 'Gambling', 'Appraisal'
                        ];
                        foreach ($skills as $skill): ?>
                            <div class="mb-2 d-flex justify-content-between align-items-center">
                                <label class="form-label mb-0"><?= $skill ?></label>
                                <input type="number" name="skills[<?= $skill ?>]" class="form-control skill-input bg-dark text-white border-secondary skill-field" value="0" min="0" max="10" required>
                            </div>
                        <?php endforeach; ?>
                        <button type="submit" class="btn btn-dnd w-100 mt-3" id="submitBtn">Proceed to Review</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        const fields = document.querySelectorAll('.skill-field');
        const remainingSpan = document.getElementById('remaining');
        const submitBtn = document.getElementById('submitBtn');
        const TOTAL_POINTS = 40;

        function update() {
            let current = 0;
            fields.forEach(f => current += parseInt(f.value) || 0);
            
            const remaining = TOTAL_POINTS - current;
            remainingSpan.innerText = remaining;
            
            if (remaining < 0) {
                remainingSpan.style.color = 'red';
                submitBtn.disabled = true;
            } else {
                remainingSpan.style.color = 'green';
                submitBtn.disabled = false;
            }
        }

        fields.forEach(f => f.addEventListener('input', update));
        update();
    </script>
</body>
</html>
