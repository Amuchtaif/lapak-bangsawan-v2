<?php
require_once '../config/database.php';

$sql = "CREATE TABLE IF NOT EXISTS `daily_sales_targets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `target_date` date NOT NULL,
  `target_qty_kg` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_target` (`product_id`, `target_date`),
  KEY `target_date` (`target_date`),
  CONSTRAINT `fk_daily_target_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($sql) === TRUE) {
    echo "Table 'daily_sales_targets' created successfully.\n";
} else {
    echo "Error creating table: " . $conn->error . "\n";
}

$conn->close();
?>
