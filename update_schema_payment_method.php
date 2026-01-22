<?php
require("db_connect.php");

// Check if 'payment_method' column exists in 'orders' table
$check_col = $conn->query("SHOW COLUMNS FROM orders LIKE 'payment_method'");

if ($check_col->num_rows == 0) {
    // Add the column
    $sql = "ALTER TABLE orders ADD COLUMN payment_method VARCHAR(20) DEFAULT 'transfer' AFTER status";
    if ($conn->query($sql)) {
        echo "Successfully added 'payment_method' column to 'orders' table.<br>";
    } else {
        echo "Error adding column: " . $conn->error . "<br>";
    }
} else {
    echo "'payment_method' column already exists.<br>";
}

// Verify
$result = $conn->query("SHOW COLUMNS FROM orders");
echo "<pre>";
while ($row = $result->fetch_assoc()) {
    print_r($row);
}
echo "</pre>";

$conn->close();
?>