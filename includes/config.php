<?php
session_start();

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'food_ordering_system');
define('DB_USER', 'root');
define('DB_PASS', '');

// Base URL
define('BASE_URL', 'http://localhost/food-ordering-system');

// Application settings
define('SITE_NAME', 'FoodExpress');
define('ADMIN_EMAIL', 'admin@foodexpress.com');
define('ORDER_NOTIFICATION_EMAIL', 'orders@foodexpress.com');

// Twilio configuration (for SMS)
define('TWILIO_SID', 'your_twilio_sid');
define('TWILIO_TOKEN', 'your_twilio_token');
define('TWILIO_NUMBER', '+1234567890');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Staff registration invite code
define('INVITE_CODE', 'YOUR_SECRET_CODE_HERE'); // Change this to a strong code

// Existing config
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// New additions for error tracking
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');
