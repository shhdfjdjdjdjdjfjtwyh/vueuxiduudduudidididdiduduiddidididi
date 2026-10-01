<?php
/**
 * Get All Categories
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/session.php';

try {
    $pdo = db();

    $stmt = $pdo->query("
        SELECT c.*, 
               (SELECT COUNT(*) FROM products WHERE category_id = c.id AND status = 'active') as product_count
        FROM categories c
        WHERE c.status = 'active' AND c.parent_id IS NULL
        ORDER BY c.sort_order ASC, c.name ASC
    ");
    $categories = $stmt->fetchAll();

    foreach ($categories as &$cat) {
        $cat['product_count'] = (int)$cat['product_count'];
        
        // Get subcategories
        $subStmt = $pdo->prepare("
            SELECT id, name, slug, image 
            FROM categories 
            WHERE parent_id = ? AND status = 'active'
            ORDER BY sort_order ASC
        ");
        $subStmt->execute([$cat['id']]);
        $cat['subcategories'] = $subStmt->fetchAll();
    }

    success('Categories loaded', ['categories' => $categories]);

} catch (Exception $e) {
    error('Failed: ' . $e->getMessage(), 500);
}