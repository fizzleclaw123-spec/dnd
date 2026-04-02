<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>D&D Character Manager | Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #1a1a1a; color: #d4af37; font-family: "Georgia", serif; }
        .reg-card { background: #2d2d2d; border: 3px solid #d4af37; border-radius: 15px; box-shadow: 0 0 20px rgba(0,0,0,0.5); }
        .form-label { color: #f8f9fa; font-weight: bold; }
        .btn-dnd { background: #8b0000; color: white; border: 2px solid #5a0000; font-weight: bold; }
        .btn-dnd:hover { background: #a50000; color: white; }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="reg-card p-4">
                    <h2 class="text-center text-warning mb-3">⚔️ Create Character 📜</h2>
                    <?php if (isset($_SESSION["error"])): ?>
                        <div class="alert alert-danger"><?= $_SESSION["error"]; unset($_SESSION["error"]); ?></div>
                    <?php endif; ?>
                    <form action="register_action.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label">📧 Email</label>
                            <input type="email" class="form-control bg-dark text-white border-secondary" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">🔑 Password</label>
                            <input type="password" class="form-control bg-dark text-white border-secondary" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-dnd w-100">Register</button>
                    </form>
                    <p class="text-center mt-3"><a href="index.php" class="text-warning">Back to Login</a></p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
