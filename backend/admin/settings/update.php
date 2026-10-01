<?php
/**
 * Update Settings (Admin)
 * POST: { settings: { key: value, ... } }
 */

require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/session.php';
require_once __DIR__ . '/../../helpers/logger.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') error('Method not allowed', 405);

$input = json_decode(file_get_contents('php://input'), true);
$adminId = getCurrentUserId();
$settings = $input['settings'] ?? [];

if (!is_array($settings) || empty($settings)) error('Settings required');

try {
    $pdo = db();

    $stmt = $pdo->prepare("
        INSERT INTO settings (key_name, value) VALUES (?, ?)
        ON DUPLICATE KEY UPDATE value = VALUES(value)
    ");

    foreach ($settings as $key => $value) {
        $stmt->execute([$key, $value]);
    }

    logActivity($adminId, 'admin_update_settings', 'Updated ' . count($settings) . ' settings', 'admin');

    success('Settings saved');

} catch (Exception $e) {
    error('Failed: ' . $e->getMessage(), 500);
}