<?php
/**
 * Global Search (Products + Categories)
 * GET: ?q=query
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';

$q = trim($_GET['q'] ?? '');

if (strlen($q) < 2) {
    success('Query too short', ['products' => [], 'categories' => []]);
}

try {
    $pdo = db();
    $like = "%$q%";

    // Products
    $stmt = $pdo->prepare("
        SELECT id, name, slug, price, mrp, main_image, rating, review_count
        FROM products
        WHERE status = 'active' AND (name LIKE ? OR brand LIKE ? OR tags LIKE ?)
        ORDER BY sold DESC
        LIMIT 10
    ");
    $stmt->execute([$like, $like, $like]);
    $products = $stmt->fetchAll();

    // Categories
    $stmt = $pdo->prepare("
        SELECT id, name, slug, image
        FROM categories
        WHERE status = 'active' AND name LIKE ?
        LIMIT 5
    ");
    $stmt->execute([$like]);
    $categories = $stmt->fetchAll();

    success('Search results', [
        'query' => $q,
        'products' => $products,
        'categories' => $categories
    ]);

} catch (Exception $e) {
    error('Failed: ' . $e->getMessage(), 500);
}