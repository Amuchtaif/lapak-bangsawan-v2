<?php
header('Content-Type: application/json');
require_once dirname(__DIR__, 2) . "/config/init.php";
require_once ROOT_PATH . "helpers/BiteshipService.php";

$input = json_decode(file_get_contents('php://input'), true);
$area_id = $input['area_id'] ?? '';
$items = $input['items'] ?? [];

if (!$area_id || empty($items)) {
    echo json_encode(['success' => false, 'message' => 'Missing area_id or items']);
    exit;
}

// Calculate total weight and prepare items for Biteship
$totalWeight = getCartTotalWeight($items); // Use the helper function we created earlier
$biteshipItems = [];

foreach ($items as $item) {
    $qty = floatval($item['weight'] ?? 1);
    $unit = $item['unit'] ?? 'kg';
    $itemWeightInGrams = ($unit === 'kg') ? ($qty * 1000) : ($qty * ($item['item_weight'] ?? 1000));

    $biteshipItems[] = [
        'name' => $item['name'],
        'description' => $item['name'] . ' (' . $qty . ' ' . $unit . ')',
        'value' => (int) $item['price'],
        'quantity' => 1, // Treat total weight as 1 quantity for simpler Biteship logic
        'weight' => (int) $itemWeightInGrams,
        'category' => 'food'
    ];
}

$couriers = $input['couriers'] ?? 'jne,jnt,sicepat,gojek,grab,anteraja,borzo,lalamove';
$dest_lat = $input['dest_lat'] ?? null;
$dest_lng = $input['dest_lng'] ?? null;

// Ensure they are numeric or null
$dest_lat = (is_numeric($dest_lat)) ? (float) $dest_lat : null;
$dest_lng = (is_numeric($dest_lng)) ? (float) $dest_lng : null;

$biteship = new BiteshipService();

// Fallback: If coordinates missing, try geocoding the area text
if (!$dest_lat || !$dest_lng) {
    $areaText = $input['area_text'] ?? $input['area_name'] ?? '';
    if (!empty($areaText)) {
        $coords = $biteship->getCoordinatesFromArea($areaText);
        if ($coords) {
            $dest_lat = $coords['latitude'];
            $dest_lng = $coords['longitude'];
        }
    }
}

$extraParams = [
    'origin_contact_name' => 'Lapak Bangsawan',
    'origin_contact_phone' => '08123456789',
    'destination_contact_name' => $input['name'] ?? 'Customer',
    'destination_contact_phone' => $input['phone'] ?? '08123456789',
];

$biteship = new BiteshipService();
$result = $biteship->checkRates(
    $area_id,
    $totalWeight,
    $biteshipItems,
    BITESHIP_ORIGIN_AREA_ID,
    $couriers,
    BITESHIP_ORIGIN_LAT,
    BITESHIP_ORIGIN_LNG,
    $dest_lat,
    $dest_lng,
    $extraParams
);

if ($result['success']) {
    echo json_encode([
        'success' => true,
        'pricing' => $result['data']['pricing'] ?? [],
        'total_weight' => $totalWeight
    ]);
} else {
    echo json_encode(['success' => false, 'message' => $result['message']]);
}
