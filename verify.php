<?php
// verify.php
require_once "db.php";

session_start();

if (!isset($_GET['token'])) {
    $_SESSION["error"] = "Invalid verification link.";
    header("Location: index.php");
    exit;
}

$token = $_GET['token'];

try {
    // Find the user with this token
    $stmt = $pdo->prepare("SELECT id FROM users WHERE verification_token = :token");
    $stmt->execute(['token' => $token]);
    $user = $stmt->fetch();

    if ($user) {
        // Activate the user
        $stmt = $pdo->prepare("UPDATE users SET is_verified = 1 WHERE id = :id");
        $stmt->execute(['id' => $user['id']]);
        
        $_SESSION["message"] = "Account verified successfully! You can now log in.";
    } else {
        $_SESSION["error"] = "Invalid or expired verification token.";
    }
} catch (PDOException $e) {
    $_SESSION["error"] = "Error verifying account: " . $e->getMessage();
}

header("Location: index.php");
exit;
?>
