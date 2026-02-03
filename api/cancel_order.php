<?php
// Suppress warnings/notices that might break JSON
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', 0);

// Buffer output to catch potential stray whitespace/warnings from includes
ob_start();

require_once dirname(__DIR__) . "/config/init.php";
require_once dirname(__DIR__) . "/helpers/BiteshipService.php";

// RE-DISABLE errors because init.php might have turned them on
ini_set('display_errors', 0);
error_reporting(0);

// Clear any accidental output from includes
ob_clean();

header('Content-Type: application/json');

// Helper function to return JSON and exit
function sendResponse($success, $message, $data = null)
{
    // Ensure buffer is ignored/cleaned
    if (ob_get_length())
        ob_clean();

    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

try {
    // 0. Method Check
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method. POST required.');
    }

    // 1. Validate Input
    $orderId = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
    $reason = isset($_POST['reason']) ? trim($_POST['reason']) : '';

    if ($orderId <= 0) {
        throw new Exception('Invalid Order ID.');
    }
    if (empty($reason)) {
        throw new Exception('Alasan pembatalan wajib diisi.');
    }

    // 2. Database Lookup
    $stmt = $conn->prepare("SELECT id, status, biteship_order_id FROM orders WHERE id = ?");
    if (!$stmt) {
        throw new Exception("Database prepare failed: " . $conn->error);
    }
    $stmt->bind_param("i", $orderId);
    if (!$stmt->execute()) {
        throw new Exception("Database execute failed: " . $stmt->error);
    }
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Order tidak ditemukan di database.');
    }

    $order = $result->fetch_assoc();
    $biteshipOrderId = $order['biteship_order_id'];
    $currentStatus = $order['status'];

    // 3. Check Current Status
    if ($currentStatus === 'cancelled') {
        sendResponse(false, 'Pesanan sudah berstatus dibatalkan.');
    }
    if ($currentStatus === 'completed' || $currentStatus === 'delivered') {
        sendResponse(false, 'Pesanan sudah selesai/diterima, tidak dapat dibatalkan.');
    }

    $shouldCancelLocally = false;
    $successResultMessage = '';

    // 4. Biteship Logic
    if (empty($biteshipOrderId)) {
        // Case A: No Biteship ID associated -> Local Cancel Only
        $shouldCancelLocally = true;
        $successResultMessage = 'Pesanan dibatalkan secara lokal (ID Biteship tidak ditemukan).';
    } else {
        // Case B: Has Biteship ID -> Call API to cancel
        $biteship = new BiteshipService();
        $apiResult = $biteship->cancelOrder($biteshipOrderId, $reason);

        if ($apiResult['success']) {
            // API Cancellation Success
            $shouldCancelLocally = true;
            $successResultMessage = 'Pesanan berhasil dibatalkan di sistem Kurir & Lokal.';
        } else {
            // API Cancellation Failed
            $errorMsg = $apiResult['message'] ?? 'Unknown API Error';

            // SPECIAL HANDLING: "Not Found" logic
            // If Biteship says ID not found, it means we are out of sync, but we should allow local cancel.
            if (stripos($errorMsg, 'tidak ditemukan') !== false || stripos($errorMsg, 'not found') !== false) {
                $shouldCancelLocally = true;
                $successResultMessage = 'Pesanan dibatalkan lokal (Data pesanan tidak ditemukan di sistem kurir).';
            } else {
                // STRICT BLOCKER: Courier is on the way or other restriction
                // Do NOT update local database.
                throw new Exception("Gagal membatalkan via Kurir: " . $errorMsg);
            }
        }
    }

    // 5. Update Local Database
    if ($shouldCancelLocally) {
        $updateStmt = $conn->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ?");
        $updateStmt->bind_param("i", $orderId);

        if ($updateStmt->execute()) {
            sendResponse(true, $successResultMessage);
        } else {
            throw new Exception("Gagal mengupdate status database lokal: " . $conn->error);
        }
    }

} catch (Exception $e) {
    // 6. Global Error Handler
    // Log error to server log for debugging
    error_log("[CancelOrder API Error] " . $e->getMessage());

    // Return structured JSON error
    sendResponse(false, $e->getMessage());
}
?>