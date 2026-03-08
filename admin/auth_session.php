<?php
require_once dirname(__DIR__) . "/config/init.php";
// Session is started inside init.php

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "admin/login");
    exit();
}

// Session Timeout (8 hours)
$timeout_duration = 28800; // 8 hours in seconds

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