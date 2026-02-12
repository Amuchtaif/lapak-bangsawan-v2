<?php
require("auth_session.php");
require_once dirname(__DIR__) . "/config/init.php";
require_once ROOT_PATH . "helpers/ShippingHelper.php";

$success_msg = '';
$error_msg = '';

// Fetch current setting
$settings = ShippingHelper::getLocalShippingSettings($conn);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_settings'])) {
    $max_distance = filter_var($_POST['max_distance_local'], FILTER_VALIDATE_FLOAT);
    $price_per_km = filter_var($_POST['price_per_km_local'], FILTER_VALIDATE_FLOAT);

    if ($max_distance === false || $price_per_km === false) {
        $error_msg = "Batas jarak dan tarif harus berupa angka.";
    } else {
        // Use Prepared Statement for security
        $stmt = $conn->prepare("UPDATE settings_pengiriman SET max_distance_local = ?, price_per_km_local = ? WHERE id = 1");
        if (!$stmt) {
             // If table doesn't have ID 1 or something, maybe we need to insert?
             // But our setup script ensures there is one.
             $error_msg = "Gagal menyiapkan query: " . $conn->error;
        } else {
            $stmt->bind_param("dd", $max_distance, $price_per_km);
            if ($stmt->execute()) {
                $success_msg = "Pengaturan pengiriman berhasil diperbarui.";
                // Update local variable for display
                $settings['max_distance_local'] = $max_distance;
                $settings['price_per_km_local'] = $price_per_km;
            } else {
                $error_msg = "Gagal memperbarui pengaturan: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html class="light" lang="id">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Pengaturan Pengiriman - Lapak Bangsawan</title>
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
        <?php $page_title = "Pengaturan Pengiriman";
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
                        <div class="w-12 h-12 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-primary">
                            <span class="material-icons-round">local_shipping</span>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-slate-900 dark:text-white">Konfigurasi Kurir Lokal</h2>
                            <p class="text-sm text-slate-500">Atur batasan jarak dan tarif pengiriman internal.</p>
                        </div>
                    </div>

                    <form action="" method="POST" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                                    Batas Jarak Maksimal (KM)
                                </label>
                                <div class="relative">
                                    <input type="number" step="0.1" name="max_distance_local"
                                        value="<?php echo htmlspecialchars($settings['max_distance_local']); ?>"
                                        class="w-full rounded-lg border-slate-200 bg-slate-50 dark:bg-slate-800 dark:border-slate-700 focus:ring-primary focus:border-primary pr-12">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400 text-sm">
                                        KM
                                    </div>
                                </div>
                                <p class="mt-1 text-xs text-slate-500">Opsi kurir lokal hanya muncul jika jarak pembeli di bawah angka ini.</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                                    Tarif per Kilometer (Rp)
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400 text-sm">
                                        Rp
                                    </div>
                                    <input type="number" name="price_per_km_local"
                                        value="<?php echo htmlspecialchars($settings['price_per_km_local']); ?>"
                                        class="w-full rounded-lg border-slate-200 bg-slate-50 dark:bg-slate-800 dark:border-slate-700 focus:ring-primary focus:border-primary pl-10">
                                </div>
                                <p class="mt-1 text-xs text-slate-500">Biaya dihitung otomatis: Jarak &times; Tarif. Hasil akan dibulatkan ke atas.</p>
                            </div>
                        </div>

                        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-900/30 rounded-lg p-4">
                            <h4 class="text-sm font-bold text-blue-900 dark:text-blue-300 mb-1">Simulasi Perhitungan:</h4>
                            <p class="text-sm text-blue-800 dark:text-blue-400">
                                Jika jarak <span class="font-semibold">1.5 KM</span>, maka ongkir: 
                                1.5 &times; Rp <?php echo number_format($settings['price_per_km_local'], 0, ',', '.'); ?> = 
                                <span class="font-bold underline">Rp <?php echo number_format(ceil(1.5 * $settings['price_per_km_local']), 0, ',', '.'); ?></span>
                            </p>
                        </div>

                        <div class="flex justify-end pt-4">
                            <button type="submit" name="save_settings"
                                class="bg-primary hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-lg transition-colors shadow-lg shadow-blue-500/20 flex items-center gap-2">
                                <span class="material-icons-round text-sm">save</span>
                                Simpan Pengaturan
                            </button>
                        </div>
                    </form>
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
