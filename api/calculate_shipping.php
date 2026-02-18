<?php
header('Content-Type: application/json');
require_once '../config/init.php';

// Store Location (Base Coordinates) - Example: Cirebon
define('STORE_LAT', -6.732021);
define('STORE_LNG', 108.552316);

// Fetch settings from DB
$max_local_km = (float) get_setting('shipping_max_distance', 15.0);
$rate_per_km = (int) get_setting('shipping_rate_per_km', 1000);

/**
 * Haversine Formula to calculate distance between two coordinates in KM
 */
function calculateHaversine($lat1, $lng1, $lat2, $lng2)
{
    $earthRadius = 6371; // Earth radius in kilometers

    $dLat = deg2rad($lat2 - $lat1);
    $dLng = deg2rad($lng2 - $lng1);

    $a = sin($dLat / 2) * sin($dLat / 2) +
        cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
        sin($dLng / 2) * sin($dLng / 2);

    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    $distance = $earthRadius * $c;

    return round($distance, 2); // Return distance with 2 decimal precision
}

// Handle POST Request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    $latitude = $input['latitude'] ?? null;
    $longitude = $input['longitude'] ?? null;
    $customerId = $input['customer_id'] ?? null;

    if ($latitude === null || $longitude === null) {
        echo json_encode(['success' => false, 'message' => 'Coordinates missing']);
        exit;
    }

    // 1. Calculate Distance
    $distance = calculateHaversine(STORE_LAT, STORE_LNG, $latitude, $longitude);

    // 2. Save Coordinates to Database (if customer_id is provided)
    if ($customerId && isset($conn)) {
        $sql = "UPDATE customers SET latitude = ?, longitude = ? WHERE id = ?";
        try {
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ddi", $latitude, $longitude, $customerId);
            $stmt->execute();
        } catch (Exception $e) {
            // Silently fail or log error
        }
    }

    // 3. Determine Shipping Options
    $shippingOptions = [];

    // Option A: Local Courier (Hybrid Logic)
    if ($distance <= $max_local_km) {
        $cost = 0;
        $name = 'Kurir Langsung (Gratis)';
        
        if ($distance >= 1.0) {
            $cost = floor($distance) * $rate_per_km;
            $name = 'Kurir Langsung (Instant)';
        }
        
        $shippingOptions[] = [
            'code' => 'LOCAL_INSTANT',
            'name' => $name,
            'service' => 'Sesuai Jarak',
            'cost' => $cost,
            'etd' => '1-3 Jam',
            'type' => 'local'
        ];
    }

    // Option B: Regular/Logistics (Fallback or Alternative)
    // You could integrate Biteship or other APIs here
    $shippingOptions[] = [
        'code' => 'REGULAR',
        'name' => 'Kurir Ekspedisi',
        'service' => 'Standar',
        'cost' => 15000, // Example flat rate or dynamic
        'etd' => '2-4 Hari',
        'type' => 'logistic'
    ];

    echo json_encode([
        'success' => true,
        'distance' => $distance,
        'options' => $shippingOptions
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
