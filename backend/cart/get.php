<?php
/**
 * Get Cart Items
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/session.php';

requireLogin();
$myId = getCurrentUserId();

try {
    $pdo = db();

    $stmt = $pdo->prepare("
        SELECT 
            c.id, c.quantity,
            p.id as product_id, p.name, p.slug, p.price, p.mrp, p.discount_percent,
            p.main_image, p.quantity as stock, p.status as product_status,
            u.username as seller_name
        FROM carts c
        JOIN products p ON p.id = c.product_id
        LEFT JOIN users u ON u.id = (SELECT user_id FROM products WHERE id = p.id LIMIT 1)
        WHERE c.user_id = ?
        ORDER BY c.added_at DESC
    ");
    $stmt->execute([$myId]);
    $items = $stmt->fetchAll();

    $subtotal = 0;
    $totalItems = 0;

    foreach ($items as &$item) {
        $item['price'] = (float)$item['price'];
        $item['mrp'] = (float)$item['mrp'];
        $item['discount_percent'] = (float)$item['discount_percent'];
        $item['quantity'] = (int)$item['quantity'];
        $item['stock'] = (int)$item['stock'];
        $item['line_total'] = $item['price'] * $item['quantity'];
        $item['in_stock'] = $item['stock'] >= $item['quantity'] && $item['product_status'] === 'active';
        $subtotal += $item['line_total'];
        $totalItems += $item['quantity'];
    }

    // Calculate totals
    $discount = 0;
    $shipping = $subtotal > 499 ? 0 : 40;
    $tax = round($subtotal * 0.05, 2); // 5% GST
    $total = $subtotal + $shipping + $tax - $discount;

    success('Cart loaded', [
        'items'        => $items,
        'subtotal'     => $subtotal,
        'discount'     => $discount,
        'shipping'     => $shipping,
        'tax'          => $tax,
        'total'        => $total,
        'total_items'  => $totalItems
    ]);

} catch (Exception $e) {
    error('Failed: ' . $e->getMessage(), 500);
}