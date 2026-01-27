<?php
// config/init.php

// 1. Define Root Path (Folder System)
define('ROOT_PATH', dirname(__DIR__) . '/');

// 2. Define Base URL (Web Address) - Auto Detect
if (!defined('BASE_URL')) {
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'];
    $base_dir = str_replace('\\', '/', dirname(__DIR__));
    $doc_root = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
    $base_path = str_ireplace($doc_root, '', $base_dir);
    $final_base = $protocol . $host . '/' . ltrim($base_path, '/') . '/';
    define('BASE_URL', preg_replace('/([^:])(\/{2,})/', '$1/', $final_base));
}

// 3. Start Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 4. Database Connection
require_once ROOT_PATH . 'config/database.php';
require_once ROOT_PATH . 'helpers/AppHelper.php'; // Load Settings Helper
require_once ROOT_PATH . 'includes/cart_helper.php';
// Note: database.php creates $conn. It is now available globally if we include init.php.

// 5. Error Reporting (Optional but good for dev)
error_reporting(E_ALL);
ini_set('display_errors', 1);

?>