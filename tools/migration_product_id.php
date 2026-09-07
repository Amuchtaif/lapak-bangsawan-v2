<?php
require_once dirname(__DIR__) . "/config/init.php";

echo "--- STARTING DATABASE MIGRATION: ADDING product_id TO order_items ---\n";

// 1. Check if column exists
$check_col = $conn->query("SHOW COLUMNS FROM order_items LIKE 'product_id'");
if ($check_col->num_rows == 0) {
    echo "Adding column 'product_id' to 'order_items'...\n";
    $alter = $conn->query("ALTER TABLE order_items ADD COLUMN product_id INT NULL AFTER order_id");
    if ($alter) {
        echo "Column 'product_id' successfully added.\n";
    } else {
        die("ERROR adding column: " . $conn->error . "\n");
    }
} else {
    echo "Column 'product_id' already exists.\n";
}

// 2. Migrate existing records
echo "Migrating existing order_items to match products...\n";
$update_sql = "
    UPDATE order_items oi
    SET oi.product_id = (
        SELECT p.id 
        FROM products p 
        WHERE TRIM(p.name) = TRIM(oi.product_name)
        ORDER BY p.status = 'active' DESC, p.id ASC
        LIMIT 1
    )
    WHERE oi.product_id IS NULL
";

if ($conn->query($update_sql)) {
    $affected = $conn->affected_rows;
    echo "Migration complete! Updated $affected rows in order_items.\n";
} else {
    echo "ERROR updating existing rows: " . $conn->error . "\n";
}

// 3. Verify migration
$unmatched = $conn->query("SELECT DISTINCT product_name FROM order_items WHERE product_id IS NULL");
if ($unmatched->num_rows > 0) {
    echo "WARNING: The following product names could not be matched to any product in the database (they will have NULL product_id):\n";
    while ($row = $unmatched->fetch_assoc()) {
        echo "- '{$row['product_name']}'\n";
    }
} else {
    echo "SUCCESS: All order items have been successfully linked to a product ID!\n";
}
