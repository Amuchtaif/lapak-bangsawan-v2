<?php
require_once __DIR__ . "/../config/init.php";
$result = $conn->query("DESCRIBE products");
while ($row = $result->fetch_assoc()) {
    print_r($row);
}