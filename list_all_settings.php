<?php
require 'config/database.php';
$res = $conn->query("SELECT * FROM site_settings");
while($row = $res->fetch_assoc()){
    echo "{$row['setting_key']}: {$row['setting_value']}\n";
}
