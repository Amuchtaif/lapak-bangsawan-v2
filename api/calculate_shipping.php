<?php
header('Content-Type: application/json');
require_once '../config/database.php';

// Load Helper
require_once '../helpers/ShippingHelper.php';

// Store Location (Base Coordinates) - Example: Cirebon
// These could also be moved to settings_pengiriman if desired
define('STORE_LAT', -6.732021);
define('STORE_LNG', 108.552316);

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

    // 1. Fetch Dynamic Settings
    $settings = ShippingHelper::getLocalShippingSettings($conn);
    $max_distance = $settings['max_distance_local'];
    $price_per_km = $settings['price_per_km_local'];

    // 2. Calculate Distance
    $distance = ShippingHelper::calculateDistance(STORE_LAT, STORE_LNG, $latitude, $longitude);

    // 3. Save Coordinates to Database (if customer_id is provided)
    if ($customerId && isset($conn)) {
        $sql = "UPDATE customers SET latitude = ?, longitude = ? WHERE id = ?";
        try {
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("ddi", $latitude, $longitude, $customerId);
                $stmt->execute();
                $stmt->close();
            }
        } catch (Exception $e) {
            // Ignore DB error for column missing in this demo
        }
    }

    // 4. Determine Shipping Options
    $shippingOptions = [];

    // Option A: Local Courier (Hybrid Logic)
    if ($distance <= $max_distance) {
        $cost = ShippingHelper::calculateLocalCost($distance, $price_per_km);
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
