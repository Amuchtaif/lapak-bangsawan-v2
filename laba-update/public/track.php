<?php
require_once dirname(__DIR__) . "/config/init.php";

$order_number = $_GET['order_number'] ?? '';
$phone = $_GET['phone'] ?? '';
$error = '';
$order = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' || ($order_number && $phone)) {
    $order_number = mysqli_real_escape_string($conn, $_POST['order_number'] ?? $order_number);
    $phone = mysqli_real_escape_string($conn, $_POST['phone'] ?? $phone);

    if (empty($order_number) || empty($phone)) {
        $error = "Silakan masukkan Nomor Order dan Nomor HP.";
    } else {
        $query = "SELECT * FROM orders WHERE order_number = '$order_number' AND customer_phone = '$phone'";
        $res = $conn->query($query);
        if ($res && $res->num_rows > 0) {
            $order = $res->fetch_assoc();
        } else {
            $error = "Nomor Order atau Nomor HP tidak ditemukan/tidak cocok.";
        }
    }
}
?>
<!DOCTYPE html>
<html class="light" lang="id">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Lacak Pesanan - Lapak Bangsawan</title>
    <link rel="icon" href="<?= BASE_URL ?>assets/images/favicon-laba.png" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
        rel="stylesheet" />
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
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
    class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-white font-display min-h-screen flex flex-col">

    <!-- Header -->
    <?php include ROOT_PATH . "includes/public_header.php"; ?>

    <main class="flex-grow max-w-4xl mx-auto px-4 w-full pt-12 md:pt-20 mb-12">
        <div class="text-center mb-10">
            <h1 class="text-3xl font-black text-slate-900 dark:text-white mb-2">Lacak Pesanan Anda</h1>
            <p class="text-slate-500">Masukkan detail pesanan untuk melihat status pengiriman real-time.</p>
        </div>

        <!-- Search Form -->
        <div
            class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl shadow-slate-200/50 dark:shadow-none border border-slate-200 dark:border-slate-800 p-6 md:p-10 mb-8 max-w-2xl mx-auto">
            <form method="POST" class="space-y-6">
                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Nomor Order</label>
                    <div class="relative group">
                        <span
                            class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary transition-colors">receipt</span>
                        <input type="text" name="order_number" required placeholder="Contoh: LB-2026..."
                            value="<?= htmlspecialchars($order_number) ?>"
                            class="w-full pl-12 pr-4 py-3.5 rounded-xl border-slate-200 bg-slate-50 dark:bg-slate-800 dark:border-slate-700 focus:ring-primary focus:border-primary transition-all font-mono font-bold uppercase">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Nomor HP</label>
                    <div class="relative group">
                        <span
                            class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary transition-colors">phone_iphone</span>
                        <input type="tel" name="phone" required placeholder="0812..."
                            value="<?= htmlspecialchars($phone) ?>"
                            class="w-full pl-12 pr-4 py-3.5 rounded-xl border-slate-200 bg-slate-50 dark:bg-slate-800 dark:border-slate-700 focus:ring-primary focus:border-primary transition-all font-bold">
                    </div>
                </div>

                <button type="submit"
                    class="w-full bg-primary hover:bg-blue-700 text-white font-bold py-4 px-6 rounded-xl shadow-lg shadow-blue-500/30 transition-all flex justify-center items-center gap-2 active:scale-95">
                    <span class="material-symbols-outlined">search</span>
                    Lacak Pesanan
                </button>
            </form>

            <?php if ($error): ?>
                <div
                    class="mt-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-100 dark:border-red-900/30 rounded-xl flex items-center gap-3 text-red-600 dark:text-red-400">
                    <span class="material-symbols-outlined">error</span>
                    <p class="text-sm font-medium">
                        <?= $error ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Result Display -->
        <?php if ($order): ?>
            <div class="animate-in fade-in slide-in-from-bottom-4 duration-500 max-w-2xl mx-auto">
                <div
                    class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl shadow-slate-200/50 dark:shadow-none border border-slate-200 dark:border-slate-800 overflow-hidden">
                    <!-- Status Header -->
                    <div class="bg-primary p-6 md:p-8 text-white">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div>
                                <p class="text-blue-100 text-xs font-bold uppercase tracking-widest mb-1">Status Saat Ini
                                </p>
                                <h2 class="text-2xl font-black uppercase tracking-tight">
                                    <?= ucfirst($order['status']) ?>
                                </h2>
                            </div>
                            <div class="bg-white/20 backdrop-blur-md px-4 py-2 rounded-lg text-sm font-bold">
                                #
                                <?= $order['order_number'] ?>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 md:p-8 space-y-8">
                        <!-- Progress (Simplified) -->
                        <!-- Detailed Timeline (Vertical) -->
                        <div class="border-t border-slate-100 dark:border-slate-800 pt-6">
                            <h3 class="font-bold text-slate-800 dark:text-white mb-6 flex items-center gap-2">
                                <span class="material-symbols-outlined text-primary">history</span>
                                Riwayat Pesanan
                            </h3>

                            <div class="relative pl-4 border-l-2 border-slate-100 dark:border-slate-800 space-y-8"
                                id="tracking-timeline">
                                <!-- Default Status from Database -->
                                <div class="relative">
                                    <div
                                        class="absolute -left-[21px] top-1 rounded-full bg-primary border-4 border-white dark:border-slate-900 w-3.5 h-3.5">
                                    </div>
                                    <div class="flex flex-col">
                                        <span
                                            class="text-sm font-bold text-slate-900 dark:text-white uppercase"><?= $order['status'] ?></span>
                                        <span
                                            class="text-xs text-slate-400 mt-1"><?= date('d M Y H:i', strtotime($order['created_at'])) ?>
                                            (Waktu Pemesanan)</span>
                                        <p class="text-sm text-slate-600 dark:text-slate-400 mt-2">
                                            Status terakhir pesanan Anda di sistem kami.
                                        </p>
                                    </div>
                                </div>

                                <!-- Courier Info (Local) -->
                                <?php if (empty($order['courier_company']) || in_array(strtolower($order['courier_company']), ['local', 'internal', 'pickup'])): ?>
                                    <div class="relative">
                                        <div
                                            class="absolute -left-[21px] top-1 rounded-full bg-blue-500 border-4 border-white dark:border-slate-900 w-3.5 h-3.5">
                                        </div>
                                        <div class="flex flex-col">
                                            <span
                                                class="text-sm font-bold text-slate-900 dark:text-white uppercase">Kurir
                                                Toko / Internal</span>
                                            <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
                                                Pengiriman ditangani langsung oleh tim Lapak Bangsawan.
                                            </p>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <!-- Loading State for External Tracking -->
                                <?php if ($order['tracking_id'] && !empty($order['courier_company']) && !in_array(strtolower($order['courier_company']), ['local', 'internal', 'pickup'])): ?>
                                    <div id="loading-tracking" class="relative">
                                        <div
                                            class="absolute -left-[21px] top-1 rounded-full bg-slate-200 dark:bg-slate-700 border-4 border-white dark:border-slate-900 w-3.5 h-3.5 animate-pulse">
                                        </div>
                                        <div class="flex flex-col gap-2">
                                            <span class="h-4 w-32 bg-slate-100 dark:bg-slate-800 rounded animate-pulse"></span>
                                            <span class="h-3 w-48 bg-slate-100 dark:bg-slate-800 rounded animate-pulse"></span>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if ($order['tracking_id'] && !empty($order['courier_company']) && !in_array(strtolower($order['courier_company']), ['local', 'internal', 'pickup'])): ?>
                            <script>
                                document.addEventListener('DOMContentLoaded', function () {
                                    const orderNo = '<?= $order['order_number'] ?>'; // Must use Order Number
                                    const phoneNo = '<?= $order['customer_phone'] ?>'; // Must use Phone

                                    const formData = new FormData();
                                    formData.append('order_number', orderNo);
                                    formData.append('phone_number', phoneNo);

                                    fetch('<?= BASE_URL ?>api/track_order.php', {
                                        method: 'POST',
                                        body: formData
                                    })
                                        .then(response => response.json())
                                        .then(data => {
                                            const timelineContainer = document.getElementById('tracking-timeline');
                                            const loader = document.getElementById('loading-tracking');
                                            if (loader) loader.remove();

                                            if (data.success && data.data && data.data.history) {
                                                // Process history (reverse to show latest first)
                                                const history = data.data.history; // Biteship usually returns oldest first, check docs. 
                                                // Usually for timeline UI, we want NEWEST at TOP.
                                                // Biteship history: [ {status, time, note}, ... ]

                                                // Append history items
                                                history.reverse().forEach(item => { // Reverse so latest is top
                                                    const date = new Date(item.updated_at);
                                                    const formattedDate = date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });

                                                    const html = `
                                                    <div class="relative animate-in fade-in slide-in-from-bottom-2 duration-500">
                                                        <div class="absolute -left-[21px] top-1 rounded-full bg-green-500 border-4 border-white dark:border-slate-900 w-3.5 h-3.5 shadow-sm shadow-green-500/50"></div>
                                                        <div class="flex flex-col">
                                                            <span class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wide text-green-600 dark:text-green-400">
                                                                ${item.status}
                                                            </span>
                                                            <span class="text-[10px] font-bold text-slate-400 mt-0.5 tracking-wider uppercase">${formattedDate}</span>
                                                            <p class="text-sm font-medium text-slate-700 dark:text-slate-300 mt-2 bg-slate-50 dark:bg-slate-800/50 p-3 rounded-lg border border-slate-100 dark:border-slate-700/50">
                                                                ${item.note || 'Posisi paket diperbarui.'}
                                                            </p>
                                                        </div>
                                                    </div>
                                                `;
                                                    timelineContainer.insertAdjacentHTML('afterbegin', html); // Insert at top
                                                });
                                            }
                                        })
                                        .catch(err => console.error('Gagal memuat tracking:', err));
                                });
                            </script>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <?php include ROOT_PATH . "includes/public_footer.php"; ?>
</body>

</html>