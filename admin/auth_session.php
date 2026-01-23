<?php
if (!defined('BASE_URL')) {
    // Robust detection for BASE_URL
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'];
    $base_dir = str_replace('\\', '/', dirname(__DIR__));
    $doc_root = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
    $base_path = str_ireplace($doc_root, '', $base_dir);
    $final_base = $protocol . $host . '/' . ltrim($base_path, '/') . '/';
    define('BASE_URL', preg_replace('/([^:])(\/{2,})/', '$1/', $final_base));
}

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "admin/login");
    exit();
}

// Session Timeout (30 minutes)
$timeout_duration = 1800; // 30 minutes in seconds

if (isset($_SESSION['last_activity'])) {
    if ((time() - $_SESSION['last_activity']) > $timeout_duration) {
        // Session expired
        session_unset();
        session_destroy();
        header("Location: " . BASE_URL . "admin/login?timeout=1");
        exit();
    }
}
$_SESSION['last_activity'] = time(); // Update last activity time
?>