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
                        <p class="dm-text" id="dm-text-<?= $log['id'] ?>"></p>
                        <button id="skip-btn-<?= $log['id'] ?>" class="btn btn-sm btn-outline-secondary mt-1" onclick="skip(<?= $log['id'] ?>)">Skip</button>
                        <script>
                            function skip(id) {
                                document.getElementById('dm-text-'+id).innerHTML = `<?= str_replace(["\r", "\n"], ['<br>', '<br>'], htmlspecialchars_decode(strip_tags($narration, '<br>'))) ?>`;
                                document.getElementById('skip-btn-'+id).style.display = 'none';
                                window['stop_'+id] = true;
                            }
                            (function() {
                                const id = <?= $log['id'] ?>;
                                const text = `<?= str_replace(["\r", "\n"], ['\n', '\n'], htmlspecialchars_decode(strip_tags($narration))) ?>`;
                                const el = document.getElementById('dm-text-'+id);
                                let i = 0;
                                function type() {
                                    if(window['stop_'+id]) return;
                                    if (i < text.length) {
                                        const char = text.charAt(i);
                                        if (char === '\n') {
                                            el.appendChild(document.createElement('br'));
                                        } else {
                                            el.appendChild(document.createTextNode(char));
                                        }
                                        i++;
                                        setTimeout(type, 15);
                                    } else {
                                        const b = document.getElementById('skip-btn-'+id);
                                        if(b) b.style.display = 'none';
                                    }
                                }
                                type();
                            })();
                        </script>
                    <?php else: ?>
                        <p class="dm-text"><?= nl2br(htmlspecialchars($narration)) ?></p>
                    <?php endif; ?>
                    <hr class="border-secondary">
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="input-area">
            <form action="adventure_action.php?id=<?= $adventure_id ?>" method="POST" id="actionForm">
                <div class="input-group">
                    <input type="text" name="action" class="form-control bg-dark text-white border-secondary" placeholder="What do you do?" id="actionInput">
                    <button type="submit" name="action" value="Skip" class="btn btn-outline-secondary" onclick="document.getElementById('actionInput').required = false;">Skip</button>
                    <button type="submit" class="btn btn-dnd" id="submitBtn">Submit</button>
                </div>
            </form>
            <script>
                document.getElementById('actionForm').addEventListener('submit', function() {
                    const btn = document.getElementById('submitBtn');
                    btn.disabled = true;
                    btn.innerText = 'Sending...';
                });
            </script>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Use a persistent scroll technique that runs after ALL assets load.
        // We use a small timeout to ensure browser layout is finalized.
        function jumpToBottom() {
            const log = document.getElementById('log');
            if (log) {
                // Scroll to the very end
                log.scrollTop = log.scrollHeight;
                // Double-check: ensure the very last item is visible
                const lastItem = log.lastElementChild;
                if (lastItem) {
                    lastItem.scrollIntoView({ behavior: 'auto', block: 'end' });
                }
            }
        }

        // Run when the window finishes loading everything
        window.addEventListener('load', () => {
            // Give the browser a moment to settle after layout paint
            setTimeout(jumpToBottom, 150);
        });

        // Add a mutation observer to keep it scrolled as text types in or refreshes
        // ONLY if the user is already near the bottom (reading new content)
        const logContainer = document.getElementById('log');
        if (logContainer) {
            const observer = new MutationObserver((mutations) => {
                // If the user has scrolled up, do NOT auto-scroll.
                const isNearBottom = logContainer.scrollHeight - logContainer.scrollTop - logContainer.clientHeight < 150;
                if (!isNearBottom) return;

                // If a new DM narration or player action element was added, jump to bottom
                let newContent = false;
                mutations.forEach(m => {
                    m.addedNodes.forEach(node => {
                        if (node.nodeType === 1 && (node.classList.contains('dm-text') || node.classList.contains('player-text'))) {
                            newContent = true;
                        }
                    });
                });
                
                if (newContent) jumpToBottom();
            });
            observer.observe(logContainer, { childList: true, subtree: true });
        }

        function speakText(btn, text) {
            window.speechSynthesis.cancel();
            const utterance = new SpeechSynthesisUtterance(text);
            const voices = window.speechSynthesis.getVoices();
            
            // Try to find a male voice, or fallback to the first available en-US voice
            // Some mobile browsers restrict voice selection significantly.
            const preferredVoice = voices.find(v => 
                v.lang.startsWith('en') && 
                (v.name.toLowerCase().includes('male') || 
                 v.name.toLowerCase().includes('daniel') || 
                 v.name.toLowerCase().includes('david') ||
                 v.name.toLowerCase().includes('tom'))
            ) || voices.find(v => v.lang.startsWith('en'));

            if (preferredVoice) utterance.voice = preferredVoice;
            
            // Adjust pitch/rate for atmosphere
            utterance.pitch = 0.7; // Lower = deeper
            utterance.rate = 0.85; // Slower = more dramatic
            
            window.speechSynthesis.speak(utterance);
            
            // Console log to help debug if the voice is being found
            console.log("Selected voice:", preferredVoice ? preferredVoice.name : "None");
        }
    </script>
</body>
</html>
