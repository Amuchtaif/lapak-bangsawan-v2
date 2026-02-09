<?php
header('Content-Type: application/json');
require_once dirname(__DIR__, 2) . "/config/init.php";
require_once ROOT_PATH . "helpers/BiteshipService.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$order_id = intval($_POST['order_id'] ?? 0);

if (!$order_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid Order ID']);
    exit;
}

// 1. Fetch Order Data
$order_query = $conn->query("SELECT * FROM orders WHERE id = $order_id LIMIT 1");
if ($order_query->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Order not found']);
    exit;
}
$order = $order_query->fetch_assoc();

// Check if already has tracking
if (!empty($order['tracking_id'])) {
    echo json_encode(['success' => true, 'tracking_id' => $order['tracking_id'], 'message' => 'Order already processed']);
    exit;
}

// 2. Fetch Order Items
$items_query = $conn->query("SELECT * FROM order_items WHERE order_id = $order_id");
$items = [];
$total_weight_grams = 0;

while ($item = $items_query->fetch_assoc()) {
    $weight_grams = floatval($item['weight']) * 1000;
    $total_weight_grams += $weight_grams;

    $items[] = [
        'name' => $item['product_name'],
        'description' => $item['product_name'],
        'value' => (int) $item['subtotal'],
        'quantity' => 1, // We treat the weight as the quantity unit in some cases, but for Biteship list it's usually 1 item of X weight
        'weight' => (int) $weight_grams
    ];
}

// 3. Prepare Biteship Payload
$biteship = new BiteshipService();

// We need destination_area_id. If missing in order, try to search it?
// Usually, it should be saved during checkout.
$destination_area_id = $order['destination_area_id'] ?? '';

if (empty($destination_area_id)) {
    // Fallback: Try to find Area ID using Postal Code or Address
    $search_query = '';
    
    // 1. Try extracting Postal Code
    if (preg_match('/\[Kode Pos: (\d+)\]/', $order['customer_address'], $matches)) {
        $search_query = $matches[1];
    } 
    // 2. Try cleaning address (take text inside parentheses if exists, e.g. Kecamatan/Kota)
    elseif (preg_match('/\((.*?)\)/', $order['customer_address'], $matches)) {
        $search_query = $matches[1];
    }
    
    // 3. Search using Query (Postal Code or Address Text)
    if ($search_query && empty($destination_area_id)) {
        $area_result = $biteship->searchArea($search_query);
        if ($area_result['success'] && !empty($area_result['data']['areas'])) {
            $destination_area_id = $area_result['data']['areas'][0]['id'];
            $conn->query("UPDATE orders SET destination_area_id = '$destination_area_id' WHERE id = $order_id");
        }
    }

    // 4. Fallback: Use Coordinates (Reverse Geocoding)
    // If ID is STILL empty, try using lat/long
    if (empty($destination_area_id) && $order['destination_latitude'] && $order['destination_longitude']) {
        $foundAreaId = $biteship->retrieveAreaIdFromCoordinates($order['destination_latitude'], $order['destination_longitude']);
        if ($foundAreaId) {
             $destination_area_id = $foundAreaId;
             $conn->query("UPDATE orders SET destination_area_id = '$destination_area_id' WHERE id = $order_id");
        }
    }
    
    // If still empty, then fail
    if (empty($destination_area_id)) {
        echo json_encode(['success' => false, 'message' => 'Destination Area ID is missing and auto-search failed. Please update address with a valid area.']);
        exit;
    }
}

$dest_lat = $order['destination_latitude'] ? (float) $order['destination_latitude'] : null;
$dest_lng = $order['destination_longitude'] ? (float) $order['destination_longitude'] : null;

