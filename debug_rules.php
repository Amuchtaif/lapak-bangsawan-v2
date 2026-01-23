<?php
$c = new mysqli('localhost', 'root', '', 'lapak_bangsawan');
$r = $c->query('SELECT * FROM wholesale_rules');
while ($row = $r->fetch_assoc())
    print_r($row);
?>