<?php
require 'db_connect.php';
$res = $conn->query("SHOW CREATE TABLE orders");
if ($res) {
    echo "ORDERS_TABLE:\n";
    print_r($res->fetch_assoc());
} else {
    echo "Error showing orders: " . $conn->error;
}
$res2 = $conn->query("SHOW CREATE TABLE customers");
if ($res2) {
    echo "\nCUSTOMERS_TABLE:\n";
    print_r($res2->fetch_assoc());
}
?>