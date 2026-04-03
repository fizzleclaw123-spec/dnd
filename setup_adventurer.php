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
    <meta charset="UTF-8">
    <title>Setup Your Adventurer</title>
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
                    <h2 class="text-center text-warning">Welcome, new traveller!</h2>
                    <p class="text-center text-light">Before you start your journey, tell me: what is your adventurer's name?</p>
                    <form action="setup_adventurer_action.php" method="POST">
                        <div class="mb-3">
                            <input type="text" class="form-control bg-dark text-white border-secondary" name="adventurer_name" placeholder="Enter name..." required>
                        </div>
                        <button type="submit" class="btn btn-dnd w-100">Proceed</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
