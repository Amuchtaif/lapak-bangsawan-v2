<?php
require_once 'config/init.php';
require_once 'helpers/BiteshipService.php';

$biteship = new BiteshipService();
$q = "Pekalipan";
$res = $biteship->searchArea($q);

header('Content-Type: application/json');
echo json_encode($res, JSON_PRETTY_PRINT);
?>