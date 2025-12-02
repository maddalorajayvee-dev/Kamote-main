<?php
// Application configuration
session_start();

// Base URL
define('BASE_URL', 'http://localhost/kamote');

// Security
define('JWT_SECRET', 'your-secret-key-here-change-in-production');

// Timezone
date_default_timezone_set('Asia/Manila');

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/email.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/email.php';
?>

