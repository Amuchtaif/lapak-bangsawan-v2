<?php
require_once "../auth_session.php";
require_once "../../config/init.php";
require_once "../../includes/admin/notification_logic.php";

// Helper to fetch settings
$settings = [];
$res = $conn->query("SELECT * FROM site_settings");
while ($row = $res->fetch_assoc()) {
    $settings[$row['setting_key']] = $row;
}
?>
<!DOCTYPE html>
<html class="light" lang="id">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Kelola Landing Page - Lapak Bangsawan</title>
    <link rel="icon" href="<?= BASE_URL ?>assets/images/favicon-laba.png" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap"
        rel="stylesheet" />
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
        rel="stylesheet" />
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: { "primary": "#0d59f2", "background-light": "#f5f6f8", "background-dark": "#101622", "surface-light": "#ffffff", "surface-dark": "#1e293b" },
                    fontFamily: { "display": ["Inter", "sans-serif"] },
                },
            },
        }
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            display: inline-block;
            line-height: 1;
            text-transform: none;
            letter-spacing: normal;
            word-wrap: normal;
            white-space: nowrap;
            direction: ltr;
        }
    </style>
</head>

<body
    class="bg-background-light dark:bg-background-dark text-slate-600 dark:text-slate-300 font-display transition-colors duration-200 antialiased overflow-hidden h-screen flex">
    <?php include ROOT_PATH . "includes/admin/sidebar.php"; ?>
    <main class="flex-1 flex flex-col h-full relative overflow-hidden">
        <?php $page_title = "Kelola Landing Page";
        include ROOT_PATH . "includes/admin/header.php"; ?>

        <div class="flex-1 overflow-y-auto p-4 md:p-8 scroll-smooth">
            <div class="w-full flex flex-col gap-6">

                <!-- Notification -->
                <?php if (isset($_SESSION['status_msg'])): ?>
                    <div
                        class="bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-700 rounded-lg p-4 mb-2 flex items-start gap-3 shadow-sm auto-close-alert">
                        <span
                            class="material-icons-round <?php echo $_SESSION['status_type'] == 'success' ? 'text-green-500' : 'text-red-500'; ?>"><?php echo $_SESSION['status_type'] == 'success' ? 'check_circle' : 'error'; ?></span>
                        <div>
                            <h3 class="font-medium text-slate-900 dark:text-white">
                                <?php echo $_SESSION['status_type'] == 'success' ? 'Berhasil' : 'Gagal'; ?>
                            </h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400"><?php echo $_SESSION['status_msg']; ?></p>
                        </div>
                    </div>
                    <?php unset($_SESSION['status_msg']);
                    unset($_SESSION['status_type']); ?>
                <?php endif; ?>

                <form action="update.php" method="POST" enctype="multipart/form-data" class="flex flex-col gap-6">

                    <!-- 1. Hero Section -->
                    <div
                        class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-6">
                        <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-6 flex items-center gap-2">
                            <span class="material-icons-round text-primary">view_carousel</span>
                            Hero Section (Bagian Atas)
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium mb-2 text-slate-700 dark:text-slate-300">Judul
                                    Utama</label>
                                <input type="text" name="hero_title"
                                    value="<?= htmlspecialchars($settings['hero_title']['setting_value'] ?? '') ?>"
                                    class="w-full rounded-lg border-slate-200 bg-slate-50 dark:bg-slate-800 dark:border-slate-700 focus:ring-primary focus:border-primary">
                            </div>
                            <div class="md:col-span-2">
                                <label
                                    class="block text-sm font-medium mb-2 text-slate-700 dark:text-slate-300">Deskripsi
                                    Singkat</label>
                                <textarea name="hero_description" rows="3"
                                    class="w-full rounded-lg border-slate-200 bg-slate-50 dark:bg-slate-800 dark:border-slate-700 focus:ring-primary focus:border-primary"><?= htmlspecialchars($settings['hero_description']['setting_value'] ?? '') ?></textarea>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium mb-2 text-slate-700 dark:text-slate-300">Gambar
                                    Banner</label>
                                <div class="flex items-start gap-4">
                                    <div
                                        class="w-32 h-20 bg-slate-100 rounded-lg overflow-hidden border border-slate-200 flex-shrink-0">
                                        <?php if (!empty($settings['hero_image']['setting_value'])): ?>
                                            <img src="<?= BASE_URL . $settings['hero_image']['setting_value'] ?>"
                                                class="w-full h-full object-cover">
                                        <?php else: ?>
                                            <div
                                                class="w-full h-full flex items-center justify-center text-slate-400 text-xs">
                                                No Image</div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex-1">
                                        <input type="file" name="hero_image" accept="image/*"
                                            class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20">
                                        <p class="text-xs text-slate-500 mt-2">Format: JPG, PNG, WEBP. Maks 2MB.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Features Section -->
                    <div
                        class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-6">
                        <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-6 flex items-center gap-2">
                            <span class="material-icons-round text-primary">stars</span>
                            Keunggulan (Features)
                        </h2>
                        <div class="mb-6">
                            <label class="block text-sm font-medium mb-2 text-slate-700 dark:text-slate-300">Judul
                                Seksi</label>
                            <input type="text" name="feature_title"
                                value="<?= htmlspecialchars($settings['feature_title']['setting_value'] ?? '') ?>"
                                class="w-full rounded-lg border-slate-200 bg-slate-50 dark:bg-slate-800 dark:border-slate-700 mb-2">
                            <textarea name="feature_desc" rows="2"
                                class="w-full rounded-lg border-slate-200 bg-slate-50 dark:bg-slate-800 dark:border-slate-700"><?= htmlspecialchars($settings['feature_desc']['setting_value'] ?? '') ?></textarea>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <?php for ($i = 1; $i <= 3; $i++): ?>
                                <div
                                    class="p-4 border border-slate-200 dark:border-slate-700 rounded-lg bg-slate-50 dark:bg-slate-800/50">
                                    <h3 class="font-bold text-sm mb-3 text-slate-500 uppercase">Fitur #<?= $i ?></h3>
                                    <div class="space-y-3">
                                        <div>
                                            <label class="text-xs font-semibold">Judul</label>
                                            <input type="text" name="feature_<?= $i ?>_title"
                                                value="<?= htmlspecialchars($settings["feature_{$i}_title"]['setting_value'] ?? '') ?>"
                                                class="w-full rounded px-2 py-1 text-sm border-slate-200 dark:bg-slate-800 dark:border-slate-600">
                                        </div>
                                        <div>
                                            <label class="text-xs font-semibold">Deskripsi</label>
                                            <textarea name="feature_<?= $i ?>_desc" rows="2"
                                                class="w-full rounded px-2 py-1 text-sm border-slate-200 dark:bg-slate-800 dark:border-slate-600"><?= htmlspecialchars($settings["feature_{$i}_desc"]['setting_value'] ?? '') ?></textarea>
                                        </div>
                                        <div>
                                            <label class="text-xs font-semibold">Icon (Material Icons)</label>
                                            <div class="flex items-center gap-2">
                                                <div class="flex-1 relative">
                                                    <input type="text" name="feature_<?= $i ?>_icon"
                                                        id="feature_<?= $i ?>_icon"
                                                        value="<?= htmlspecialchars($settings["feature_{$i}_icon"]['setting_value'] ?? '') ?>"
                                                        oninput="updateIconPreview('feature_<?= $i ?>_icon')"
                                                        class="w-full rounded px-2 py-1.5 text-sm border-slate-200 dark:bg-slate-800 dark:border-slate-600 pr-10">
                                                    <button type="button" onclick="openIconPicker('feature_<?= $i ?>_icon')"
                                                        class="absolute right-1 top-1/2 -translate-y-1/2 p-1 text-slate-400 hover:text-primary transition-colors">
                                                        <span class="material-icons-round text-lg">search</span>
                                                    </button>
                                                </div>
                                                <div id="preview_feature_<?= $i ?>_icon"
                                                    class="w-10 h-10 flex items-center justify-center border rounded bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-700 shadow-sm">
                                                    <span
                                                        class="material-symbols-outlined text-primary"><?= $settings["feature_{$i}_icon"]['setting_value'] ?? 'help' ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <!-- 3. Categories Section -->
                    <div
                        class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-6">
                        <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-6 flex items-center gap-2">
                            <span class="material-icons-round text-primary">category</span>
                            Kategori Pilihan
                        </h2>
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-2 text-slate-700 dark:text-slate-300">Judul
                                Seksi</label>
                            <input type="text" name="cat_title"
                                value="<?= htmlspecialchars($settings['cat_title']['setting_value'] ?? '') ?>"
                                class="w-full rounded-lg border-slate-200 bg-slate-50 dark:bg-slate-800 dark:border-slate-700">
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <?php for ($i = 1; $i <= 4; $i++): ?>
                                <div
                                    class="p-4 border border-slate-200 dark:border-slate-700 rounded-lg bg-slate-50 dark:bg-slate-800/50">
                                    <h3 class="font-bold text-sm mb-3 text-slate-500 uppercase tracking-wider">Kategori
                                        #<?= $i ?></h3>
                                    <div class="space-y-4">
                                        <div>
                                            <label class="text-xs font-bold text-slate-500 mb-1 block">Nama Kategori</label>
                                            <input type="text" name="cat_<?= $i ?>_name"
                                                value="<?= htmlspecialchars($settings["cat_{$i}_name"]['setting_value'] ?? '') ?>"
                                                class="w-full rounded-lg px-3 py-2 text-sm border-slate-200 dark:bg-slate-900 dark:border-slate-700 focus:ring-primary focus:border-primary transition-all">
                                        </div>
                                        <div>
                                            <label class="text-xs font-bold text-slate-500 mb-2 block">Gambar (Upload /
                                                URL)</label>
                                            <div class="space-y-3">
                                                <!-- Preview & Upload -->
                                                <div class="flex items-center gap-3">
                                                    <div
                                                        class="w-14 h-14 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 overflow-hidden flex-shrink-0 shadow-sm">
                                                        <?php
                                                        $cat_img = $settings["cat_{$i}_img"]['setting_value'] ?? '';
                                                        $full_cat_img = (!empty($cat_img) && strpos($cat_img, 'http') !== 0) ? BASE_URL . $cat_img : $cat_img;
                                                        ?>
                                                        <?php if (!empty($cat_img)): ?>
                                                            <img src="<?= $full_cat_img ?>" class="w-full h-full object-cover">
                                                        <?php else: ?>
                                                            <div
                                                                class="w-full h-full flex items-center justify-center text-[10px] text-slate-400">
                                                                Polos</div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="flex-1">
                                                        <input type="file" name="cat_<?= $i ?>_img" accept="image/*"
                                                            class="block w-full text-[10px] text-slate-500 file:mr-2 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-[10px] file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20 transition-all">
                                                    </div>
                                                </div>
                                                <!-- URL Input -->
                                                <div class="relative">
                                                    <span
                                                        class="material-icons-round absolute left-2 top-1/2 -translate-y-1/2 text-[14px] text-slate-400">link</span>
                                                    <input type="text" name="cat_<?= $i ?>_img_url"
                                                        placeholder="Atau masukkan URL gambar..."
                                                        value="<?= (isset($settings["cat_{$i}_img"]['setting_value']) && strpos($settings["cat_{$i}_img"]['setting_value'], 'http') === 0) ? htmlspecialchars($settings["cat_{$i}_img"]['setting_value']) : '' ?>"
                                                        class="w-full pl-7 pr-3 py-1.5 text-[11px] rounded-lg border-slate-200 dark:bg-slate-900 dark:border-slate-700 focus:ring-primary focus:border-primary">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <!-- 4. Popular Products Section -->
                    <div
                        class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-6">
                        <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-6 flex items-center gap-2">
                            <span class="material-icons-round text-primary">shopping_bag</span>
                            Produk Populer
                        </h2>
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-2 text-slate-700 dark:text-slate-300">Judul
                                Seksi</label>
                            <input type="text" name="prod_title"
                                value="<?= htmlspecialchars($settings['prod_title']['setting_value'] ?? '') ?>"
                                class="w-full rounded-lg border-slate-200 bg-slate-50 dark:bg-slate-800 dark:border-slate-700">
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <?php for ($i = 1; $i <= 4; $i++): ?>
                                <div
                                    class="p-4 border border-slate-200 dark:border-slate-700 rounded-lg bg-slate-50 dark:bg-slate-800/50">
                                    <h3 class="font-bold text-sm mb-3 text-slate-500 uppercase tracking-wider">Produk
                                        #<?= $i ?></h3>
                                    <div class="space-y-4">
                                        <div>
                                            <label class="text-xs font-bold text-slate-500 mb-1 block">Nama Produk</label>
                                            <input type="text" name="prod_<?= $i ?>_name"
                                                value="<?= htmlspecialchars($settings["prod_{$i}_name"]['setting_value'] ?? '') ?>"
                                                class="w-full rounded-lg px-3 py-2 text-sm border-slate-200 dark:bg-slate-900 dark:border-slate-700 focus:ring-primary focus:border-primary">
                                        </div>
                                        <div>
                                            <label class="text-xs font-bold text-slate-500 mb-1 block">Keterangan
                                                Singkat</label>
                                            <input type="text" name="prod_<?= $i ?>_desc"
                                                value="<?= htmlspecialchars($settings["prod_{$i}_desc"]['setting_value'] ?? '') ?>"
                                                class="w-full rounded-lg px-3 py-2 text-sm border-slate-200 dark:bg-slate-900 dark:border-slate-700 focus:ring-primary focus:border-primary">
                                        </div>
                                        <div>
                                            <label class="text-xs font-bold text-slate-500 mb-2 block">Gambar (Upload /
                                                URL)</label>
                                            <div class="space-y-3">
                                                <!-- Preview & Upload -->
                                                <div class="flex items-center gap-3">
                                                    <div
                                                        class="w-14 h-14 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 overflow-hidden flex-shrink-0 shadow-sm">
                                                        <?php
                                                        $prod_img = $settings["prod_{$i}_img"]['setting_value'] ?? '';
                                                        $full_prod_img = (!empty($prod_img) && strpos($prod_img, 'http') !== 0) ? BASE_URL . $prod_img : $prod_img;
                                                        ?>
                                                        <?php if (!empty($prod_img)): ?>
                                                            <img src="<?= $full_prod_img ?>" class="w-full h-full object-cover">
                                                        <?php else: ?>
                                                            <div
                                                                class="w-full h-full flex items-center justify-center text-[10px] text-slate-400">
                                                                Polos</div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="flex-1">
                                                        <input type="file" name="prod_<?= $i ?>_img" accept="image/*"
                                                            class="block w-full text-[10px] text-slate-500 file:mr-2 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-[10px] file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20 transition-all">
                                                    </div>
                                                </div>
                                                <!-- URL Input -->
                                                <div class="relative">
                                                    <span
                                                        class="material-icons-round absolute left-2 top-1/2 -translate-y-1/2 text-[14px] text-slate-400">link</span>
                                                    <input type="text" name="prod_<?= $i ?>_img_url"
                                                        placeholder="Atau masukkan URL gambar..."
                                                        value="<?= (isset($settings["prod_{$i}_img"]['setting_value']) && strpos($settings["prod_{$i}_img"]['setting_value'], 'http') === 0) ? htmlspecialchars($settings["prod_{$i}_img"]['setting_value']) : '' ?>"
                                                        class="w-full pl-7 pr-3 py-1.5 text-[11px] rounded-lg border-slate-200 dark:bg-slate-900 dark:border-slate-700 focus:ring-primary focus:border-primary">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endfor; ?>
                        </div>
                    </div>


                    <!-- 5. Contact Section -->
                    <div
                        class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-6">
                        <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-6 flex items-center gap-2">
                            <span class="material-icons-round text-primary">contact_support</span>
                            Kontak Info
                        </h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium mb-2 text-slate-700 dark:text-slate-300">Nomor
                                    WhatsApp</label>
                                <input type="text" name="contact_wa"
                                    value="<?= htmlspecialchars($settings['contact_wa']['setting_value'] ?? '') ?>"
                                    class="w-full rounded-lg border-slate-200 bg-slate-50 dark:bg-slate-800 dark:border-slate-700 focus:ring-primary focus:border-primary">
                            </div>

                            <div>
                                <label
                                    class="block text-sm font-medium mb-2 text-slate-700 dark:text-slate-300">Email</label>
                                <input type="email" name="contact_email"
                                    value="<?= htmlspecialchars($settings['contact_email']['setting_value'] ?? '') ?>"
                                    class="w-full rounded-lg border-slate-200 bg-slate-50 dark:bg-slate-800 dark:border-slate-700 focus:ring-primary focus:border-primary">
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium mb-2 text-slate-700 dark:text-slate-300">Link
                                    Instagram</label>
                                <input type="text" name="social_instagram"
                                    value="<?= htmlspecialchars($settings['social_instagram']['setting_value'] ?? '') ?>"
                                    class="w-full rounded-lg border-slate-200 bg-slate-50 dark:bg-slate-800 dark:border-slate-700 focus:ring-primary focus:border-primary">
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium mb-2 text-slate-700 dark:text-slate-300">Alamat
                                    Lengkap</label>
                                <textarea name="contact_address" rows="3"
                                    class="w-full rounded-lg border-slate-200 bg-slate-50 dark:bg-slate-800 dark:border-slate-700 focus:ring-primary focus:border-primary"><?= htmlspecialchars($settings['contact_address']['setting_value'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>

                    <div
                        class="flex justify-end pb-8 sticky bottom-0 bg-surface-light/80 dark:bg-surface-dark/80 backdrop-blur-sm p-4 border-t border-slate-200 dark:border-slate-800">
                        <button type="submit" name="update_settings"
                            class="bg-primary text-white px-8 py-3 rounded-xl font-bold hover:bg-blue-600 transition-all shadow-lg shadow-blue-500/30 flex items-center gap-2">
                            <span class="material-icons-round">save</span> Simpan Semua Perubahan
                        </button>
                    </div>

                </form>
            </div>
            <?php include ROOT_PATH . "includes/admin/footer.php"; ?>
        </div>
    </main>

    <!-- Icon Picker Modal -->
    <div id="iconPickerModal" class="fixed inset-0 z-[9999] overflow-hidden hidden" aria-labelledby="modal-title"
        role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-md transition-opacity" onclick="closeIconPicker()">
            </div>

            <div
                class="relative bg-white dark:bg-surface-dark rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800 w-full max-w-2xl max-h-[85vh] flex flex-col overflow-hidden">
                <!-- Modal Header -->
                <div
                    class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-slate-50 dark:bg-slate-900/50">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">Pilih Icon</h3>
                        <p class="text-xs text-slate-500">Pilih icon Material Design untuk ditampilkan</p>
                    </div>
                    <button onclick="closeIconPicker()" class="p-2 text-slate-400 hover:text-red-500 transition-colors">
                        <span class="material-icons-round">close</span>
                    </button>
                </div>

                <!-- Modal Filter -->
                <div class="p-6 border-b border-slate-200 dark:border-slate-800">
                    <div class="relative">
                        <span
                            class="material-icons-round absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">search</span>
                        <input type="text" id="iconSearchInput" placeholder="Cari icon... (misal: star, phone, heart)"
                            class="w-full pl-10 pr-4 py-3 rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 focus:ring-2 focus:ring-primary focus:border-transparent transition-all outline-none text-slate-900 dark:text-white">
                    </div>
                </div>

                <!-- Modal Content (Grid) -->
                <div class="flex-1 overflow-y-auto p-6 scroll-smooth custom-scrollbar">
                    <div id="iconGrid" class="grid grid-cols-4 sm:grid-cols-6 md:grid-cols-8 gap-4">
                        <!-- Icons will be injected here -->
                        <div class="col-span-full flex flex-col items-center py-10 text-slate-400">
                            <div
                                class="w-12 h-12 border-4 border-primary/20 border-t-primary rounded-full animate-spin mb-4">
                            </div>
                            <p>Memuat daftar icon...</p>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div
                    class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/50 text-right">
                    <button onclick="closeIconPicker()"
                        class="px-5 py-2 text-sm font-semibold text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors">Batal</button>
                </div>
            </div>
        </div>
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        .dark .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #334155;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>

    <script>
        let currentTargetId = null;
        let allIcons = [];

        function updateIconPreview(id) {
            const input = document.getElementById(id);
            const preview = document.getElementById('preview_' + id);
            if (input && preview) {
                const iconName = input.value.trim() || 'help';
                preview.innerHTML = `<span class="material-symbols-outlined text-primary">${iconName}</span>`;
            }
        }

        function openIconPicker(targetId) {
            currentTargetId = targetId;
            document.getElementById('iconPickerModal').classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
            document.getElementById('iconSearchInput').value = '';
            document.getElementById('iconSearchInput').focus();

            if (allIcons.length === 0) {
                fetchIcons();
            } else {
                renderIcons(allIcons);
            }
        }

        function closeIconPicker() {
            document.getElementById('iconPickerModal').classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }

        async function fetchIcons() {
            try {
                // Fetch list of icons from official Material Symbols repository for better compatibility
                const response = await fetch('https://raw.githubusercontent.com/google/material-symbols/main/font/MaterialSymbolsOutlined%5Bfill%2Cwght%2CGRAD%2Copsz%5D.codepoints');
                if (!response.ok) throw new Error('Failed to fetch');
                const data = await response.text();
                allIcons = data.split('\n')
                    .map(line => line.split(' ')[0])
                    .filter(name => name.length > 0)
                    .sort();

                renderIcons(allIcons);
            } catch (error) {
                console.error('Error fetching icons:', error);
                // Fallback icons if fetch fails
                allIcons = ["stars", "shopping_bag", "support_agent", "local_shipping", "security", "thumb_up", "speed", "verified", "workspace_premium", "favorite", "star", "home", "search", "settings", "done", "close", "menu", "add", "remove", "person", "group", "call", "email", "location_on", "calendar_month", "schedule", "payment", "shopping_cart", "credit_card", "local_offer", "flash_on", "public", "language", "build", "military_tech", "handshake", "inventory", "article", "description", "contact_page", "help", "info", "warning", "error", "check_circle", "cancel", "visibility", "visibility_off", "cloud_done", "cloud_upload", "download", "upload", "share", "send", "chat", "forum", "notifications", "notifications_active", "volume_up", "mic", "photo_camera", "videocam", "image", "movie", "audiotrack", "grid_view", "list", "view_module", "view_list", "dashboard", "apps", "widgets", "explore", "navigation", "map", "directions_car", "directions_bike", "flight", "hotel", "restaurant", "local_atm", "attach_money", "trending_up", "pie_chart", "bar_chart", "show_chart", "qr_code", "history", "restore", "sync", "refresh", "lock", "lock_open", "fingerprint", "vpn_key", "password"];
                renderIcons(allIcons);
            }
        }

        function renderIcons(icons) {
            const grid = document.getElementById('iconGrid');
            if (icons.length === 0) {
                grid.innerHTML = '<div class="col-span-full py-10 text-center text-slate-400">Tidak ada icon ditemukan.</div>';
                return;
            }

            // Limit shown icons to 300 for performance, unless searching specifically
            const limit = 300;
            const shownList = icons.slice(0, limit);

            grid.innerHTML = shownList.map(icon => `
                <button type="button" onclick="selectIcon('${icon}')" 
                    title="${icon}"
                    class="group flex flex-col items-center justify-center p-3 rounded-xl border border-transparent hover:border-primary hover:bg-primary/5 dark:hover:bg-primary/10 transition-all duration-200">
                    <span class="material-symbols-outlined text-2xl text-slate-600 dark:text-slate-400 group-hover:text-primary mb-1">${icon}</span>
                    <span class="text-[10px] truncate w-full text-center text-slate-400 group-hover:text-primary transition-colors">${icon}</span>
                </button>
            `).join('');

            if (icons.length > limit) {
                grid.innerHTML += `<div class="col-span-full pt-4 border-t border-slate-100 dark:border-slate-800 text-center text-[10px] text-slate-400">Menampilkan ${limit} dari ${icons.length} icon. Gunakan pencarian untuk lebih spesifik.</div>`;
            }
        }

        function selectIcon(icon) {
            if (currentTargetId) {
                const input = document.getElementById(currentTargetId);
                input.value = icon;
                updateIconPreview(currentTargetId);
                closeIconPicker();
            }
        }

        // Search logic
        document.getElementById('iconSearchInput').addEventListener('input', (e) => {
            const term = e.target.value.toLowerCase().trim();
            const filtered = allIcons.filter(icon => icon.includes(term));
            renderIcons(filtered);
        });

        // Close on ESC
        window.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeIconPicker();
        });
    </script>
</body>

</html>