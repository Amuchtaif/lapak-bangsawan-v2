<?php
require_once dirname(__DIR__) . "/config/init.php";

$sql = "ALTER TABLE orders MODIFY COLUMN status ENUM('pending', 'confirmed', 'ready_to_ship', 'shipped', 'delivered', 'completed', 'cancelled') DEFAULT 'pending'";

if ($conn->query($sql) === TRUE) {
    echo "Order status enum updated successfully";
} else {
    echo "Error updating order status enum: " . $conn->error;
}
?>