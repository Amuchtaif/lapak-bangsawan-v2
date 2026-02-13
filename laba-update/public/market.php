<?php
require_once dirname(__DIR__) . "/config/init.php";

// Fetch Categories
$cat_query = "SELECT * FROM categories";
$categories_result = $conn->query($cat_query);
$categories = [];
while ($row = mysqli_fetch_assoc($categories_result)) {
    $categories[] = $row;
}

// Fetch Products
$where = "";
if (isset($_GET['category'])) {
    $cat_slug = mysqli_real_escape_string($conn, $_GET['category']);
    $where = "WHERE categories.slug = '$cat_slug'";
}

// Search
if (isset($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $where .= ($where ? " AND " : " WHERE ") . "products.name LIKE '%$search%'";
}

// Sort
$order = "ORDER BY (products.stock > 0) DESC, products.id DESC"; // Default
if (isset($_GET['sort'])) {
    switch ($_GET['sort']) {
        case 'price_asc':
            $order = "ORDER BY products.price ASC";
            break;
        case 'price_desc':
            $order = "ORDER BY products.price DESC";
            break;
        case 'name_asc':
            $order = "ORDER BY products.name ASC";
            break;
        case '':
            $order = "ORDER BY (products.stock > 0) DESC, products.id DESC";
            break;
    }
}

$query = "SELECT products.*, categories.slug as category_slug, categories.name as category_name FROM products LEFT JOIN categories ON products.category_id = categories.id $where $order";
$products = $conn->query($query);

// Best Seller Query
$best_seller_query = "
    SELECT p.*, c.name as category_name, SUM(oi.weight) as total_sold 
    FROM order_items oi 
    JOIN orders o ON oi.order_id = o.id
    JOIN products p ON oi.product_name = p.name 
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE o.status IN ('completed', 'delivered')
    GROUP BY p.id 
    ORDER BY total_sold DESC 
    LIMIT 6";
$best_sellers = $conn->query($best_seller_query);
?>
<!DOCTYPE html>
<html class="light" lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Lapak Bangsawan - Daftar Produk</title>
    <link rel="icon" href="<?= BASE_URL ?>assets/images/favicon-laba.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com" rel="preconnect" />
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&amp;display=swap"
        rel="stylesheet" />
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap"
        rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#0d59f2",
                        "background-light": "#f5f6f8",
                        "background-dark": "#101622",
                        "card-dark": "#1e2736",
                    },
                    fontFamily: {
                        "display": ["Inter", "sans-serif"]
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
</head>

<body
    class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-white font-display antialiased flex flex-col min-h-screen">

    <!-- Toast Notification -->
    <div id="toast"
        class="fixed bottom-6 right-6 z-[100] transition-all duration-300 opacity-0 invisible translate-y-4">
        <div
            class="bg-slate-900 dark:bg-card-dark text-white px-5 py-4 rounded-xl shadow-2xl flex items-center gap-4 border border-white/10">
            <div class="bg-green-500/20 p-2 rounded-full text-green-400">
                <span class="material-symbols-outlined text-[20px]">check_circle</span>
            </div>
            <div>
                <h4 class="font-bold text-sm">Produk Ditambahkan!</h4>
                <p class="text-xs text-slate-400 mt-0.5">Cek keranjang belanja Anda.</p>
            </div>
            <a href="<?= BASE_URL ?>cart"
                class="ml-2 bg-white/10 hover:bg-white/20 text-white text-xs font-bold px-3 py-2 rounded-lg transition-colors">
                Lihat Keranjang
            </a>
        </div>
    </div>

    <?php include ROOT_PATH . "includes/public_header.php"; ?>

    <!-- Sub Header Search for Market -->
    <div class="bg-white/50 dark:bg-[#111318]/50 border-b border-slate-200/50 dark:border-white/5 py-3 lg:hidden">
        <div class="max-w-[1400px] mx-auto px-4 md:px-10">
            <form action="" method="GET" class="relative group">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <span class="material-symbols-outlined text-slate-400">search</span>
                </div>
                <input name="search"
                    value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
                    class="block w-full rounded-xl border-0 py-2.5 pl-10 pr-4 text-slate-900 dark:text-white bg-slate-100 dark:bg-white/5 ring-0 placeholder:text-slate-500 focus:ring-2 focus:ring-primary sm:text-sm transition-all"
                    placeholder="Cari produk..." type="text" />
            </form>
        </div>
    </div>
    <main
        class="flex-grow w-full max-w-[1400px] mx-auto px-4 md:px-8 pt-6 md:pt-10 pb-8 gap-8 flex flex-col md:flex-row">
        <aside class="hidden md:block md:w-64 lg:w-72 shrink-0 space-y-8">
            <div class="space-y-6">
                <div class="flex items-center justify-between px-1">
                    <h3 class="text-xs font-black uppercase tracking-[0.1em] text-slate-400 dark:text-slate-500">
                        Kategori Produk
                    </h3>
                </div>

                <div class="flex flex-col gap-1.5">
                    <?php
                    // Category Icon Mapping
                    $cat_icons = [
                        'ayam' => 'restaurant',
                        'ikan' => 'sailing',
                        'makanan-laut' => 'waves',
                        'makanan-beku' => 'ac_unit'
                    ];

                    $is_all_active = !isset($_GET['category']);
                    ?>

                    <!-- All Products -->
                    <a href="<?= BASE_URL ?>market"
                        class="group flex items-center justify-between p-3.5 rounded-2xl transition-all duration-300 <?= $is_all_active ? 'bg-primary text-white shadow-lg shadow-primary/20 scale-[1.02]' : 'bg-white dark:bg-white/5 border border-slate-200 dark:border-white/5 text-slate-600 dark:text-slate-400 hover:border-primary/50 hover:bg-slate-50 dark:hover:bg-white-[0.07]' ?>">
                        <div class="flex items-center gap-3.5">
                            <div
                                class="size-9 rounded-xl flex items-center justify-center transition-colors <?= $is_all_active ? 'bg-white/20' : 'bg-slate-100 dark:bg-white/10 group-hover:bg-primary/10 group-hover:text-primary' ?>">
                                <span class="material-symbols-outlined !text-[20px]">grid_view</span>
                            </div>
                            <span class="text-sm font-bold">Semua Produk</span>
                        </div>
                        <?php if ($is_all_active): ?>
                            <div class="size-1.5 rounded-full bg-white animate-pulse"></div>
                        <?php endif; ?>
                    </a>

                    <?php foreach ($categories as $cat):
                        $is_cat_active = (isset($_GET['category']) && $_GET['category'] == $cat['slug']);
                        $icon = $cat_icons[$cat['slug']] ?? 'category';
                        ?>
                        <a href="?category=<?php echo $cat['slug']; ?>"
                            class="group flex items-center justify-between p-3.5 rounded-2xl transition-all duration-300 <?= $is_cat_active ? 'bg-primary text-white shadow-lg shadow-primary/20 scale-[1.02]' : 'bg-white dark:bg-white/5 border border-slate-200 dark:border-white/5 text-slate-600 dark:text-slate-400 hover:border-primary/50 hover:bg-slate-50 dark:hover:bg-white-[0.07]' ?>">
                            <div class="flex items-center gap-3.5">
                                <div
                                    class="size-9 rounded-xl flex items-center justify-center transition-colors <?= $is_cat_active ? 'bg-white/20' : 'bg-slate-100 dark:bg-white/10 group-hover:bg-primary/10 group-hover:text-primary' ?>">
                                    <span class="material-symbols-outlined !text-[20px]"><?= $icon ?></span>
                                </div>
                                <span class="text-sm font-bold"><?php echo htmlspecialchars($cat['name']); ?></span>
                            </div>
                            <?php if ($is_cat_active): ?>
                                <div class="size-1.5 rounded-full bg-white animate-pulse"></div>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Sorting Filter -->
            <div class="space-y-4">
                <div class="flex items-center justify-between px-1">
                    <h3 class="text-xs font-black uppercase tracking-[0.1em] text-slate-400 dark:text-slate-500">
                        Urutkan Produk
                    </h3>
                </div>
                <div class="custom-select-wrapper relative w-full"
                    data-onchange="window.location.href = updateQueryStringParameter('sort', '%val%')">
                    <select class="hidden">
                        <option value="">Rekomendasi</option>
                        <option value="price_asc">Harga: Rendah ke Tinggi</option>
                        <option value="price_desc">Harga: Tinggi ke Rendah</option>
                        <option value="name_asc">Nama: A-Z</option>
                    </select>
                    <button type="button"
                        class="custom-select-trigger w-full flex items-center justify-between rounded-2xl border border-slate-200 dark:border-white/5 bg-white dark:bg-white/5 px-4 py-3.5 text-sm font-bold text-slate-700 dark:text-slate-200 transition-all text-left hover:border-primary/50">
                        <span class="selected-label">
                            <?php
                            $sort = $_GET['sort'] ?? '';
                            if ($sort == 'price_asc')
                                echo 'Harga: Rendah ke Tinggi';
                            else if ($sort == 'price_desc')
                                echo 'Harga: Tinggi ke Rendah';
                            else if ($sort == 'name_asc')
                                echo 'Nama: A-Z';
                            else
                                echo 'Rekomendasi';
                            ?>
                        </span>
                        <span
                            class="material-symbols-outlined text-slate-400 selected-icon transition-transform">expand_more</span>
                    </button>
                    <div
                        class="custom-select-options hidden absolute z-[110] w-full mt-2 bg-white dark:bg-[#1a202c] border border-slate-200 dark:border-white/5 rounded-2xl shadow-2xl opacity-0 translate-y-2 transition-all duration-200 overflow-hidden">
                        <div class="max-h-60 overflow-y-auto p-2">
                            <div class="custom-option px-4 py-3 rounded-xl hover:bg-primary/5 hover:text-primary cursor-pointer transition-colors text-sm font-bold <?= $sort == '' ? 'bg-primary/10 text-primary font-black' : '' ?>"
                                data-value="">Rekomendasi</div>
                            <div class="custom-option px-4 py-3 rounded-xl hover:bg-primary/5 hover:text-primary cursor-pointer transition-colors text-sm font-bold <?= $sort == 'price_asc' ? 'bg-primary/10 text-primary font-black' : '' ?>"
                                data-value="price_asc">Harga: Rendah ke Tinggi</div>
                            <div class="custom-option px-4 py-3 rounded-xl hover:bg-primary/5 hover:text-primary cursor-pointer transition-colors text-sm font-bold <?= $sort == 'price_desc' ? 'bg-primary/10 text-primary font-black' : '' ?>"
                                data-value="price_desc">Harga: Tinggi ke Rendah</div>
                            <div class="custom-option px-4 py-3 rounded-xl hover:bg-primary/5 hover:text-primary cursor-pointer transition-colors text-sm font-bold <?= $sort == 'name_asc' ? 'bg-primary/10 text-primary font-black' : '' ?>"
                                data-value="name_asc">Nama: A-Z</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Optional: Delivery Info Banner in Sidebar -->
            <div
                class="p-5 rounded-3xl bg-slate-900 dark:bg-white text-white dark:text-slate-900 overflow-hidden relative group">
                <div
                    class="absolute top-0 right-0 -mr-6 -mt-6 opacity-10 group-hover:scale-110 transition-transform duration-500">
                    <span class="material-symbols-outlined text-[100px]">local_shipping</span>
                </div>
                <div class="relative z-10 flex flex-col gap-3">
                    <span class="text-[10px] font-black uppercase tracking-widest opacity-60">Info Pengiriman</span>
                    <p class="text-sm font-bold leading-snug">Gratis ongkir untuk wilayah Cirebon Kota!</p>
                    <a href="<?= BASE_URL ?>about"
                        class="text-[10px] font-black uppercase tracking-widest underline decoration-primary underline-offset-4">Pelajari
                        Selengkapnya</a>
                </div>
            </div>
        </aside>
        <section class="flex-1 min-w-0 flex flex-col gap-6">

            <!-- Toolbar Mobile (Only Categories) -->
            <div class="md:hidden flex flex-col gap-4 mb-2">
                <div class="flex items-center gap-3">
                    <label class="text-xs font-black uppercase tracking-widest text-slate-400"
                        for="category">Kategori</label>
                    <div class="custom-select-wrapper relative flex-1"
                        data-onchange="window.location.href = updateQueryStringParameter('category', '%val%')">
                        <select class="hidden">
                            <option value="">Semua Kategori</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['slug']; ?>" <?php echo (isset($_GET['category']) && $_GET['category'] == $cat['slug']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="button"
                            class="custom-select-trigger w-full flex items-center justify-between rounded-xl border border-slate-200 dark:border-white/5 bg-white dark:bg-white/5 px-4 py-2.5 text-sm font-bold text-slate-700 dark:text-slate-200 transition-all text-left">
                            <span class="selected-label">
                                <?php
                                $current_cat = 'Semua Kategori';
                                if (isset($_GET['category'])) {
                                    foreach ($categories as $cat) {
                                        if ($cat['slug'] == $_GET['category']) {
                                            $current_cat = $cat['name'];
                                            break;
                                        }
                                    }
                                }
                                echo htmlspecialchars($current_cat);
                                ?>
                            </span>
                            <span
                                class="material-symbols-outlined text-slate-400 selected-icon transition-transform">expand_more</span>
                        </button>
                        <div
                            class="custom-select-options hidden absolute z-[110] w-full mt-2 bg-white dark:bg-[#1a202c] border border-slate-200 dark:border-white/5 rounded-xl shadow-xl opacity-0 translate-y-2 transition-all duration-200 overflow-hidden">
                            <div class="max-h-60 overflow-y-auto p-2">
                                <div class="custom-option px-4 py-2 rounded-lg hover:bg-primary/5 hover:text-primary cursor-pointer transition-colors text-sm <?= !isset($_GET['category']) ? 'bg-primary/10 text-primary font-bold' : '' ?>"
                                    data-value="">Semua Kategori</div>
                                <?php foreach ($categories as $cat): ?>
                                    <div class="custom-option px-4 py-2 rounded-lg hover:bg-primary/5 hover:text-primary cursor-pointer transition-colors text-sm <?= (isset($_GET['category']) && $_GET['category'] == $cat['slug']) ? 'bg-primary/10 text-primary font-bold' : '' ?>"
                                        data-value="<?php echo $cat['slug']; ?>">
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <label class="text-xs font-black uppercase tracking-widest text-slate-400"
                        for="sort-mobile">Urutkan</label>
                    <div class="custom-select-wrapper relative flex-1"
                        data-onchange="window.location.href = updateQueryStringParameter('sort', '%val%')">
                        <select class="hidden">
                            <option value="">Rekomendasi</option>
                            <option value="price_asc">Harga: Rendah ke Tinggi</option>
                            <option value="price_desc">Harga: Tinggi ke Rendah</option>
                            <option value="name_asc">Nama: A-Z</option>
                        </select>
                        <button type="button"
                            class="custom-select-trigger w-full flex items-center justify-between rounded-xl border border-slate-200 dark:border-white/5 bg-white dark:bg-white/5 px-4 py-2.5 text-sm font-bold text-slate-700 dark:text-slate-200 transition-all text-left">
                            <span class="selected-label">
                                <?php
                                $sort = $_GET['sort'] ?? '';
                                if ($sort == 'price_asc')
                                    echo 'Harga: Rendah ke Tinggi';
                                else if ($sort == 'price_desc')
                                    echo 'Harga: Tinggi ke Rendah';
                                else if ($sort == 'name_asc')
                                    echo 'Nama: A-Z';
                                else
                                    echo 'Rekomendasi';
                                ?>
                            </span>
                            <span
                                class="material-symbols-outlined text-slate-400 selected-icon transition-transform">expand_more</span>
                        </button>
                        <div
                            class="custom-select-options hidden absolute z-[110] w-full mt-2 bg-white dark:bg-[#1a202c] border border-slate-200 dark:border-white/5 rounded-xl shadow-xl opacity-0 translate-y-2 transition-all duration-200 overflow-hidden">
                            <div class="max-h-60 overflow-y-auto p-2">
                                <div class="custom-option px-4 py-2 rounded-lg hover:bg-primary/5 hover:text-primary cursor-pointer transition-colors text-sm <?= $sort == '' ? 'bg-primary/10 text-primary font-bold' : '' ?>"
                                    data-value="">Rekomendasi</div>
                                <div class="custom-option px-4 py-2 rounded-lg hover:bg-primary/5 hover:text-primary cursor-pointer transition-colors text-sm <?= $sort == 'price_asc' ? 'bg-primary/10 text-primary font-bold' : '' ?>"
                                    data-value="price_asc">Harga: Rendah ke Tinggi</div>
                                <div class="custom-option px-4 py-2 rounded-lg hover:bg-primary/5 hover:text-primary cursor-pointer transition-colors text-sm <?= $sort == 'price_desc' ? 'bg-primary/10 text-primary font-bold' : '' ?>"
                                    data-value="price_desc">Harga: Tinggi ke Rendah</div>
                                <div class="custom-option px-4 py-2 rounded-lg hover:bg-primary/5 hover:text-primary cursor-pointer transition-colors text-sm <?= $sort == 'name_asc' ? 'bg-primary/10 text-primary font-bold' : '' ?>"
                                    data-value="name_asc">Nama: A-Z</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Best Seller Section -->
            <?php if (!isset($_GET['search']) && !isset($_GET['category']) && !isset($_GET['sort']) && mysqli_num_rows($best_sellers) > 0): ?>
                <div class="mb-8">
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                        <span class="material-symbols-outlined text-amber-500">trophy</span>
                        Produk Terlaris
                    </h2>
                    <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-3 gap-3 sm:gap-6">
                        <?php while ($bs_product = mysqli_fetch_assoc($best_sellers)):
                            $category = $bs_product['category_name'];
                            $isPcsCategory = in_array($category, ['Frozen Food', 'Produk Jadi']);
                            $unit = $isPcsCategory ? 'pcs' : 'kg';
                            $step = $isPcsCategory ? 1 : 0.5;
                            $initialQtyDisplay = $isPcsCategory ? '1' : '1.0';
                            $initialQty = 1;
                            ?>
                            <article
                                class="group bg-white dark:bg-card-dark rounded-xl shadow-sm border border-amber-200 dark:border-amber-900/30 hover:shadow-md hover:border-amber-500/50 transition-all duration-300 flex flex-col h-full relative overflow-hidden"
                                data-id="<?php echo $bs_product['id']; ?>" data-price="<?php echo $bs_product['price']; ?>"
                                data-name="<?php echo htmlspecialchars($bs_product['name']); ?>" data-image="<?php
                                   $img_src = $bs_product['image'];
                                   if ($img_src && !filter_var($img_src, FILTER_VALIDATE_URL)) {
                                       $img_src = BASE_URL . $img_src;
                                   }
                                   echo htmlspecialchars($img_src);
                                   ?>" data-category="<?php echo htmlspecialchars($bs_product['category_name']); ?>">

                                <!-- Badge Best Seller -->
                                <div
                                    class="absolute top-0 right-0 bg-amber-500 text-white text-[10px] sm:text-xs font-bold px-3 py-1 rounded-bl-lg shadow-sm z-10 flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[14px]">star</span>
                                    Terlaris
                                </div>

                                <div class="relative h-32 sm:h-48 overflow-hidden rounded-t-xl bg-slate-100">
                                    <?php
                                    if ($bs_product['image']) {
                                        $img_src = $bs_product['image'];
                                        if (!filter_var($img_src, FILTER_VALIDATE_URL)) {
                                            $img_src = BASE_URL . $img_src;
                                        }
                                        ?>
                                        <img alt="<?php echo htmlspecialchars($bs_product['name']); ?>"
                                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                                            src="<?php echo htmlspecialchars($img_src); ?>" />
                                    <?php } else { ?>
                                        <div class="w-full h-full flex items-center justify-center text-slate-400">
                                            <span class="material-symbols-outlined text-4xl">image_not_supported</span>
                                        </div>
                                    <?php } ?>
                                </div>
                                <div class="p-3 sm:p-5 flex flex-col flex-1">
                                    <div class="flex justify-between items-start gap-2 mb-2">
                                        <h3
                                            class="font-bold text-sm sm:text-lg leading-tight text-slate-900 dark:text-white group-hover:text-primary transition-colors">
                                            <?php echo htmlspecialchars($bs_product['name']); ?>
                                        </h3>
                                    </div>
                                    <p class="text-slate-500 dark:text-slate-400 text-xs sm:text-sm mb-2 sm:mb-4 line-clamp-2" title="<?php echo htmlspecialchars($bs_product['description']); ?>">
                                        <?php echo htmlspecialchars($bs_product['description']); ?>
                                    </p>
                                    <!-- Sold Count -->
                                    <p
                                        class="text-xs text-amber-600 dark:text-amber-400 font-medium mb-3 flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[14px]">local_fire_department</span>
                                        Terjual <?php echo floatval($bs_product['total_sold']) . ' ' . $unit; ?>
                                    </p>

                                    <div class="mt-auto pt-4 border-t border-slate-100 dark:border-slate-700 space-y-4">
                                        <div class="flex items-baseline justify-between mb-2 flex-nowrap gap-2">
                                            <span class="text-[10px] sm:text-xs font-semibold uppercase text-slate-400">Harga
                                                Satuan</span>
                                            <span
                                                class="font-medium text-[11px] sm:text-sm text-slate-700 dark:text-slate-300">Rp
                                                <?php echo number_format($bs_product['price'], 0, ',', '.'); ?> /
                                                <?php echo $unit; ?></span>
                                        </div>

                                        <!-- Weight Control -->
                                        <div
                                            class="bg-background-light dark:bg-slate-800 rounded-lg p-1.5 sm:p-3 flex items-center justify-between mb-2">
                                            <span
                                                class="text-[9px] sm:text-xs font-bold uppercase text-slate-500"><?php echo $isPcsCategory ? 'Jumlah' : 'Berat'; ?></span>
                                            <div class="flex items-center gap-1 sm:gap-3">
                                                <button onclick="updateWeight(this, -<?php echo $step; ?>)"
                                                    class="size-5 sm:size-6 flex items-center justify-center rounded bg-white dark:bg-slate-700 shadow-sm text-slate-600 dark:text-slate-300 hover:text-primary hover:bg-slate-50 transition-colors">
                                                    <span class="material-symbols-outlined text-xs sm:text-base">remove</span>
                                                </button>
                                                <div class="text-[10px] sm:text-sm font-bold w-10 sm:w-16 text-center">
                                                    <span class="weight-display"><?php echo $initialQtyDisplay; ?></span>
                                                    <?php echo $unit; ?>
                                                </div>
                                                <button onclick="updateWeight(this, <?php echo $step; ?>)"
                                                    class="size-5 sm:size-6 flex items-center justify-center rounded bg-white dark:bg-slate-700 shadow-sm text-slate-600 dark:text-slate-300 hover:text-primary hover:bg-slate-50 transition-colors">
                                                    <span class="material-symbols-outlined text-xs sm:text-base">add</span>
                                                </button>
                                            </div>
                                        </div>

                                        <div class="flex items-center justify-between gap-2 sm:gap-4">
                                            <div class="flex flex-col">
                                                <span
                                                    class="text-[8px] uppercase font-bold text-slate-400 tracking-wide">Total</span>
                                                <span
                                                    class="text-[10px] sm:text-2xl font-black text-slate-900 dark:text-white">Rp
                                                    <span
                                                        class="total-price-display"><?php echo number_format($bs_product['price'], 0, ',', '.'); ?></span></span>
                                            </div>
                                            <button onclick="addToCart(this)"
                                                class="flex-1 bg-primary hover:bg-blue-700 text-white font-bold py-1.5 px-3 sm:py-2.5 sm:px-4 rounded-lg shadow-sm shadow-blue-500/20 active:scale-95 transition-all flex justify-center items-center gap-1 sm:gap-2">
                                                <span class="material-symbols-outlined text-xs sm:text-sm">shopping_cart</span>
                                                <span class="text-xs sm:text-base">Beli</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        <?php endwhile; ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-3 gap-3 sm:gap-6 min-h-[50vh]">
                <?php while ($product = mysqli_fetch_assoc($products)):

                    $category = $product['category_name'];

                    // Kategori yang dihitung per pcs
                    $isPcsCategory = in_array($category, ['Frozen Food', 'Produk Jadi']);

                    $unit = $product['unit'] ?: ($isPcsCategory ? 'pcs' : 'kg');
                    $step = ($unit == 'pcs' || $unit == 'box' || $unit == 'porsi') ? 1 : 0.5;
                    $min = ($unit == 'pcs' || $unit == 'box' || $unit == 'porsi') ? 1 : 0.5;

                    $initialQty = $isPcsCategory ? 1 : 1.0;
                    $initialQtyDisplay = $isPcsCategory ? '1' : '1.0';

                    ?>

                    <article
                        class="group bg-white dark:bg-card-dark rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 hover:shadow-md hover:border-primary/30 transition-all duration-300 flex flex-col h-full"
                        data-id="<?php echo $product['id']; ?>" data-price="<?php echo $product['price']; ?>"
                        data-name="<?php echo htmlspecialchars($product['name']); ?>" data-image="<?php
                           $img_src = $product['image'];
                           if ($img_src && !filter_var($img_src, FILTER_VALIDATE_URL)) {
                               $img_src = BASE_URL . $img_src;
                           }
                           echo htmlspecialchars($img_src);
                           ?>" data-category="<?php echo htmlspecialchars($product['category_name']); ?>"
                        data-unit="<?php echo htmlspecialchars($unit); ?>">
                        <div class="relative h-32 sm:h-48 overflow-hidden rounded-t-xl bg-slate-100">
                            <?php
                            if ($product['image']) {
                                $img_src = $product['image'];
                                if (!filter_var($img_src, FILTER_VALIDATE_URL)) {
                                    $img_src = BASE_URL . $img_src;
                                }
                                ?>
                                <img alt="<?php echo htmlspecialchars($product['name']); ?>"
                                    class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                                    src="<?php echo htmlspecialchars($img_src); ?>" />
                            <?php } else { ?>
                                <div class="w-full h-full flex items-center justify-center text-slate-400">
                                    <span class="material-symbols-outlined text-4xl">image_not_supported</span>
                                </div>
                            <?php } ?>
                            <?php if ($product['stock'] == 0): ?>
                                <div
                                    class="absolute top-3 left-3 bg-slate-800 text-white text-xs font-bold px-2 py-1 rounded shadow-sm">
                                    Stok Habis
                                </div>
                            <?php elseif ($product['stock'] < 5): ?>
                                <div
                                    class="absolute top-3 left-3 bg-red-500 text-white text-xs font-bold px-2 py-1 rounded shadow-sm">
                                    Stok Menipis
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="p-3 sm:p-5 flex flex-col flex-1">
                            <div class="flex justify-between items-start gap-2 mb-2">
                                <h3
                                    class="font-bold text-sm sm:text-lg leading-tight text-slate-900 dark:text-white group-hover:text-primary transition-colors">
                                    <?php echo htmlspecialchars($product['name']); ?>
                                </h3>
                            </div>
                            <p class="text-slate-500 dark:text-slate-400 text-xs sm:text-sm mb-2 sm:mb-4 line-clamp-2">
                                <?php echo htmlspecialchars($product['description']); ?>
                            </p>

                            <div class="mt-auto pt-4 border-t border-slate-100 dark:border-slate-700 space-y-4">
                                <div class="flex items-baseline justify-between mb-2 flex-nowrap gap-2">
                                    <span class="text-[10px] sm:text-xs font-semibold uppercase text-slate-400">Harga
                                        Satuan</span>
                                    <span class="font-medium text-[11px] sm:text-sm text-slate-700 dark:text-slate-300">Rp
                                        <?php echo number_format($product['price'], 0, ',', '.'); ?> /
                                        <?php echo $unit; ?></span>
                                </div>

                                <!-- Weight Control -->
                                <div
                                    class="bg-background-light dark:bg-slate-800 rounded-lg p-1.5 sm:p-3 flex items-center justify-between mb-2">
                                    <span
                                        class="text-[9px] sm:text-xs font-bold uppercase text-slate-500"><?php echo $isPcsCategory ? 'Jumlah' : 'Berat'; ?></span>
                                    <div class="flex items-center gap-1 sm:gap-3">
                                        <button onclick="updateWeight(this, -<?php echo $step; ?>)"
                                            class="size-5 sm:size-6 flex items-center justify-center rounded bg-white dark:bg-slate-700 shadow-sm text-slate-600 dark:text-slate-300 hover:text-primary hover:bg-slate-50 transition-colors">
                                            <span class="material-symbols-outlined text-xs sm:text-base">remove</span>
                                        </button>
                                        <div class="text-[10px] sm:text-sm font-bold w-10 sm:w-16 text-center">
                                            <span class="weight-display"><?php echo $initialQtyDisplay; ?></span>
                                            <?php echo $unit; ?>
                                        </div>
                                        <button onclick="updateWeight(this, <?php echo $step; ?>)"
                                            class="size-5 sm:size-6 flex items-center justify-center rounded bg-white dark:bg-slate-700 shadow-sm text-slate-600 dark:text-slate-300 hover:text-primary hover:bg-slate-50 transition-colors">
                                            <span class="material-symbols-outlined text-xs sm:text-base">add</span>
                                        </button>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between gap-2 sm:gap-4">
                                    <div class="flex flex-col">
                                        <span
                                            class="text-[8px] uppercase font-bold text-slate-400 tracking-wide">Total</span>
                                        <span class="text-[10px] sm:text-2xl font-black text-slate-900 dark:text-white">Rp
                                            <span
                                                class="total-price-display"><?php echo number_format($product['price'] * $initialQty, 0, ',', '.'); ?></span></span>
                                    </div>
                                    <?php if ($product['stock'] > 0): ?>
                                        <button onclick="addToCart(this)"
                                            class="flex-1 bg-primary hover:bg-blue-700 text-white font-bold py-1.5 px-3 sm:py-2.5 sm:px-4 rounded-lg shadow-sm shadow-blue-500/20 active:scale-95 transition-all flex justify-center items-center gap-1 sm:gap-2">
                                            <span class="material-symbols-outlined text-xs sm:text-sm">shopping_cart</span>
                                            <span class="text-xs sm:text-base">Beli</span>
                                        </button>
                                    <?php else: ?>
                                        <button disabled
                                            class="flex-1 bg-slate-300 dark:bg-slate-700 text-slate-500 cursor-not-allowed font-bold py-1.5 px-3 sm:py-2.5 sm:px-4 rounded-lg shadow-none flex justify-center items-center gap-1 sm:gap-2">
                                            <span class="material-symbols-outlined text-xs sm:text-sm">block</span>
                                            <span class="text-xs sm:text-base">Habis</span>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </article>
                <?php endwhile; ?>
            </div>

            <?php if (mysqli_num_rows($products) == 0): ?>
                <div class="text-center py-12">
                    <span class="material-symbols-outlined text-6xl text-slate-300 mb-4">search_off</span>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Produk tidak ditemukan</h3>
                    <p class="text-slate-500">Coba sesuaikan pencarian atau filter kategori Anda.</p>
                </div>
            <?php endif; ?>

        </section>
    </main>

    <script>
        // Update Query String helpers
        function updateQueryStringParameter(key, value) {
            const url = new URL(window.location.href);
            if (value) {
                url.searchParams.set(key, value);
            } else {
                url.searchParams.delete(key);
            }
            // Reset to page 1 if any filter changes
            if (url.searchParams.has('page')) {
                url.searchParams.set('page', '1');
            }
            return url.toString();
        }

        // Product Logic
        function updateWeight(btn, change) {
            const article = btn.closest('article');
            const weightDisplay = article.querySelector('.weight-display');
            const totalPriceDisplay = article.querySelector('.total-price-display');
            const unitPrice = parseFloat(article.dataset.price);
            const unit = article.dataset.unit;
            const isPcsCategory = (unit == 'pcs' || unit == 'box' || unit == 'porsi');

            let currentWeight = parseFloat(weightDisplay.innerText);
            let newWeight = currentWeight + change;

            const min = isPcsCategory ? 1 : 0.5;
            if (newWeight < min) newWeight = min;

            weightDisplay.innerText = isPcsCategory
                ? newWeight
                : newWeight.toFixed(1);

            const total = unitPrice * newWeight;
            totalPriceDisplay.innerText = new Intl.NumberFormat('id-ID').format(total);
        }

        function addToCart(btn) {
            const article = btn.closest('article');
            const id = article.dataset.id;
            const name = article.dataset.name;
            const image = article.dataset.image;
            const category = article.dataset.category;
            const unit = article.dataset.unit;
            const unitPrice = parseFloat(article.dataset.price);
            const weight = parseFloat(article.querySelector('.weight-display').innerText);
            const totalPrice = unitPrice * weight;

            let cart = JSON.parse(localStorage.getItem('cart')) || [];

            cart.push({
                product_id: id,
                name: name,
                image: image,
                price: unitPrice,
                weight: weight,
                unit: unit,
                category: category,
                total_price: totalPrice,
                added_at: Date.now()
            });

            localStorage.setItem('cart', JSON.stringify(cart));
            updateCartBadge();
            showToast();
        }

        function updateCartBadge() {
            let cart = JSON.parse(localStorage.getItem('cart')) || [];
            const badge = document.getElementById('cart-badge-header');
            if (cart.length > 0) {
                badge.classList.remove('hidden');
                // Optional: show count
            } else {
                badge.classList.add('hidden');
            }
        }

        function showToast() {
            const toast = document.getElementById('toast');
            toast.classList.remove('opacity-0', 'invisible', 'translate-y-4');
            setTimeout(() => {
                toast.classList.add('opacity-0', 'invisible', 'translate-y-4');
            }, 3000);
        }

        // Initialize
        updateCartBadge();

        // Custom Select Logic
        document.querySelectorAll('.custom-select-wrapper').forEach(wrapper => {
            const trigger = wrapper.querySelector('.custom-select-trigger');
            const options = wrapper.querySelector('.custom-select-options');
            const customOptions = wrapper.querySelectorAll('.custom-option');
            const select = wrapper.querySelector('select');
            const label = wrapper.querySelector('.selected-label');
            const icon = wrapper.querySelector('.selected-icon');

            trigger.addEventListener('click', (e) => {
                e.stopPropagation();
                // Close other selects
                document.querySelectorAll('.custom-select-options').forEach(opt => {
                    if (opt !== options) {
                        opt.classList.add('hidden', 'opacity-0', 'translate-y-2');
                        opt.previousElementSibling.querySelector('.selected-icon').classList.remove('rotate-180');
                    }
                });

                const isHidden = options.classList.contains('hidden');
                if (isHidden) {
                    options.classList.remove('hidden');
                    setTimeout(() => {
                        options.classList.remove('opacity-0', 'translate-y-2');
                        icon.classList.add('rotate-180');
                    }, 10);
                } else {
                    options.classList.add('opacity-0', 'translate-y-2');
                    icon.classList.remove('rotate-180');
                    setTimeout(() => options.classList.add('hidden'), 200);
                }
            });

            customOptions.forEach(opt => {
                opt.addEventListener('click', () => {
                    const val = opt.dataset.value;
                    label.innerText = opt.innerText;

                    if (select) select.value = val;

                    const onchange = wrapper.dataset.onchange;
                    if (onchange) {
                        const action = onchange.replace('%val%', val);
                        eval(action);
                    }

                    options.classList.add('opacity-0', 'translate-y-2');
                    icon.classList.remove('rotate-180');
                    setTimeout(() => options.classList.add('hidden'), 200);
                });
            });
        });

        // Close dropdowns when clicking outside
        document.addEventListener('click', () => {
            document.querySelectorAll('.custom-select-options').forEach(opt => {
                opt.classList.add('opacity-0', 'translate-y-2');
                opt.previousElementSibling.querySelector('.selected-icon').classList.remove('rotate-180');
                setTimeout(() => opt.classList.add('hidden'), 200);
            });
        });
    </script>
    <?php include ROOT_PATH . "includes/public_footer.php"; ?>
</body>

</html>