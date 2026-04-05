<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'db.php';
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $type = $_POST['type']; // 'solo' or 'multiplayer'
    $user_id = $_SESSION['user_id'];
    $adventure_name = "New Adventure";

    $stmt = $pdo->prepare("INSERT INTO adventures (name, type, created_by) VALUES (?, ?, ?)");
    $stmt->execute([$adventure_name, $type, $user_id]);
    $adventure_id = $pdo->lastInsertId();

    // Default to adding the creator as a member
    $stmt = $pdo->prepare("INSERT INTO adventure_members (adventure_id, user_id, is_ready) VALUES (?, ?, ?)");
    $is_ready = ($type == 'solo') ? 1 : 0;
    $stmt->execute([$adventure_id, $user_id, $is_ready]);

    if ($type == 'solo') {
        // Generate initial greeting via Gemini
        $context = "You are a Dungeon Master for a D&D adventure. Player: (new adventurer). Start the adventure with an engaging opening scene.";
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

        $stmt = $pdo->prepare("UPDATE adventures SET status = 'active', name = ? WHERE id = ?");
        $stmt->execute([$adventure_title, $adventure_id]);

        $stmt = $pdo->prepare("INSERT INTO adventure_logs (user_id, adventure_id, turn_number, player_action, dm_narration) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $adventure_id, 1, 'Started Adventure', $initial_narration]);
        header("Location: adventure.php?id=" . $adventure_id);
    } else {
        header("Location: lobby.php?id=" . $adventure_id);
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Adventure</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #1a1a1a; color: #d4af37; font-family: "Georgia", serif; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .card { background: #2d2d2d; border: 3px solid #d4af37; border-radius: 15px; padding: 2rem; width: 100%; max-width: 450px; color: #d4af37; }
        .btn-dnd { background: #8b0000; color: white; border: 2px solid #5a0000; padding: 0.75rem; }
        .btn-dnd:hover { background: #8b0000 !important; color: white !important; border: 2px solid #5a0000 !important; filter: brightness(1.2); }
        .form-control { background: #1a1a1a !important; color: white !important; border-color: #d4af37 !important; padding: 12px; }
        .form-control::placeholder { color: #888 !important; opacity: 1; }
    </style>
</head>
<body class="bg-dark text-light">
    <div class="card shadow-lg">
        <h3 class="card-title text-center text-warning mb-4">Start a New Adventure</h3>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Type</label>
                <select name="type" class="form-select bg-dark text-light border-warning">
                    <option value="solo">Solo</option>
                    <option value="multiplayer">Multiplayer</option>
                </select>
            </div>
            <button type="submit" class="btn btn-dnd w-100 fw-bold">Create</button>
        </form>
        <a href="dashboard.php" class="btn btn-outline-secondary w-100 mt-3">Cancel</a>
    </div>
</body>
</html>
