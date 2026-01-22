<?php
require "db_connect.php";

$sql = "ALTER TABLE products MODIFY stock DECIMAL(10,2) NOT NULL DEFAULT 0.00";

if ($conn->query($sql) === TRUE) {
    echo "Table products updated successfully (stock column changed to DECIMAL).";
} else {
    echo "Error updating table: " . $conn->error;
}

$conn->close();
?>