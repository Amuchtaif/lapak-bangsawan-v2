<?php
require 'config/database.php';
$res = $conn->query("SHOW TABLES LIKE 'settings_pengiriman'");
if ($res->num_rows > 0) {
    echo "Table 'settings_pengiriman' EXISTS.\n";
    $res2 = $conn->query("SELECT * FROM settings_pengiriman");
    while($row = $res2->fetch_assoc()) {
        print_r($row);
    }
} else {
    echo "Table 'settings_pengiriman' DOES NOT EXIST.\n";
}
?>
