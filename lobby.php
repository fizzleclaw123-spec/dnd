<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$adventure_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Handle ready/unready action
if (isset($_POST['toggle_ready'])) {
    $stmt = $pdo->prepare("SELECT id FROM adventurers WHERE user_id = ? LIMIT 1");
    $stmt->execute([$user_id]);
    $adv = $stmt->fetch();
    if ($adv) {
        $stmt = $pdo->prepare("UPDATE adventure_members SET is_ready = NOT is_ready, adventurer_id = ? WHERE adventure_id = ? AND user_id = ?");
        $stmt->execute([$adv['id'], $adventure_id, $user_id]);
    }
    header("Location: lobby.php?id=" . $adventure_id);
    exit();
}

// Fetch adventure and members
$stmt = $pdo->prepare("SELECT a.*, am.user_id as member_id, am.is_ready, adv.name as char_name 
                       FROM adventures a 
                       JOIN adventure_members am ON a.id = am.adventure_id 
                       LEFT JOIN adventurers adv ON am.adventurer_id = adv.id 
                       WHERE a.id = ?");
$stmt->execute([$adventure_id]);
$members = $stmt->fetchAll();
$adventure = $members[0];

// Check if all players are ready to auto-start (simple check)
$all_ready = true;
foreach ($members as $m) {
    if (!$m['is_ready']) {
        $all_ready = false;
        break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lobby - <?= htmlspecialchars($adventure['name']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #1a1a1a; color: #d4af37; font-family: "Georgia", serif; min-height: 100vh; padding: 20px; }
        .card { background: #2d2d2d; border: 3px solid #d4af37; border-radius: 15px; padding: 1.5rem; }
        .btn-dnd { background: #8b0000; color: white; border: 2px solid #5a0000; }
        .btn-dnd:hover { background: #8b0000 !important; color: white !important; filter: brightness(1.2); }
    </style>
</head>
<body class="bg-dark text-light">
    <div class="container mt-5">
        <div class="card shadow-lg">
            <h2 class="text-warning">Lobby: <?= htmlspecialchars($adventure['name']) ?></h2>
            <div class="table-responsive">
                <table class="table table-dark table-striped">
                    <thead><tr><th>Status</th><th>Player</th></tr></thead>
                    <tbody id="lobby-members">
                        <?php foreach ($members as $m): ?>
                        <tr>
                            <td><?= $m['is_ready'] ? '✅ Ready' : '❌ Not Ready' ?></td>
                            <td><?= htmlspecialchars($m['char_name'] ?? "Unknown") ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <form method="POST" class="mt-3">
                <button type="submit" name="toggle_ready" class="btn btn-dnd w-100 fw-bold">Toggle Ready Status</button>
            </form>
            
            <div id="start-button-container">
            <?php if ($all_ready && count($members) > 0): ?>
                <a href="start_adventure.php?id=<?= $adventure_id ?>" class="btn btn-success w-100 mt-3 fw-bold">Start Adventure!</a>
            <?php endif; ?>
            </div>
            
            <a href="dashboard.php" class="btn btn-outline-secondary w-100 mt-3">Back to Dashboard</a>
        </div>
    </div>
        
    <script>
        function refreshLobby() {
            fetch('get_lobby_members.php?id=<?= $adventure_id ?>')
                .then(response => response.text())
                .then(data => {
                    document.getElementById('lobby-members').innerHTML = data;
                });
            
            fetch('check_status.php?id=<?= $adventure_id ?>')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'active') {
                        // Check if we have logs AND if it's been a few seconds to let things settle
                        fetch('adventure_log_exists.php?id=<?= $adventure_id ?>')
                            .then(res => res.json())
                            .then(exists => {
                                if (exists.hasContent) {
                                    // Give the database a brief window to ensure everything is saved
                                    setTimeout(() => {
                                        window.location.href = 'adventure.php?id=<?= $adventure_id ?>';
                                    }, 2000);
                                }
                            });
                    }
                });

            fetch('check_start_adventure.php?id=<?= $adventure_id ?>')
                .then(response => response.text())
                .then(data => {
                    document.getElementById('start-button-container').innerHTML = data;
                });
        }
        setInterval(refreshLobby, 3000); // Poll every 3 seconds
    </script>
</body>
</body>
</html>
