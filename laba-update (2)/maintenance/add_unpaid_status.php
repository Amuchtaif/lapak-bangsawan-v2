<?php
require_once dirname(__DIR__) . "/config/init.php";

echo "Adding 'unpaid' to orders status enum...\n";

// Get current column definition might be complex, so we just run the ALTER command to redefine it
// We include all existing values plus 'unpaid'
$sql = "ALTER TABLE orders MODIFY COLUMN status ENUM('pending','completed','cancelled','unpaid') DEFAULT 'pending'";

if ($conn->query($sql)) {
    echo "Success: 'unpaid' status added to orders table.\n";
} else {
    echo "Error: " . $conn->error . "\n";
}
?>