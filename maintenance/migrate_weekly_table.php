<?php
require_once dirname(__DIR__) . "/config/database.php";

echo "Migrating database needed for Weekly Stock Target...\n";

// Create weekly_sales_targets Table
$sql = "CREATE TABLE IF NOT EXISTS `weekly_sales_targets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `start_date` date NOT NULL COMMENT 'Monday of the week',
  `target_qty_kg` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_weekly_target` (`product_id`,`start_date`),
  KEY `start_date` (`start_date`),
  CONSTRAINT `fk_weekly_target_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

if ($conn->query($sql) === TRUE) {
    echo "Table 'weekly_sales_targets' created successfully.\n";
} else {
    echo "Error creating table: " . $conn->error . "\n";
    exit(1);
}

echo "Migration completed successfully.\n";
?>
