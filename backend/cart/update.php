<?php
/**
 * Update Cart Quantity
 * POST: cart_id, quantity
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
$qty = (int)($input['quantity'] ?? 1);

if (!$cartId) error('Cart ID required');
if ($qty < 1) error('Quantity must be at least 1');

try {
    $pdo = db();

    // Verify ownership
    $stmt = $pdo->prepare("
        SELECT c.id, p.quantity as stock 
        FROM carts c
        JOIN products p ON p.id = c.product_id
        WHERE c.id = ? AND c.user_id = ?
    ");
    $stmt->execute([$cartId, $myId]);
    $cart = $stmt->fetch();

    if (!$cart) error('Cart item not found', 404);
    if ($qty > $cart['stock']) error('Only ' . $cart['stock'] . ' items available');

    $pdo->prepare("UPDATE carts SET quantity = ? WHERE id = ?")
        ->execute([$qty, $cartId]);

    success('Cart updated');

} catch (Exception $e) {
    error('Failed: ' . $e->getMessage(), 500);
}