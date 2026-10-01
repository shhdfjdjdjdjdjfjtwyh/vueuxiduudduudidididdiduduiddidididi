<?php
/**
 * Forgot Password - Send reset link
 * POST: email
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/security.php';
require_once __DIR__ . '/../helpers/session.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') error('Method not allowed', 405);

$input = json_decode(file_get_contents('php://input'), true);
$email = clean($input['email'] ?? '');

if (!validateEmail($email)) error('Invalid email address');

try {
    $pdo = db();

    $stmt = $pdo->prepare("SELECT id, username FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // Always success message (security)
    if (!$user) {
        success('If this email exists, a reset link has been sent.');
    }

    // Generate token
    $token = generateToken(64);
    $expires = date('Y-m-d H:i:s', time() + 3600); // 1 hour

    $pdo->prepare("
        UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?
    ")->execute([$token, $expires, $user['id']]);

    // Send email
    $resetLink = SITE_URL . "/reset-password.html?token=$token";
    
    // Use PHPMailer
    require_once __DIR__ . '/../helpers/mailer.php';
    sendEmail($email, 'Password Reset - ' . SITE_NAME, "
        <h2>Reset Your Password</h2>
        <p>Hi {$user['username']},</p>
        <p>Click the link below to reset your password:</p>
        <p><a href='$resetLink'>$resetLink</a></p>
        <p>This link expires in 1 hour.</p>
        <p>If you didn't request this, ignore this email.</p>
    ");

    success('Reset link sent to your email');

} catch (Exception $e) {
    error('Failed: ' . $e->getMessage(), 500);
}