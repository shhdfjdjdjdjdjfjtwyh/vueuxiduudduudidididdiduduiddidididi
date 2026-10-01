-- ============================================================
-- SHOPVAULT DATABASE SCHEMA
-- MySQL 8.0+
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- ============================================================
-- USERS
-- ============================================================
CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) UNIQUE,
    `email` VARCHAR(100) UNIQUE,
    `phone` VARCHAR(15) UNIQUE,
    `password` VARCHAR(255),
    `full_name` VARCHAR(100),
    `avatar` VARCHAR(255),
    `google_id` VARCHAR(100),
    `balance` DECIMAL(15,2) DEFAULT 0.00,
    `total_spent` DECIMAL(15,2) DEFAULT 0.00,
    `total_deposit` DECIMAL(15,2) DEFAULT 0.00,
    `agent_id` INT DEFAULT NULL,
    `role` ENUM('user','agent','admin') DEFAULT 'user',
    `status` ENUM('active','banned','pending') DEFAULT 'active',
    `email_verified` TINYINT(1) DEFAULT 0,
    `phone_verified` TINYINT(1) DEFAULT 0,
    `last_login` DATETIME,
    `ip_address` VARCHAR(45),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_agent` (`agent_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- AGENTS
-- ============================================================
CREATE TABLE `agents` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNIQUE,
    `agent_code` VARCHAR(20) UNIQUE,
    `business_name` VARCHAR(100),
    `commission_rate` DECIMAL(5,2) DEFAULT 5.00,
    `wallet_balance` DECIMAL(15,2) DEFAULT 0.00,
    `total_earned` DECIMAL(15,2) DEFAULT 0.00,
    `total_users` INT DEFAULT 0,
    `can_create_users` TINYINT(1) DEFAULT 1,
    `can_recharge` TINYINT(1) DEFAULT 1,
    `can_manage_products` TINYINT(1) DEFAULT 0,
    `can_view_orders` TINYINT(1) DEFAULT 1,
    `parent_agent_id` INT DEFAULT NULL,
    `status` ENUM('active','suspended') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`parent_agent_id`) REFERENCES `agents`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- CATEGORIES
-- ============================================================
CREATE TABLE `categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(100) UNIQUE,
    `description` TEXT,
    `image` VARCHAR(255),
    `parent_id` INT DEFAULT NULL,
    `sort_order` INT DEFAULT 0,
    `status` ENUM('active','inactive') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`parent_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- PRODUCTS
