<?php
session_start();
require 'db.php';
require 'config.php';

if (!isset($_SESSION["user_id"]) || !isset($_POST["action"])) {
    header("Location: adventure.php?id=" . ($_GET['id'] ?? ''));
    exit;
}

$user_id = $_SESSION["user_id"];
$adventure_id = $_GET["id"] ?? $_SESSION["active_adventure_id"];
$player_action = $_POST["action"];

// Get character info
$stmt = $pdo->prepare("SELECT a.id, a.name, c.name as class, s.strength, s.perception, s.endurance, s.charisma, s.intelligence, s.agility, s.luck FROM adventurers a LEFT JOIN class_library c ON a.class_id = c.id LEFT JOIN adventurer_stats s ON a.id = s.adventurer_id WHERE a.user_id = ?");
$stmt->execute([$user_id]);
$adv = $stmt->fetch();

$stats = "Stats: Strength: {$adv['strength']}, Perception: {$adv['perception']}, Endurance: {$adv['endurance']}, Int: {$adv['intelligence']}, Cha: {$adv['charisma']}, Agility: {$adv['agility']}, Luck: {$adv['luck']}. ";

// Get ALL party member details for the prompt context
$stmt = $pdo->prepare("SELECT a.id, a.name, c.name as class FROM adventurers a 
                       JOIN adventure_members am ON a.id = am.adventurer_id 
                       JOIN class_library c ON a.class_id = c.id
                       WHERE am.adventure_id = ?");
$stmt->execute([$adventure_id]);
$party = $stmt->fetchAll(PDO::FETCH_ASSOC);

// DEBUGGING PARTY
error_log("Party count: " . count($party));

$party_desc = "";
foreach ($party as $m) {
    $party_desc .= "{$m['name']} (the {$m['class']}), ";
}
$party_desc = rtrim($party_desc, ", ");

$stmt = $pdo->prepare("SELECT MAX(turn_number) as last_turn FROM adventure_logs WHERE adventure_id = ?");
$stmt->execute([$adventure_id]);
$current_turn = ($stmt->fetch()['last_turn'] ?? 1); 

$party_members_count = count($party);

// Check if we already finished narration for this turn
$stmt = $pdo->prepare("SELECT COUNT(*) FROM adventure_logs WHERE adventure_id = ? AND turn_number = ? AND user_id = 0");
$stmt->execute([$adventure_id, $current_turn]);
$is_narrated = ($stmt->fetchColumn() > 0);

// If narrated, the user is likely submitting an action for the *next* turn.
// Find the next turn number by checking if there's already a narration for $current_turn.
if ($is_narrated) {
    $current_turn++;
    $is_narrated = false; // We are now working on a new turn that hasn't been narrated yet
}

// 1. Save action (using the corrected turn number)
$stmt = $pdo->prepare("INSERT INTO adventure_logs (user_id, adventure_id, turn_number, player_action) VALUES (?, ?, ?, ?)");
$stmt->execute([$adv['id'], $adventure_id, $current_turn, $player_action]);

// Now calculate submitted count for the NEW current turn
$stmt = $pdo->prepare("SELECT COUNT(DISTINCT user_id) FROM adventure_logs WHERE adventure_id = ? AND turn_number = ? AND user_id != 0");
$stmt->execute([$adventure_id, $current_turn]);
$submitted_count = $stmt->fetchColumn();

// Fetch the adventurer IDs directly from the adventure_members table based on adventure_id
$stmt = $pdo->prepare("SELECT adventurer_id FROM adventure_members WHERE adventure_id = ?");
$stmt->execute([$adventure_id]);
$member_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Check if all of these adventurer_ids have an entry in logs for this turn
$missing_count = 0;
foreach ($member_ids as $m_id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM adventure_logs WHERE adventure_id = ? AND turn_number = ? AND user_id = ?");
    $stmt->execute([$adventure_id, $current_turn, $m_id]);
    $count = $stmt->fetchColumn();
    if ($count == 0) {
        $missing_count++;
    }
}

// ERROR LOGGING
error_log("Turn: $current_turn, Missing: $missing_count");

// Only trigger if narration hasn't happened yet AND we have enough submissions
if (!$is_narrated && $missing_count == 0 && count($member_ids) > 0) {
    // 3. Auto-trigger AI narration
    // Fetch all actions for this turn to build prompt
    $stmt = $pdo->prepare("SELECT a.name as char_name, c.name as class, al.player_action 
                           FROM adventure_logs al 
                           LEFT JOIN adventurers a ON al.user_id = a.id
                           LEFT JOIN class_library c ON a.class_id = c.id
                           WHERE al.adventure_id = ? AND al.turn_number = ? AND al.player_action IS NOT NULL");
    $stmt->execute([$adventure_id, $current_turn]);
    $actions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Only proceed if there are actually actions to narrate
    if (!empty($actions)) {
        $full_prompt = "DM context for all actions: ";
        foreach($actions as $act) {
            $full_prompt .= "{$act['char_name']} (the {$act['class']}) did: {$act['player_action']}. ";
        }
        
        // Call Gemini API
        $payload = json_encode([
            'contents' => [['parts' => [['text' => $full_prompt . " Describe the DM response, maintaining focus on the party's interactions. Write in a rich, descriptive D&D narrative style that addresses all party members involved in the scene by name. 
            CRITICAL: For ANY action, if there is a chance of failure, you MUST calculate the result yourself (d20+stat vs DC).
            YOU MUST FORMAT THE MECHANICS AT THE VERY BEGINNING OF YOUR RESPONSE LIKE THIS: [ROLL: Action/Stat/DC: Result, Status]
            Example: [ROLL: Disarm/Agility/15: 18, Success]
            Follow this immediately with your detailed narrative text. Do NOT list out 'if' conditions or possibilities."]]]]
        ]);

        $ch = curl_init(API_BASE_URL . '?key=' . API_KEY);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60); 
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
        $response = curl_exec($ch);
        $data = json_decode($response, true);
        curl_close($ch);

        $dm_narration = $data['candidates'][0]['content']['parts'][0]['text'] ?? "Narration failed to generate.";

        $stmt = $pdo->prepare("INSERT INTO adventure_logs (user_id, adventure_id, turn_number, dm_narration) VALUES (0, ?, ?, ?)");
        $stmt->execute([$adventure_id, $current_turn, $dm_narration]);
    } else {
        // If no actions (e.g. someone skipped but the loop didn't capture them), we still need to trigger narration if the count is met
        // Trigger a generic narration to move the turn forward
        $stmt = $pdo->prepare("INSERT INTO adventure_logs (user_id, adventure_id, turn_number, dm_narration) VALUES (0, ?, ?, ?)");
        $stmt->execute([$adventure_id, $current_turn, "The party hesitates, waiting for something to happen. The silence is heavy."]);
    }
}

header("Location: adventure.php?id=$adventure_id");
exit;
