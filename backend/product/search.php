<?php
/**
 * Search Products (autocomplete)
 * GET: ?q=query
 */

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
        SELECT id, name, slug, price, main_image, rating
        FROM products
        WHERE status = 'active' AND (name LIKE ? OR brand LIKE ? OR tags LIKE ?)
        ORDER BY sold DESC
        LIMIT 8
    ");
    $stmt->execute([$like, $like, $like]);
    $products = $stmt->fetchAll();

    foreach ($products as &$p) {
        $p['price'] = (float)$p['price'];
        $p['rating'] = (float)$p['rating'];
    }

    // Categories
    $stmt = $pdo->prepare("
        SELECT id, name, slug
        FROM categories
        WHERE status = 'active' AND name LIKE ?
        LIMIT 5
    ");
    $stmt->execute([$like]);
    $categories = $stmt->fetchAll();

    success('Search results', [
        'products'   => $products,
        'categories' => $categories
    ]);

} catch (Exception $e) {
    error('Failed: ' . $e->getMessage(), 500);
}