-- ============================================================
CREATE TABLE `products` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(200) NOT NULL,
    `slug` VARCHAR(200) UNIQUE,
    `sku` VARCHAR(50) UNIQUE,
    `description` LONGTEXT,
    `short_description` VARCHAR(500),
    `category_id` INT,
    `brand` VARCHAR(100),
    `price` DECIMAL(10,2) NOT NULL,
    `mrp` DECIMAL(10,2),
    `discount_percent` DECIMAL(5,2) DEFAULT 0,
    `quantity` INT DEFAULT 0,
    `min_order_qty` INT DEFAULT 1,
    `main_image` VARCHAR(255),
    `gallery_images` JSON,
    `attributes` JSON,
    `tags` VARCHAR(500),
    `is_featured` TINYINT(1) DEFAULT 0,
    `is_new` TINYINT(1) DEFAULT 1,
    `agent_id` INT DEFAULT NULL,
    `status` ENUM('active','inactive','out_of_stock') DEFAULT 'active',
    `views` INT DEFAULT 0,
    `sold` INT DEFAULT 0,
    `rating` DECIMAL(3,2) DEFAULT 0.00,
    `review_count` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`agent_id`) REFERENCES `agents`(`id`) ON DELETE SET NULL,
    INDEX `idx_category` (`category_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_featured` (`is_featured`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- CARTS
-- ============================================================
CREATE TABLE `carts` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `product_id` INT NOT NULL,
    `quantity` INT DEFAULT 1,
    `added_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_cart` (`user_id`, `product_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- ORDERS
-- ============================================================
CREATE TABLE `orders` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `order_number` VARCHAR(50) UNIQUE,
    `user_id` INT NOT NULL,
    `agent_id` INT DEFAULT NULL,
    `total_items` INT DEFAULT 0,
    `subtotal` DECIMAL(15,2) NOT NULL,
    `discount` DECIMAL(15,2) DEFAULT 0,
    `shipping` DECIMAL(15,2) DEFAULT 0,
    `tax` DECIMAL(15,2) DEFAULT 0,
    `total_amount` DECIMAL(15,2) NOT NULL,
    `payment_method` ENUM('razorpay','stripe','upi','wallet','cod') DEFAULT 'razorpay',
    `payment_status` ENUM('pending','paid','failed','refunded') DEFAULT 'pending',
    `payment_id` VARCHAR(100),
    `transaction_id` VARCHAR(100),
    `shipping_name` VARCHAR(100),
    `shipping_phone` VARCHAR(15),
    `shipping_email` VARCHAR(100),
    `shipping_address` TEXT,
    `shipping_city` VARCHAR(100),
    `shipping_state` VARCHAR(100),
    `shipping_pincode` VARCHAR(10),
    `status` ENUM('pending','processing','shipped','delivered','cancelled','returned') DEFAULT 'pending',
    `paid_at` DATETIME,
    `processed_at` DATETIME,
    `shipped_at` DATETIME,
    `delivered_at` DATETIME,
    `cancelled_at` DATETIME,
    `tracking_number` VARCHAR(100),
    `courier_name` VARCHAR(100),
    `notes` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`),
    FOREIGN KEY (`agent_id`) REFERENCES `agents`(`id`) ON DELETE SET NULL,
    INDEX `idx_user` (`user_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_payment` (`payment_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- ORDER ITEMS
-- ============================================================
CREATE TABLE `order_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT NOT NULL,
    `product_id` INT NOT NULL,
    `product_name` VARCHAR(200),
    `product_image` VARCHAR(255),
    `quantity` INT NOT NULL,
    `price` DECIMAL(10,2) NOT NULL,
    `total` DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TRANSACTIONS
-- ============================================================
CREATE TABLE `transactions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT,
    `agent_id` INT,
    `order_id` INT,
    `type` ENUM('deposit','withdrawal','purchase','refund','commission','admin_credit','admin_debit') NOT NULL,
    `amount` DECIMAL(15,2) NOT NULL,
    `balance_before` DECIMAL(15,2),
    `balance_after` DECIMAL(15,2),
    `payment_method` VARCHAR(50),
    `payment_id` VARCHAR(100),
    `reference_id` VARCHAR(100),
    `description` VARCHAR(500),
    `status` ENUM('pending','success','failed') DEFAULT 'success',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`agent_id`) REFERENCES `agents`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE SET NULL,
    INDEX `idx_user` (`user_id`),
    INDEX `idx_type` (`type`),
    INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- SUPPORT TICKETS
-- ============================================================
CREATE TABLE `support_tickets` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `ticket_number` VARCHAR(50) UNIQUE,
    `user_id` INT NOT NULL,
    `subject` VARCHAR(200),
    `priority` ENUM('low','medium','high','urgent') DEFAULT 'medium',
    `status` ENUM('open','in_progress','resolved','closed') DEFAULT 'open',
    `assigned_to` INT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `ticket_messages` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `ticket_id` INT NOT NULL,
    `sender_id` INT NOT NULL,
    `sender_type` ENUM('user','admin','agent') NOT NULL,
    `message` TEXT,
    `attachment` VARCHAR(255),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`ticket_id`) REFERENCES `support_tickets`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- NOTIFICATIONS
-- ============================================================
CREATE TABLE `notifications` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `type` VARCHAR(50),
    `title` VARCHAR(200),
    `message` TEXT,
    `link` VARCHAR(500),
    `is_read` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_user_read` (`user_id`, `is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- PRODUCT REVIEWS
-- ============================================================
CREATE TABLE `product_reviews` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `product_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `order_id` INT,
    `rating` INT CHECK (`rating` BETWEEN 1 AND 5),
    `title` VARCHAR(200),
    `review` TEXT,
    `images` JSON,
    `is_verified_purchase` TINYINT(1) DEFAULT 0,
    `status` ENUM('pending','approved','rejected') DEFAULT 'pending',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- SETTINGS
-- ============================================================
CREATE TABLE `settings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `key_name` VARCHAR(100) UNIQUE,
    `value` TEXT,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `settings` (`key_name`, `value`) VALUES 
('site_name', 'ShopVault'),
('site_email', 'support@shopvault.in'),
('site_phone', '+91 XXXXXXXXXX'),
('site_currency', 'INR'),
('site_logo', 'logo.png'),
('min_deposit', '100'),
('max_deposit', '100000'),
('min_withdrawal', '500'),
('default_commission', '5'),
('razorpay_key', ''),
('razorpay_secret', ''),
('stripe_key', ''),
('stripe_secret', ''),
('smtp_host', ''),
('smtp_user', ''),
('smtp_pass', ''),
('google_client_id', ''),
('google_client_secret', ''),
('maintenance_mode', '0');

-- ============================================================
-- ACTIVITY LOGS
-- ============================================================
CREATE TABLE `activity_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT,
    `user_type` ENUM('user','agent','admin') DEFAULT 'user',
    `action` VARCHAR(100),
    `details` TEXT,
    `ip_address` VARCHAR(45),
    `user_agent` VARCHAR(500),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_user` (`user_id`),
    INDEX `idx_action` (`action`),
    INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- DEFAULT ADMIN
-- Password: admin123
-- ============================================================
INSERT INTO `users` (`username`, `email`, `password`, `full_name`, `role`, `status`, `email_verified`) 
VALUES ('admin', 'admin@shopvault.in', 
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'Super Admin', 'admin', 'active', 1);