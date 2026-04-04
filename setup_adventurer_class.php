<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Class</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #1a1a1a; color: #d4af37; font-family: "Georgia", serif; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .setup-card { background: #2d2d2d; border: 3px solid #d4af37; border-radius: 15px; padding: 2rem; width: 100%; max-width: 450px; }
        .btn-dnd { background: #8b0000; color: white; border: 2px solid #5a0000; padding: 0.75rem; }
        .form-select { background: #1a1a1a !important; color: white !important; border-color: #d4af37 !important; padding: 12px; }
    </style>
</head>
<body>
    <div class="setup-card shadow-lg">
        <h2 class="text-center text-warning mb-4">What is your calling?</h2>
        <?php if (isset($_SESSION["message"])): ?>
            <div class="alert alert-warning border-0"><?= $_SESSION["message"]; unset($_SESSION["message"]); ?></div>
        <?php endif; ?>
        <form action="setup_adventurer_class_action.php" method="POST">
            <select class="form-select mb-4" name="adventurer_class" required>
                <option value="">Select your class...</option>
                <option value="Knight">Knight</option>
                <option value="Rogue">Rogue</option>
                <option value="Archer">Archer</option>
                <option value="Mage">Mage</option>
                <option value="Doctor">Doctor</option>
            </select>
            <button type="submit" class="btn btn-dnd w-100 fw-bold">Confirm Class</button>
        </form>
    </div>
</body>
</html>
