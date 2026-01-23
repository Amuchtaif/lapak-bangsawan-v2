<?php
require("../config/database.php");

// Add order_token column
$sql = "ALTER TABLE orders ADD COLUMN order_token VARCHAR(64) NULL";
if ($conn->query($sql) === TRUE) {
    echo "Column order_token added successfully\n";
} else {
    echo "Error adding column: " . $conn->error . "\n";
}

// Add UNIQUE constraint
$sql = "ALTER TABLE orders ADD CONSTRAINT unique_order_token UNIQUE (order_token)";
if ($conn->query($sql) === TRUE) {
    echo "Unique constraint added successfully\n";
} else {
    echo "Error adding constraint: " . $conn->error . "\n";
}

$conn->close();
?>