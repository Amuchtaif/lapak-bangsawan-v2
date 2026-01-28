<?php
require_once 'config/init.php';
require_once 'helpers/BiteshipService.php';

$biteship = new BiteshipService();
$area_id = "IDNP9IDNC105IDND175IDZ45115"; // Pekalipan
$totalWeight = 2000;
$biteshipItems = [
    [
        'name' => 'Test Item',
        'value' => 100000,
        'quantity' => 1,
        'weight' => 2000,
        'category' => 'food'
    ]
];
$couriers = "jne,jnt,sicepat,gojek,grab,anteraja,borzo,lalamove";
$originLat = -6.7607;
$originLng = 108.5305;
$destLat = -6.7214;
$destLng = 108.5624;
$extraParams = [
    'origin_contact_name' => 'Lapak Bangsawan',
    'origin_contact_phone' => '08123456789',
    'destination_contact_name' => 'Customer',
    'destination_contact_phone' => '08123456789'
];

$res = $biteship->checkRates($area_id, $totalWeight, $biteshipItems, BITESHIP_ORIGIN_AREA_ID, $couriers, $originLat, $originLng, $destLat, $destLng, $extraParams);

header('Content-Type: application/json');
echo json_encode($res, JSON_PRETTY_PRINT);
?>