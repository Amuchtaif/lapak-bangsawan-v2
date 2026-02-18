<?php
require("auth_session.php");
require_once dirname(__DIR__) . "/config/init.php";

$userId = $_SESSION['user_id'];
$success_msg = '';
$error_msg = '';

// Update Site Settings for Shipping
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_shipping'])) {
    $max_dist = mysqli_real_escape_string($conn, $_POST['shipping_max_distance']);
    $rate_km = mysqli_real_escape_string($conn, $_POST['shipping_rate_per_km']);
    
    $conn->query("INSERT INTO site_settings (setting_key, setting_value) VALUES ('shipping_max_distance', '$max_dist') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
    $conn->query("INSERT INTO site_settings (setting_key, setting_value) VALUES ('shipping_rate_per_km', '$rate_km') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
    
    $success_msg = "Pengaturan pengiriman berhasil diperbarui.";
}

// Fetch current shipping settings
$shipping_max_distance = get_setting('shipping_max_distance', '15');
$shipping_rate_per_km = get_setting('shipping_rate_per_km', '1000');
?>
<!DOCTYPE html>
<html class="light" lang="id">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Pengaturan Kurir - Lapak Bangsawan</title>
    <link rel="icon" href="<?= BASE_URL ?>assets/images/favicon-laba.png" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&amp;display=swap"
        rel="stylesheet" />
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet" />
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#0d59f2",
                        "surface-light": "#ffffff",
                        "surface-dark": "#1e293b",
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
    class="bg-background-light dark:bg-background-dark text-slate-600 dark:text-slate-300 font-display antialiased flex h-screen overflow-hidden">
    <?php include ROOT_PATH . "includes/admin/sidebar.php"; ?>

    <main class="flex-1 flex flex-col h-full relative overflow-hidden">
        <?php $page_title = "Pengaturan Kurir";
        include ROOT_PATH . "includes/admin/header.php"; ?>

        <div class="flex-1 overflow-auto p-6">
            <div class="w-full max-w-full mx-auto">
                <?php if ($success_msg): ?>
                    <div
                        class="bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-700 rounded-lg p-4 mb-6 flex items-start gap-3 shadow-sm auto-close-alert transition-opacity duration-500">
                        <span class="material-icons-round text-green-500">check_circle</span>
                        <div>
                            <h3 class="font-medium text-slate-900 dark:text-white">Berhasil</h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400"><?php echo $success_msg; ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($error_msg): ?>
                    <div
                        class="bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-700 rounded-lg p-4 mb-6 flex items-start gap-3 shadow-sm auto-close-alert transition-opacity duration-500">
                        <span class="material-icons-round text-red-500">error</span>
                        <div>
                            <h3 class="font-medium text-slate-900 dark:text-white">Gagal</h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400"><?php echo $error_msg; ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <div
                    class="bg-surface-light dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm p-6 md:p-8">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="size-10 rounded-lg bg-primary/10 text-primary flex items-center justify-center">
                            <span class="material-icons-round">local_shipping</span>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-slate-900 dark:text-white">Pengaturan Kurir Lokal</h2>
                            <p class="text-xs text-slate-500">Konfigurasi jangkauan dan tarif pengantaran internal toko.</p>
                        </div>
                    </div>

                    <form action="" method="POST" class="space-y-6">
                        <input type="hidden" name="update_shipping" value="1">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Jarak Maksimal Pengiriman (Km)</label>
                                <div class="relative">
                                    <input type="number" step="0.1" name="shipping_max_distance"
                                        value="<?php echo htmlspecialchars($shipping_max_distance); ?>"
                                        class="w-full rounded-lg border-slate-200 bg-slate-50 dark:bg-slate-800 dark:border-slate-700 focus:ring-primary focus:border-primary pl-4 pr-12 py-2.5">
                                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-sm text-slate-400 font-medium">Km</span>
                                </div>
                                <p class="text-xs text-slate-500 mt-2">Pesanan di luar jarak ini tidak akan menampilkan opsi kurir internal.</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Tarif per Kilometer (IDR)</label>
                                <div class="relative">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm text-slate-400 font-medium">Rp</span>
                                    <input type="number" name="shipping_rate_per_km"
                                        value="<?php echo htmlspecialchars($shipping_rate_per_km); ?>"
                                        class="w-full rounded-lg border-slate-200 bg-slate-50 dark:bg-slate-800 dark:border-slate-700 focus:ring-primary focus:border-primary pl-10 pr-4 py-2.5">
                                </div>
                                <p class="text-xs text-slate-500 mt-2">Biaya per kilometer (floor). Jarak < 1km akan otomatis gratis.</p>
                            </div>
                        </div>

                        <div class="flex justify-end pt-4">
                            <button type="submit"
                                class="bg-primary hover:bg-blue-700 text-white font-bold py-2.5 px-8 rounded-lg transition-all shadow-lg shadow-blue-500/20 active:scale-95">
                                Simpan Pengaturan
                            </button>
                        </div>
                    </form>
                </div>

                <div class="mt-8 bg-blue-50 dark:bg-blue-900/10 border border-blue-100 dark:border-blue-900/30 rounded-xl p-6">
                    <h3 class="text-sm font-bold text-blue-800 dark:text-blue-300 flex items-center gap-2 mb-2">
                        <span class="material-icons-round text-lg">info</span>
                        Cara Kerja Kalkulasi:
                    </h3>
                    <ul class="text-xs text-blue-700 dark:text-blue-400 space-y-2 list-disc pl-5">
                        <li>Sistem menggunakan koordinat GPS (Latitude/Longitude) untuk menghitung jarak lurus (Haversine).</li>
                        <li><b>Gratis Ongkir:</b> Jika jarak di bawah 1.0 km, biaya otomatis Rp 0.</li>
                        <li><b>Berbayar:</b> Jika jarak &ge; 1.0 km, biaya dihitung berdasarkan pembulatan ke bawah (floor) dikali tarif.</li>
                        <li>Contoh: Jarak 1.8 km dengan tarif 1.000 &rarr; floor(1.8) * 1.000 = Rp 1.000.</li>
                    </ul>
                </div>
            </div>
            <?php include ROOT_PATH . "includes/admin/footer.php"; ?>
        </div>
    </main>
    <script>
        // Auto-close alerts after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.auto-close-alert');
            alerts.forEach(alert => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);
    </script>
</body>

</html>
