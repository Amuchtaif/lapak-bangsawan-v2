<?php
require_once 'config/database.php';

$sql = "CREATE TABLE IF NOT EXISTS settings_pengiriman (
    id INT AUTO_INCREMENT PRIMARY KEY,
    max_distance_local DECIMAL(10,2) DEFAULT 10.00,
    price_per_km_local DECIMAL(15,2) DEFAULT 1000.00
)";

if ($conn->query($sql) === TRUE) {
    echo "Table created successfully\n";
    
    // Check if empty
    $check = $conn->query("SELECT COUNT(*) as count FROM settings_pengiriman");
    $row = $check->fetch_assoc();
    if ($row['count'] == 0) {
        $conn->query("INSERT INTO settings_pengiriman (max_distance_local, price_per_km_local) VALUES (10, 1000)");
        echo "Default row inserted\n";
    }
} else {
    echo "Error: " . $conn->error . "\n";
}
?>
