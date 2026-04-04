<?php
session_start();
require 'db.php'; 

if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Your Adventurer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #1a1a1a; color: #d4af37; font-family: "Georgia", serif; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .setup-card { background: #2d2d2d; border: 3px solid #d4af37; border-radius: 15px; padding: 2rem; width: 100%; max-width: 450px; }
        .btn-dnd { background: #8b0000; color: white; border: 2px solid #5a0000; padding: 0.75rem; }
        .form-control { background: #1a1a1a !important; color: white !important; border-color: #d4af37 !important; padding: 12px; }
    </style>
</head>
<body>
    <div class="setup-card shadow-lg">
        <h2 class="text-center text-warning mb-4">Welcome, new traveller!</h2>
        <p class="text-center text-light mb-4">Before you start your journey, tell me: what is your adventurer's name?</p>
        <form action="setup_adventurer_action.php" method="POST">
            <div class="mb-3">
                <input type="text" class="form-control" name="adventurer_name" placeholder="Enter name..." required>
            </div>
            <button type="submit" class="btn btn-dnd w-100 fw-bold">Proceed</button>
        </form>
    </div>
</body>
</html>
