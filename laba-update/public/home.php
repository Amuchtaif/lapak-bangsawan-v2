<?php require_once dirname(__DIR__) . '/config/init.php'; ?>
<!DOCTYPE html>

<html class="light" lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Lapak Bangsawan - Premium Protein Hewani</title>
    <link rel="icon" href="<?= BASE_URL ?>assets/images/favicon-laba.png" type="image/x-icon">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect" />
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect" />
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;900&amp;family=Noto+Sans:wght@400;500;700&amp;display=swap"
        rel="stylesheet" />
    <!-- Material Symbols -->
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap"
        rel="stylesheet" />
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap"
        rel="stylesheet" />
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <!-- Tailwind Config -->
    <script id="tailwind-config">
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
                        "display": ["Inter", "sans-serif"],
                        "body": ["Noto Sans", "sans-serif"],
                    },
                    borderRadius: {
                        "DEFAULT": "0.25rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "full": "9999px"
                    },
                },
            },
        }
    </script>
    <style>
        /* Custom scrollbar for webkit */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }

        .material-symbols-outlined {
            font-variation-settings:
                'FILL' 0,
                'wght' 400,
                'GRAD' 0,
                'opsz' 24
        }
    </style>
</head>

<body class="bg-background-light dark:bg-background-dark text-[#111318] dark:text-white font-display">
    <div class="relative flex h-auto min-h-screen w-full flex-col overflow-x-hidden">
        <!-- Navigation -->
        <?php include ROOT_PATH . "includes/public_header.php"; ?>
        <!-- Main Content Area -->
        <main class="flex-1 flex flex-col items-center w-full">
            <div class="w-full max-w-[1200px] px-4 md:px-10 lg:px-40 pb-20">
                <!-- Hero Section -->
                <div class="@container mt-12 md:mt-24 mb-12">
                    <div class="flex flex-col gap-6 py-2 @[864px]:flex-row @[864px]:items-center">

                        <!-- TEXT -->
                        <div class="w-full @[864px]:w-1/2 flex flex-col gap-5 justify-center">
                            <div class="flex flex-col gap-3 text-left">
                                <span
                                    class="inline-block px-3 py-1 rounded-full bg-primary/10 text-primary text-xs font-bold w-fit">
                                    100% SEGAR & HALAL
                                </span>

                                <h1 <h1
                                    class="text-[#111318] dark:text-white text-3xl @[480px]:text-4xl lg:text-6xl font-black leading-tight tracking-[-0.03em]">
                                    <?= get_setting('hero_title', 'Segar Hari Ini,<br class="hidden @[864px]:block"> Dikirim Hari Ini') ?>
                                </h1>

                                <p class="text-[#606e8a] dark:text-[#a0aec0] text-base leading-relaxed max-w-[480px]">
                                    <?= get_setting('hero_description', 'Ayam, ikan, dan seafood premium langsung dari sumber terpercaya ke dapur Anda.') ?>
                                </p>
                            </div>

                            <!-- CTA -->
                            <div class="flex flex-col sm:flex-row gap-3 mt-2">
                                <a href="<?= BASE_URL ?>market"
                                    class="flex w-full sm:w-auto min-w-[160px] items-center justify-center rounded-lg h-12 px-6 bg-primary hover:bg-blue-700 transition-colors text-white text-base font-bold">
                                    Mulai Belanja
                                </a>
                            </div>
                        </div>

                        <!-- IMAGE -->
                        <div
                            class="w-full @[864px]:w-1/2 aspect-[16/9] @[864px]:aspect-[4/3] rounded-2xl overflow-hidden shadow-xl">
                            <div class="w-full h-full bg-cover bg-center"
                                style="background-image: url('<?= BASE_URL . get_setting('hero_image', 'assets/images/hero.jpg') ?>');">
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Features Section -->
                <div class="flex flex-col gap-10 py-10 mt-8">
                    <div class="text-center max-w-[720px] mx-auto">
                        <h2 class="text-[#111318] dark:text-white text-2xl md:text-3xl font-bold leading-tight mb-3">
                            <?= get_setting('feature_title', 'Mengapa Memilih Lapak Bangsawan?') ?>
                        </h2>
                        <p class="text-[#606e8a] dark:text-[#a0aec0]">
                            <?= get_setting('feature_desc', 'Kami memprioritaskan kebersihan dan kecepatan untuk memastikan Anda mendapatkan kualitas terbaik.') ?>
                        </p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">

                        <?php for ($i = 1; $i <= 3; $i++):
                            $title = get_setting("feature_{$i}_title", "Fitur $i");
                            $desc = get_setting("feature_{$i}_desc", "Deskripsi fitur $i");
                            $icon = get_setting("feature_{$i}_icon", "check");
                            ?>
                            <!-- Card -->
                            <div class="flex flex-col gap-4 rounded-xl border border-[#dbdfe6] dark:border-[#2d3748]
                    bg-white dark:bg-[#1a202c] p-6 shadow-sm hover:shadow-md transition-shadow
                    items-center md:items-start text-center md:text-left">

                                <div class="size-12 rounded-full bg-blue-50 dark:bg-blue-900/30
                        flex items-center justify-center text-primary
                        mx-auto md:mx-0">
                                    <span class="material-symbols-outlined"><?= $icon ?></span>
                                </div>

                                <div class="flex flex-col gap-2">
                                    <h3 class="text-[#111318] dark:text-white text-lg font-bold">
                                        <?= $title ?>
                                    </h3>
                                    <p class="text-[#606e8a] dark:text-[#a0aec0] text-sm">
                                        <?= $desc ?>
                                    </p>
                                </div>
                            </div>
                        <?php endfor; ?>

                    </div>
                </div>

                <!-- Shopping Flow Section -->
                <div class="py-12 mt-8 mb-8 border-t border-slate-100 dark:border-slate-800">
                    <h2 class="text-2xl md:text-3xl font-bold text-center text-[#111318] dark:text-white mb-12">
                        Alur Belanja
                    </h2>

                    <!-- ================= DESKTOP TIMELINE ================= -->
                    <div class="hidden md:grid grid-cols-5 gap-8 relative max-w-[900px] mx-auto">
                        <!-- Horizontal line -->
                        <div class="absolute top-[28px] left-[8%] right-[8%] h-0.5 bg-slate-200 dark:bg-slate-700">
                        </div>

                        <!-- Step 1 -->
                        <div class="flex flex-col items-center gap-4 relative z-10">
                            <div
                                class="size-14 rounded-full bg-primary text-white flex items-center justify-center shadow-lg ring-8 ring-white dark:ring-[#1a202c]">
                                <span class="material-symbols-outlined">shopping_cart</span>
                            </div>
                            <h3 class="font-bold text-sm text-center">Pilih Produk</h3>
                        </div>

                        <!-- Step 2 -->
                        <div class="flex flex-col items-center gap-4 relative z-10">
                            <div
                                class="size-14 rounded-full bg-primary text-white flex items-center justify-center shadow-lg ring-8 ring-white dark:ring-[#1a202c]">
                                <span class="material-symbols-outlined">badge</span>
                            </div>
                            <h3 class="font-bold text-sm text-center">Isi Biodata</h3>
                        </div>

                        <!-- Step 3 -->
                        <div class="flex flex-col items-center gap-4 relative z-10">
                            <div
                                class="size-14 rounded-full bg-primary text-white flex items-center justify-center shadow-lg ring-8 ring-white dark:ring-[#1a202c]">
                                <span class="material-symbols-outlined">payments</span>
                            </div>
                            <h3 class="font-bold text-sm text-center">Pembayaran</h3>
                        </div>

                        <!-- Step 4 -->
                        <div class="flex flex-col items-center gap-4 relative z-10">
                            <div
                                class="size-14 rounded-full bg-primary text-white flex items-center justify-center shadow-lg ring-8 ring-white dark:ring-[#1a202c]">
                                <span class="material-symbols-outlined">inventory</span>
                            </div>
                            <h3 class="font-bold text-sm text-center">Proses</h3>
                        </div>

                        <!-- Step 5 -->
                        <div class="flex flex-col items-center gap-4 relative z-10">
                            <div
                                class="size-14 rounded-full bg-primary text-white flex items-center justify-center shadow-lg ring-8 ring-white dark:ring-[#1a202c]">
                                <span class="material-symbols-outlined">local_shipping</span>
                            </div>
                            <h3 class="font-bold text-sm text-center">Pengiriman</h3>
                        </div>
                    </div>

                    <!-- ================= MOBILE TIMELINE ================= -->
                    <div class="md:hidden max-w-sm mx-auto px-4 py-8">
                        <div class="relative">

                            <!-- STEP 1 -->
                            <div class="grid grid-cols-2 items-start relative pb-24">
                                <div class="text-right pr-10 pt-5 font-bold">Pilih Produk</div>

                                <div class="absolute left-1/2 -translate-x-1/2 top-0 z-10">
                                    <div
                                        class="size-16 rounded-full bg-primary text-white flex items-center justify-center shadow-lg ring-8 ring-white dark:ring-[#1a202c]">
                                        <span class="material-symbols-outlined">shopping_cart</span>
                                    </div>
                                </div>

                                <div
                                    class="absolute top-16 left-1/2 -translate-x-1/2 w-0.5 h-[calc(100%-4rem)] bg-slate-200 dark:bg-slate-700">
                                </div>
                            </div>

                            <!-- STEP 2 -->
                            <div class="grid grid-cols-2 items-start relative pb-24">
                                <div></div>
                                <div class="text-left pl-10 pt-5 font-bold">Isi Biodata</div>

                                <div class="absolute left-1/2 -translate-x-1/2 top-0 z-10">
                                    <div
                                        class="size-16 rounded-full bg-primary text-white flex items-center justify-center shadow-lg ring-8 ring-white dark:ring-[#1a202c]">
                                        <span class="material-symbols-outlined">badge</span>
                                    </div>
                                </div>

                                <div
                                    class="absolute top-16 left-1/2 -translate-x-1/2 w-0.5 h-[calc(100%-4rem)] bg-slate-200 dark:bg-slate-700">
                                </div>
                            </div>

                            <!-- STEP 3 -->
                            <div class="grid grid-cols-2 items-start relative pb-24">
                                <div class="text-right pr-10 pt-5 font-bold">Pembayaran</div>

                                <div class="absolute left-1/2 -translate-x-1/2 top-0 z-10">
                                    <div
                                        class="size-16 rounded-full bg-primary text-white flex items-center justify-center shadow-lg ring-8 ring-white dark:ring-[#1a202c]">
                                        <span class="material-symbols-outlined">payments</span>
                                    </div>
                                </div>

                                <div
                                    class="absolute top-16 left-1/2 -translate-x-1/2 w-0.5 h-[calc(100%-4rem)] bg-slate-200 dark:bg-slate-700">
                                </div>
                            </div>

                            <!-- STEP 4 -->
                            <div class="grid grid-cols-2 items-start relative pb-24">
                                <div></div>
                                <div class="text-left pl-10 pt-5 font-bold">Proses</div>

                                <div class="absolute left-1/2 -translate-x-1/2 top-0 z-10">
                                    <div
                                        class="size-16 rounded-full bg-primary text-white flex items-center justify-center shadow-lg ring-8 ring-white dark:ring-[#1a202c]">
                                        <span class="material-symbols-outlined">inventory</span>
                                    </div>
                                </div>

                                <div
                                    class="absolute top-16 left-1/2 -translate-x-1/2 w-0.5 h-[calc(100%-4rem)] bg-slate-200 dark:bg-slate-700">
                                </div>
                            </div>

                            <!-- STEP 5 (LAST - NO LINE) -->
                            <div class="grid grid-cols-2 items-start relative">
                                <div class="text-right pr-10 pt-5 font-bold">Pengiriman</div>

                                <div class="absolute left-1/2 -translate-x-1/2 top-0 z-10">
                                    <div
                                        class="size-16 rounded-full bg-primary text-white flex items-center justify-center shadow-lg ring-8 ring-white dark:ring-[#1a202c]">
                                        <span class="material-symbols-outlined">local_shipping</span>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Categories -->
                <div class="py-8 mt-8">
                    <div class="flex items-center justify-between mb-6">
                        <h2
                            class="text-[#111318] dark:text-white text-[22px] font-bold leading-tight tracking-[-0.015em]">
                            <?= get_setting('cat_title', 'Kategori Kami') ?>
                        </h2>
                        <a class="text-primary text-sm font-semibold hover:underline" href="<?= BASE_URL ?>market">Lihat
                            Semua</a>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <?php for ($i = 1; $i <= 4; $i++):
                            $name = get_setting("cat_{$i}_name", "Kategori $i");
                            // For images, if it's a URL (http) stick with it, if not, prepend BASE_URL if it's relative
                            $img = get_setting("cat_{$i}_img", "");
                            if (!empty($img) && strpos($img, 'http') !== 0) {
                                $img = BASE_URL . $img;
                            }
                            ?>
                            <a class="group flex flex-col gap-3 rounded-xl border border-[#dbdfe6] dark:border-[#2d3748] bg-white dark:bg-[#1a202c] p-4 items-center hover:border-primary transition-colors cursor-pointer"
                                href="<?= BASE_URL ?>market?category=<?= urlencode($name) ?>">
                                <div class="bg-center bg-no-repeat bg-cover rounded-full w-20 h-20 shrink-0 group-hover:scale-105 transition-transform"
                                    data-alt="<?= $name ?> icon" style='background-image: url("<?= $img ?>");'>
                                </div>
                                <h3 class="text-[#111318] dark:text-white text-base font-bold"><?= $name ?></h3>
                            </a>
                        <?php endfor; ?>
                    </div>
                </div>
                <!-- Best Sellers -->
                <div class="py-8">
                    <div class="flex items-center justify-between mb-6">
                        <h2
                            class="text-[#111318] dark:text-white text-[22px] font-bold leading-tight tracking-[-0.015em]">
                            <?= get_setting('prod_title', 'Produk Populer') ?>
                        </h2>
                        <a class="text-primary text-sm font-semibold hover:underline" href="<?= BASE_URL ?>market">Lihat
                            Semua</a>
                    </div>
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
                        <?php for ($i = 1; $i <= 4; $i++):
                            $name = get_setting("prod_{$i}_name", "Produk $i");
                            $desc = get_setting("prod_{$i}_desc", "Deskripsi $i");
                            $img = get_setting("prod_{$i}_img", "");
                            if (!empty($img) && strpos($img, 'http') !== 0) {
                                $img = BASE_URL . $img;
                            }
                            ?>
                            <!-- Product Card -->
                            <div
                                class="flex flex-col gap-3 rounded-xl border border-[#dbdfe6] dark:border-[#2d3748] bg-white dark:bg-[#1a202c] p-0 overflow-hidden group hover:shadow-lg transition-all">
                                <div class="w-full aspect-square bg-cover bg-center group-hover:scale-105 transition-transform duration-500"
                                    data-alt="<?= $name ?>" style='background-image: url("<?= $img ?>");'>
                                </div>
                                <div class="p-4 flex flex-col gap-2">
                                    <div class="flex justify-between items-start">
                                        <h3
                                            class="text-[#111318] dark:text-white text-base font-bold leading-tight line-clamp-1">
                                            <?= $name ?>
                                        </h3>
                                    </div>
                                    <p class="text-[#606e8a] dark:text-[#a0aec0] text-sm"><?= $desc ?></p>
                                </div>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>

                <!-- Call To Action -->
                <div
                    class="my-12 rounded-2xl bg-gradient-to-r from-primary to-blue-700 overflow-hidden relative shadow-xl">
                    <!-- Background Pattern -->
                    <div class="absolute top-0 right-0 -mt-10 -mr-10 opacity-10 rotate-12">
                        <span class="material-symbols-outlined text-[300px] text-white">shopping_basket</span>
                    </div>
                    <div class="absolute bottom-0 left-0 -mb-10 -ml-10 opacity-10 -rotate-12">
                        <span class="material-symbols-outlined text-[200px] text-white">restaurant</span>
                    </div>

                    <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-8 p-8 md:p-12">
                        <div class="flex flex-col gap-4 text-center md:text-left max-w-2xl">
                            <h2 class="text-white text-3xl md:text-4xl font-black tracking-tight leading-tight">
                                Sudah Siap Masak <br><span class="text-blue-200">Masakan Spesial?</span>
                            </h2>
                            <p class="text-blue-100 text-lg">
                                Dapatkan bahan-bahan segar berkualitas premium untuk hidangan terbaik keluarga Anda.
                                Stok terbatas untuk produk favorit!
                            </p>
                        </div>

                        <div class="flex-shrink-0">
                            <a href="<?= BASE_URL ?>market"
                                class="inline-flex items-center justify-center h-14 px-8 rounded-full bg-white text-primary font-bold text-lg hover:bg-blue-50 transition-all shadow-lg hover:shadow-xl transform hover:-translate-y-1 group">
                                Belanja Sekarang
                                <span
                                    class="material-symbols-outlined ml-2 group-hover:translate-x-1 transition-transform">arrow_forward</span>
                            </a>
                        </div>
                    </div>
                </div>
                <!-- Newsletter -->
                <div class="mt-12 rounded-2xl bg-[#101622] p-8 md:p-12 relative overflow-hidden">
                    <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-8">
                        <div class="flex flex-col gap-2 text-center md:text-left">
                            <h2 class="text-white text-2xl md:text-3xl font-bold">Dapatkan Penawaran Segar
                                Mingguan</h2>
                            <p class="text-gray-400">Berlangganan buletin kami untuk penawaran dan resep
                                eksklusif.</p>
                        </div>
                        <div class="w-full md:w-auto flex flex-col sm:flex-row gap-3">
                            <input
                                class="h-12 rounded-lg px-4 bg-white/10 border border-white/20 text-white placeholder:text-gray-400 focus:ring-primary focus:border-primary w-full sm:w-80"
                                placeholder="Masukkan email Anda" type="email" />
                            <button
                                class="h-12 px-6 bg-primary hover:bg-blue-600 text-white font-bold rounded-lg transition-colors whitespace-nowrap">
                                Berlangganan
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
        <!-- Footer -->
        <?php include ROOT_PATH . "includes/public_footer.php"; ?>
    </div>
</body>

</html>