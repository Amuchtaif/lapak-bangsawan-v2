<?php
require("../config/database.php");

echo "Checking 'orders' table structure...\n";
$result = $conn->query("SHOW COLUMNS FROM orders LIKE 'order_notes'");
if ($result->num_rows > 0) {
    echo "[PASS] Column 'order_notes' exists.\n";
} else {
    echo "[FAIL] Column 'order_notes' missing.\n";
    exit;
}

// Test Insert Query Syntax (Rollback immediately)
$conn->begin_transaction();
try {
    $test_id = 999999; // usage of high ID to avoid collision
    $sql = "INSERT INTO orders (id, customer_name, customer_phone, customer_address, total_amount, status, payment_method, order_notes) 
            VALUES ($test_id, 'Test User', '08123456789', 'Test Address', 10000, 'pending', 'cod', 'This is a test note')";

    if ($conn->query($sql)) {
        echo "[PASS] Test INSERT with 'order_notes' succeeded.\n";
    } else {
        throw new Exception($conn->error);
    }

    // Verify Update
    $verify_q = $conn->query("SELECT order_notes FROM orders WHERE id=$test_id");
    $row = $verify_q->fetch_assoc();
    if ($row['order_notes'] === 'This is a test note') {
        echo "[PASS] Data verification succeeded: " . $row['order_notes'] . "\n";
    } else {
        echo "[FAIL] Data mismatch.\n";
    }

} catch (Exception $e) {
    echo "[FAIL] Insert failed: " . $e->getMessage() . "\n";
} finally {
    $conn->rollback(); // Always rollback test data
    echo "Test transaction rolled back.\n";
}
?>