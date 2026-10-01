<?php
/**
 * Get User's Support Tickets
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/session.php';

requireLogin();
$myId = getCurrentUserId();

try {
    $pdo = db();

    $stmt = $pdo->prepare("
        SELECT t.*, 
               (SELECT COUNT(*) FROM ticket_messages WHERE ticket_id = t.id) as message_count
        FROM support_tickets t
        WHERE t.user_id = ?
        ORDER BY t.updated_at DESC
    ");
    $stmt->execute([$myId]);
    $tickets = $stmt->fetchAll();

    success('Tickets loaded', ['tickets' => $tickets]);

} catch (Exception $e) {
    error('Failed: ' . $e->getMessage(), 500);
}