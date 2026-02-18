<?php
require_once 'config/init.php';
require_once 'helpers/BiteshipService.php';

$biteship = new BiteshipService();
// Search for Cirebon area to get a valid ID
$search = $biteship->searchArea("Cirebon");

header('Content-Type: application/json');
echo json_encode($search, JSON_PRETTY_PRINT);
?>
