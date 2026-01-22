<?php
require("db_connect.php");

// Check if column exists
$check = $conn->query("SHOW COLUMNS FROM orders LIKE 'order_notes'");
if ($check->num_rows == 0) {
    // Add column
    $sql = "ALTER TABLE orders ADD COLUMN order_notes TEXT NULL AFTER customer_address";
    if ($conn->query($sql)) {
        echo "Column 'order_notes' added successfully.";
    } else {
        echo "Error adding column: " . $conn->error;
    }
} else {
    echo "Column 'order_notes' already exists.";
}
?>