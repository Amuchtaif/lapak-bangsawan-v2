<?php
// config/init.php

// 1. Define Root Path (Folder System)
define('ROOT_PATH', dirname(__DIR__) . '/');

// 2. Define Base URL (Web Address) - Auto Detect
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
// Adjust based on your folder structure relative to document root. 
// If lapak-bangsawan is in the root, path is /. If in a subdir, it captures it.
$path = str_replace(basename($_SERVER['SCRIPT_NAME']), "", $_SERVER['SCRIPT_NAME']);
// We need to be careful with auto-detection if this file is included from a sub-folder.
// Better approach for development: hardcode or cleaner detection logic.
// Simpler robust detection for this structure (assuming accessed via http://localhost/lapak-bangsawan/)
// We'll normalize the path to the project root.
$script_dirname = dirname($_SERVER['SCRIPT_NAME']);
// To find the base url relevant to the project root, we can use a known relative path or manual config.
// Let's rely on a manual config or a smarter calculation if we want 'clean' URL.
// For now, let's hardcode for localhost or use a relative calc. 
// BUT, the user asked for auto-detect OR manual. 
// A robust auto-detect relies on knowing where init.php is relative to root.
// init.php is in /config. So project root is up one level.

// Let's use a simpler approach that usually works well:
// Force BASE_URL to be manually set if auto-detect is flaky, but here is a standard auto-detect:
$project_dir = basename(dirname(__DIR__)); // "lapak-bangsawan"
$base_url = $protocol . $host . '/' . $project_dir . '/';
define('BASE_URL', $base_url);

// 3. Start Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 4. Database Connection
require_once ROOT_PATH . 'config/database.php';
// Note: database.php creates $conn. It is now available globally if we include init.php.

// 5. Error Reporting (Optional but good for dev)
error_reporting(E_ALL);
ini_set('display_errors', 1);

?>
