<?php
$c = new mysqli('localhost', 'root', '', 'lapak_bangsawan');
$c->query("INSERT INTO wholesale_rules (category_name, min_weight_kg, discount_per_kg, is_active) VALUES ('Seafood', 5, 2000, 1)");
echo "Inserted: " . $c->affected_rows;
?>