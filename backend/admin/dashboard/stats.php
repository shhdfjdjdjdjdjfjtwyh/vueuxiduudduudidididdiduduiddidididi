<?php
/**
 * Admin Dashboard Stats
 */

require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/session.php';

requireAdmin();

try {
    $pdo = db();

    // Total counts
    $stats = [];

    $stats['total_users'] = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();
    $stats['total_agents'] = (int)$pdo->query("SELECT COUNT(*) FROM agents")->fetchColumn();
    $stats['total_products'] = (int)$pdo->query("SELECT COUNT(*) FROM products WHERE status = 'active'")->fetchColumn();
    $stats['total_orders'] = (int)$pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();

    // Revenue
    $stats['total_revenue'] = (float)$pdo->query("
        SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE payment_status = 'paid'
    ")->fetchColumn();

    $stats['today_revenue'] = (float)$pdo->query("
        SELECT COALESCE(SUM(total_amount), 0) FROM orders 
        WHERE payment_status = 'paid' AND DATE(created_at) = CURDATE()
    ")->fetchColumn();

    // Today's orders
    $stats['today_orders'] = (int)$pdo->query("
        SELECT COUNT(*) FROM orders WHERE DATE(created_at) = CURDATE()
    ")->fetchColumn();

    // Pending orders
    $stats['pending_orders'] = (int)$pdo->query("
        SELECT COUNT(*) FROM orders WHERE status = 'pending'
    ")->fetchColumn();

    // Recent orders
    $recentOrders = $pdo->query("
        SELECT o.id, o.order_number, o.total_amount, o.status, o.payment_status, o.created_at,
               u.username, u.full_name
        FROM orders o
        JOIN users u ON u.id = o.user_id
        ORDER BY o.created_at DESC
        LIMIT 10
    ")->fetchAll();

    // Top products
    $topProducts = $pdo->query("
        SELECT id, name, main_image, price, sold, rating
        FROM products
        WHERE status = 'active'
        ORDER BY sold DESC
        LIMIT 5
    ")->fetchAll();

    // Recent users
    $recentUsers = $pdo->query("
        SELECT id, username, email, full_name, avatar, created_at
        FROM users
        WHERE role = 'user'
        ORDER BY created_at DESC
        LIMIT 5
    ")->fetchAll();

    // Sales chart (last 7 days)
    $salesChart = $pdo->query("
        SELECT DATE(created_at) as date, 
               COUNT(*) as orders,
               COALESCE(SUM(total_amount), 0) as revenue
        FROM orders
        WHERE payment_status = 'paid' 
          AND created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
        GROUP BY DATE(created_at)
        ORDER BY date ASC
    ")->fetchAll();

    // Status breakdown
    $orderStatus = $pdo->query("
        SELECT status, COUNT(*) as count
        FROM orders
        GROUP BY status
    ")->fetchAll();

    success('Dashboard stats', [
        'stats'         => $stats,
        'recent_orders' => $recentOrders,
        'top_products'  => $topProducts,
        'recent_users'  => $recentUsers,
        'sales_chart'   => $salesChart,
        'order_status'  => $orderStatus
    ]);

} catch (Exception $e) {
    error('Failed: ' . $e->getMessage(), 500);
}