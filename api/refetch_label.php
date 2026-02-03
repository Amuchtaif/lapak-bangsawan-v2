<?php
// api/refetch_label.php
header('Content-Type: application/json'); // Default to JSON unless we redirect
require_once dirname(__DIR__) . "/config/init.php";
require_once dirname(__DIR__) . "/helpers/BiteshipService.php";

// Helper for JSON Error
function sendJsonError($message)
{
    echo json_encode([
        'status' => 'error',
        'message' => $message
    ]);
    exit;
}

// 1. Validate Input
$orderId = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$orderId) {
    sendJsonError("Invalid Order ID");
}

// 2. Fetch Local Order Data
$stmt = $conn->prepare("SELECT id, biteship_order_id, tracking_id, shipping_label_url FROM orders WHERE id = ?");
$stmt->bind_param("i", $orderId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    sendJsonError("Order tidak ditemukan di database lokal.");
}

$order = $result->fetch_assoc();
$biteshipOrderId = $order['biteship_order_id'];
$waybillId = $order['tracking_id'];

$biteship = new BiteshipService();

// 3. Resolve Biteship ID if missing (Critical for GET /v1/orders/{id})
if (empty($biteshipOrderId)) {
    if (empty($waybillId)) {
        sendJsonError("Biteship Order ID dan Tracking ID kosong. Tidak bisa melacak ke API.");
    }

    // Attempt to search via retrieveOrders (listing) using Tracking ID
    $searchRes = $biteship->retrieveOrders(50); // Fetch recent 50 orders

    $found = false;
    if ($searchRes['success'] && !empty($searchRes['data']['orders'])) {
        foreach ($searchRes['data']['orders'] as $bOrder) {
            // Check Courier Waybill
            $bWaybill = $bOrder['courier']['waybill_id'] ?? '';
            // Or maybe the ID itself matches the tracking ID (rare but possible)

            if (trim((string) $bWaybill) === trim((string) $waybillId)) {
                $biteshipOrderId = $bOrder['id'];

                // Update Local DB immediately to save future searches
                $upd = $conn->prepare("UPDATE orders SET biteship_order_id = ? WHERE id = ?");
                $upd->bind_param("si", $biteshipOrderId, $orderId);
                $upd->execute();

                $found = true;
                break;
            }
        }
    }

    if (!$found) {
        sendJsonError("ID Biteship tidak ditemukan. Sinkronisasi gagal.");
    }
}

// 4. Call Biteship API (Get Single Order)
$response = $biteship->getOrder($biteshipOrderId);

if (!$response['success']) {
    sendJsonError("Gagal mengambil data dari Biteship API: " . ($response['message'] ?? 'Unknown Error'));
}

$data = $response['data'];

// 5. Intelligent Link Finding
$pdfLink = null;

// Collect Candidates
// Note: Usually standard is at root 'shipping_label_url'. 
// Some responses might nest it.
$candidates = [
    $data['shipping_label_url'] ?? null,
    $data['shipping_label_link'] ?? null, // Legacy?
    $data['courier']['shipping_label_url'] ?? null,
    $data['courier']['link'] ?? null, // Usually tracking, but we check validation
    $order['shipping_label_url'] ?? null  // Even check our own DB? No, we want to REFETCH.
];

// Validate Candidates
foreach ($candidates as $candidateUrl) {
    if (empty($candidateUrl))
        continue;

    // Strict Filter: Reject Tracking Links
    if (strpos($candidateUrl, 'track.biteship.com') !== false) {
        continue; // REJECT
    }

    // Optional: Prefer PDF/Download indicators?
    // User said: "Link yang benar biasanya mengandung kata pdf atau download."
    // We won't strictly enforce it, but if we found one earlier, great.
    // For now, basically if it's NOT a tracking link, we assume it's the label.

    $pdfLink = $candidateUrl;
    break; // Found one!
}

// 6. Outcome Decisions
if ($pdfLink) {
    // Correct Link Found!

    // Update Database
    $updStmt = $conn->prepare("UPDATE orders SET shipping_label_url = ? WHERE id = ?");
    $updStmt->bind_param("si", $pdfLink, $orderId);
    $updStmt->execute();

    // Redirect User to Download
    // Note: We sent Content-Type: json earlier. We must clear headers or just overwrite?
    // header() calls replace previous same-name headers.
    // But Content-Type?
    // Since we are doing a Location redirect, the body content type doesn't matter much.
    header("Location: " . $pdfLink);
    exit;

} else {
    // No PDF found
    sendJsonError("PDF Resi belum digenerate oleh kurir. Silakan coba 5 menit lagi atau cek di dashboard Biteship.");
}
?>