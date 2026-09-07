<?php
// tools/run_pending_migrations.php
// Set header to plain text for readable browser output if not in CLI
if (php_sapi_name() !== 'cli') {
    header("Content-Type: text/plain; charset=utf-8");
}

require_once dirname(__DIR__) . "/config/init.php";

echo "=== STARTING DATABASE MIGRATION SCRIPT ===\n\n";

// Helper function to check if a table exists
function tableExists($conn, $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    return $result && $result->num_rows > 0;
}

// Helper function to check if a column exists
function columnExists($conn, $table, $column) {
    if (!tableExists($conn, $table)) return false;
    $result = $conn->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
    return $result && $result->num_rows > 0;
}

// Helper function to run query and report status
function runQuery($conn, $sql, $successMsg, $failMsg) {
    if ($conn->query($sql)) {
        echo "[SUCCESS] " . $successMsg . "\n";
        return true;
    } else {
        echo "[ERROR] " . $failMsg . " -> " . $conn->error . "\n";
        return false;
    }
}

// --- 1. PARTNERS SYSTEM (Migration: 20260209090000_create_partners) ---

// Create partners table
if (!tableExists($conn, 'partners')) {
    echo "Creating 'partners' table...\n";
    $sql_partners = "CREATE TABLE `partners` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(100) NOT NULL,
        `contact` varchar(50) DEFAULT NULL,
        `address` text DEFAULT NULL,
        `status` enum('active','inactive') DEFAULT 'active',
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    runQuery($conn, $sql_partners, "Table 'partners' created successfully.", "Failed to create 'partners' table");
} else {
    echo "Table 'partners' already exists.\n";
}

// Add partner columns to products
if (tableExists($conn, 'products')) {
    if (!columnExists($conn, 'products', 'partner_id')) {
        echo "Adding 'partner_id' column to 'products' table...\n";
        $sql = "ALTER TABLE `products` ADD COLUMN `partner_id` int(11) DEFAULT NULL AFTER `category_id`";
        if (runQuery($conn, $sql, "Column 'partner_id' added to 'products'.", "Failed to add 'partner_id'")) {
            // Add foreign key constraint
            $fk_sql = "ALTER TABLE `products` ADD CONSTRAINT `fk_product_partner` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE SET NULL ON UPDATE NO_ACTION";
            runQuery($conn, $fk_sql, "Foreign key for 'partner_id' created.", "Failed to add foreign key for 'partner_id'");
        }
    } else {
        echo "Column 'partner_id' already exists in 'products'.\n";
    }

    if (!columnExists($conn, 'products', 'product_type')) {
        echo "Adding 'product_type' column to 'products' table...\n";
        $sql = "ALTER TABLE `products` ADD COLUMN `product_type` enum('internal','consignment') DEFAULT 'internal' AFTER `partner_id`";
        runQuery($conn, $sql, "Column 'product_type' added to 'products'.", "Failed to add 'product_type'");
    } else {
        echo "Column 'product_type' already exists in 'products'.\n";
    }

    if (!columnExists($conn, 'products', 'buy_price')) {
        echo "Adding 'buy_price' column to 'products' table...\n";
        $sql = "ALTER TABLE `products` ADD COLUMN `buy_price` decimal(15,2) DEFAULT 0.00 AFTER `price`";
        runQuery($conn, $sql, "Column 'buy_price' added to 'products'.", "Failed to add 'buy_price'");
    } else {
        echo "Column 'buy_price' already exists in 'products'.\n";
    }
}


// --- 2. PACKAGES SYSTEM (Migration: 20260606000000_create_packages) ---

if (tableExists($conn, 'products')) {
    if (!columnExists($conn, 'products', 'is_package')) {
        echo "Adding 'is_package' column to 'products' table...\n";
        $sql = "ALTER TABLE `products` ADD COLUMN `is_package` tinyint(1) NOT NULL DEFAULT 0 AFTER `created_at`";
        runQuery($conn, $sql, "Column 'is_package' added to 'products'.", "Failed to add 'is_package'");
    } else {
        echo "Column 'is_package' already exists in 'products'.\n";
    }

    if (!columnExists($conn, 'products', 'status')) {
        echo "Adding 'status' column to 'products' table...\n";
        $sql = "ALTER TABLE `products` ADD COLUMN `status` enum('active','inactive') NOT NULL DEFAULT 'active' AFTER `is_package`";
        runQuery($conn, $sql, "Column 'status' added to 'products'.", "Failed to add 'status'");
    } else {
        echo "Column 'status' already exists in 'products'.\n";
    }
}

// Create package_items table
if (!tableExists($conn, 'package_items')) {
    echo "Creating 'package_items' table...\n";
    $sql_pkg_items = "CREATE TABLE `package_items` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `package_id` int(11) NOT NULL,
        `product_id` int(11) NOT NULL,
        `quantity` decimal(10,2) NOT NULL,
        PRIMARY KEY (`id`),
        KEY `package_id` (`package_id`),
        KEY `product_id` (`product_id`),
        CONSTRAINT `fk_pkg_items_package` FOREIGN KEY (`package_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
        CONSTRAINT `fk_pkg_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    runQuery($conn, $sql_pkg_items, "Table 'package_items' created successfully.", "Failed to create 'package_items' table");
} else {
    echo "Table 'package_items' already exists.\n";
}


// --- 3. CATEGORIES SEEDING (Paket Promo) ---

if (tableExists($conn, 'categories')) {
    $check_cat = $conn->query("SELECT id FROM categories WHERE slug = 'paket-promo' LIMIT 1");
    if ($check_cat && $check_cat->num_rows == 0) {
        echo "Inserting default 'Paket Promo' category...\n";
        $sql_cat = "INSERT INTO categories (name, slug) VALUES ('Paket Promo', 'paket-promo')";
        runQuery($conn, $sql_cat, "Category 'Paket Promo' inserted.", "Failed to insert 'Paket Promo' category");
    } else {
        echo "Category 'Paket Promo' already exists.\n";
    }
}

echo "\n=== MIGRATION SCRIPT COMPLETED ===\n";
