<?php
require_once dirname(__DIR__) . "/config/init.php";

$sql = "ALTER TABLE orders ADD COLUMN biteship_order_id VARCHAR(50) NULL AFTER order_token";

if ($conn->query("SHOW COLUMNS FROM `orders` LIKE 'biteship_order_id'")->num_rows == 0) {
    if ($conn->query($sql) === TRUE) {
        echo "Column biteship_order_id added successfully";
    } else {
        echo "Error adding column: " . $conn->error;
    }
} else {
    echo "Column biteship_order_id already exists";
}
?>