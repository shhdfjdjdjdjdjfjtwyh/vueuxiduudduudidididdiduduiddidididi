<?php
/**
 * Add Product Review
 * POST: product_id, rating, title, review
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/security.php';
require_once __DIR__ . '/../helpers/session.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') error('Method not allowed', 405);

$input = json_decode(file_get_contents('php://input'), true);
$myId = getCurrentUserId();

$productId = (int)($input['product_id'] ?? 0);
$rating    = (int)($input['rating'] ?? 0);
$title     = clean($input['title'] ?? '');
$review    = clean($input['review'] ?? '');

if (!$productId) error('Product ID required');
if ($rating < 1 || $rating > 5) error('Rating must be 1-5');
if (empty($review)) error('Review text required');
if (strlen($review) > 2000) error('Review too long');

try {
    $pdo = db();

    // Check product exists
    $stmt = $pdo->prepare("SELECT id FROM products WHERE id = ? AND status = 'active'");
    $stmt->execute([$productId]);
    if (!$stmt->fetch()) error('Product not found', 404);

    // Check if already reviewed
    $stmt = $pdo->prepare("SELECT id FROM product_reviews WHERE product_id = ? AND user_id = ?");
    $stmt->execute([$productId, $myId]);
    if ($stmt->fetch()) error('You have already reviewed this product');

    // Check if verified purchase
    $stmt = $pdo->prepare("
        SELECT o.id FROM orders o
        JOIN order_items oi ON oi.order_id = o.id
        WHERE o.user_id = ? AND oi.product_id = ? AND o.status = 'delivered'
        LIMIT 1
    ");
    $stmt->execute([$myId, $productId]);
    $isVerified = (bool)$stmt->fetch();

    // Insert review
    $stmt = $pdo->prepare("
        INSERT INTO product_reviews (product_id, user_id, rating, title, review, is_verified_purchase)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$productId, $myId, $rating, $title, $review, $isVerified ? 1 : 0]);

    // Update product rating
    $pdo->prepare("
        UPDATE products SET 
            rating = (SELECT AVG(rating) FROM product_reviews WHERE product_id = ? AND status = 'approved'),
            review_count = (SELECT COUNT(*) FROM product_reviews WHERE product_id = ? AND status = 'approved')
        WHERE id = ?
    ")->execute([$productId, $productId, $productId]);

    success('Review submitted! It will appear after approval.');

} catch (Exception $e) {
    error('Failed: ' . $e->getMessage(), 500);
}