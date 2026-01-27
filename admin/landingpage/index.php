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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet" />
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
</head>
<body class="bg-background-light dark:bg-background-dark text-slate-600 dark:text-slate-300 font-display transition-colors duration-200 antialiased overflow-hidden h-screen flex">
    <?php include ROOT_PATH . "includes/admin/sidebar.php"; ?>
    <main class="flex-1 flex flex-col h-full relative overflow-hidden">
        <?php $page_title = "Kelola Landing Page"; include ROOT_PATH . "includes/admin/header.php"; ?>
        
        <div class="flex-1 overflow-y-auto p-4 md:p-8 scroll-smooth">
            <div class="w-full flex flex-col gap-6">
                
                <!-- Notification -->
                <?php if (isset($_SESSION['status_msg'])): ?>
                    <div class="bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-700 rounded-lg p-4 mb-2 flex items-start gap-3 shadow-sm auto-close-alert">
                        <span class="material-icons-round <?php echo $_SESSION['status_type'] == 'success' ? 'text-green-500' : 'text-red-500'; ?>"><?php echo $_SESSION['status_type'] == 'success' ? 'check_circle' : 'error'; ?></span>
                        <div>
                            <h3 class="font-medium text-slate-900 dark:text-white"><?php echo $_SESSION['status_type'] == 'success' ? 'Berhasil' : 'Gagal'; ?></h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400"><?php echo $_SESSION['status_msg']; ?></p>
                        </div>
                    </div>
                    <?php unset($_SESSION['status_msg']); unset($_SESSION['status_type']); ?>
                <?php endif; ?>

                <form action="update.php" method="POST" enctype="multipart/form-data" class="flex flex-col gap-6">
                    
                    <!-- 1. Hero Section -->
                    <div class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-6">
                        <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-6 flex items-center gap-2">
                            <span class="material-icons-round text-primary">view_carousel</span>
                            Hero Section (Bagian Atas)
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium mb-2 text-slate-700 dark:text-slate-300">Judul Utama</label>
                                <input type="text" name="hero_title" value="<?= htmlspecialchars($settings['hero_title']['setting_value'] ?? '') ?>" 
                                    class="w-full rounded-lg border-slate-200 bg-slate-50 dark:bg-slate-800 dark:border-slate-700 focus:ring-primary focus:border-primary">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium mb-2 text-slate-700 dark:text-slate-300">Deskripsi Singkat</label>
                                <textarea name="hero_description" rows="3" 
                                    class="w-full rounded-lg border-slate-200 bg-slate-50 dark:bg-slate-800 dark:border-slate-700 focus:ring-primary focus:border-primary"><?= htmlspecialchars($settings['hero_description']['setting_value'] ?? '') ?></textarea>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium mb-2 text-slate-700 dark:text-slate-300">Gambar Banner</label>
                                <div class="flex items-start gap-4">
                                    <div class="w-32 h-20 bg-slate-100 rounded-lg overflow-hidden border border-slate-200 flex-shrink-0">
                                        <?php if (!empty($settings['hero_image']['setting_value'])): ?>
                                            <img src="<?= BASE_URL . $settings['hero_image']['setting_value'] ?>" class="w-full h-full object-cover">
                                        <?php else: ?>
                                            <div class="w-full h-full flex items-center justify-center text-slate-400 text-xs">No Image</div>
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
                    <div class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-6">
                        <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-6 flex items-center gap-2">
                            <span class="material-icons-round text-primary">stars</span>
                            Keunggulan (Features)
                        </h2>
                        <div class="mb-6">
                            <label class="block text-sm font-medium mb-2 text-slate-700 dark:text-slate-300">Judul Seksi</label>
                            <input type="text" name="feature_title" value="<?= htmlspecialchars($settings['feature_title']['setting_value'] ?? '') ?>" class="w-full rounded-lg border-slate-200 bg-slate-50 dark:bg-slate-800 dark:border-slate-700 mb-2">
                            <textarea name="feature_desc" rows="2" class="w-full rounded-lg border-slate-200 bg-slate-50 dark:bg-slate-800 dark:border-slate-700"><?= htmlspecialchars($settings['feature_desc']['setting_value'] ?? '') ?></textarea>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <?php for($i=1; $i<=3; $i++): ?>
                            <div class="p-4 border border-slate-200 dark:border-slate-700 rounded-lg bg-slate-50 dark:bg-slate-800/50">
                                <h3 class="font-bold text-sm mb-3 text-slate-500 uppercase">Fitur #<?= $i ?></h3>
                                <div class="space-y-3">
                                    <div>
                                        <label class="text-xs font-semibold">Judul</label>
                                        <input type="text" name="feature_<?= $i ?>_title" value="<?= htmlspecialchars($settings["feature_{$i}_title"]['setting_value'] ?? '') ?>" class="w-full rounded px-2 py-1 text-sm border-slate-200 dark:bg-slate-800 dark:border-slate-600">
                                    </div>
                                    <div>
                                        <label class="text-xs font-semibold">Deskripsi</label>
                                        <textarea name="feature_<?= $i ?>_desc" rows="2" class="w-full rounded px-2 py-1 text-sm border-slate-200 dark:bg-slate-800 dark:border-slate-600"><?= htmlspecialchars($settings["feature_{$i}_desc"]['setting_value'] ?? '') ?></textarea>
                                    </div>
                                    <div>
                                        <label class="text-xs font-semibold">Icon (Material Icons)</label>
                                        <div class="flex items-center gap-2">
                                            <input type="text" name="feature_<?= $i ?>_icon" value="<?= htmlspecialchars($settings["feature_{$i}_icon"]['setting_value'] ?? '') ?>" class="flex-1 rounded px-2 py-1 text-sm border-slate-200 dark:bg-slate-800 dark:border-slate-600">
                                            <span class="material-icons-round text-primary"><?= $settings["feature_{$i}_icon"]['setting_value'] ?? 'help' ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <!-- 3. Categories Section -->
                    <div class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-6">
                        <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-6 flex items-center gap-2">
                            <span class="material-icons-round text-primary">category</span>
                            Kategori Pilihan
                        </h2>
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-2 text-slate-700 dark:text-slate-300">Judul Seksi</label>
                            <input type="text" name="cat_title" value="<?= htmlspecialchars($settings['cat_title']['setting_value'] ?? '') ?>" class="w-full rounded-lg border-slate-200 bg-slate-50 dark:bg-slate-800 dark:border-slate-700">
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                             <?php for($i=1; $i<=4; $i++): ?>
                            <div class="p-4 border border-slate-200 dark:border-slate-700 rounded-lg bg-slate-50 dark:bg-slate-800/50">
                                <h3 class="font-bold text-sm mb-3 text-slate-500 uppercase">Kategori #<?= $i ?></h3>
                                <div class="space-y-3">
                                    <div>
                                        <label class="text-xs font-semibold">Nama</label>
                                        <input type="text" name="cat_<?= $i ?>_name" value="<?= htmlspecialchars($settings["cat_{$i}_name"]['setting_value'] ?? '') ?>" class="w-full rounded px-2 py-1 text-sm border-slate-200 dark:bg-slate-800 dark:border-slate-600">
                                    </div>
                                    <div>
                                        <label class="text-xs font-semibold">URL Gambar</label>
                                        <input type="text" name="cat_<?= $i ?>_img" value="<?= htmlspecialchars($settings["cat_{$i}_img"]['setting_value'] ?? '') ?>" class="w-full rounded px-2 py-1 text-sm border-slate-200 dark:bg-slate-800 dark:border-slate-600 mb-2">
                                        <div class="w-12 h-12 bg-gray-100 rounded-full bg-cover bg-center border border-gray-200" style="background-image: url('<?= $settings["cat_{$i}_img"]['setting_value'] ?? '' ?>');"></div>
                                    </div>
                                </div>
                            </div>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <!-- 4. Popular Products Section -->
                     <div class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-6">
                        <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-6 flex items-center gap-2">
                            <span class="material-icons-round text-primary">shopping_bag</span>
                            Produk Populer
                        </h2>
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-2 text-slate-700 dark:text-slate-300">Judul Seksi</label>
                            <input type="text" name="prod_title" value="<?= htmlspecialchars($settings['prod_title']['setting_value'] ?? '') ?>" class="w-full rounded-lg border-slate-200 bg-slate-50 dark:bg-slate-800 dark:border-slate-700">
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                             <?php for($i=1; $i<=4; $i++): ?>
                            <div class="p-4 border border-slate-200 dark:border-slate-700 rounded-lg bg-slate-50 dark:bg-slate-800/50">
                                <h3 class="font-bold text-sm mb-3 text-slate-500 uppercase">Produk #<?= $i ?></h3>
                                <div class="space-y-3">
                                    <div>
                                        <label class="text-xs font-semibold">Nama</label>
                                        <input type="text" name="prod_<?= $i ?>_name" value="<?= htmlspecialchars($settings["prod_{$i}_name"]['setting_value'] ?? '') ?>" class="w-full rounded px-2 py-1 text-sm border-slate-200 dark:bg-slate-800 dark:border-slate-600">
                                    </div>
                                    <div>
                                        <label class="text-xs font-semibold">Keterangan</label>
                                        <input type="text" name="prod_<?= $i ?>_desc" value="<?= htmlspecialchars($settings["prod_{$i}_desc"]['setting_value'] ?? '') ?>" class="w-full rounded px-2 py-1 text-sm border-slate-200 dark:bg-slate-800 dark:border-slate-600">
                                    </div>
                                     <div>
                                        <label class="text-xs font-semibold">Gambar</label>
                                        <!-- For simplicity using text URL or File Upload? Let's use File Upload logic for products too if we want, but for now user script used relative paths. Let's allowing updating the path string or uploading. -->
                                        <!-- The logic in update.php handles 'hero_image', but we need to handle these dynamic inputs. Let's just use text input for path for now or duplicate upload logic? -->
                                        <!-- Plan: To be safe, just use text input for path for now as per seed. If user wants upload we need to update update.php logic significantly. User request was "Change content/text and image", implies upload. -->
                                        <!-- I will add file input for each product and update logic in next step. -->
                                        <div class="flex flex-col gap-2">
                                            <input type="file" name="prod_<?= $i ?>_img" accept="image/*" class="text-xs">
                                            <input type="hidden" name="existing_prod_<?= $i ?>_img" value="<?= htmlspecialchars($settings["prod_{$i}_img"]['setting_value'] ?? '') ?>">
                                            <?php if(!empty($settings["prod_{$i}_img"]['setting_value'])): ?>
                                            <img src="<?= BASE_URL . $settings["prod_{$i}_img"]['setting_value'] ?>" class="w-full h-24 object-cover rounded border">
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endfor; ?>
                        </div>
                    </div>


                    <!-- 5. Contact Section -->
                    <div class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-6">
                        <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-6 flex items-center gap-2">
                            <span class="material-icons-round text-primary">contact_support</span>
                            Kontak Info
                        </h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium mb-2 text-slate-700 dark:text-slate-300">Nomor WhatsApp</label>
                                <input type="text" name="contact_wa" value="<?= htmlspecialchars($settings['contact_wa']['setting_value'] ?? '') ?>" 
                                    class="w-full rounded-lg border-slate-200 bg-slate-50 dark:bg-slate-800 dark:border-slate-700 focus:ring-primary focus:border-primary">
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-2 text-slate-700 dark:text-slate-300">Email</label>
                                <input type="email" name="contact_email" value="<?= htmlspecialchars($settings['contact_email']['setting_value'] ?? '') ?>" 
                                    class="w-full rounded-lg border-slate-200 bg-slate-50 dark:bg-slate-800 dark:border-slate-700 focus:ring-primary focus:border-primary">
                            </div>

                             <div class="md:col-span-2">
                                <label class="block text-sm font-medium mb-2 text-slate-700 dark:text-slate-300">Link Instagram</label>
                                <input type="text" name="social_instagram" value="<?= htmlspecialchars($settings['social_instagram']['setting_value'] ?? '') ?>" 
                                    class="w-full rounded-lg border-slate-200 bg-slate-50 dark:bg-slate-800 dark:border-slate-700 focus:ring-primary focus:border-primary">
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium mb-2 text-slate-700 dark:text-slate-300">Alamat Lengkap</label>
                                <textarea name="contact_address" rows="3" 
                                    class="w-full rounded-lg border-slate-200 bg-slate-50 dark:bg-slate-800 dark:border-slate-700 focus:ring-primary focus:border-primary"><?= htmlspecialchars($settings['contact_address']['setting_value'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end pb-8 sticky bottom-0 bg-surface-light/80 dark:bg-surface-dark/80 backdrop-blur-sm p-4 border-t border-slate-200 dark:border-slate-800">
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
</body>
</html>
