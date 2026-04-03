<?php
session_start();
require 'db.php'; // $pdo available here

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        header("Location: dashboard.php");
        exit;
    } else {
        $_SESSION['message'] = "Invalid credentials.";
        header("Location: index.php");
        exit;
    }
}
header("Location: index.php");
exit;
?>
