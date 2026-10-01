<?php
/**
 * Get All Products with filters
 * GET: ?page=1&limit=20&category=&search=&sort=&min_price=&max_price=
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/session.php';
require_once __DIR__ . '/../helpers/pagination.php';

$page     = max(1, (int)($_GET['page'] ?? 1));
$limit    = min(50, max(1, (int)($_GET['limit'] ?? 20)));
$offset   = ($page - 1) * $limit;

$category = (int)($_GET['category'] ?? 0);
$search   = trim($_GET['search'] ?? '');
$sort     = $_GET['sort'] ?? 'newest';
$minPrice = (float)($_GET['min_price'] ?? 0);
$maxPrice = (float)($_GET['max_price'] ?? 0);
$featured = isset($_GET['featured']) ? (int)$_GET['featured'] : 0;
$brand    = trim($_GET['brand'] ?? '');

try {
    $pdo = db();

    $where = ["p.status = 'active'"];
    $params = [];

    if ($category > 0) {
        $where[] = "(p.category_id = ? OR p.category_id IN (SELECT id FROM categories WHERE parent_id = ?))";
        $params[] = $category;
        $params[] = $category;
    }

    if ($search !== '') {
        $where[] = "(p.name LIKE ? OR p.short_description LIKE ? OR p.brand LIKE ? OR p.tags LIKE ?)";
        $like = "%$search%";
        array_push($params, $like, $like, $like, $like);
    }

    if ($minPrice > 0) {
        $where[] = "p.price >= ?";
        $params[] = $minPrice;
    }

    if ($maxPrice > 0) {
        $where[] = "p.price <= ?";
        $params[] = $maxPrice;
    }

    if ($featured) {
        $where[] = "p.is_featured = 1";
    }

    if ($brand !== '') {
        $where[] = "p.brand = ?";
        $params[] = $brand;
    }

    $whereSQL = implode(' AND ', $where);

    // Sort
    $orderBy = match($sort) {
        'price_low'   => 'p.price ASC',
        'price_high'  => 'p.price DESC',
        'rating'      => 'p.rating DESC',
        'popular'     => 'p.sold DESC',
        'discount'    => 'p.discount_percent DESC',
        default       => 'p.created_at DESC'
    };

    // Total count
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM products p WHERE $whereSQL");
    $countStmt->execute($params);
    $total = (int)$countStmt->fetchColumn();

    // Products
    $stmt = $pdo->prepare("
        SELECT p.id, p.name, p.slug, p.price, p.mrp, p.discount_percent,
               p.main_image, p.quantity, p.rating, p.review_count, p.sold,
               p.is_featured, p.is_new, p.brand, p.short_description,
               c.name as category_name, c.slug as category_slug
        FROM products p
        LEFT JOIN categories c ON c.id = p.category_id
        WHERE $whereSQL
        ORDER BY $orderBy
        LIMIT $limit OFFSET $offset
    ");
    $stmt->execute($params);
    $products = $stmt->fetchAll();

    // Cast types
    foreach ($products as &$p) {
        $p['price']            = (float)$p['price'];
        $p['mrp']              = (float)$p['mrp'];
        $p['discount_percent'] = (float)$p['discount_percent'];
        $p['quantity']         = (int)$p['quantity'];
        $p['rating']           = (float)$p['rating'];
        $p['review_count']     = (int)$p['review_count'];
        $p['sold']             = (int)$p['sold'];
        $p['is_featured']      = (bool)$p['is_featured'];
        $p['is_new']           = (bool)$p['is_new'];
        $p['in_stock']         = $p['quantity'] > 0;
    }

    paginated($products, $total, $page, $limit, 'Products loaded');

} catch (Exception $e) {
    error('Failed: ' . $e->getMessage(), 500);
}