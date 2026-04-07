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
$stmt = $pdo->prepare("SELECT a.name, c.name as class FROM adventurers a JOIN adventure_members am ON a.id = am.adventurer_id JOIN class_library c ON a.class_id = c.id WHERE am.adventure_id = ?");
$stmt->execute([$adventure_id]);
$party_members = $stmt->fetchAll();

$party_desc = "";
foreach ($party_members as $member) {
    $party_desc .= $member['name'] . " (the " . $member['class'] . "), ";
}
$party_desc = rtrim($party_desc, ", ");

// Generate initial greeting via Gemini
$context = "You are a Dungeon Master for a collaborative D&D adventure party consisting of: {$party_desc}. Start the adventure with an engaging, detailed opening scene that incorporates this party. Provide a short, 2-3 word title for the adventure. 
            Format your response exactly as: TITLE: [Title]
            NARRATION: [Narration (with paragraph breaks)]";
$payload = json_encode(['contents' => [['parts' => [['text' => $context]]]]]);

$ch = curl_init(API_BASE_URL . '?key=' . API_KEY);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);
$raw_response = $data['candidates'][0]['content']['parts'][0]['text'] ?? "TITLE: New Adventure\nNARRATION: Your adventure begins...";

// Parse Title and Narration
if (preg_match('/TITLE: (.*?)\nNARRATION: (.*)/s', $raw_response, $matches)) {
    $adventure_title = trim($matches[1]);
    $initial_narration = trim($matches[2]);
} else {
    $adventure_title = "New Adventure";
    $initial_narration = $raw_response;
}

// Update adventure status AND name
$stmt = $pdo->prepare("UPDATE adventures SET status = 'active', name = ? WHERE id = ?");
$stmt->execute([$adventure_title, $adventure_id]);

// Save log - use the creator's ID for the first log
$stmt = $pdo->prepare("INSERT INTO adventure_logs (user_id, adventure_id, turn_number, player_action, dm_narration) VALUES (?, ?, ?, ?, ?)");
$stmt->execute([$user_id, $adventure_id, 1, 'Started Adventure', $initial_narration]);

header("Location: adventure.php?id=" . $adventure_id);
exit();
