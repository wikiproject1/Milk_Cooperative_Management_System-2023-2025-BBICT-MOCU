<?php
// Database configuration
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'milk_cooperative');

// Initialize database connection
$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set charset to utf8
mysqli_set_charset($conn, "utf8");

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Time zone
date_default_timezone_set('Africa/Dar_es_Salaam');

// Site configuration
define('SITE_NAME', 'Milk Cooperative System');
define('SITE_URL', 'http://localhost/MCS');
define('ADMIN_EMAIL', 'admin@milkcooperative.com');

// Payment configuration
define('MIN_PAYMENT_AMOUNT', 1000);
define('MAX_PAYMENT_AMOUNT', 1000000);
define('DEFAULT_CURRENCY', 'TZS'); 