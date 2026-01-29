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
        'weight' => (int) $weight_grams,
        'category' => 'food'
    ];
}

// 3. Prepare Biteship Payload
$biteship = new BiteshipService();

// We need destination_area_id. If missing in order, try to search it?
// Usually, it should be saved during checkout.
$destination_area_id = $order['destination_area_id'] ?? '';

if (empty($destination_area_id)) {
    echo json_encode(['success' => false, 'message' => 'Destination Area ID is missing for this order.']);
    exit;
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
    'destination_contact_name' => $order['customer_name'],
    'destination_contact_phone' => $order['customer_phone'],
    'destination_address' => $order['customer_address'],
    'destination_area_id' => $destination_area_id,
    'destination_latitude' => $order['destination_latitude'] ? (float)$order['destination_latitude'] : null,
    'destination_longitude' => $order['destination_longitude'] ? (float)$order['destination_longitude'] : null,
    'courier_company' => $order['courier_company'] ?: 'jne', // Fallback
    'courier_type' => strtolower($order['courier_type'] ?: 'reg'),     // Fallback & Lowercase
    'delivery_type' => 'now', // or 'scheduled'
    'order_note' => $order['order_notes'] ?? '',
    'weight' => $total_weight_grams,
    'items' => $items
];

// 4. Call Biteship API
$response = $biteship->createOrder($payload);

if ($response['success']) {
    $biteship_data = $response['data'];
    $tracking_id = $biteship_data['courier']['waybill_id'] ?? $biteship_data['id'] ?? '';

    if ($tracking_id) {
        // Update order with tracking ID
        $conn->query("UPDATE orders SET tracking_id = '$tracking_id', status = 'confirmed' WHERE id = $order_id");
        echo json_encode([
            'success' => true,
            'tracking_id' => $tracking_id,
            'message' => 'Pickup request successful'
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
