<?php
require_once __DIR__ . "/../config/init.php";

echo "Starting migration...\n";

// 1. Add weight column if not exists
$check_column = $conn->query("SHOW COLUMNS FROM products LIKE 'weight'");
if ($check_column->num_rows == 0) {
    if ($conn->query("ALTER TABLE products ADD COLUMN weight INT DEFAULT 1000")) {
        echo "Column 'weight' added to 'products' table.\n";
    } else {
        echo "Error adding column: " . $conn->error . "\n";
    }
} else {
    echo "Column 'weight' already exists.\n";
}

// 2. Update existing rows where weight is 0 or NULL
if ($conn->query("UPDATE products SET weight = 1000 WHERE weight IS NULL OR weight = 0")) {
    echo "Cleaned up existing product weights.\n";
} else {
    echo "Error cleaning up weights: " . $conn->error . "\n";
}

echo "Migration finished.\n";
