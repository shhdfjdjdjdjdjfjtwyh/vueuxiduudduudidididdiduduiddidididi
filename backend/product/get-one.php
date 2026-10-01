<?php
/**
 * Get Single Product by ID or Slug
 * GET: ?id=123 OR ?slug=product-name
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/session.php';

$id   = (int)($_GET['id'] ?? 0);
$slug = trim($_GET['slug'] ?? '');

if (!$id && !$slug) error('Product ID or slug required');

try {
    $pdo = db();

    $where = $id ? "p.id = ?" : "p.slug = ?";
    $param = $id ?: $slug;

    $stmt = $pdo->prepare("
        SELECT p.*, c.name as category_name, c.slug as category_slug
        FROM products p
        LEFT JOIN categories c ON c.id = p.category_id
        WHERE $where AND p.status = 'active'
        LIMIT 1
    ");
    $stmt->execute([$param]);
    $product = $stmt->fetch();

    if (!$product) error('Product not found', 404);

    // Cast types
    $product['price']            = (float)$product['price'];
    $product['mrp']              = (float)$product['mrp'];
    $product['discount_percent'] = (float)$product['discount_percent'];
    $product['quantity']         = (int)$product['quantity'];
    $product['rating']           = (float)$product['rating'];
    $product['review_count']     = (int)$product['review_count'];
    $product['sold']             = (int)$product['sold'];
    $product['views']            = (int)$product['views'];
    $product['is_featured']      = (bool)$product['is_featured'];
    $product['is_new']           = (bool)$product['is_new'];
    $product['in_stock']         = $product['quantity'] > 0;

    // Parse JSON fields
    $product['gallery_images'] = $product['gallery_images'] ? json_decode($product['gallery_images'], true) : [];
    $product['attributes']     = $product['attributes'] ? json_decode($product['attributes'], true) : [];

    // Increment views
    $pdo->prepare("UPDATE products SET views = views + 1 WHERE id = ?")
        ->execute([$product['id']]);

    // Related products
    $stmt = $pdo->prepare("
        SELECT id, name, price, mrp, discount_percent, main_image, rating
        FROM products
        WHERE category_id = ? AND id != ? AND status = 'active'
        ORDER BY RAND()
        LIMIT 8
    ");
    $stmt->execute([$product['category_id'], $product['id']]);
    $related = $stmt->fetchAll();

    foreach ($related as &$r) {
        $r['price'] = (float)$r['price'];
        $r['mrp'] = (float)$r['mrp'];
        $r['rating'] = (float)$r['rating'];
    }

    success('Product loaded', [
        'product' => $product,
        'related' => $related
    ]);

} catch (Exception $e) {
    error('Failed: ' . $e->getMessage(), 500);
}