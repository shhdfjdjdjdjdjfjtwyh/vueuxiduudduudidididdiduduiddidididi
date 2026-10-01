<?php
/**
 * Get Product Reviews
 * GET: ?product_id=X&page=1
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/session.php';

$productId = (int)($_GET['product_id'] ?? 0);
$page  = max(1, (int)($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;

if (!$productId) error('Product ID required');

try {
    $pdo = db();

    // Total count
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM product_reviews WHERE product_id = ? AND status = 'approved'");
    $stmt->execute([$productId]);
    $total = (int)$stmt->fetchColumn();

    // Reviews
    $stmt = $pdo->prepare("
        SELECT r.id, r.rating, r.title, r.review, r.images, 
               r.is_verified_purchase, r.created_at,
               u.username, u.full_name, u.avatar
        FROM product_reviews r
        JOIN users u ON u.id = r.user_id
        WHERE r.product_id = ? AND r.status = 'approved'
        ORDER BY r.created_at DESC
        LIMIT $limit OFFSET $offset
    ");
    $stmt->execute([$productId]);
    $reviews = $stmt->fetchAll();

    foreach ($reviews as &$r) {
        $r['rating'] = (int)$r['rating'];
        $r['images'] = $r['images'] ? json_decode($r['images'], true) : [];
        $r['is_verified_purchase'] = (bool)$r['is_verified_purchase'];
    }

    // Rating breakdown
    $stmt = $pdo->prepare("
        SELECT rating, COUNT(*) as count 
        FROM product_reviews 
        WHERE product_id = ? AND status = 'approved'
        GROUP BY rating
    ");
    $stmt->execute([$productId]);
    $breakdown = [];
    for ($i = 5; $i >= 1; $i--) {
        $breakdown[$i] = 0;
    }
    foreach ($stmt->fetchAll() as $row) {
        $breakdown[(int)$row['rating']] = (int)$row['count'];
    }

    success('Reviews loaded', [
        'reviews'   => $reviews,
        'total'     => $total,
        'page'      => $page,
        'pages'     => ceil($total / $limit),
        'breakdown' => $breakdown
    ]);

} catch (Exception $e) {
    error('Failed: ' . $e->getMessage(), 500);
}