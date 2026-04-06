<?php
session_start();
require_once 'db.php';
require_once 'config.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$adventure_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Set adventure to active
$stmt = $pdo->prepare("UPDATE adventures SET status = 'active' WHERE id = ?");
$stmt->execute([$adventure_id]);

// Get ALL party member details
$stmt = $pdo->prepare("SELECT a.name, a.class FROM adventurers a JOIN adventure_members am ON a.id = am.adventurer_id WHERE am.adventure_id = ?");
$stmt->execute([$adventure_id]);
$party_members = $stmt->fetchAll();

$party_desc = "";
foreach ($party_members as $member) {
    $party_desc .= $member['name'] . " (the " . $member['class'] . "), ";
}
$party_desc = rtrim($party_desc, ", ");

// Generate initial greeting via Gemini
$context = "You are a Dungeon Master for a D&D adventure. Party: {$party_desc}. Start the adventure with an engaging opening scene that incorporates this party.";
$payload = json_encode(['contents' => [['parts' => [['text' => $context]]]]]);

$ch = curl_init(API_BASE_URL . '?key=' . API_KEY);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);
$initial_narration = $data['candidates'][0]['content']['parts'][0]['text'] ?? "Your adventure begins...";

// NEW: Try to extract a title from the DM narration
$context_title = "Given this D&D adventure opening, provide a short, 2-3 word title for the adventure. Output ONLY the title. Text: " . substr($initial_narration, 0, 500);
$payload_title = json_encode(['contents' => [['parts' => [['text' => $context_title]]]]]);
$ch_title = curl_init(API_BASE_URL . '?key=' . API_KEY);
curl_setopt($ch_title, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch_title, CURLOPT_POST, true);
curl_setopt($ch_title, CURLOPT_POSTFIELDS, $payload_title);
curl_setopt($ch_title, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response_title = curl_exec($ch_title);
curl_close($ch_title);
$data_title = json_decode($response_title, true);
$adventure_title = $data_title['candidates'][0]['content']['parts'][0]['text'] ?? "New Adventure";

// Update adventure status AND name
$stmt = $pdo->prepare("UPDATE adventures SET status = 'active', name = ? WHERE id = ?");
$stmt->execute([$adventure_title, $adventure_id]);

// Save log - use the creator's ID for the first log
$stmt = $pdo->prepare("INSERT INTO adventure_logs (user_id, adventure_id, turn_number, player_action, dm_narration) VALUES (?, ?, ?, ?, ?)");
$stmt->execute([$user_id, $adventure_id, 1, 'Started Adventure', $initial_narration]);

header("Location: adventure.php?id=" . $adventure_id);
exit();
