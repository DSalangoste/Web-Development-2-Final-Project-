<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'serverside');
define('DB_USER', 'root');
define('DB_PASS', '');

// Path Configuration
define('PROJECT_ROOT', __DIR__);
define('UPLOAD_DIR', PROJECT_ROOT . '/uploads/');
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024);  // 5MB

// URL Configuration
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];
$script_path = dirname($_SERVER['SCRIPT_NAME']);
define('BASE_URL', $protocol . $host . str_replace(' ', '%20', $script_path));

// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', PROJECT_ROOT . '/debug.log');

// Required Extensions
$required_extensions = ['pdo', 'pdo_mysql', 'gd'];
foreach ($required_extensions as $ext) {
    if (!extension_loaded($ext)) {
        error_log("Required PHP extension not loaded: " . $ext);
    }
}
?>
