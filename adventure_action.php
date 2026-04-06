<?php
session_start();
require 'db.php';
require 'config.php';

if (!isset($_SESSION["user_id"]) || !isset($_POST["action"])) {
    header("Location: adventure.php");
    exit;
}

$user_id = $_SESSION["user_id"];
$adventure_id = $_GET["id"] ?? $_SESSION["active_adventure_id"];
$player_action = $_POST["action"];

// Get character info
$stmt = $pdo->prepare("SELECT name, class, strength, perception, endurance, charisma, intelligence, agility FROM adventurers WHERE user_id = ?");
$stmt->execute([$user_id]);
$adv = $stmt->fetch();

$stats = "Stats: Strength: {$adv['strength']}, Perception: {$adv['perception']}, Endurance: {$adv['endurance']}, Int: {$adv['intelligence']}, Cha: {$adv['charisma']}, Agility: {$adv['agility']}. ";

// Get ALL party member details for the prompt context
$stmt = $pdo->prepare("SELECT a.name, a.class FROM adventurers a 
                       JOIN adventure_members am ON a.id = am.adventurer_id 
                       WHERE am.adventure_id = ?");
$stmt->execute([$adventure_id]);
$party = $stmt->fetchAll(PDO::FETCH_ASSOC);

$party_desc = "";
foreach ($party as $m) {
    $party_desc .= "{$m['name']} (the {$m['class']}), ";
}
$party_desc = rtrim($party_desc, ", ");

// Get context - FILTER BY adventure_id
$stmt = $pdo->prepare("SELECT al.user_id, a.name as char_name, al.player_action, al.dm_narration 
                       FROM adventure_logs al 
                       LEFT JOIN adventurers a ON al.user_id = a.user_id
                       WHERE al.adventure_id = ? 
                       ORDER BY al.turn_number DESC LIMIT 5");
$stmt->execute([$adventure_id]);
$history = $stmt->fetchAll(PDO::FETCH_ASSOC);

$context = "You are a Dungeon Master for a collaborative D&D adventure party consisting of: {$party_desc}. 
            Treat all party members as equal protagonists. 
            The current player taking an action is {$adv['name']} ({$adv['class']}). {$stats}";

foreach (array_reverse($history) as $h) {
    $context .= "{$h['char_name']}: {$h['player_action']}. DM: {$h['dm_narration']} ";
}

$prompt = $context . " Player {$adv['name']} action: $player_action. 
            Describe the DM response, maintaining focus on the party's interactions. 
            CRITICAL: For ANY action, if there is a chance of failure, calculate the d20+stat roll vs DC.
            YOU MUST FORMAT THE MECHANICS AT THE VERY BEGINNING OF YOUR RESPONSE LIKE THIS EXACTLY: 
            [ROLL: Action/Stat/DC: Result, Status] 
            Example: [ROLL: Disarm/Agility/15: 18, Success]
            Follow this immediately with your narrative text. 
            If failure, narrate the failure and its consequences. Be fair but firm.";

// Call Gemini API
$payload = json_encode([
    'contents' => [['parts' => [['text' => $prompt]]]]
]);

$ch = curl_init(API_BASE_URL . '?key=' . API_KEY);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Add a 30s timeout explicitly
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
$response = curl_exec($ch);
if (curl_errno($ch)) {
    die('Curl Error: ' . curl_error($ch));
}
curl_close($ch);

$data = json_decode($response, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    die('JSON Error: ' . json_last_error_msg() . ' | Response: ' . $response);
}

if (!isset($data['candidates'][0]['content']['parts'][0]['text'])) {
    die('API Error: ' . json_encode($data));
}
$dm_narration = $data['candidates'][0]['content']['parts'][0]['text'];

$stmt = $pdo->prepare("SELECT MAX(turn_number) as last_turn FROM adventure_logs WHERE adventure_id = ?");
$stmt->execute([$adventure_id]);
$next_turn = ($stmt->fetch()['last_turn'] ?? 0) + 1;

// Save log - ADD adventure_id
$stmt = $pdo->prepare("INSERT INTO adventure_logs (user_id, adventure_id, turn_number, player_action, dm_narration) VALUES (?, ?, ?, ?, ?)");
$stmt->execute([$user_id, $adventure_id, $next_turn, $player_action, $dm_narration]);

header("Location: adventure.php?id=$adventure_id");
exit;
?>
