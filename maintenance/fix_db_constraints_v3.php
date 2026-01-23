<?php
require '../config/database.php';

// Enable exception reporting
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

echo "Starting DB Fix V3...\n";

try {
    // 1. Cleanup orphan order_items
    $sql = "DELETE FROM order_items WHERE order_id NOT IN (SELECT id FROM orders)";
    $conn->query($sql);
    echo "Cleaned orphan order_items.\n";
} catch (Exception $e) {
    echo "Error cleaning order_items: " . $e->getMessage() . "\n";
}

try {
    // 2. Cleanup orphan orders
    // Use column check implicitly by running it. If customer_id doesn't exist, it will throw.
    $sql = "DELETE FROM orders WHERE customer_id NOT IN (SELECT id FROM customers)";
    $conn->query($sql);
    echo "Cleaned orphan orders.\n";
} catch (Exception $e) {
    echo "Error cleaning orders: " . $e->getMessage() . "\n";
}

try {
    // 3. Drop possibly existing FKs
    // Loop through known names or just try
    $names = ['fk_orders_customer', 'orders_ibfk_1'];
    foreach ($names as $name) {
        try {
            $conn->query("ALTER TABLE orders DROP FOREIGN KEY $name");
            echo "Dropped FK $name from orders.\n";
        } catch (Exception $e) {
            // Ignore drop errors (doesn't exist)
        }
    }

    $names_items = ['fk_order_items_order', 'order_items_ibfk_1'];
    foreach ($names_items as $name) {
        try {
            $conn->query("ALTER TABLE order_items DROP FOREIGN KEY $name");
            echo "Dropped FK $name from order_items.\n";
        } catch (Exception $e) {
            // Ignore
        }
    }

    // 4. Add Constraints
    $sql = "ALTER TABLE orders ADD CONSTRAINT fk_orders_customer FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE";
    $conn->query($sql);
    echo "Added FK CASCADE to orders.\n";

    $sql = "ALTER TABLE order_items ADD CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE";
    $conn->query($sql);
    echo "Added FK CASCADE to order_items.\n";

} catch (Exception $e) {
    echo "Error modifying constraints: " . $e->getMessage() . "\n";
}

echo "Done.\n";
?>