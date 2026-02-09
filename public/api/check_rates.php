<?php
header('Content-Type: application/json');
require_once dirname(__DIR__, 2) . "/config/init.php";
require_once ROOT_PATH . "helpers/BiteshipService.php";
require_once ROOT_PATH . "includes/DistanceCalculator.php";
require_once ROOT_PATH . "includes/LocalDeliveryService.php";

$input = json_decode(file_get_contents('php://input'), true);
$area_id = $input['area_id'] ?? '';
$postal_code = $input['postal_code'] ?? '';
$items = $input['items'] ?? [];
$dest_lat = $input['dest_lat'] ?? null;
$dest_lng = $input['dest_lng'] ?? null;

// Ensure they are numeric or null
$dest_lat = (is_numeric($dest_lat)) ? (float) $dest_lat : null;
$dest_lng = (is_numeric($dest_lng)) ? (float) $dest_lng : null;

// 1. Validation
if (empty($items)) {
    echo json_encode(['success' => false, 'message' => 'Missing items']);
    exit;
}

// Postal Code Validation (Optional now)
if (!empty($postal_code) && !preg_match('/^\d{5}$/', $postal_code)) {
    // Just reset it if invalid, don't block
    $postal_code = ''; 
}

// 2. Geocoding Fallback if coordinates missing
$biteship = new BiteshipService();

if (!$dest_lat || !$dest_lng) {
    if (!empty($area_id)) {
         // If we have area_id, we might rely on Biteship, but we need distance for Hybrid Logic.
         // Biteship Check Rates provides coordinates in response sometimes? No.
         // We need coords. Try geocoding area name if present.
         $areaText = $input['area_text'] ?? $input['area_name'] ?? '';
         if (!empty($areaText)) {
             $coords = $biteship->getCoordinatesFromArea($areaText);
             if ($coords) {
                 $dest_lat = $coords['latitude'];
                 $dest_lng = $coords['longitude'];
             }
         }
    }
}

// If still no coordinates, we cannot determine distance logic reliably.
// We will default to Biteship (assuming > 2km or unable to verify local) OR fail.
// Let's default to trying Biteship to be safe, but local delivery won't be an option.
$canCalculateDistance = ($dest_lat && $dest_lng && defined('BITESHIP_ORIGIN_LAT') && defined('BITESHIP_ORIGIN_LNG'));
$distance = null;

if ($canCalculateDistance) {
    $distance = DistanceCalculator::haversine(
        BITESHIP_ORIGIN_LAT,
        BITESHIP_ORIGIN_LNG,
        $dest_lat,
        $dest_lng
    );
}

// 3. Prepare Items & Total Weight
$totalWeight = getCartTotalWeight($items); // Returns grams
$biteshipItems = [];

foreach ($items as $item) {
    $qty = floatval($item['weight'] ?? 1); // For food items, weight is often the qty unit
    $unit = $item['unit'] ?? 'kg';
    
    // Calculate individual weight in grams
    if ($unit === 'kg') {
         $weightInGrams = $qty * 1000;
    } else {
         // Assuming item['weight'] is just quantity for pcs, need item_weight from DB if possible,
         // but here we just use what we have or default to 1kg per item if unknown?
         // check_rates.php logic previously used ($qty * ($item['item_weight'] ?? 1000))
         // let's stick to safe default
         $weightInGrams = $qty * ($item['item_weight'] ?? 1000); 
    }

    $biteshipItems[] = [
        'name' => $item['name'],
        'description' => $item['name'] . ' (' . $qty . ' ' . $unit . ')',
        'value' => (int) $item['price'],
        'quantity' => 1, // Treat as packet quantity 1 with total weight
        'weight' => (int) $weightInGrams,
        'category' => 'food'
    ];
}

$pricing = [];
$recommendation = null;
$debugInfo = [
    'distance' => $distance, 
    'source' => 'unknown'
];

