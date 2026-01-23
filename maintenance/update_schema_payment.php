<?php
require "../config/database.php";

$sql = "ALTER TABLE orders ADD COLUMN payment_method VARCHAR(50) DEFAULT NULL AFTER total_amount";

if ($conn->query($sql) === TRUE) {
    echo "Table orders updated successfully (payment_method column added).";
} else {
    echo "Error updating table: " . $conn->error;
}

$conn->close();
?>