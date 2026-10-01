<?php
/**
 * Delete Product (Admin)
 * POST: product_id
 */

require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/session.php';
require_once __DIR__ . '/../../helpers/logger.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') error('Method not allowed', 405);

$input = json_decode(file_get_contents('php://input'), true);
$adminId = getCurrentUserId();
$productId = (int)($input['product_id'] ?? 0);

if (!$productId) error('Product ID required');

try {
    $pdo = db();

    // Get images to delete
    $stmt = $pdo->prepare("SELECT main_image, gallery_images FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();

    if (!$product) error('Product not found', 404);

    // Delete images
    if ($product['main_image'] && file_exists(PRODUCT_PATH . $product['main_image'])) {
        @unlink(PRODUCT_PATH . $product['main_image']);
    }

    $gallery = json_decode($product['gallery_images'] ?: '[]', true);
    foreach ($gallery as $img) {
        if (file_exists(PRODUCT_PATH . $img)) @unlink(PRODUCT_PATH . $img);
    }

    // Soft delete
    $pdo->prepare("UPDATE products SET status = 'inactive' WHERE id = ?")
        ->execute([$productId]);

    logActivity($adminId, 'admin_delete_product', "Deleted product #$productId", 'admin');

    success('Product deleted');

} catch (Exception $e) {
    error('Failed: ' . $e->getMessage(), 500);
}