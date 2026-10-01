<?php
/**
 * Verify OTP
 * POST: phone, otp
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/security.php';
require_once __DIR__ . '/../helpers/session.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') error('Method not allowed', 405);

$input = json_decode(file_get_contents('php://input'), true);
$phone = clean($input['phone'] ?? '');
$otp   = clean($input['otp'] ?? '');

if (!validatePhone($phone)) error('Invalid phone number');
if (strlen($otp) !== 6) error('Invalid OTP');

try {
    $pdo = db();

    // Find OTP
    $stmt = $pdo->prepare("
        SELECT * FROM otp_logs 
        WHERE phone = ? AND otp = ? AND is_used = 0 AND expires_at > NOW()
        ORDER BY id DESC LIMIT 1
    ");
    $stmt->execute([$phone, $otp]);
    $log = $stmt->fetch();

    if (!$log) error('Invalid or expired OTP', 401);

    // Mark used
    $pdo->prepare("UPDATE otp_logs SET is_used = 1 WHERE id = ?")
        ->execute([$log['id']]);

    // Check if user exists with this phone
    $stmt = $pdo->prepare("SELECT * FROM users WHERE phone = ? LIMIT 1");
    $stmt->execute([$phone]);
    $user = $stmt->fetch();

    if ($user) {
        // Existing user - login
        if ($user['status'] === 'banned') error('Account banned', 403);
        
        $pdo->prepare("UPDATE users SET phone_verified = 1, last_login = NOW() WHERE id = ?")
            ->execute([$user['id']]);

        unset($user['password']);
        setUserSession($user);
        success('Login successful', ['user' => $user, 'is_new' => false]);
        
    } else {
        // New user - need more info
        success('Phone verified. Please complete signup.', [
            'phone' => $phone,
            'is_new' => true,
            'verified' => true
        ]);
    }

} catch (Exception $e) {
    error('Verification failed: ' . $e->getMessage(), 500);
}