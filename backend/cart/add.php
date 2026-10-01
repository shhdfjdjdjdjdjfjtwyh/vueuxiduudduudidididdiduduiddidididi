<?php
/**
 * Add to Cart
 * POST: product_id, quantity
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/session.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') error('Method not allowed', 405);

$input = json_decode(file_get_contents('php://input'), true);
$myId = getCurrentUserId();

$productId = (int)($input['product_id'] ?? 0);
$quantity  = max(1, (int)($input['quantity'] ?? 1));

if (!$productId) error('Product ID required');

try {
    $pdo = db();

    // Check product exists & has stock
    $stmt = $pdo->prepare("
        SELECT id, name, price, quantity, main_image 
        FROM products 
        WHERE id = ? AND status = 'active'
    ");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();

    if (!$product) error('Product not found', 404);
    if ($product['quantity'] < $quantity) error('Not enough stock available');

    // Check if already in cart
    $stmt = $pdo->prepare("SELECT id, quantity FROM carts WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$myId, $productId]);
    $existing = $stmt->fetch();

    if ($existing) {
        $newQty = $existing['quantity'] + $quantity;
        if ($newQty > $product['quantity']) error('Cannot add more than available stock');
        
        $pdo->prepare("UPDATE carts SET quantity = ? WHERE id = ?")
            ->execute([$newQty, $existing['id']]);
    } else {
        $pdo->prepare("INSERT INTO carts (user_id, product_id, quantity) VALUES (?, ?, ?)")
            ->execute([$myId, $productId, $quantity]);
    }

    // Get updated cart count
    $stmt = $pdo->prepare("SELECT SUM(quantity) FROM carts WHERE user_id = ?");
    $stmt->execute([$myId]);
    $cartCount = (int)$stmt->fetchColumn();

    success('Added to cart', [
        'product' => $product,
        'cart_count' => $cartCount
    ]);

} catch (Exception $e) {
    error('Failed: ' . $e->getMessage(), 500);
}