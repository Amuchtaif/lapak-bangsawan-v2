<?php
require_once 'config/init.php';
require_once 'helpers/BiteshipService.php';
$biteship = new BiteshipService();
$res = $biteship->searchArea("Pekalipan");
file_put_contents('debug_output.txt', print_r($res, true));
echo "Done";
?>