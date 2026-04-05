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
    $stmt = $pdo->prepare("UPDATE adventure_members SET is_ready = NOT is_ready WHERE adventure_id = ? AND user_id = ?");
    $stmt->execute([$adventure_id, $user_id]);
    header("Location: lobby.php?id=" . $adventure_id);
    exit();
}

// Fetch adventure and members
$stmt = $pdo->prepare("SELECT a.*, am.user_id as member_id, am.is_ready, u.email as player_name 
                       FROM adventures a 
                       JOIN adventure_members am ON a.id = am.adventure_id 
                       LEFT JOIN users u ON am.user_id = u.id 
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
    <title>Lobby - <?= htmlspecialchars($adventure['name']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark text-light">
    <div class="container mt-5">
        <h2>Lobby: <?= htmlspecialchars($adventure['name']) ?></h2>
        <table class="table table-dark">
            <thead><tr><th>Player</th><th>Status</th></tr></thead>
            <tbody>
                <?php foreach ($members as $m): ?>
                <tr>
                    <td><?= htmlspecialchars($m['player_name']) ?></td>
                    <td><?= $m['is_ready'] ? '✅ Ready' : '❌ Not Ready' ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <form method="POST">
            <button type="submit" name="toggle_ready" class="btn btn-warning">Toggle Ready Status</button>
        </form>
        
        <?php if ($all_ready && count($members) > 0): ?>
            <a href="start_adventure.php?id=<?= $adventure_id ?>" class="btn btn-success mt-3">Start Adventure!</a>
        <?php endif; ?>
        
        <a href="dashboard.php" class="btn btn-secondary mt-3">Back to Dashboard</a>
    </div>
</body>
</html>
