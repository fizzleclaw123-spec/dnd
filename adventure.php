<?php
session_start();
require 'db.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION["user_id"];

// Fetch the adventure logs for the SPECIFIC adventure
$adventure_id = $_GET['id'] ?? null;
if (!$adventure_id) {
    header("Location: dashboard.php");
    exit();
}
$_SESSION['active_adventure_id'] = $adventure_id;

$stmt = $pdo->prepare("SELECT * FROM adventure_logs WHERE adventure_id = ? ORDER BY turn_number ASC");
$stmt->execute([$adventure_id]);
$logs = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adventure Log</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { 
            background: #1a1a1a; 
            color: #d4af37; 
            font-family: "Georgia", serif; 
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .container { flex: 1; display: flex; flex-direction: column; }
        .log-container { 
            background: #2d2d2d; 
            border: 3px solid #d4af37; 
            border-radius: 15px; 
            padding: 15px; 
            flex-grow: 1;
            overflow-y: auto; 
            margin-bottom: 80px;
        }
        .dm-text { color: #f8f9fa; margin-bottom: 0.5rem; line-height: 1.5; }
        .player-text { color: #d4af37; font-weight: bold; margin-bottom: 0.5rem; word-wrap: break-word; }
        .input-area { 
            position: fixed; 
            bottom: 0; 
            left: 0; 
            width: 100%; 
            padding: 15px; 
            background: #1a1a1a; 
            border-top: 2px solid #d4af37;
        }
        .btn-dnd { background: #8b0000; color: white; border: 2px solid #5a0000; }
    </style>
</head>
<body>
    <div class="container py-3">
        <h2 class="text-center text-warning">Adventure Log</h2>
        <div class="text-center mb-3">
            <a href="dashboard.php" class="btn btn-outline-secondary">Leave Adventure (Return to Dashboard)</a>
        </div>
        <div class="log-container" id="log">
            <?php if (empty($logs)): ?>
                <p class="text-center">The adventure hasn't started yet.</p>
            <?php else: ?>
                <?php 
                $totalLogs = count($logs);
                foreach ($logs as $index => $log): 
                    $isLast = ($index === $totalLogs - 1);
                    $name_stmt = $pdo->prepare("SELECT a.name FROM adventurers a JOIN adventure_members am ON a.id = am.adventurer_id WHERE am.user_id = ? AND am.adventure_id = ?");
                    $name_stmt->execute([$log['user_id'], $adventure_id]);
                    $player_name = $name_stmt->fetchColumn() ?: "Adventurer";
                    
                    // Split the mechanics (if present in brackets) from the narration
                    $narration = $log['dm_narration'];
                    $roll_info = "";
                    if (preg_match('/^\[ROLL: (.*?)\]/', $narration, $matches)) {
                        $roll_info = $matches[1];
                        $narration = trim(substr($narration, strlen($matches[0])));
                    }
                ?>
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <p class="player-text mb-0">> <?= htmlspecialchars($player_name) ?>: <?= htmlspecialchars($log['player_action']) ?></p>
                        <button class="btn btn-sm btn-outline-warning" onclick="speakText(this, `<?= htmlspecialchars(addslashes(str_replace(['<br />', '<br>'], ' ', $narration))) ?>`)">🔊 Listen</button>
                    </div>
                    
                    <?php if ($roll_info): 
                        // Logic to determine badge color: Success vs Fail
                        $is_success = (strpos(strtolower($roll_info), 'success') !== false);
                        $badge_class = $is_success ? "btn-outline-success" : "btn-outline-danger";
                        $badge_text = $is_success ? "Success" : "Failure";
                    ?>
                        <div class="mb-2">
                            <button class="btn btn-sm <?= $badge_class ?>" type="button" data-bs-toggle="collapse" data-bs-target="#roll-<?= $log['id'] ?>">
                                <?= $badge_text ?> (Details)
                            </button>
                            <div class="collapse mt-2" id="roll-<?= $log['id'] ?>">
                                <div class="card card-body bg-dark text-white border-secondary">
                                    <code><?= htmlspecialchars($roll_info) ?></code>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($isLast): ?>
                        <p class="dm-text" id="dm-text-<?= $log['id'] ?>"><?= nl2br(htmlspecialchars($narration)) ?></p>
                        <script>
                            // Updated typewriter logic to handle the hidden roll (if needed)
                        </script>
                    <?php else: ?>
                        <p class="dm-text"><?= nl2br(htmlspecialchars($narration)) ?></p>
                    <?php endif; ?>
                    <hr class="border-secondary">
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="input-area">
            <form action="adventure_action.php?id=<?= $adventure_id ?>" method="POST">
                <div class="input-group">
                    <input type="text" name="action" class="form-control bg-dark text-white border-secondary" placeholder="What do you do?" required>
                    <button type="submit" class="btn btn-dnd">Submit</button>
                </div>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Force scroll to bottom immediately on page load
        // This runs AFTER the browser restores the old position
        window.addEventListener('load', () => {
            const log = document.getElementById('log');
            if (log) {
                log.scrollTop = log.scrollHeight;
            }
        });

        // Add a mutation observer to keep it scrolled as text types in
        const logContainer = document.getElementById('log');
        if (logContainer) {
            const observer = new MutationObserver(() => {
                logContainer.scrollTop = logContainer.scrollHeight;
            });
            observer.observe(logContainer, { childList: true, subtree: true });
        }

        function speakText(btn, text) {
            window.speechSynthesis.cancel();
            const utterance = new SpeechSynthesisUtterance(text);
            const voices = window.speechSynthesis.getVoices();
            const preferredVoice = voices.find(v => v.lang.startsWith('en') && (v.name.includes('Google') || v.name.includes('Microsoft') || v.name.includes('Neural')));
            if (preferredVoice) utterance.voice = preferredVoice;
            utterance.lang = 'en-US';
            window.speechSynthesis.speak(utterance);
        }
    </script>
</body>
</html>
