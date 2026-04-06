<?php
session_start();
require_once 'db.php';

if (!isset($_GET['id'])) exit();

$adventure_id = $_GET['id'];
$stmt = $pdo->prepare("SELECT is_ready FROM adventure_members WHERE adventure_id = ?");
$stmt->execute([$adventure_id]);
$members = $stmt->fetchAll(PDO::FETCH_COLUMN);

$all_ready = true;
if (empty($members)) {
    $all_ready = false;
} else {
    foreach ($members as $is_ready) {
        if (!$is_ready) {
            $all_ready = false;
            break;
        }
    }
}

if ($all_ready) {
    echo '<a href="start_adventure.php?id=' . htmlspecialchars($adventure_id) . '" class="btn btn-success w-100 mt-3 fw-bold">Start Adventure!</a>';
}
?>
