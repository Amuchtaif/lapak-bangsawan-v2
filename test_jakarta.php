<?php
require_once 'config/init.php';
require_once 'helpers/BiteshipService.php';
$biteship = new BiteshipService();
$res = $biteship->searchArea("Jakarta");
echo json_encode($res);
?>