<?php
require_once 'db_connect.php';

$today = date('Y-m-d');

// Get all products
$products = $conn->query("SELECT id FROM products");

if ($products) {
    echo "Seeding targets for $today...\n";
    $stmt = $conn->prepare("INSERT INTO daily_sales_targets (product_id, target_date, target_qty_kg) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE target_qty_kg = VALUES(target_qty_kg)");
    
    while ($row = $products->fetch_assoc()) {
        $product_id = $row['id'];
        // Random target between 10 and 100
        $target = rand(10, 100); 
        $stmt->bind_param("isd", $product_id, $today, $target);
        if (!$stmt->execute()) {
            echo "Error for product $product_id: " . $stmt->error . "\n";
        }
    }
    echo "Seeding completed.\n";
} else {
    echo "No products found.\n";
}

$conn->close();
?>
