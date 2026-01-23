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
            $order = "ORDER BY (products.stock > 0) DESC, price ASC";
            break;
        case 'price_desc':
            $order = "ORDER BY (products.stock > 0) DESC, price DESC";
            break;
        case 'name_asc':
            $order = "ORDER BY (products.stock > 0) DESC, name ASC";
            break;
    }
}

$query = "SELECT products.*, categories.slug as category_slug, categories.name as category_name FROM products LEFT JOIN categories ON products.category_id = categories.id $where $order";
$products = $conn->query($query);

// Best Seller Query
$best_seller_query = "
    SELECT p.*, c.name as category_name, SUM(oi.weight) as total_sold 
    FROM order_items oi 
    JOIN products p ON oi.product_name = p.name 
    LEFT JOIN categories c ON p.category_id = c.id
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
    <div id="toast" class="fixed top-5 right-5 z-[100] transition-all duration-300 opacity-0 invisible translate-x-4">
        <div class="bg-slate-900 text-white px-6 py-3 rounded-lg shadow-lg flex items-center gap-3">
            <span class="material-symbols-outlined text-green-400">check_circle</span>
            <span class="font-medium">Ditambahkan ke Keranjang!</span>
        </div>
    </div>

    <header
        class="sticky top-0 z-50 bg-white/90 dark:bg-background-dark/90 backdrop-blur-md border-b border-slate-200 dark:border-slate-800">
        <div class="max-w-[1400px] mx-auto px-4 md:px-8 py-3">
            <div class="flex items-center justify-between gap-4 md:gap-8">
                <a href="<?= BASE_URL ?>public/home" class="flex items-center gap-3 min-w-fit">
                    <div class="size-12 text-primary">
                        <img src="<?= BASE_URL ?>assets/images/logo.jpeg" alt="Logo">
                    </div>
                    <h1 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white hidden sm:block">Lapak
                        Bangsawan</h1>
                </a>
                <div class="flex-1 max-w-2xl">
                    <form action="" method="GET" class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="material-symbols-outlined text-slate-400">search</span>
                        </div>
                        <input name="search"
                            value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
                            class="block w-full rounded-lg border-0 py-2.5 pl-10 pr-4 text-slate-900 dark:text-white bg-slate-100 dark:bg-slate-800 ring-1 ring-inset ring-transparent placeholder:text-slate-500 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6 transition-all"
                            placeholder="Cari produk..." type="text" />
                    </form>
                </div>
                <div class="flex items-center gap-2 sm:gap-4">
                    <nav class="hidden md:flex items-center gap-6 mr-4">
                        <a class="text-sm font-medium hover:text-primary transition-colors"
                            href="<?= BASE_URL ?>public/home">Beranda</a>
                        <a class="text-sm font-medium text-primary" href="<?= BASE_URL ?>public/market">Belanja</a>
                        <a class="text-sm font-medium hover:text-primary transition-colors"
                            href="<?= BASE_URL ?>public/about">Tentang
                            Kami</a>
                    </nav>
                    <a href="<?= BASE_URL ?>public/cart"
                        class="relative p-2 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors group">
                        <span class="material-symbols-outlined">shopping_cart</span>
                        <span id="cart-badge"
                            class="absolute top-1.5 right-1.5 size-2 bg-red-500 rounded-full hidden"></span>
                    </a>
                </div>
            </div>
        </div>
    </header>
    <main class="flex-grow w-full max-w-[1400px] mx-auto px-4 md:px-8 py-6 md:py-8 gap-8 flex flex-col md:flex-row">
        <aside class="hidden md:block md:w-64 lg:w-72 shrink-0 space-y-8">
            <div class="space-y-4">
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 px-1">
                    Kategori
                </h3>
                <div class="flex flex-col gap-2">
                    <a href="<?= BASE_URL ?>public/market"
                        class="group flex items-center justify-between p-3 rounded-lg border <?php echo !isset($_GET['category']) ? 'border-primary bg-primary/5 ring-1 ring-primary' : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-card-dark hover:border-primary/50'; ?> cursor-pointer transition-all">
                        <div class="flex items-center gap-3">
                            <span class="text-sm font-medium">Semua Produk</span>
                        </div>
                    </a>
                    <?php foreach ($categories as $cat): ?>
                        <a href="?category=<?php echo $cat['slug']; ?>"
                            class="group flex items-center justify-between p-3 rounded-lg border <?php echo (isset($_GET['category']) && $_GET['category'] == $cat['slug']) ? 'border-primary bg-primary/5 ring-1 ring-primary' : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-card-dark hover:border-primary/50'; ?> cursor-pointer transition-all">
                            <div class="flex items-center gap-3">
                                <span class="text-sm font-medium"><?php echo htmlspecialchars($cat['name']); ?></span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </aside>
        <section class="flex-1 min-w-0 flex flex-col gap-6">

            <div class="flex flex-col sm:flex-row-reverse sm:items-right justify-between gap-4">
                <div class="flex flex-col sm:flex-row gap-3 items-right w-full sm:w-auto">
                    <!-- Mobile Category Dropdown -->
                    <div class="flex items-center gap-3 md:hidden">
                        <label class="text-sm font-medium text-slate-500 dark:text-slate-400 whitespace-nowrap"
                            for="category">Kategori:</label>
                        <select onchange="window.location.href = this.value"
                            class="appearance-none cursor-pointer block w-full pl-4 pr-10 py-2.5 text-sm font-medium text-slate-700 dark:text-slate-200 bg-white dark:bg-card-dark border border-slate-200 dark:border-slate-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20fill%3D%22none%22%20viewBox%3D%220%200%2024%2024%22%20stroke-width%3D%221.5%22%20stroke%3D%22%2364748b%22%3E%3Cpath%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%20d%3D%22m19.5%208.25-7.5%207.5-7.5-7.5%22%2F%3E%3C%2Fsvg%3E')] bg-[right_0.75rem_center] bg-[length:1.25rem_1.25rem] bg-no-repeat shadow-sm transition-all hover:border-primary/50">
                            <option value="market.php" class="py-2 bg-white dark:bg-card-dark">Semua Kategori</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="?category=<?php echo $cat['slug']; ?>" <?php echo (isset($_GET['category']) && $_GET['category'] == $cat['slug']) ? 'selected' : ''; ?> class="py-2 bg-white
                                dark:bg-card-dark">
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="flex items-center gap-3">
                        <label class="text-sm font-medium text-slate-500 dark:text-slate-400 whitespace-nowrap"
                            for="sort">Urutkan:</label>
                        <select
                            onchange="window.location.search = updateQueryStringParameter(window.location.search, 'sort', this.value)"
                            class="appearance-none cursor-pointer block w-full pl-4 pr-10 py-2.5 text-sm font-medium text-slate-700 dark:text-slate-200 bg-white dark:bg-card-dark border border-slate-200 dark:border-slate-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20fill%3D%22none%22%20viewBox%3D%220%200%2024%2024%22%20stroke-width%3D%221.5%22%20stroke%3D%22%2364748b%22%3E%3Cpath%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%20d%3D%22m19.5%208.25-7.5%207.5-7.5-7.5%22%2F%3E%3C%2Fsvg%3E')] bg-[right_0.75rem_center] bg-[length:1.25rem_1.25rem] bg-no-repeat shadow-sm transition-all hover:border-primary/50"
                            id="sort">
                            <option value="" class="py-2 bg-white dark:bg-card-dark">Rekomendasi</option>
                            <option value="price_asc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'price_asc') ? 'selected' : ''; ?> class="py-2 bg-white dark:bg-card-dark">Harga: Rendah ke Tinggi
                            </option>
                            <option value="price_desc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'price_desc') ? 'selected' : ''; ?> class="py-2 bg-white dark:bg-card-dark">Harga: Tinggi ke Rendah
                            </option>
                            <option value="name_asc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'name_asc') ? 'selected' : ''; ?> class="py-2 bg-white dark:bg-card-dark">Nama: A-Z</option>
                        </select>
                    </div>
                </div>
                <p class="text-slate-500 dark:text-slate-400 font-medium">Menampilkan <span
                        class="text-slate-900 dark:text-white font-bold"><?php echo mysqli_num_rows($products); ?></span>
                    hasil</p>
            </div>

            <!-- Best Seller Section -->
            <?php if (!isset($_GET['search']) && !isset($_GET['category']) && mysqli_num_rows($best_sellers) > 0): ?>
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
    <?php include ROOT_PATH . "includes/admin/footer.php"; ?>

    <script>
        // Update Query String helpers
        function updateQueryStringParameter(uri, key, value) {
            var re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
            var separator = uri.indexOf('?') !== -1 ? "&" : "?";
            if (uri.match(re)) {
                return uri.replace(re, '$1' + key + "=" + value + '$2');
            }
            else {
                return uri + separator + key + "=" + value;
            }
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
            const badge = document.getElementById('cart-badge');
            if (cart.length > 0) {
                badge.classList.remove('hidden');
                // Optional: show count
            } else {
                badge.classList.add('hidden');
            }
        }

        function showToast() {
            const toast = document.getElementById('toast');
            toast.classList.remove('opacity-0', 'invisible', 'translate-x-4');
            setTimeout(() => {
                toast.classList.add('opacity-0', 'invisible', 'translate-x-4');
            }, 3000);
        }

        // Initialize
        updateCartBadge();
    </script>
</body>

</html>