// Fallback: If coordinates missing, try to fetch from address
if (!$dest_lat || !$dest_lng) {
    // 1. Try full address
    $coords = $biteship->getCoordinatesFromArea($order['customer_address']);

    // 2. If failed, try extracting text inside parentheses (e.g., "Detail (Kecamatan, Kota, Prov)")
    if (!$coords && preg_match('/\((.*?)\)/', $order['customer_address'], $matches)) {
        $cleanAddr = $matches[1];
        $coords = $biteship->getCoordinatesFromArea($cleanAddr);
    }

    if ($coords) {
        $dest_lat = $coords['latitude'];
        $dest_lng = $coords['longitude'];
        // Update DB for future reference
        $conn->query("UPDATE orders SET destination_latitude = $dest_lat, destination_longitude = $dest_lng WHERE id = $order_id");
    }
}

    // Extract Postal Code if present in address
    $postal_code = '';
    if (preg_match('/\[Kode Pos: (\d+)\]/', $order['customer_address'], $matches)) {
        $postal_code = $matches[1];
    }

    $payload = [
        'shipper_contact_name' => 'Lapak Bangsawan',
        'shipper_contact_phone' => '0859110022099',
        'shipper_contact_email' => 'lapakbangsawan@gmail.com',
        'origin_contact_name' => 'Lapak Bangsawan',
        'origin_contact_phone' => '0859110022099',
        'origin_address' => 'Jl. Wanagati, Karyamulya, Kesambi, Kota Cirebon, Jawa Barat',
        'origin_area_id' => BITESHIP_ORIGIN_AREA_ID,
        'origin_latitude' => BITESHIP_ORIGIN_LAT,
        'origin_longitude' => BITESHIP_ORIGIN_LNG,
        'origin_coordinate' => [
            'latitude' => BITESHIP_ORIGIN_LAT,
            'longitude' => BITESHIP_ORIGIN_LNG
        ],
        'destination_contact_name' => $order['customer_name'],
        'destination_contact_phone' => $order['customer_phone'],
        'destination_address' => $order['customer_address'],
        'destination_postal_code' => $postal_code, // Added Postal Code
        'destination_area_id' => $destination_area_id,
        'destination_latitude' => $dest_lat,
        'destination_longitude' => $dest_lng,
        'destination_coordinate' => [
        'latitude' => $dest_lat,
        'longitude' => $dest_lng
    ],
    'courier_company' => $order['courier_company'] ?: 'jne', // Fallback
    'courier_type' => strtolower($order['courier_type'] ?: 'reg'),     // Fallback & Lowercase
    'delivery_type' => 'now', // or 'scheduled'
    'order_note' => $order['order_notes'] ?? '',
    'reference_id' => (string) $order_id . '-' . time(), // Append time to allow retries
    'weight' => $total_weight_grams,
    'items' => $items
];

// Add COD Parameters if Payment Method is 'cod'
$payment_method_normalized = strtolower(trim($order['payment_method'] ?? ''));

if ($payment_method_normalized === 'cod') {
    // Format Updated based on specific User JSON Example (Flat Payload Style)
    $payload['destination_cash_on_delivery'] = (int) $order['total_amount'];
    $payload['destination_cash_on_delivery_type'] = '3_days'; // Default is 7_days if not specified
}

// DEBUG: Log the payload request
@file_put_contents('biteship_request_log.txt', print_r($payload, true), FILE_APPEND);

// 4. Call Biteship API
$response = $biteship->createOrder($payload);

if ($response['success']) {
    $biteship_data = $response['data'];
    $tracking_id = $biteship_data['courier']['waybill_id'] ?? $biteship_data['id'] ?? '';

    if ($tracking_id) {
        // DEBUG: Log the entire response to a file to verify the field name
        @file_put_contents('biteship_response_log.txt', print_r($biteship_data, true), FILE_APPEND);

        // Extract Shipping Label URL
        // We ONLY want the PDF/Image label. Do NOT fallback to tracking links here.
        $shipping_label_url = $biteship_data['shipping_label_url'] ?? $biteship_data['shipping_label_link'] ?? null;

        $shipping_label_sql = $shipping_label_url ? "'$shipping_label_url'" : "NULL";

        // Get Biteship Order ID
        $biteship_order_id = $biteship_data['id'] ?? '';

        // Update order with tracking ID and Shipping Label and Biteship ID
        $updateStmt = $conn->prepare("UPDATE orders SET tracking_id = ?, shipping_label_url = ?, biteship_order_id = ?, status = 'ready_to_ship' WHERE id = ?");
        $updateStmt->bind_param("sssi", $tracking_id, $shipping_label_url, $biteship_order_id, $order_id);
        $updateStmt->execute();

        echo json_encode([
            'success' => true,
            'tracking_id' => $tracking_id,
            'message' => 'Pickup request successful, Label Generated.',
            'debug_label' => $shipping_label_url // Return this to frontend too for quick check
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to retrieve tracking ID from response',
            'debug' => $biteship_data
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => $response['message'] ?? 'Biteship API Error',
        'raw' => $response['raw'] ?? null
    ]);
}
