<?php
require("db_connect.php");

function describeTable($conn, $table)
{
    echo "Table: $table\n";
    $result = $conn->query("SHOW COLUMNS FROM $table");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            echo $row['Field'] . " - " . $row['Type'] . "\n";
        }
    } else {
        echo "Error: " . $conn->error . "\n";
    }
    echo "\n";
}

describeTable($conn, 'order_items');
describeTable($conn, 'customers');
describeTable($conn, 'products');
?>