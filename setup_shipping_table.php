<?php
require_once 'config/database.php';

$sql = "CREATE TABLE IF NOT EXISTS settings_pengiriman (
    id INT AUTO_INCREMENT PRIMARY KEY,
    max_distance_local DECIMAL(10,2) DEFAULT 10.00,
    price_per_km_local DECIMAL(15,2) DEFAULT 1000.00,
    free_distance_local DECIMAL(10,2) DEFAULT 1.00
)";

if ($conn->query($sql) === TRUE) {
    echo "Table created successfully\n";
    
    // Check if free_distance_local exists, add if not (for existing tables)
    $res = $conn->query("SHOW COLUMNS FROM settings_pengiriman LIKE 'free_distance_local'");
    if ($res->num_rows == 0) {
        $conn->query("ALTER TABLE settings_pengiriman ADD COLUMN free_distance_local DECIMAL(10,2) DEFAULT 1.00 AFTER price_per_km_local");
        echo "Column free_distance_local added\n";
    }

    // Check if empty
    $check = $conn->query("SELECT COUNT(*) as count FROM settings_pengiriman");
    $row = $check->fetch_assoc();
    if ($row['count'] == 0) {
        $conn->query("INSERT INTO settings_pengiriman (max_distance_local, price_per_km_local, free_distance_local) VALUES (10, 1000, 1.0)");
        echo "Default row inserted\n";
    }
} else {
    echo "Error: " . $conn->error . "\n";
}
?>
