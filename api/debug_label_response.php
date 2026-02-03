<?php
// api/debug_label_response.php
// Script to inspect RAW Biteship response for debugging missing labels
require_once dirname(__DIR__) . "/config/init.php";
require_once dirname(__DIR__) . "/helpers/BiteshipService.php";

$orderId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$orderId) {
    die("Please provide ?id=ORDER_ID");
}

echo "<h1>Debug Biteship Response for Order #$orderId</h1>";

// 1. Get Biteship Order ID from DB
$stmt = $conn->prepare("SELECT biteship_order_id, tracking_id FROM orders WHERE id = ?");
$stmt->bind_param("i", $orderId);
$stmt->execute();
$res = $stmt->get_result();
$order = $res->fetch_assoc();

if (!$order) {
    die("Order not found in local DB.");
}

$biteshipId = $order['biteship_order_id'];
echo "<p><strong>Local Biteship ID:</strong> " . ($biteshipId ?: 'MISSING') . "</p>";
echo "<p><strong>Local Tracking ID:</strong> " . ($order['tracking_id'] ?: 'MISSING') . "</p>";

if (!$biteshipId) {
    die("Cannot debug without Biteship ID. Try refetching label first to sync ID.");
}

// 2. Call API
$biteship = new BiteshipService();
$response = $biteship->getOrder($biteshipId);

echo "<h3>API Response Status</h3>";
if ($response['success']) {
    echo "<span style='color:green'>SUCCESS</span>";
} else {
    echo "<span style='color:red'>FAILED: " . htmlspecialchars($response['message']) . "</span>";
}

echo "<h3>Full Data Dump</h3>";
echo "<pre style='background:#f4f4f4; padding:10px; border:1px solid #ccc;'>";
// Print full data
print_r($response);
echo "</pre>";
?>