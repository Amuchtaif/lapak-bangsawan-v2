<?php
require_once 'config/init.php';
require_once 'helpers/BiteshipService.php';

$areaId = 'IDNP9IDNC105IDND171IDZ45171'; // Example area (Cirebon?)
$totalWeight = 1000;
$items = [[
    'name' => 'Test Product',
    'value' => 50000,
    'quantity' => 1,
    'weight' => 1000
]];

$biteship = new BiteshipService();
$res = $biteship->checkRates(
    $areaId,
    $totalWeight,
    $items,
    BITESHIP_ORIGIN_AREA_ID,
    'paxel,jne,jnt,sicepat,gojek,grab,anteraja,borzo,lalamove',
    BITESHIP_ORIGIN_LAT,
    BITESHIP_ORIGIN_LNG,
    null,
    null,
    []
);

header('Content-Type: application/json');
echo json_encode($res, JSON_PRETTY_PRINT);
?>