// 4. Hybrid Logic Implementation
// INTERNAL COURIER (Priority 1)
if ($canCalculateDistance && $distance < 2.0) {
    $localService = new LocalDeliveryService();
    $localRate = $localService->getRate($distance);
    
    if ($localRate) {
        $pricing[] = $localRate;
        $recommendation = [
            'type' => 'instant',
            'title' => 'Kurir Internal (Free Ongkir)',
            'message' => 'Jarak Anda sangat dekat (< 2km). Kami antar langsung gratis!'
        ];
        $debugInfo['source'] = 'internal_priority';
    }
}

// BITESHP API (Always Check as Backup/Alternative)
// Even if distance < 2km, we want to show other options in case internal is busy
// Config params
$couriers = $input['couriers'] ?? 'paxel,jne,jnt,sicepat,gojek,grab,anteraja,borzo,lalamove';
$extraParams = [
    'origin_contact_name' => BITESHIP_ORIGIN_CONTACT_NAME,
    'origin_contact_phone' => BITESHIP_ORIGIN_CONTACT_PHONE,
    'destination_contact_name' => $input['name'] ?? 'Customer',
    'destination_contact_phone' => $input['phone'] ?? '08123456789',
    'destination_postal_code' => $postal_code // Official param for accuracy
];

try {
    $biteshipResult = $biteship->checkRates(
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

    if ($biteshipResult['success'] && !empty($biteshipResult['data']['pricing'])) {
        // Merge Biteship rates into pricing array
        // If internal courier exists, it's already at index 0 (top)
        $pricing = array_merge($pricing, $biteshipResult['data']['pricing']);
        
        if(empty($debugInfo['source'])) {
             $debugInfo['source'] = 'biteship_api';
        } else {
             $debugInfo['source'] .= '_and_biteship';
        }
    } else {
        // API Error Handling - Return empty pricing instead of crash
        // Log error internally if possible
        $debugInfo['error'] = $biteshipResult['message'] ?? 'Unknown API Error';
    }
} catch (Exception $e) {
    // Catch Curl/Connection Exceptions
    $debugInfo['error'] = $e->getMessage();
}

// 5. Generate Recommendations if using Biteship (and pricing found)
if (!empty($pricing) && $debugInfo['source'] === 'biteship_api') {
    $hasInstant = false;
    $hasCold = false;
    $hasNextDay = false;
    
    foreach ($pricing as $rate) {
        $type = strtolower($rate['service_type'] ?? $rate['type'] ?? '');
        $company = strtolower($rate['company'] ?? '');
        $serviceName = strtolower($rate['courier_service_name'] ?? '');

        if ($type === 'instant' || $type === 'same_day') $hasInstant = true;
        if ($company === 'paxel' && (strpos($serviceName, 'cold') !== false || strpos($serviceName, 'beku') !== false)) $hasCold = true;
        if (strpos($serviceName, 'next day') !== false || $type === 'next_day') $hasNextDay = true;
    }

    if ($hasInstant && ($distance === null || $distance < 15)) {
        $recommendation = [
            'type' => 'instant',
            'title' => 'Rekomendasi Instan',
            'message' => 'Gunakan kurir Instan untuk kualitas terbaik (Jarak ±' . round($distance, 1) . ' km).'
        ];
    } elseif ($hasCold) {
        $recommendation = [
            'type' => 'cold',
            'title' => 'Rekomendasi Paxel Cold',
            'message' => 'Sangat disarankan untuk produk Frozen Food jarah jauh.'
        ];
    } elseif ($hasNextDay) {
        $recommendation = [
            'type' => 'next_day',
            'title' => 'Rekomendasi Next Day',
            'message' => 'Pilihan hemat dan cepat untuk luar kota.'
        ];
    }
}

// 6. Final Response
echo json_encode([
    'success' => true,
    'pricing' => $pricing,
    'total_weight' => $totalWeight,
    'recommendation' => $recommendation,
    'distance' => isset($distance) ? round($distance, 2) : null,
    'debug' => $debugInfo
]);
