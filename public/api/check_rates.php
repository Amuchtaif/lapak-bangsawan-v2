<?php
header('Content-Type: application/json');
require_once dirname(dirname(__DIR__)) . "/config/init.php";
require_once ROOT_PATH . "includes/LocalDeliveryService.php";
require_once ROOT_PATH . "includes/cart_helper.php";
require_once ROOT_PATH . "includes/BiteshipService.php";

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['items'])) {
    echo json_encode(['success' => false, 'message' => 'Input tidak valid.']);
    exit;
}

$items = $input['items'];
$destinationAreaId = $input['area_id'] ?? '';
$postalCode = $input['postal_code'] ?? '';
$dest_lat = $input['dest_lat'] ?? null;
$dest_lng = $input['dest_lng'] ?? null;

// 1. Calculate Total Weight (Grams)
$totalWeight = getCartTotalWeight($items);

// 2. Determine Distance if Coordinates exist
$distance = null;
$canCalculateDistance = false;
$STORE_LAT = -6.732021;
$STORE_LNG = 108.552316;

if ($dest_lat && $dest_lng) {
    // Haversine Formula
    $earthRadius = 6371;
    $dLat = deg2rad($dest_lat - $STORE_LAT);
    $dLng = deg2rad($dest_lng - $STORE_LNG);
    $a = sin($dLat / 2) * sin($dLat / 2) +
        cos(deg2rad($STORE_LAT)) * cos(deg2rad($dest_lat)) *
        sin($dLng / 2) * sin($dLng / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    $distance = $earthRadius * $c;
    $canCalculateDistance = true;
}

$pricing = [];
$recommendation = null;
$debugInfo = [
    'weight' => $totalWeight,
    'distance' => $distance,
    'can_calculate' => $canCalculateDistance
];

// 4. Hybrid Logic Implementation
// INTERNAL COURIER (Priority 1)
$max_local_dist = (float) get_setting('shipping_max_distance', 15.0);
if ($canCalculateDistance && $distance <= $max_local_dist) {
    $localService = new LocalDeliveryService();
    $localRate = $localService->getRate($distance);
    
    if ($localRate) {
        $pricing[] = $localRate;
        
        if ($distance < 1.0) {
            $recommendation = [
                'type' => 'instant',
                'title' => 'Kurir Internal (Free Ongkir)',
                'message' => 'Jarak Anda sangat dekat (< 1km). Kami antar langsung gratis!'
            ];
        } else {
            $recommendation = [
                'type' => 'instant',
                'title' => 'Kurir Internal Toko',
                'message' => 'Tersedia kurir toko untuk jarak ' . round($distance, 1) . ' km. Lebih cepat & terpercaya!'
            ];
        }
        $debugInfo['source'] = 'internal_priority';
    }
}

// BITESHIP API (Backup/Alternative)
$biteship = new BiteshipService();
$extraParams = [];

try {
    $biteshipResult = $biteship->checkRates(
        'IDR',
        $totalWeight,
        $destinationAreaId,
        $dest_lat,
        $dest_lng,
        $extraParams
    );

    if ($biteshipResult['success'] && !empty($biteshipResult['data']['pricing'])) {
        $pricing = array_merge($pricing, $biteshipResult['data']['pricing']);
        if(empty($debugInfo['source'])) $debugInfo['source'] = 'biteship_api';
    }
} catch (Exception $e) {
    $debugInfo['error'] = $e->getMessage();
}

// Translate Duration to Indonesian
foreach ($pricing as &$rate) {
    if (isset($rate['duration'])) {
        $search = ['hours', 'hour', 'days', 'day', 'mins', 'min'];
        $replace = ['jam', 'jam', 'hari', 'hari', 'menit', 'menit'];
        $rate['duration'] = str_ireplace($search, $replace, $rate['duration']);
    }
}

// Final Response
echo json_encode([
    'success' => true,
    'pricing' => $pricing,
    'total_weight' => $totalWeight,
    'recommendation' => $recommendation,
    'distance' => isset($distance) ? round($distance, 2) : null,
    'debug' => $debugInfo
]);
