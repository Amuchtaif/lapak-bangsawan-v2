<?php
require 'db_connect.php';
$stmt = $conn->query("SHOW CREATE TABLE orders");
if ($stmt) {
    $row = $stmt->fetch_assoc();
    echo "CREATE TABLE: \n" . $row['Create Table'];
} else {
    echo "Error: " . $conn->error;
}
?>