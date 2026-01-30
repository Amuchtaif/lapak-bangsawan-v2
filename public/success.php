<?php
require_once dirname(__DIR__) . "/config/init.php";

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if ($order_id === 0) {
    header("Location: " . BASE_URL);
    exit;
}

// Fetch Order Details including order_number
$stmt = $conn->prepare("
    SELECT o.*, 
    (SELECT SUM(weight) FROM order_items WHERE order_id = o.id) as total_items
    FROM orders o 
    WHERE o.id = ?
");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: " . BASE_URL);
    exit;
}

$order = $result->fetch_assoc();
$order_number = $order['order_number'] ?? str_pad($order['id'], 5, '0', STR_PAD_LEFT);

// Fetch Site Settings for WhatsApp Number
$admin_wa = get_setting('whatsapp_number', '6281234567890');
// Helper to format WA number (remove 0/+, add 62)
$wa_number = preg_replace('/^0|^\+62/', '62', $admin_wa);

// Create WhatsApp Message
$wa_message = "Halo Admin Lapak Bangsawan,\n\n";
$wa_message .= "Saya baru saja membuat pesanan dengan detail berikut:\n";
$wa_message .= "No. Order: *$order_number*\n";
$wa_message .= "Nama: " . $order['customer_name'] . "\n";
$wa_message .= "Total: Rp " . number_format($order['total_amount'], 0, ',', '.') . "\n";
$wa_message .= "Metode Pembayaran: " . strtoupper($order['payment_method']) . "\n\n";
$wa_message .= "Mohon segera diproses ya. Terima kasih!";

$wa_url = "https://wa.me/$wa_number?text=" . urlencode($wa_message);

// Fetch Items for Summary
$items_q = $conn->query("SELECT product_name, weight, subtotal FROM order_items WHERE order_id = $order_id");
?>
<!DOCTYPE html>
<html class="light" lang="id">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Pesanan Berhasil - Lapak Bangsawan</title>
    <link rel="icon" href="<?= BASE_URL ?>assets/images/favicon-laba.png" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
        rel="stylesheet" />
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet" />
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
                    fontFamily: {
                        "display": ["Inter", "sans-serif"]
                    },
                },
            },
        }
    </script>
</head>

<body
    class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-white font-display min-h-screen flex flex-col items-center justify-center p-4">

    <div
        class="max-w-md w-full bg-white dark:bg-slate-900 rounded-3xl shadow-2xl p-8 text-center border border-slate-100 dark:border-slate-800 animate-fade-in-up">

        <!-- Success Icon -->
        <div
            class="w-20 h-20 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center mx-auto mb-6">
            <span class="material-icons-round text-5xl text-green-600 dark:text-green-400">check_circle</span>
        </div>

        <h1 class="text-2xl font-black text-slate-900 dark:text-white mb-2">Pesanan Berhasil Dibuat!</h1>
        <p class="text-slate-500 text-sm mb-6">Terima kasih telah berbelanja di Lapak Bangsawan.</p>

        <!-- Order Number Highlight -->
        <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-xl border border-blue-100 dark:border-blue-800 mb-6">
            <p class="text-xs font-bold text-blue-600 dark:text-blue-400 uppercase tracking-widest mb-1">Nomor Order
                Anda</p>
            <h2 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight select-all text-primary">
                <?= $order_number ?>
            </h2>
            <p class="text-[10px] text-slate-400 mt-2 italic flex items-center justify-center gap-1">
                <span class="material-icons-round text-xs">info</span>
                Simpan nomor ini untuk melacak status pesanan.
            </p>
        </div>

        <!-- Order Summary -->
        <div class="text-left mb-8">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white mb-3">Ringkasan Pesanan</h3>
            <div class="bg-slate-50 dark:bg-slate-800/50 rounded-xl p-4 space-y-3">
                <?php while ($item = $items_q->fetch_assoc()): ?>
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-600 dark:text-slate-300 font-medium truncate w-[60%]">
                            <?= htmlspecialchars($item['product_name']) ?>
                        </span>
                        <span class="text-slate-500">
                            <?= $item['weight'] ?> kg
                        </span>
                    </div>
                <?php endwhile; ?>
                <div
                    class="border-t border-slate-200 dark:border-slate-700 pt-3 flex justify-between font-bold text-slate-900 dark:text-white">
                    <span>Total Pembayaran</span>
                    <span>Rp
                        <?= number_format($order['total_amount'], 0, ',', '.') ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- WhatsApp Button -->
        <!-- WhatsApp Button -->
        <!-- WhatsApp Button -->
        <a href="<?= $wa_url ?>" target="_blank" id="wa-btn"
            class="w-full flex items-center justify-center gap-2 bg-[#25D366] hover:bg-[#128C7E] text-white py-4 px-6 rounded-xl shadow-lg shadow-green-500/30 transition-all mb-6 group transform hover:scale-[1.02]">
            <!-- WhatsApp SVG Icon -->
            <svg class="w-8 h-8 fill-current shrink-0" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
            </svg>
            <span class="text-lg font-bold tracking-wide">Konfirmasi ke Penjual</span>
            <span class="material-icons-round group-hover:translate-x-1 transition-transform">arrow_forward</span>
        </a>

        <!-- Navigation Links -->
        <div class="flex gap-3 justify-center">
            <a href="<?= BASE_URL ?>public/track.php"
                class="text-sm font-bold text-slate-500 hover:text-primary transition-colors py-2 px-4 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800">
                Lacak Pesanan
            </a>
            <span class="text-slate-300 py-2">|</span>
            <a href="<?= BASE_URL ?>"
                class="text-sm font-bold text-slate-500 hover:text-primary transition-colors py-2 px-4 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800">
                Ke Beranda
            </a>
        </div>
    </div>

    <!-- Auto Click Script -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Optional: Auto redirect to WA after 3 seconds
            setTimeout(() => {
                // document.getElementById('wa-btn').click(); 
                // Uncomment line above if you want auto-redirect
            }, 3000);
        });
    </script>
</body>

</html>