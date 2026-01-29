<?php
require_once 'helpers/BiteshipService.php';
$biteship = new BiteshipService();
$res = $biteship->getCoordinatesFromArea("Talun, Cirebon");
header('Content-Type: application/json');
echo json_encode($res, JSON_PRETTY_PRINT);
?>