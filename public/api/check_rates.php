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

$couriers = $input['couriers'] ?? 'paxel,jne,jnt,sicepat,gojek,grab,anteraja,borzo,lalamove';
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

// Recommendation Logic
$recommendation = null;
$hasInstant = false;
$hasCold = false;
$hasNextDay = false;

// Distance-based calculation (always try to calculate if coords available)
$distance = null;
if ($dest_lat && $dest_lng && defined('BITESHIP_ORIGIN_LAT') && defined('BITESHIP_ORIGIN_LNG')) {
    $lat1 = deg2rad(BITESHIP_ORIGIN_LAT);
    $lon1 = deg2rad(BITESHIP_ORIGIN_LNG);
    $lat2 = deg2rad($dest_lat);
    $lon2 = deg2rad($dest_lng);

    $deltaLat = $lat2 - $lat1;
    $deltaLon = $lon2 - $lon1;

    $a = sin($deltaLat / 2) * sin($deltaLat / 2) +
        cos($lat1) * cos($lat2) *
        sin($deltaLon / 2) * sin($deltaLon / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    $distance = 6371 * $c; // Earth radius in km
}

if ($result['success'] && !empty($result['data']['pricing'])) {
    foreach ($result['data']['pricing'] as $rate) {
        $type = strtolower($rate['service_type'] ?? $rate['courier_service_type'] ?? '');
        $company = strtolower($rate['company'] ?? '');
        $serviceName = strtolower($rate['courier_service_name'] ?? '');

        if ($type === 'instant' || $type === 'same_day')
            $hasInstant = true;
        if ($company === 'paxel' && (strpos($serviceName, 'cold') !== false || strpos($serviceName, 'beku') !== false || strpos($serviceName, 'frozen') !== false || strpos($serviceName, 'chill') !== false))
            $hasCold = true;
        if ($type === 'next_day' || $type === 'nextday' || strpos($serviceName, 'next day') !== false || $type === 'express' || $type === 'one_day')
            $hasNextDay = true;
    }

    // Prioritize Recommendations
    if ($hasInstant && ($distance === null || $distance < 50)) {
        $msg = 'Kami sangat menyarankan menggunakan kurir **Instan** agar produk tetap fresh dan terjaga kualitasnya.';
        if ($distance !== null) {
            $msg = 'Jarak pengiriman cukup dekat (±' . round($distance, 1) . ' km). ' . $msg;
        }
        $recommendation = [
            'type' => 'instant',
            'title' => 'Rekomendasi Kurir Instan',
            'message' => $msg
        ];
    } elseif ($hasCold) {
        $recommendation = [
            'type' => 'cold',
            'title' => 'Rekomendasi Paxel Cold',
            'message' => 'Untuk menjaga kualitas produk makanan Anda selama pengiriman jarak jauh, kami menyarankan menggunakan **Paxel Cold** (Dilengkapi pendingin).'
        ];
    } elseif ($hasNextDay) {
        $recommendation = [
            'type' => 'next_day',
            'title' => 'Rekomendasi Layanan Next Day',
            'message' => 'Untuk pengiriman ke luar kota, pilihlah layanan **Next Day** agar paket tidak terlalu lama di perjalanan.'
        ];
    }
}

if ($result['success']) {
    echo json_encode([
        'success' => true,
        'pricing' => $result['data']['pricing'] ?? [],
        'total_weight' => $totalWeight,
        'recommendation' => $recommendation,
        'distance' => isset($distance) ? round($distance, 2) : null,
        'debug' => [
            'hasInstant' => $hasInstant,
            'hasCold' => $hasCold,
            'hasNextDay' => $hasNextDay,
            'distance' => $distance,
            'dest' => ['lat' => $dest_lat, 'lng' => $dest_lng]
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'message' => $result['message']]);
}
