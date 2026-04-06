<?php
session_start();
require_once 'db.php';
if (!isset($_SESSION['user_id'])) exit();
$user_id = $_SESSION['user_id'];
$type = $_GET['type'];

if ($type == 'my') {
    $stmt = $pdo->prepare("SELECT a.* FROM adventures a 
                           JOIN adventure_members am ON a.id = am.adventure_id 
                           WHERE am.user_id = ?");
    $stmt->execute([$user_id]);
    $list = $stmt->fetchAll();
    foreach ($list as $adv) {
        echo '<div class="card mb-2" style="background: #3d3d3d; padding: 10px;">';
        echo '<strong>' . htmlspecialchars($adv['name']) . '</strong> (' . htmlspecialchars($adv['type']) . ')<br>';
        echo '<small>Status: ' . htmlspecialchars($adv['status']) . '</small><br>';
        if ($adv['status'] === 'lobby') {
            echo '<a href="lobby.php?id=' . $adv['id'] . '" class="btn btn-sm btn-outline-warning mt-2">Enter Lobby</a> ';
        } else {
            echo '<a href="adventure.php?id=' . $adv['id'] . '" class="btn btn-sm btn-outline-success mt-2">Resume Adventure</a> ';
        }
        
        if ($adv['created_by'] == $user_id) {
            echo '<a href="delete_adventure.php?id=' . $adv['id'] . '" class="btn btn-sm btn-outline-danger mt-2" onclick="return confirm(\'Are you sure you want to delete this adventure?\');">Delete</a>';
        }
        echo '</div>';
    }
} else {
    $stmt = $pdo->prepare("SELECT a.* FROM adventures a 
                           WHERE a.type = 'multiplayer' AND a.status = 'lobby'
                           AND a.id NOT IN (SELECT adventure_id FROM adventure_members WHERE user_id = ?)");
    $stmt->execute([$user_id]);
    $list = $stmt->fetchAll();
    foreach ($list as $adv) {
        echo '<div class="card mb-2" style="background: #3d3d3d; padding: 10px;">';
        echo '<strong>' . htmlspecialchars($adv['name']) . '</strong><br>';
        echo '<a href="join_adventure.php?id=' . $adv['id'] . '" class="btn btn-sm btn-outline-info mt-2">Join Lobby</a>';
        echo '</div>';
    }
}
?>