<?php
/**
 * Payment Gateway Configuration
 */

// Razorpay
define('RAZORPAY_KEY_ID', 'rzp_test_XXXXXXXXXXXX');
define('RAZORPAY_KEY_SECRET', 'XXXXXXXXXXXXXXXXXXXX');
define('RAZORPAY_WEBHOOK_SECRET', 'webhook_secret_here');

// Stripe
define('STRIPE_KEY', 'pk_test_XXXXXXXXXXXX');
define('STRIPE_SECRET', 'sk_test_XXXXXXXXXXXX');

// UPI (direct)
define('UPI_ID', 'shopvault@upi');
define('UPI_NAME', 'ShopVault');

// Wallet
define('MIN_WALLET_DEPOSIT', 100);
define('MAX_WALLET_DEPOSIT', 100000);
define('MIN_WALLET_WITHDRAW', 500);
define('WALLET_WITHDRAW_FEE_PERCENT', 2); // 2% fee