<?php
session_start();
require_once 'db.php';

if (!isset($_GET['id'])) exit();

$adventure_id = $_GET['id'];
$stmt = $pdo->prepare("SELECT status FROM adventures WHERE id = ?");
$stmt->execute([$adventure_id]);
$adventure = $stmt->fetch();

echo json_encode(['status' => $adventure['status']]);
?>
