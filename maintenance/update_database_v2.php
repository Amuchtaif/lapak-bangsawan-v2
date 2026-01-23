<?php
require_once '../config/database.php';

// 1. Create customers table
$sql_customers = "CREATE TABLE IF NOT EXISTS `customers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) NOT NULL,
  `address` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($sql_customers) === TRUE) {
    echo "Table 'customers' created successfully.\n";
} else {
    echo "Error creating table 'customers': " . $conn->error . "\n";
}

// 2. Add full_name to users table if not exists
$check_col = "SHOW COLUMNS FROM `users` LIKE 'full_name'";
$result = $conn->query($check_col);
if ($result->num_rows == 0) {
    $sql_alter = "ALTER TABLE `users` ADD `full_name` VARCHAR(100) DEFAULT 'Admin User' AFTER `username`";
    if ($conn->query($sql_alter) === TRUE) {
        echo "Column 'full_name' added to 'users' table successfully.\n";
    } else {
        echo "Error adding column 'full_name': " . $conn->error . "\n";
    }
} else {
    echo "Column 'full_name' already exists in 'users' table.\n";
}

$conn->close();
?>