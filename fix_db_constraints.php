<?php
require 'db_connect.php';

function executeQuery($conn, $sql, $desc)
{
    echo "Executing: $desc ... ";
    if ($conn->query($sql) === TRUE) {
        echo "OK\n";
    } else {
        echo "Error: " . $conn->error . "\n";
    }
}

// 1. Cleanup orphans in order_items (if any exist pointing to non-existent orders)
$cleanup_items = "DELETE FROM order_items WHERE order_id NOT IN (SELECT id FROM orders)";
executeQuery($conn, $cleanup_items, "Cleanup orphan order_items");

// 2. Cleanup orphans in orders (if any exist pointing to non-existent customers)
$cleanup_orders = "DELETE FROM orders WHERE customer_id NOT IN (SELECT id FROM customers)";
executeQuery($conn, $cleanup_orders, "Cleanup orphan orders");

// 3. Drop existing FKs if possible (We guess names or check info schema dynamically, but for now let's try adding first. If add fails, we might need to drop. 
// A safer way is to try to ADD, if it fails, it might exist.
// Let's try to DROP 'fk_orders_customer' just in case we created it successfully before.
$conn->query("ALTER TABLE orders DROP FOREIGN KEY fk_orders_customer");
$conn->query("ALTER TABLE order_items DROP FOREIGN KEY fk_order_items_order");

// 4. Add Constraints
$add_fk_orders = "ALTER TABLE orders ADD CONSTRAINT fk_orders_customer FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE";
executeQuery($conn, $add_fk_orders, "Add FK CASCADE to orders");

$add_fk_items = "ALTER TABLE order_items ADD CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE";
executeQuery($conn, $add_fk_items, "Add FK CASCADE to order_items");

echo "Database constraints updated.\n";
?>