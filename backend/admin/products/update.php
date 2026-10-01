<?php
/**
 * Update Product (Admin)
 */

require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/security.php';
require_once __DIR__ . '/../../helpers/session.php';
require_once __DIR__ . '/../../helpers/file-upload.php';
require_once __DIR__ . '/../../helpers/logger.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') error('Method not allowed', 405);

$adminId = getCurrentUserId();
$productId = (int)($_POST['id'] ?? 0);

if (!$productId) error('Product ID required');

try {
    $pdo = db();

    // Check exists
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();

    if (!$product) error('Product not found', 404);

    // Fields
    $name        = clean($_POST['name'] ?? $product['name']);
    $description = $_POST['description'] ?? $product['description'];
    $shortDesc   = clean($_POST['short_description'] ?? $product['short_description']);
    $price       = (float)($_POST['price'] ?? $product['price']);
    $mrp         = (float)($_POST['mrp'] ?? $product['mrp']);
    $quantity    = (int)($_POST['quantity'] ?? $product['quantity']);
    $categoryId  = (int)($_POST['category_id'] ?? $product['category_id']);
    $brand       = clean($_POST['brand'] ?? $product['brand']);
    $status      = $_POST['status'] ?? $product['status'];
    $isFeatured  = (int)($_POST['is_featured'] ?? $product['is_featured']);

    $discount = 0;
    if ($mrp > $price) {
        $discount = round((($mrp - $price) / $mrp) * 100, 2);
    }

    // Main image
    $mainImage = $product['main_image'];
    if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] === UPLOAD_ERR_OK) {
        $uploader = new FileUpload(PRODUCT_PATH);
        $newImg = $uploader->upload($_FILES['main_image'], 'main');
        if ($newImg) {
            // Delete old
            if ($mainImage && file_exists(PRODUCT_PATH . $mainImage)) {
                @unlink(PRODUCT_PATH . $mainImage);
            }
            $mainImage = $newImg;
        }
    }

    // Gallery
    $gallery = json_decode($product['gallery_images'] ?: '[]', true);
    if (!empty($_FILES['gallery_images']['name'][0])) {
        $uploader = new FileUpload(PRODUCT_PATH);
        $newGallery = $uploader->uploadMultiple($_FILES['gallery_images'], 'gal');
        $gallery = array_merge($gallery, $newGallery);
    }

    // Update
    $stmt = $pdo->prepare("
        UPDATE products SET
            name = ?, description = ?, short_description = ?, category_id = ?, brand = ?,
            price = ?, mrp = ?, discount_percent = ?, quantity = ?,
            main_image = ?, gallery_images = ?, is_featured = ?, status = ?,
            updated_at = NOW()
        WHERE id = ?
    ");
    $stmt->execute([
        $name, $description, $shortDesc, $categoryId, $brand,
        $price, $mrp, $discount, $quantity,
        $mainImage, json_encode($gallery), $isFeatured, $status,
        $productId
    ]);

    logActivity($adminId, 'admin_update_product', "Updated product #$productId", 'admin');

    success('Product updated');

} catch (Exception $e) {
    error('Failed: ' . $e->getMessage(), 500);
}