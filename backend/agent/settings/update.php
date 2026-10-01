<?php
/**
 * Update Agent Settings
 * POST: business_name, phone
 */

require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/security.php';
require_once __DIR__ . '/../../helpers/session.php';
require_once __DIR__ . '/../../middleware/agent-auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') error('Method not allowed', 405);

$input = json_decode(file_get_contents('php://input'), true);
$agentId = getCurrentAgentId();

$businessName = clean($input['business_name'] ?? '');
$phone = clean($input['phone'] ?? '');

try {
    $pdo = db();

    if ($businessName) {
        $pdo->prepare("UPDATE agents SET business_name = ? WHERE id = ?")
            ->execute([$businessName, $agentId]);
    }

    if ($phone) {
        if (!validatePhone($phone)) error('Invalid phone');
        
        $stmt = $pdo->prepare("SELECT user_id FROM agents WHERE id = ?");
        $stmt->execute([$agentId]);
        $userId = $stmt->fetchColumn();
        
        $pdo->prepare("UPDATE users SET phone = ? WHERE id = ?")
            ->execute([$phone, $userId]);
    }

    success('Settings updated');

} catch (Exception $e) {
    error('Failed: ' . $e->getMessage(), 500);
}