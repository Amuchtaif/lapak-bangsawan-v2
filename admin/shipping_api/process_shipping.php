<?php
require_once "../auth_session.php";
require_once "../../config/init.php";
require_once "../../helpers/BiteshipService.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$order_id = intval($_POST['order_id'] ?? 0);

if ($order_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid Order ID.']);
    exit;
}

// 1. Fetch Order Data
$order_q = $conn->query("SELECT * FROM orders WHERE id = $order_id");
if ($order_q->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Order not found.']);
    exit;
}
$order = $order_q->fetch_assoc();

// Check if already processed
if (!empty($order['tracking_id'])) {
    echo json_encode(['success' => false, 'message' => 'Order already has a tracking ID.']);
    exit;
}

// Check if courier info is present
if (empty($order['courier_company']) || empty($order['destination_area_id'])) {
    echo json_encode(['success' => false, 'message' => 'Courier or Destination Area information is missing.']);
    exit;
}

// 2. Fetch Order Items
$items_res = $conn->query("SELECT * FROM order_items WHERE order_id = $order_id");
$biteship_items = [];
while ($item = $items_res->fetch_assoc()) {
    $biteship_items[] = [
        'name' => $item['product_name'],
        'quantity' => (int) ($item['weight'] > 0 ? ceil($item['weight']) : 1), // Standardizing quantity for fulfillment
        'value' => (int) $item['price_per_kg'],
        'weight' => 1000 // Simplified: 1kg per quantity unit
    ];
}

// 3. Prepare Biteship Order Data
// Documentation: https://api.biteship.com/v1/orders
$orderData = [
    'shipper_contact_name' => 'Lapak Bangsawan',
    'shipper_contact_phone' => '0859110022099',
    'shipper_contact_email' => 'admin@lapakbangsawan.com',
    'shipper_organization' => 'Lapak Bangsawan',
    'origin_contact_name' => 'Lapak Bangsawan',
    'origin_contact_phone' => '0859110022099',
    'origin_address' => 'Jl. Bangsawan No. 1, Jakarta', // Example Address
    'origin_area_id' => BITESHIP_ORIGIN_AREA_ID,

    'destination_contact_name' => $order['customer_name'],
    'destination_contact_phone' => $order['customer_phone'],
    'destination_contact_email' => $order['email'] ?? '',
    'destination_address' => $order['customer_address'],
    'destination_area_id' => $order['destination_area_id'],

    'courier_company' => $order['courier_company'],
    'courier_type' => $order['courier_type'],
    'delivery_type' => 'pickup', // Most common
    'order_note' => $order['order_notes'],
    'items' => $biteship_items
];

// 4. Call Biteship API
$biteship = new BiteshipService();
$result = $biteship->createOrder($orderData);

if ($result['success']) {
    $biteship_data = $result['data'];
    $biteship_order_id = mysqli_real_escape_string($conn, $biteship_data['id']);
    $tracking_id = mysqli_real_escape_string($conn, $biteship_data['courier']['waybill_id'] ?? '');

    // Update Database
    $update_sql = "UPDATE orders SET 
                    biteship_order_id = '$biteship_order_id', 
                    tracking_id = '$tracking_id', 
                    status = 'completed' 
                   WHERE id = $order_id";

    if ($conn->query($update_sql)) {
        echo json_encode([
            'success' => true,
            'message' => 'Shipping processed successfully!',
            'tracking_id' => $tracking_id
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Shipping processed but failed to update local database: ' . $conn->error]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Biteship Error: ' . ($result['message'] ?? 'Unknown error'),
        'details' => $result['raw'] ?? null
    ]);
}
