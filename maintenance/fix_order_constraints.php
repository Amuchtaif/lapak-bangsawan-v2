<?php
require '../config/database.php';

// Enable exception reporting
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

echo "Starting DB Fix (SET NULL)...\n";

try {
    // 1. Modify customer_id to be NULLABLE
    $conn->query("ALTER TABLE orders MODIFY customer_id INT NULL");
    echo "Modified customer_id to allow NULL.\n";

    // 2. Drop existing FKs
    $names = ['fk_orders_customer', 'orders_ibfk_1'];
    foreach ($names as $name) {
        try {
            $conn->query("ALTER TABLE orders DROP FOREIGN KEY $name");
            echo "Dropped FK $name from orders.\n";
        } catch (Exception $e) {
            // Ignore if doesn't exist
        }
    }

    // 3. Add Constraints with SET NULL
    $sql = "ALTER TABLE orders ADD CONSTRAINT fk_orders_customer FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL";
    $conn->query($sql);
    echo "Added FK SET NULL to orders.\n";

} catch (Exception $e) {
    echo "Error modifying constraints: " . $e->getMessage() . "\n";
}

echo "Done.\n";
?>