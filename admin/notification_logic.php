<?php
// Shared Logic for Notifications

// Ensure DB connection is available
if (!isset($conn)) {
    // Basic check, though usually included by parent file
    if (file_exists("../config/database.php")) {
        require_once("../config/database.php");
    } elseif (file_exists("db_connect.php")) {
        require_once("db_connect.php");
    }
}

// Low Stock Threshold
$low_stock_threshold = 5;

// 1. Count Empty Stock Products (stock = 0)
$empty_stock_query = "SELECT COUNT(*) as c FROM products WHERE stock = 0";
$empty_stock_result = mysqli_query($conn, $empty_stock_query);
$empty_stock_count = 0;
if ($empty_stock_result) {
    $row = mysqli_fetch_assoc($empty_stock_result);
    $empty_stock_count = $row['c'];
}

// 2. Count Low Stock Products (0 < stock <= threshold)
$low_stock_query = "SELECT COUNT(*) as c FROM products WHERE stock > 0 AND stock <= $low_stock_threshold";
$low_stock_result = mysqli_query($conn, $low_stock_query);
$low_stock_count = 0;
if ($low_stock_result) {
    $row = mysqli_fetch_assoc($low_stock_result);
    $low_stock_count = $row['c'];
}

// 3. Fetch Empty Stock Items for Popup (Limit 5)
$empty_stock_items_query = "SELECT id, name, stock, image FROM products WHERE stock = 0 ORDER BY name ASC LIMIT 5";
$empty_stock_items_result = mysqli_query($conn, $empty_stock_items_query);

// 4. Fetch Low Stock Items for Popup (Limit 5)
$low_stock_items_query = "SELECT id, name, stock, image FROM products WHERE stock > 0 AND stock <= $low_stock_threshold ORDER BY stock ASC LIMIT 5";
$low_stock_items_result = mysqli_query($conn, $low_stock_items_query);

// Count Unread Messages
$unread_msg_query = "SELECT COUNT(*) as c FROM messages WHERE is_read = 0";
$unread_msg_result = mysqli_query($conn, $unread_msg_query);
$unread_msg_count = 0;
if ($unread_msg_result) {
    $row = mysqli_fetch_assoc($unread_msg_result);
    $unread_msg_count = $row['c'];
}

// Fetch Latest Unread Messages for Popup (Limit 3)
$unread_messages_query = "SELECT id, name, message, created_at FROM messages WHERE is_read = 0 ORDER BY created_at DESC LIMIT 3";
$unread_messages_result = mysqli_query($conn, $unread_messages_query);
?>