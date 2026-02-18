<?php
require_once 'config/init.php';
require_once 'helpers/BiteshipService.php';

$areaId = 'IDNP9IDNC105IDND171IDZ45171'; 
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
    $items
);

header('Content-Type: application/json');
echo json_encode([
    'success' => $res['success'],
    'data_keys' => isset($res['data']) ? array_keys($res['data']) : null,
    'pricing_count' => isset($res['data']['pricing']) ? count($res['data']['pricing']) : 0,
    'first_pricing_item' => isset($res['data']['pricing'][0]) ? $res['data']['pricing'][0] : null
], JSON_PRETTY_PRINT);
?>
