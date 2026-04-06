<?php
session_start();
require_once 'db.php';

if (!isset($_GET['id'])) exit();

$adventure_id = $_GET['id'];
$stmt = $pdo->prepare("SELECT adv.name as char_name, am.is_ready 
                       FROM adventure_members am 
                       LEFT JOIN adventurers adv ON am.adventurer_id = adv.id 
                       WHERE am.adventure_id = ?");
$stmt->execute([$adventure_id]);
$members = $stmt->fetchAll();

foreach ($members as $m) {
    $status = $m['is_ready'] ? '✅ Ready' : '❌ Not Ready';
    echo "<tr><td>" . $status . "</td><td>" . htmlspecialchars($m['char_name'] ?? 'Unknown') . "</td></tr>";
}
?>
