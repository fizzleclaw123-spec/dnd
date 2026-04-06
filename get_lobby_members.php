<?php
session_start();
require_once 'db.php';

if (!isset($_GET['id'])) exit();

$adventure_id = $_GET['id'];
$stmt = $pdo->prepare("SELECT u.email as player_name, am.is_ready 
                       FROM adventure_members am 
                       JOIN users u ON am.user_id = u.id 
                       WHERE am.adventure_id = ?");
$stmt->execute([$adventure_id]);
$members = $stmt->fetchAll();

foreach ($members as $m) {
    $status = $m['is_ready'] ? '✅ Ready' : '❌ Not Ready';
    echo "<tr><td>" . htmlspecialchars($m['player_name']) . "</td><td>" . $status . "</td></tr>";
}
?>
