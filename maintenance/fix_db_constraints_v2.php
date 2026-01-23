<?php
require '../config/database.php';

echo "Starting DB Fix...\n";

// 1. Cleanup orphan order_items
$sql = "DELETE FROM order_items WHERE order_id NOT IN (SELECT id FROM orders)";
if ($conn->query($sql) === TRUE) {
    echo "Cleaned orphan order_items.\n";
} else {
    echo "Error cleaning order_items: " . $conn->error . "\n";
}

// 2. Cleanup orphan orders
$sql = "DELETE FROM orders WHERE customer_id NOT IN (SELECT id FROM customers)";
if ($conn->query($sql) === TRUE) {
    echo "Cleaned orphan orders.\n";
} else {
    echo "Error cleaning orders: " . $conn->error . "\n";
}

// 3. Drop possibly existing FKs (Try-Catch style via query check or just ignore error)
// We try to drop; if it fails, it likely didn't exist.
$conn->query("ALTER TABLE orders DROP FOREIGN KEY fk_orders_customer");
$conn->query("ALTER TABLE order_items DROP FOREIGN KEY fk_order_items_order");
// Also try dropping default names if they exist (hard to guess, but we'll stick to our plan)

// 4. Add Constraints
$sql = "ALTER TABLE orders ADD CONSTRAINT fk_orders_customer FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE";
if ($conn->query($sql) === TRUE) {
    echo "Added FK CASCADE to orders.\n";
} else {
    echo "Error adding FK to orders: " . $conn->error . "\n";
}

$sql = "ALTER TABLE order_items ADD CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE";
if ($conn->query($sql) === TRUE) {
    echo "Added FK CASCADE to order_items.\n";
} else {
    echo "Error adding FK to order_items: " . $conn->error . "\n";
}

echo "Done.\n";
?>