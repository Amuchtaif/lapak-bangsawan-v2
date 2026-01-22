<?php
require 'db_connect.php';
$db = 'lapak_bangsawan_v2';
$sql = "SELECT CONSTRAINT_NAME 
        FROM information_schema.KEY_COLUMN_USAGE 
        WHERE TABLE_NAME = 'orders' 
        AND COLUMN_NAME = 'customer_id' 
        AND REFERENCED_TABLE_NAME = 'customers'";
$res = $conn->query($sql);
if ($res && $row = $res->fetch_assoc()) {
    echo "CONSTRAINT_NAME: " . $row['CONSTRAINT_NAME'];
} else {
    echo "NO_CONSTRAINT_FOUND";
}
?>