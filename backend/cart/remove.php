<?php
/**
 * Remove from Cart
 * POST: cart_id
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/session.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') error('Method not allowed', 405);

$input = json_decode(file_get_contents('php://input'), true);
$myId = getCurrentUserId();
$cartId = (int)($input['cart_id'] ?? 0);

if (!$cartId) error('Cart ID required');

try {
    $pdo = db();
    $stmt = $pdo->prepare("DELETE FROM carts WHERE id = ? AND user_id = ?");
    $stmt->execute([$cartId, $myId]);

    if ($stmt->rowCount() === 0) error('Item not found');

    success('Removed from cart');

} catch (Exception $e) {
    error('Failed: ' . $e->getMessage(), 500);
}