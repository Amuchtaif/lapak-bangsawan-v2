<?php
session_start();
require_once dirname(__DIR__) . '/config/init.php';
log_activity("LOGOUT", "Admin telah keluar dari sistem.");
session_unset();
session_destroy();
header("Location: login");
exit();
?>