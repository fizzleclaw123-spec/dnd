<?php
// db.php
$db_file = __DIR__ . '/dnd.db';
try {
    $pdo = new PDO("sqlite:" . $db_file);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create users table if it doesn't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        email TEXT UNIQUE NOT NULL,
        password_hash TEXT NOT NULL,
        verification_token TEXT NOT NULL,
        is_verified INTEGER DEFAULT 0
    )");
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
