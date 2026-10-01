<?php
/**
 * Create Product (Admin)
 * POST (multipart): name, description, price, mrp, quantity, category_id, brand, main_image, gallery_images[]
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

$name        = clean($_POST['name'] ?? '');
$description = $_POST['description'] ?? '';
$shortDesc   = clean($_POST['short_description'] ?? '');
$price       = (float)($_POST['price'] ?? 0);
$mrp         = (float)($_POST['mrp'] ?? 0);
$quantity    = (int)($_POST['quantity'] ?? 0);
$categoryId  = (int)($_POST['category_id'] ?? 0);
$brand       = clean($_POST['brand'] ?? '');
$tags        = clean($_POST['tags'] ?? '');
$isFeatured  = (int)($_POST['is_featured'] ?? 0);

if (empty($name)) error('Product name required');
if ($price <= 0) error('Valid price required');
if ($quantity < 0) error('Valid quantity required');
if (!$categoryId) error('Category required');

try {
    $pdo = db();

    // Generate slug & SKU
    $slug = strtolower(preg_replace('/[^a-z0-9]+/', '-', $name));
    $slug = trim($slug, '-');

    // Ensure unique slug
    $stmt = $pdo->prepare("SELECT id FROM products WHERE slug = ?");
    $stmt->execute([$slug]);
    if ($stmt->fetch()) {
        $slug .= '-' . time();
    }

    $sku = 'SV-' . strtoupper(substr(md5(uniqid()), 0, 8));

    // Calculate discount
    $discount = 0;
    if ($mrp > $price) {
        $discount = round((($mrp - $price) / $mrp) * 100, 2);
    }

    // Upload main image
    $mainImage = null;
    if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] === UPLOAD_ERR_OK) {
        $uploader = new FileUpload(PRODUCT_PATH);
        $mainImage = $uploader->upload($_FILES['main_image'], 'main');
        if (!$mainImage) error('Main image upload failed: ' . $uploader->firstError());
    }

    // Upload gallery
    $gallery = [];
    if (!empty($_FILES['gallery_images']['name'][0])) {
        $uploader = new FileUpload(PRODUCT_PATH);
        $gallery = $uploader->uploadMultiple($_FILES['gallery_images'], 'gal');
    }

    // Insert
    $stmt = $pdo->prepare("
        INSERT INTO products (
            name, slug, sku, description, short_description, category_id, brand,
            price, mrp, discount_percent, quantity, main_image, gallery_images,
            tags, is_featured, status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')
    ");
    $stmt->execute([
        $name, $slug, $sku, $description, $shortDesc, $categoryId, $brand,
        $price, $mrp, $discount, $quantity, $mainImage, json_encode($gallery),
        $tags, $isFeatured
    ]);

    $productId = $pdo->lastInsertId();

    logActivity($adminId, 'admin_create_product', "Created product: $name", 'admin');

    success('Product created', ['product_id' => $productId, 'slug' => $slug]);

} catch (Exception $e) {
    error('Failed: ' . $e->getMessage(), 500);
}