<?php
/**
 * Create Support Ticket
 * POST: subject, message, priority
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

$subject = clean($input['subject'] ?? '');
$message = clean($input['message'] ?? '');
$priority = $input['priority'] ?? 'medium';

if (!$subject || !$message) error('Subject and message required');
if (!in_array($priority, ['low', 'medium', 'high', 'urgent'])) $priority = 'medium';

try {
    $pdo = db();
    $pdo->beginTransaction();

    $ticketNumber = 'TKT' . date('Ymd') . rand(1000, 9999);

    // Create ticket
    $stmt = $pdo->prepare("
        INSERT INTO support_tickets (ticket_number, user_id, subject, priority, status)
        VALUES (?, ?, ?, ?, 'open')
    ");
    $stmt->execute([$ticketNumber, $myId, $subject, $priority]);

    $ticketId = $pdo->lastInsertId();

    // First message
    $pdo->prepare("
        INSERT INTO ticket_messages (ticket_id, sender_id, sender_type, message)
        VALUES (?, ?, 'user', ?)
    ")->execute([$ticketId, $myId, $message]);

    $pdo->commit();

    success('Ticket created', [
        'ticket_id' => $ticketId,
        'ticket_number' => $ticketNumber
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error('Failed: ' . $e->getMessage(), 500);
}