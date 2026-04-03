<?php
session_start();
require 'db.php';
require 'config.php';

if (!isset($_SESSION["user_id"]) || !isset($_POST["action"])) {
    header("Location: adventure.php");
    exit;
}

$user_id = $_SESSION["user_id"];
$player_action = $_POST["action"];

// Get character info
$stmt = $pdo->prepare("SELECT name, class FROM adventurers WHERE user_id = ?");
$stmt->execute([$user_id]);
$adv = $stmt->fetch();

// Get context
$stmt = $pdo->prepare("SELECT player_action, dm_narration FROM adventure_logs WHERE user_id = ? ORDER BY turn_number DESC LIMIT 3");
$stmt->execute([$user_id]);
$history = $stmt->fetchAll(PDO::FETCH_ASSOC);

$context = "You are a Dungeon Master for a D&D adventure. Player: {$adv['name']}, Class: {$adv['class']}. ";
foreach (array_reverse($history) as $h) {
    $context .= "Player: {$h['player_action']}. DM: {$h['dm_narration']} ";
}

$prompt = $context . "Player action: $player_action. Describe the DM response.";

// Call Gemini API
$payload = json_encode([
    'contents' => [['parts' => [['text' => $prompt]]]]
]);

$ch = curl_init(API_BASE_URL . '?key=' . API_KEY);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);
$dm_narration = $data['candidates'][0]['content']['parts'][0]['text'] ?? "The adventure continues...";

// Get next turn
$stmt = $pdo->prepare("SELECT MAX(turn_number) as last_turn FROM adventure_logs WHERE user_id = ?");
$stmt->execute([$user_id]);
$next_turn = ($stmt->fetch()['last_turn'] ?? 0) + 1;

// Save log
$stmt = $pdo->prepare("INSERT INTO adventure_logs (user_id, turn_number, player_action, dm_narration) VALUES (?, ?, ?, ?)");
$stmt->execute([$user_id, $next_turn, $player_action, $dm_narration]);

header("Location: adventure.php");
exit;
?>
