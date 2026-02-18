<?php
require_once dirname(__DIR__) . '/config/init.php';

echo "<h2>Adding payment columns to orders table...</h2>";

// Add payment_proof column
try {
    $sql = "ALTER TABLE orders ADD COLUMN payment_proof VARCHAR(255) DEFAULT NULL AFTER payment_method";
    if ($conn->query($sql)) {
        echo "Successfully added 'payment_proof' column.<br>";
    } else {
        echo "Error adding 'payment_proof' (might already exist): " . $conn->error . "<br>";
    }
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "<br>";
}

// Add payment_status column
try {
    $sql = "ALTER TABLE orders ADD COLUMN payment_status ENUM('unpaid', 'waiting_verification', 'paid') DEFAULT 'unpaid' AFTER payment_proof";
    if ($conn->query($sql)) {
        echo "Successfully added 'payment_status' column.<br>";
    } else {
        echo "Error adding 'payment_status' (might already exist): " . $conn->error . "<br>";
    }
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "<br>";
}

echo "<h3>Done.</h3>";
?>