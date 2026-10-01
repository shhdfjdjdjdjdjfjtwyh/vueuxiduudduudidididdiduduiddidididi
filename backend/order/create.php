<?php
/**
 * Create Order (from cart)
 * POST: address_id, payment_method, notes
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/security.php';
require_once __DIR__ . '/../helpers/session.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') error('Method not allowed', 405);

$input = json_decode(file_get_contents('php://input'), true);
$myId = getCurrentUserId();

$addressId     = (int)($input['address_id'] ?? 0);
$paymentMethod = clean($input['payment_method'] ?? 'razorpay');
$notes         = clean($input['notes'] ?? '');

if (!$addressId) error('Please select delivery address');
if (!in_array($paymentMethod, ['razorpay', 'stripe', 'upi', 'wallet', 'cod'])) {
    error('Invalid payment method');
}

try {
    $pdo = db();
    $pdo->beginTransaction();

    // Get address
    $stmt = $pdo->prepare("SELECT * FROM addresses WHERE id = ? AND user_id = ?");
    $stmt->execute([$addressId, $myId]);
    $address = $stmt->fetch();

    if (!$address) {
        $pdo->rollBack();
        error('Address not found');
    }

    // Get cart items
    $stmt = $pdo->prepare("
        SELECT c.quantity, p.id as product_id, p.name, p.price, p.main_image, p.quantity as stock
        FROM carts c
        JOIN products p ON p.id = c.product_id
        WHERE c.user_id = ? AND p.status = 'active'
    ");
    $stmt->execute([$myId]);
    $cartItems = $stmt->fetchAll();

    if (empty($cartItems)) {
        $pdo->rollBack();
        error('Cart is empty');
    }

    // Verify stock and calculate totals
    $subtotal = 0;
    $totalItems = 0;

    foreach ($cartItems as $item) {
        if ($item['stock'] < $item['quantity']) {
            $pdo->rollBack();
            error("Product '{$item['name']}' is out of stock");
        }
        $subtotal += $item['price'] * $item['quantity'];
        $totalItems += $item['quantity'];
    }

    $shipping = $subtotal > 499 ? 0 : 40;
    $tax = round($subtotal * 0.05, 2);
    $total = $subtotal + $shipping + $tax;

    // Generate order number
    $orderNumber = 'SV' . date('Ymd') . rand(10000, 99999);

    // Insert order
    $stmt = $pdo->prepare("
        INSERT INTO orders (
            order_number, user_id, total_items, subtotal, shipping, tax, total_amount,
            payment_method, payment_status, status,
            shipping_name, shipping_phone, shipping_address, shipping_city, 
            shipping_state, shipping_pincode, notes
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'pending', ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $orderNumber, $myId, $totalItems, $subtotal, $shipping, $tax, $total,
        $paymentMethod,
        $address['full_name'], $address['phone'], $address['address'],
        $address['city'], $address['state'], $address['pincode'],
        $notes
    ]);

    $orderId = $pdo->lastInsertId();

    // Insert order items & reduce stock
    foreach ($cartItems as $item) {
        $lineTotal = $item['price'] * $item['quantity'];

        $pdo->prepare("
            INSERT INTO order_items (order_id, product_id, product_name, product_image, quantity, price, total)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ")->execute([
            $orderId, $item['product_id'], $item['name'], $item['main_image'],
            $item['quantity'], $item['price'], $lineTotal
        ]);

        // Reduce stock
        $pdo->prepare("UPDATE products SET quantity = quantity - ?, sold = sold + ? WHERE id = ?")
            ->execute([$item['quantity'], $item['quantity'], $item['product_id']]);
    }

    // Clear cart
    $pdo->prepare("DELETE FROM carts WHERE user_id = ?")->execute([$myId]);

    $pdo->commit();

    success('Order created', [
        'order_id'     => $orderId,
        'order_number' => $orderNumber,
        'total_amount' => $total
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error('Order failed: ' . $e->getMessage(), 500);
}