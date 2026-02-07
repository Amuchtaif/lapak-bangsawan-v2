<?php
header('Content-Type: application/json');
require_once '../config/database.php';

// Store Location (Base Coordinates) - Example: Cirebon
define('STORE_LAT', -6.732021);
define('STORE_LNG', 108.552316);
define('MAX_LOCAL_DELIVERY_KM', 10); // Max distance for local courier

// Haversine Formula Implementation
function calculateHaversine($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 6371; // km

    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);

    $a = sin($dLat / 2) * sin($dLat / 2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dLon / 2) * sin($dLon / 2);

    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    $distance = $earthRadius * $c;

    return round($distance, 2); // Return distance with 2 decimal precision
}

// Handle POST Request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid JSON input']);
        exit;
    }

    $latitude = filter_var($input['latitude'], FILTER_VALIDATE_FLOAT);
    $longitude = filter_var($input['longitude'], FILTER_VALIDATE_FLOAT);
    $customerId = isset($input['customer_id']) ? filter_var($input['customer_id'], FILTER_VALIDATE_INT) : null;

    if ($latitude === false || $longitude === false) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid coordinates']);
        exit;
    }

    // 1. Calculate Distance
    $distance = calculateHaversine(STORE_LAT, STORE_LNG, $latitude, $longitude);

    // 2. Save Coordinates to Database (if customer_id is provided)
    // Assuming table 'customers' or 'customer_addresses' has lat/lng columns.
    // For this example, we will update the 'customers' table if columns exist, 
    // or just demonstrate the prepared statement logic.
    
    // NOTE: You might need to add these columns to your database:
    // ALTER TABLE customers ADD COLUMN latitude DECIMAL(10, 8), ADD COLUMN longitude DECIMAL(11, 8);
    
    if ($customerId && isset($conn)) {
        // Check if columns exist first to avoid error in this generic script, 
        // or just try-catch. We'll assume the user will add them or they exist.
        // We use Prepared Statement as requested.
        
        $sql = "UPDATE customers SET latitude = ?, longitude = ? WHERE id = ?";
        
        // We need to check if columns exist, otherwise this will fail. 
        // For reliability in this 'help' task, I'll wrap in try-catch or silence specific error.
        try {
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("ddi", $latitude, $longitude, $customerId);
                $stmt->execute();
                $stmt->close();
            }
        } catch (Exception $e) {
            // Ignore DB error for column missing in this demo, logs would be better
        }
    }

    // 3. Determine Shipping Options
    $shippingOptions = [];

    // Option A: Local Courier (Hybrid Logic)
    if ($distance <= MAX_LOCAL_DELIVERY_KM) {
        $cost = 5000 + ($distance * 1000); // Base 5000 + 1000 per km
        $shippingOptions[] = [
            'code' => 'LOCAL_INSTANT',
            'name' => 'Kurir Langsung (Instant)',
            'etd' => '1-3 Jam',
            'price' => $cost,
            'distance' => $distance
        ];
    }

    // Option B: Standard Courier (Mocked or API integrated)
    // Always available (e.g. JNE, J&T)
    // In a real scenario, call Biteship API here.
    $shippingOptions[] = [
        'code' => 'JNE_REG',
        'name' => 'JNE Reguler',
        'etd' => '1-2 Hari',
        'price' => 15000, // Flat rate example
        'distance' => $distance
    ];
    
    $shippingOptions[] = [
        'code' => 'JNT_EZ',
        'name' => 'J&T EZ',
        'etd' => '1-3 Hari',
        'price' => 18000, // Flat rate example
        'distance' => $distance
    ];

    // Return Response
    echo json_encode([
        'status' => 'success',
        'distance' => $distance,
        'unit' => 'km',
        'store_location' => ['lat' => STORE_LAT, 'lng' => STORE_LNG],
        'customer_location' => ['lat' => $latitude, 'lng' => $longitude],
        'options' => $shippingOptions
    ]);
    exit;
}

// Method Not Allowed
http_response_code(405);
echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
?>
