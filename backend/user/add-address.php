<?php
/**
 * Add Address
 * POST: full_name, phone, address, city, state, pincode, is_default
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/security.php';
require_once __DIR__ . '/../helpers/session.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') error('Method not allowed', 405);

$input = json_decode(file_get_contents('php://input'), true);
$myId = getCurrentUserId();

$fullName = clean($input['full_name'] ?? '');
$phone    = clean($input['phone'] ?? '');
$address  = clean($input['address'] ?? '');
$city     = clean($input['city'] ?? '');
$state    = clean($input['state'] ?? '');
$pincode  = clean($input['pincode'] ?? '');
$isDefault = (int)($input['is_default'] ?? 0);

if (!$fullName || !$phone || !$address || !$city || !$state || !$pincode) {
    error('All fields required');
}
if (!validatePhone($phone)) error('Invalid phone');
if (!preg_match('/^\d{6}$/', $pincode)) error('Invalid pincode');

try {
    $pdo = db();

    // Create addresses table if not exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS addresses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        full_name VARCHAR(100),
        phone VARCHAR(15),
        address TEXT,
        city VARCHAR(100),
        state VARCHAR(100),
        pincode VARCHAR(10),
        is_default TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");

    // If setting default, remove other defaults
    if ($isDefault) {
        $pdo->prepare("UPDATE addresses SET is_default = 0 WHERE user_id = ?")
            ->execute([$myId]);
    }

    $stmt = $pdo->prepare("
        INSERT INTO addresses (user_id, full_name, phone, address, city, state, pincode, is_default)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$myId, $fullName, $phone, $address, $city, $state, $pincode, $isDefault]);

    success('Address added', ['id' => $pdo->lastInsertId()]);

} catch (Exception $e) {
    error('Failed: ' . $e->getMessage(), 500);
}