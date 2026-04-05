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
                ?>
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <p class="player-text mb-0">> <?= htmlspecialchars($log['player_action']) ?></p>
                        <button class="btn btn-sm btn-outline-warning" onclick="speakText(this, `<?= htmlspecialchars(addslashes(str_replace(['<br />', '<br>'], ' ', $log['dm_narration']))) ?>`)">🔊 Listen</button>
                    </div>
                    
                    <?php if ($isLast): ?>
                        <p class="dm-text" id="dm-text-<?= $log['id'] ?>"></p>
                        <button id="skip-btn-<?= $log['id'] ?>" class="btn btn-sm btn-outline-secondary mt-1" onclick="skipTypewriter(<?= $log['id'] ?>, `<?= htmlspecialchars(addslashes(str_replace(['<br />', '<br>'], ' ', $log['dm_narration']))) ?>`)">Skip Animation</button>
                        <script>
                            let timer_<?= $log['id'] ?>;
                            function skipTypewriter(id, text) {
                                clearTimeout(timer_<?= $log['id'] ?>);
                                const element = document.getElementById('dm-text-' + id);
                                element.innerHTML = text.replace(/\n/g, '<br>');
                                document.getElementById('skip-btn-' + id).style.display = 'none';
                            }

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
                                        timer_<?= $log['id'] ?> = setTimeout(type, 15);
                                    } else {
                                        document.getElementById('skip-btn-<?= $log['id'] ?>').style.display = 'none';
                                    }
                                }
                                type();
                            })();
                        </script>
                    <?php else: ?>
                        <p class="dm-text"><?= nl2br(htmlspecialchars($log['dm_narration'])) ?></p>
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
