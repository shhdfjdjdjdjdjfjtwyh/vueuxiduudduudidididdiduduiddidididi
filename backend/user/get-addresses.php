<?php
/**
 * Get User Addresses
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/session.php';

requireLogin();
$myId = getCurrentUserId();

try {
    $pdo = db();

    // Ensure table exists
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

    $stmt = $pdo->prepare("SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC, id DESC");
    $stmt->execute([$myId]);
    $addresses = $stmt->fetchAll();

    foreach ($addresses as &$a) {
        $a['is_default'] = (bool)$a['is_default'];
    }

    success('Addresses loaded', ['addresses' => $addresses]);

} catch (Exception $e) {
    error('Failed: ' . $e->getMessage(), 500);
}