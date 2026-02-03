<?php
require_once dirname(__DIR__) . "/config/init.php";
require_once dirname(__DIR__) . "/helpers/BiteshipService.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    // Also allow POST if needed, but GET is fine for sync usually. Let's strictly follow request which implies GET or simple call.
    // The user said "panggil api/sync_biteship_id.php" which usually implies XHR. I'll support GET/POST.
}

$orderId = isset($_REQUEST['order_id']) ? intval($_REQUEST['order_id']) : 0;

if (!$orderId) {
    echo json_encode(['success' => false, 'message' => 'Missing Order ID']);
    exit;
}

// 1. Get Order Data
$stmt = $conn->prepare("SELECT id, tracking_id, biteship_order_id FROM orders WHERE id = ?");
$stmt->bind_param("i", $orderId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Order not found']);
    exit;
}

$order = $result->fetch_assoc();
$localWaybill = $order['tracking_id'];

if (!empty($order['biteship_order_id'])) {
    echo json_encode(['success' => true, 'message' => 'Biteship ID already exists', 'biteship_id' => $order['biteship_order_id']]);
    exit;
}

if (empty($localWaybill)) {
    echo json_encode(['success' => false, 'message' => 'Tracking ID/Waybill is missing locally. Cannot sync.']);
    exit;
}

// 2. Fetch Orders from Biteship
$biteship = new BiteshipService();
$response = $biteship->retrieveOrders(50); // Get last 50 orders

if (!$response['success']) {
    echo json_encode(['success' => false, 'message' => 'Failed to retrieve orders from Biteship: ' . ($response['message'] ?? 'Unknown error')]);
    exit;
}

$biteshipOrders = $response['data']['orders'] ?? [];
$foundId = null;

// 3. Loop and Match
// We match based on waybill_id usually found in 'courier' object inside order
foreach ($biteshipOrders as $bOrder) {
    // Check courier object
    $bWaybill = $bOrder['courier']['waybill_id'] ?? '';

    // Fallback: sometimes waybill might be in other fields or identical to ID? No, waybill is specific.
    // Also strict comparison? Let's use loose, or trim.
    if (trim((string) $bWaybill) === trim((string) $localWaybill)) {
        $foundId = $bOrder['id'];
        break;
    }
}

if ($foundId) {
    // 4. Update Database
    $updateStmt = $conn->prepare("UPDATE orders SET biteship_order_id = ? WHERE id = ?");
    $updateStmt->bind_param("si", $foundId, $orderId);

    if ($updateStmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'ID berhasil disinkronisasi',
            'biteship_id' => $foundId
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database Update Failed: ' . $conn->error]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => "Order with Waybill $localWaybill not found in Biteship last 50 orders."
    ]);
}
?>