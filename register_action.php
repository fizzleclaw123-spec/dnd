<?php
// register_action.php
require_once "db.php";
require_once "vendor/autoload.php";
require_once ".env.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);
    $password = $_POST["password"];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION["error"] = "Invalid email format.";
        header("Location: register.php");
        exit;
    }

    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $verification_token = bin2hex(random_bytes(16));

    try {
        $stmt = $pdo->prepare("INSERT INTO users (email, password_hash, verification_token) VALUES (:email, :password_hash, :token)");
        $stmt->execute([
            "email" => $email,
            "password_hash" => $password_hash,
            "token" => $verification_token
        ]);

        $mail = new PHPMailer(true);

        // Server settings
        $mail->isSMTP();
        $mail->Host       = getenv('SMTP_HOST') ?: "smtp.gmail.com";
        $mail->SMTPAuth   = true;
        $mail->Username   = getenv('SMTP_USER') ?: "YOUR_GMAIL_ADDRESS@gmail.com";
        $mail->Password   = getenv('SMTP_PASS') ?: "YOUR_APP_PASSWORD"; // MUST use App Password, not main login password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom("noreply@dnd.local", "D&D Manager");
        $mail->addAddress($email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = "Confirm your D&D Registration";
        $host = $_SERVER['HTTP_HOST'];
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https" : "http";
        $verify_link = $protocol . "://" . $host . "/verify.php?token=" . $verification_token;
        $mail->Body    = "Welcome adventurer! Please click the link to verify your account: <a href=\"$verify_link\">Verify Account</a>";

        $mail->send();
        $_SESSION["message"] = "Registration successful! Please check your email to verify your account.";
        header("Location: index.php");
        exit;

    } catch (Exception $e) {
        $_SESSION["error"] = "Registration successful, but failed to send verification email: " . $mail->ErrorInfo;
        header("Location: register.php");
        exit;
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            $_SESSION["error"] = "Email is already registered.";
        } else {
            $_SESSION["error"] = "Error: " . $e->getMessage();
        }
        header("Location: register.php");
        exit;
    }
}
?>
