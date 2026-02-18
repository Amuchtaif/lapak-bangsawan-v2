<?php
require 'config/database.php';
$res = $conn->query("SELECT * FROM site_settings WHERE setting_key = 'biteship_api_key'");
if($row = $res->fetch_assoc()){
    echo "biteship_api_key: {$row['setting_value']}\n";
} else {
    echo "biteship_api_key: NOT FOUND\n";
}
