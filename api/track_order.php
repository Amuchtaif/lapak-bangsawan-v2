<?php
header('Content-Type: application/json');

// Helper untuk response JSON
function jsonResponse($success, $message, $data = null)
{
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

// 1. Terima Input
$orderNumber = $_POST['order_number'] ?? '';
$phoneNumber = $_POST['phone_number'] ?? '';

if (empty($orderNumber) || empty($phoneNumber)) {
    jsonResponse(false, 'Order ID dan Nomor Telepon wajib diisi.');
}

// Load Config & Helper
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/BiteshipService.php';

try {
    // 2. Verifikasi Database
    // Note: Di tabel orders, kolom telepon bernama 'customer_phone'
    $sql = "SELECT tracking_id, courier_company, status 
            FROM orders 
            WHERE order_number = ? AND customer_phone = ? 
            LIMIT 1";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $orderNumber, $phoneNumber);
    $stmt->execute();
    $result = $stmt->get_result();

    // 3. Logika Percabangan
    if ($result->num_rows === 0) {
        jsonResponse(false, 'Data pesanan tidak ditemukan. Mohon periksa kembali ID Pesanan dan Nomor Telepon Anda.');
    }

    $order = $result->fetch_assoc();
    $waybillId = $order['tracking_id'];
    $courierCode = $order['courier_company']; // Asumsi kolom ini berisi kode kurir (jne, sicepat, dll)

    // Cek apakah resi sudah ada
    if (empty($waybillId)) {
        $statusMsg = 'Pesanan sedang diproses.';
        if ($order['status'] === 'cancelled') {
            $statusMsg = 'Pesanan telah dibatalkan.';
        }
        jsonResponse(false, $statusMsg . ' Nomor resi belum tersedia.');
    }

    // 4. Panggil Biteship API
    $biteship = new BiteshipService();
    $trackingData = $biteship->getTracking($waybillId, $courierCode);

    if ($trackingData['success']) {
        // --- AUTO-SYNC LOGIC START ---
        $biteshipStatus = $trackingData['data']['status'] ?? '';

        // Map Biteship status to Local Status
        // Biteship: placed, scheduled, confirmed, allocated, picking_up, picked, dropping_off, return_in_transit, delivered, rejected, courier_not_found, returned, cancelled, disposed
        $newLocalStatus = '';

        switch ($biteshipStatus) {
            case 'picking_up':
            case 'picked':
            case 'dropping_off':
                $newLocalStatus = 'shipped'; // Dalam Pengiriman
                break;
            case 'delivered':
                $newLocalStatus = 'delivered'; // Selesai / Diterima
                break;
            case 'cancelled':
            case 'rejected':
            case 'courier_not_found':
            case 'returned':
                $newLocalStatus = 'cancelled';
                break;
            case 'confirmed':
            case 'allocated':
                $newLocalStatus = 'ready_to_ship';
                break;
        }

        // Update DB only if status is valid and different from current status
        if ($newLocalStatus && $newLocalStatus !== $order['status']) {
            // Prevent reverting 'completed' if already set manually (optional safety)
            // But here we trust Biteship status more for logistics
            if ($order['status'] !== 'completed' && $order['status'] !== 'cancelled') {
                // Use order_number from input since we retrieved data using it
                $updateStmt = $conn->prepare("UPDATE orders SET status = ? WHERE order_number = ?");
                $updateStmt->bind_param("ss", $newLocalStatus, $orderNumber);
                $updateStmt->execute();
            }
        }
        // --- AUTO-SYNC LOGIC END ---

        jsonResponse(true, 'Data tracking ditemukan.', $trackingData['data']);
    } else {
        // Jika gagal dari Biteship (misal resi belum terlacak di sistem kurir)
        jsonResponse(false, 'Gagal melacak resi: ' . ($trackingData['message'] ?? 'Kesalahan tidak diketahui.'));
    }

} catch (Exception $e) {
    jsonResponse(false, 'Terjadi kesalahan sistem: ' . $e->getMessage());
}
