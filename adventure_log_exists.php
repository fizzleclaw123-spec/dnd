<?php
session_start();
require_once 'db.php';

if (!isset($_GET['id'])) exit();

$adventure_id = $_GET['id'];
$stmt = $pdo->prepare("SELECT count(*) FROM adventure_logs WHERE adventure_id = ? AND dm_narration IS NOT NULL AND dm_narration != ''");
$stmt->execute([$adventure_id]);
$count = $stmt->fetchColumn();

echo json_encode(['hasContent' => ($count > 0)]);
?>
