<?php
$conn = mysqli_connect('localhost', 'root', '', 'lapak_bangsawan');
if (!$conn)
    die("Connection failed: " . mysqli_connect_error());

function addColumn($conn, $table, $column, $type)
{
    $res = mysqli_query($conn, "SHOW COLUMNS FROM `$table` LIKE '$column'");
    if (mysqli_num_rows($res) == 0) {
        $sql = "ALTER TABLE `$table` ADD COLUMN `$column` $type";
        if (mysqli_query($conn, $sql)) {
            echo "Added $column to $table\n";
        } else {
            echo "Error adding $column to $table: " . mysqli_error($conn) . "\n";
        }
    } else {
        echo "$column already exists in $table\n";
    }
}

addColumn($conn, 'products', 'buy_price', "DECIMAL(10,2) DEFAULT 0.00 AFTER price");
addColumn($conn, 'order_items', 'buy_price', "DECIMAL(10,2) DEFAULT 0.00 AFTER price_per_kg");

$tables = mysqli_query($conn, "SHOW TABLES");
echo "Tables in database:\n";
while ($row = mysqli_fetch_row($tables)) {
    echo $row[0] . "\n";
}

mysqli_close($conn);
?>