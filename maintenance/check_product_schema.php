<?php
require("../config/database.php");
$result = $conn->query("SHOW COLUMNS FROM products");
while ($row = $result->fetch_assoc()) {
    print_r($row);
}
?>