<?php
require '../config/database.php';
$res = $conn->query("SHOW COLUMNS FROM orders LIKE 'customer_id'");
if ($res && $row = $res->fetch_assoc()) {
    print_r($row);
} else {
    echo "Column not found or error: " . $conn->error;
}
?>