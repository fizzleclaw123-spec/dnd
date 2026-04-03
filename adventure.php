<?php
session_start();
require 'db.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION["user_id"];

// Fetch the adventure history
$stmt = $pdo->prepare("SELECT * FROM adventure_logs WHERE user_id = ? ORDER BY turn_number ASC");
$stmt->execute([$user_id]);
$logs = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adventure Log</title>
    <!-- Use the same Bootstrap/CSS approach as Login/Register pages for consistency -->
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
            margin-bottom: 80px; /* Space for fixed form */
        }
        .dm-text { color: #f8f9fa; margin-bottom: 1rem; line-height: 1.5; }
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
        <div class="log-container" id="log">
            <?php if (empty($logs)): ?>
                <p class="text-center">The adventure hasn't started yet.</p>
            <?php else: ?>
                <?php foreach ($logs as $log): ?>
                    <p class="player-text">> <?= htmlspecialchars($log['player_action']) ?></p>
                    <p class="dm-text" id="dm-text-<?= $log['id'] ?>"></p>
                    <button class="btn btn-sm btn-outline-warning mb-2" onclick="speakText(this, `<?= htmlspecialchars(addslashes(str_replace(['<br />', '<br>'], ' ', $log['dm_narration']))) ?>`)">🔊 Listen</button>
                    <hr class="border-secondary">
                    <script>
                        (function() {
                            const rawText = `<?= htmlspecialchars_decode($log['dm_narration']) ?>`;
                            const text = rawText.replace(/<br \/>/g, '\n');
                            const element = document.getElementById('dm-text-<?= $log['id'] ?>');
                            let i = 0;
                            function type() {
                                if (i < text.length) {
                                    const char = text.charAt(i);
                                    if (char === '\n') {
                                        element.appendChild(document.createElement('br'));
                                    } else {
                                        element.appendChild(document.createTextNode(char));
                                    }
                                    i++;
                                    setTimeout(type, 15);
                                }
                            }
                            type();
                        })();
                    </script>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="input-area">
            <form action="adventure_action.php" method="POST">
                <div class="input-group">
                    <input type="text" name="action" class="form-control bg-dark text-white border-secondary" placeholder="What do you do?" required>
                    <button type="submit" class="btn btn-dnd">Submit</button>
                </div>
            </form>
        </div>
    </div>
    <script>
        const log = document.getElementById('log');
        log.scrollTop = log.scrollHeight;

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
