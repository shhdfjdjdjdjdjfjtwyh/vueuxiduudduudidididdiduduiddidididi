<?php
/**
 * Send OTP
 * POST: phone (10 digits)
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/security.php';
require_once __DIR__ . '/../helpers/session.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') error('Method not allowed', 405);

$input = json_decode(file_get_contents('php://input'), true);
$phone = clean($input['phone'] ?? '');

if (!validatePhone($phone)) error('Invalid phone number');

try {
    $pdo = db();

    // Rate limit: 1 OTP per 60 seconds
    $stmt = $pdo->prepare("
        SELECT created_at FROM otp_logs 
        WHERE phone = ? AND created_at > DATE_SUB(NOW(), INTERVAL 60 SECOND)
        ORDER BY id DESC LIMIT 1
    ");
    
    // Create otp_logs table if needed
    $pdo->exec("CREATE TABLE IF NOT EXISTS otp_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        phone VARCHAR(15),
        otp VARCHAR(6),
        expires_at DATETIME,
        is_used TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_phone (phone)
    )");

    $stmt->execute([$phone]);
    if ($stmt->fetch()) {
        error('Please wait 60 seconds before requesting another OTP', 429);
    }

    // Generate OTP
    $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $expiresAt = date('Y-m-d H:i:s', time() + 300); // 5 min

    // Save OTP
    $pdo->prepare("INSERT INTO otp_logs (phone, otp, expires_at) VALUES (?, ?, ?)")
        ->execute([$phone, $otp, $expiresAt]);

    // Send SMS (using MSG91 or similar)
    // For dev, just log it
    error_log("OTP for $phone: $otp");
    
    // In production, use MSG91:
    /*
    $authKey = MSG91_AUTH_KEY;
    $url = "https://api.msg91.com/api/v5/otp?template_id=XXX&mobile=91$phone&authkey=$authKey&otp=$otp";
    file_get_contents($url);
    */

    success('OTP sent successfully', [
        'phone' => $phone,
        'expires_in' => 300,
        // Remove this in production
        'dev_otp' => $otp
    ]);

} catch (Exception $e) {
    error('Failed to send OTP: ' . $e->getMessage(), 500);
}