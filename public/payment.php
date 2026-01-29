<?php
require_once dirname(__DIR__) . "/config/init.php";

$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($order_id === 0) {
    header("Location: " . BASE_URL . "home");
    exit();
}

// Fetch Order
$query = "SELECT * FROM orders WHERE id = $order_id LIMIT 1";
$result = $conn->query($query);

if ($result->num_rows === 0) {
    header("Location: " . BASE_URL . "home");
    exit();
}

$order = $result->fetch_assoc();

// Access Control Logic
if ($order['status'] !== 'unpaid' || $order['payment_method'] !== 'transfer') {
    // If user tries to access payment page for COD or already paid order
    header("Location: " . BASE_URL . "home");
    exit();
}

// Handle Upload (Basic stub implementation or placeholder)
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['payment_proof'])) {

    $file = $_FILES['payment_proof'];

    // Validation
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = "Gagal mengupload file (Error Code: " . $file['error'] . ")";
    } else {
        $allowed = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);

        if (!in_array($mime, $allowed)) {
            $error = "Hanya file gambar (JPG, PNG, WEBP) yang diperbolehkan.";
        } elseif ($file['size'] > 5 * 1024 * 1024) { // 5MB
            $error = "Ukuran file maksimal 5MB.";
        } else {
            // Process Upload
            $upload_dir = dirname(__DIR__) . '/uploads/payment_proofs/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'proof_' . $order_id . '_' . time() . '.' . $ext;
            $destination = $upload_dir . $filename;

            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $db_path = 'uploads/payment_proofs/' . $filename;

                // Update Order
                // Set status to 'pending' so admin sees it, and payment_status to 'waiting_verification'
                $stmt = $conn->prepare("UPDATE orders SET payment_proof = ?, payment_status = 'waiting_verification', status = 'pending' WHERE id = ?");
                $stmt->bind_param("si", $db_path, $order_id);

                if ($stmt->execute()) {
                    $success = "Bukti pembayaran berhasil diupload! Admin akan memverifikasi pesanan Anda.";

                    // Construct WhatsApp Notification
                    $wa_phone = "62859110022099";
                    $wa_msg = "Halo Lapak Bangsawan, saya sudah upload bukti pembayaran untuk:\n\n";
                    $wa_msg .= "*Order Number:* " . $order['order_number'] . "\n";
                    $wa_msg .= "*Total:* Rp " . number_format($order['total_amount'], 0, ',', '.') . "\n\n";
                    $wa_msg .= "Mohon segera diverifikasi. Terima kasih!";
                    $wa_url = "https://wa.me/$wa_phone?text=" . urlencode($wa_msg);

                    echo "<script>
                        alert('$success'); 
                        window.open('$wa_url', '_blank');
                        window.location.href='" . BASE_URL . "home';
                    </script>";
                    exit;
                } else {
                    $error = "Gagal menyimpan data ke database.";
                }
            } else {
                $error = "Gagal memindahkan file upload.";
            }
        }
    }

    if ($error) {
        echo "<script>alert('$error');</script>";
    }
}

?>
<!DOCTYPE html>
<html class="light" lang="id">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Pembayaran Order #
        <?= str_pad($order['id'], 5, '0', STR_PAD_LEFT) ?> - Lapak Bangsawan
    </title>
    <link rel="icon" href="<?= BASE_URL ?>assets/images/favicon-laba.png" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
        rel="stylesheet" />
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#0d59f2",
                        "background-light": "#f5f6f8",
                        "background-dark": "#101622",
                    },
                    fontFamily: { "display": ["Inter", "sans-serif"] },
                },
            },
        }
    </script>
</head>

<body
    class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-white font-display min-h-screen flex flex-col items-center justify-center p-4">

    <div
        class="max-w-md w-full bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 p-8">

        <div class="text-center mb-8">
            <div
                class="size-16 bg-blue-100 dark:bg-blue-900/30 text-primary rounded-full flex items-center justify-center mx-auto mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
            <h1 class="text-2xl font-black mb-2">Selesaikan Pembayaran</h1>
            <p class="text-slate-500 text-sm">Order Number:
                <span
                    class="font-mono font-bold text-slate-700 dark:text-slate-300"><?= htmlspecialchars($order['order_number']) ?></span>
            </p>
        </div>

        <div
            class="bg-slate-50 dark:bg-slate-800 rounded-xl p-6 mb-8 text-center border border-slate-200 dark:border-slate-700">
            <p class="text-sm text-slate-500 uppercase tracking-widest font-bold mb-2">Total Tagihan</p>
            <h2 class="text-3xl font-black text-primary">Rp
                <?= number_format($order['total_amount'], 0, ',', '.') ?>
            </h2>
        </div>

        <div class="space-y-6 mb-8">
            <div class="flex items-start gap-4">
                <div
                    class="flex-shrink-0 size-10 bg-slate-100 dark:bg-slate-800 rounded-lg flex items-center justify-center">
                    <span class="font-bold text-slate-700 dark:text-slate-300">1</span>
                </div>
                <div>
                    <h3 class="font-bold mb-1">Transfer ke Rekening BSI</h3>
                    <p class="text-sm text-slate-500 mb-2">Bank Syariah Indonesia (BSI)</p>
                    <div class="bg-slate-100 dark:bg-slate-800 p-3 rounded-lg flex items-center justify-between cursor-pointer active:bg-slate-200 dark:active:bg-slate-700 transition-colors"
                        onclick="navigator.clipboard.writeText('7252428245'); alert('Nomor rekening disalin!')">
                        <span class="font-mono font-bold tracking-wider">7252428245</span>
                        <span class="text-xs text-primary font-bold">SALIN</span>
                    </div>
                    <p class="text-xs text-slate-400 mt-1">a.n. Shohibudin</p>
                </div>
            </div>

            <div class="flex items-start gap-4">
                <div
                    class="flex-shrink-0 size-10 bg-slate-100 dark:bg-slate-800 rounded-lg flex items-center justify-center">
                    <span class="font-bold text-slate-700 dark:text-slate-300">2</span>
                </div>
                <div>
                    <h3 class="font-bold mb-1">Upload Bukti Transfer</h3>
                    <p class="text-sm text-slate-500">Foto struk atau screenshot bukti transfer Anda.</p>
                </div>
            </div>
        </div>

        <form method="POST" enctype="multipart/form-data" class="space-y-4">
            <div class="relative">
                <input type="file" name="payment_proof" required accept="image/*" class="w-full text-sm text-slate-500
                file:mr-4 file:py-2.5 file:px-4
                file:rounded-full file:border-0
                file:text-sm file:font-semibold
                file:bg-primary/10 file:text-primary
                hover:file:bg-primary/20
                cursor-pointer
                border border-slate-200 dark:border-slate-700 rounded-xl
              " />
            </div>

            <button type="submit"
                class="w-full bg-primary hover:bg-blue-600 text-white font-bold py-4 rounded-xl shadow-lg shadow-blue-500/30 transition-all flex justify-center items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                </svg>
                Kirim Bukti Pembayaran
            </button>
        </form>

        <div class="mt-6 text-center">
            <a href="<?= BASE_URL ?>home"
                class="text-sm font-bold text-slate-400 hover:text-slate-600 transition-colors">Kembali ke Beranda</a>
        </div>
    </div>

</body>

</html>