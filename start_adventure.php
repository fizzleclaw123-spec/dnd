<?php
session_start();
require 'db.php';
require 'config.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION["user_id"];

// Check if adventure already started
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM adventure_logs WHERE user_id = ?");
$stmt->execute([$user_id]);
if ($stmt->fetch()['count'] > 0) {
    header("Location: adventure.php");
    exit;
}

// Fetch character details
$stmt = $pdo->prepare("SELECT name, class FROM adventurers WHERE user_id = ?");
$stmt->execute([$user_id]);
$adv = $stmt->fetch();

// Prepare DM intro prompt
$prompt = "You are a Dungeon Master for a D&D adventure. Player: {$adv['name']}, Class: {$adv['class']}. Please narrate the opening scene of their adventure in a dark fantasy setting, and invite them to take their first action.";

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
$dm_narration = $data['candidates'][0]['content']['parts'][0]['text'] ?? "Your journey begins in a mysterious land. What do you do?";

// Save intro
$stmt = $pdo->prepare("INSERT INTO adventure_logs (user_id, turn_number, player_action, dm_narration) VALUES (?, ?, ?, ?)");
$stmt->execute([$user_id, 1, 'Started Adventure', $dm_narration]);

header("Location: adventure.php");
exit;
?>
