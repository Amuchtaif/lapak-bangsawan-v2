<?php
require("db_connect.php");

echo "<h2>Table: order_items</h2>";
$result = $conn->query("DESCRIBE order_items");
while ($row = $result->fetch_assoc()) {
    print_r($row);
    echo "<br>";
}

echo "<h2>Table: products</h2>";
$result = $conn->query("DESCRIBE products");
while ($row = $result->fetch_assoc()) {
    print_r($row);
    echo "<br>";
}
?>