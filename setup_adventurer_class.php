<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Select Class</title>
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
                <div class="setup-card p-4">
                    <h2 class="text-center text-warning">What is your calling?</h2>
                    <?php if (isset($_SESSION["message"])): ?>
                        <div class="alert alert-info"><?= $_SESSION["message"]; unset($_SESSION["message"]); ?></div>
                    <?php endif; ?>
                    <form action="setup_adventurer_class_action.php" method="POST">
                        <select class="form-select bg-dark text-white border-secondary mb-3" name="adventurer_class" required>
                            <option value="">Select your class...</option>
                            <option value="Knight">Knight</option>
                            <option value="Rogue">Rogue</option>
                            <option value="Archer">Archer</option>
                            <option value="Mage">Mage</option>
                            <option value="Doctor">Doctor</option>
                        </select>
                        <button type="submit" class="btn btn-dnd w-100">Confirm Class</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
