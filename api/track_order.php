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
        jsonResponse(true, 'Data tracking ditemukan.', $trackingData['data']);
    } else {
        // Jika gagal dari Biteship (misal resi belum terlacak di sistem kurir)
        jsonResponse(false, 'Gagal melacak resi: ' . ($trackingData['message'] ?? 'Kesalahan tidak diketahui.'));
    }

} catch (Exception $e) {
    jsonResponse(false, 'Terjadi kesalahan sistem: ' . $e->getMessage());
}
