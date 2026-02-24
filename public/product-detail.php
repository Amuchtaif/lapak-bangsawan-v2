<?php
require_once dirname(__DIR__) . "/config/init.php";

// Fetch Product Details
if (!isset($_GET['id'])) {
    header("Location: " . BASE_URL . "market");
    exit();
}

$product_id = mysqli_real_escape_string($conn, $_GET['id']);
$query = "SELECT products.*, categories.name as category_name, categories.slug as category_slug 
          FROM products 
          LEFT JOIN categories ON products.category_id = categories.id 
          WHERE products.id = '$product_id' LIMIT 1";
$result = $conn->query($query);

if (mysqli_num_rows($result) === 0) {
    header("Location: " . BASE_URL . "market");
    exit();
}

$product = mysqli_fetch_assoc($result);

// Fetch Similar Products
$cat_id = $product['category_id'];
$similar_query = "SELECT products.*, categories.name as category_name 
                  FROM products 
                  LEFT JOIN categories ON products.category_id = categories.id 
                  WHERE products.category_id = '$cat_id' AND products.id != '$product_id' 
                  ORDER BY RAND() LIMIT 4";
$similar_products = $conn->query($similar_query);

// Unit settings (same logic as in market.php)
$category_name = $product['category_name'];
$isPcsCategory = in_array($category_name, ['Frozen Food', 'Produk Jadi']);
$unit = $product['unit'] ?: ($isPcsCategory ? 'pcs' : 'kg');
$step = ($unit == 'pcs' || $unit == 'box' || $unit == 'porsi') ? 1 : 0.5;
$initialQty = $isPcsCategory ? 1 : 1.0;
$initialQtyDisplay = $isPcsCategory ? '1' : '1.0';

?>
<!DOCTYPE html>
<html class="light" lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title><?= htmlspecialchars($product['name']) ?> - Lapak Bangsawan</title>
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

    <main class="flex-grow w-full max-w-[1200px] mx-auto px-4 md:px-8 py-6 md:py-10">
        <!-- Breadcrumbs -->
        <nav class="flex items-center gap-1.5 text-[10px] md:text-xs font-bold uppercase tracking-widest text-slate-400 mb-4 md:mb-8 overflow-x-auto whitespace-nowrap pb-2 md:pb-0 no-scrollbar">
            <a href="<?= BASE_URL ?>home" class="hover:text-primary transition-colors shrink-0">Home</a>
            <span class="material-symbols-outlined text-[12px] md:text-[14px] shrink-0">chevron_right</span>
            <a href="<?= BASE_URL ?>market" class="hover:text-primary transition-colors shrink-0">Market</a>
            <span class="material-symbols-outlined text-[12px] md:text-[14px] shrink-0">chevron_right</span>
            <a href="<?= BASE_URL ?>market?category=<?= $product['category_slug'] ?>" class="hover:text-primary transition-colors shrink-0"><?= htmlspecialchars($product['category_name']) ?></a>
            <span class="material-symbols-outlined text-[12px] md:text-[14px] shrink-0">chevron_right</span>
            <span class="text-slate-600 dark:text-slate-300 truncate"><?= htmlspecialchars($product['name']) ?></span>
        </nav>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 lg:gap-12">
            <!-- Product Image -->
            <div class="space-y-4">
                <div class="aspect-square relative rounded-2xl md:rounded-3xl overflow-hidden bg-white dark:bg-card-dark border border-slate-200 dark:border-white/5 shadow-sm group">
                    <?php
                    $img_src = $product['image'];
                    if ($img_src && !filter_var($img_src, FILTER_VALIDATE_URL)) {
                        $img_src = BASE_URL . $img_src;
                    }
                    if ($img_src): ?>
                        <img src="<?= htmlspecialchars($img_src) ?>" 
                             alt="<?= htmlspecialchars($product['name']) ?>" 
                             class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                    <?php else: ?>
                        <div class="w-full h-full flex flex-col items-center justify-center text-slate-300 dark:text-slate-700">
                            <span class="material-symbols-outlined text-8xl mb-2">image_not_supported</span>
                            <span class="font-bold uppercase tracking-widest text-sm">No Image</span>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Mobile Sticky Back Button -->
                    <a href="javascript:history.back()" class="md:hidden absolute top-4 left-4 size-10 rounded-full bg-white/80 dark:bg-black/50 backdrop-blur-md flex items-center justify-center text-slate-800 dark:text-white shadow-lg">
                        <span class="material-symbols-outlined">arrow_back</span>
                    </a>
                </div>
            </div>

            <!-- Product info -->
            <div class="flex flex-col h-full" 
                 data-id="<?= $product['id'] ?>" 
                 data-price="<?= $product['price'] ?>"
                 data-name="<?= htmlspecialchars($product['name']) ?>" 
                 data-image="<?= htmlspecialchars($img_src) ?>" 
                 data-category="<?= htmlspecialchars($product['category_name']) ?>"
                 data-unit="<?= htmlspecialchars($unit) ?>">
                
                <div class="mb-6">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-[9px] md:text-[10px] font-black uppercase tracking-widest bg-primary/10 text-primary mb-2 md:mb-3">
                        <?= htmlspecialchars($product['category_name']) ?>
                    </span>
                    <h1 class="text-2xl md:text-3xl lg:text-4xl font-black text-slate-900 dark:text-white leading-tight mb-2 md:mb-4">
                        <?= htmlspecialchars($product['name']) ?>
                    </h1>
                    <div class="flex items-center gap-4 text-sm font-medium">
                        <?php if ($product['stock'] > 0): ?>
                            <div class="flex items-center gap-1.5 text-green-500">
                                <span class="material-symbols-outlined text-[18px]">check_circle</span>
                                Stok Tersedia: <?= (int)$product['stock'] ?> <?= $unit ?>
                            </div>
                        <?php else: ?>
                            <div class="flex items-center gap-1.5 text-red-500 font-bold uppercase tracking-widest text-xs">
                                <span class="material-symbols-outlined text-[18px]">block</span>
                                Habis Terjual
                            </div>
                        <?php endif; ?>

                    </div>
                </div>

                <div class="bg-white dark:bg-card-dark rounded-3xl p-6 border border-slate-200 dark:border-white/5 shadow-sm space-y-6">
                    <div>
                        <span class="text-xs font-bold uppercase text-slate-400 tracking-widest block mb-1">Harga Satuan</span>
                        <div class="flex items-baseline gap-1">
                            <span class="text-2xl font-black text-slate-900 dark:text-white">Rp <?= number_format($product['price'], 0, ',', '.') ?></span>
                            <span class="text-slate-400 font-bold text-sm">/ <?= $unit ?></span>
                        </div>
                    </div>

                    <div class="space-y-4 md:space-y-6">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] md:text-xs font-bold uppercase text-slate-400 tracking-widest"><?= $isPcsCategory ? 'Jumlah Pesanan' : 'Atur Berat' ?></span>
                            <div class="flex items-center gap-2 md:gap-3">
                                <button onclick="updateWeight(this, -<?= $step ?>)" 
                                        class="size-9 md:size-10 flex items-center justify-center rounded-xl bg-slate-100 dark:bg-white/5 text-slate-600 dark:text-slate-300 hover:bg-primary/10 hover:text-primary transition-all border border-transparent hover:border-primary/20">
                                    <span class="material-symbols-outlined text-[20px]">remove</span>
                                </button>
                                <div class="text-lg md:text-xl font-black w-20 md:w-24 text-center">
                                    <span class="weight-display text-slate-900 dark:text-white"><?= $initialQtyDisplay ?></span>
                                    <span class="text-[10px] md:text-sm font-bold opacity-50 ml-0.5"><?= $unit ?></span>
                                </div>
                                <button onclick="updateWeight(this, <?= $step ?>)" 
                                        class="size-9 md:size-10 flex items-center justify-center rounded-xl bg-slate-100 dark:bg-white/5 text-slate-600 dark:text-slate-300 hover:bg-primary/10 hover:text-primary transition-all border border-transparent hover:border-primary/20">
                                    <span class="material-symbols-outlined text-[20px]">add</span>
                                </button>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-slate-100 dark:border-white/5 flex items-center justify-between gap-4">
                            <div>
                                <span class="text-[10px] md:text-xs font-bold uppercase text-slate-400 tracking-widest block mb-1">Total Harga</span>
                                <span class="text-2xl md:text-3xl font-black text-primary">Rp <span class="total-price-display"><?= number_format($product['price'] * $initialQty, 0, ',', '.') ?></span></span>
                            </div>
                            <?php if ($product['stock'] > 0): ?>
                                <button onclick="addToCart(this)" 
                                        class="flex-1 bg-primary hover:bg-blue-700 text-white font-black py-3.5 md:py-4 px-6 md:px-8 rounded-xl md:rounded-2xl shadow-xl shadow-primary/20 hover:shadow-primary/30 active:scale-95 transition-all flex items-center justify-center gap-2 md:gap-3">
                                    <span class="material-symbols-outlined text-[20px] md:text-[24px]">shopping_cart</span>
                                    <span>Beli Sekarang</span>
                                </button>
                            <?php else: ?>
                                <button disabled 
                                        class="flex-1 bg-slate-200 dark:bg-white/5 text-slate-400 dark:text-slate-600 font-black py-3.5 md:py-4 px-6 md:px-8 rounded-xl md:rounded-2xl cursor-not-allowed flex items-center justify-center gap-2 md:gap-3">
                                    <span class="material-symbols-outlined">block</span>
                                    <span>Stok Habis</span>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Description -->
                <div class="mt-8">
                    <h3 class="text-xs font-black uppercase text-slate-400 tracking-[0.2em] mb-4">Deskripsi Produk</h3>
                    <div class="text-slate-600 dark:text-slate-300 leading-relaxed text-sm md:text-base prose dark:prose-invert max-w-none">
                        <?= nl2br(htmlspecialchars($product['description'])) ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Similar Products -->
        <?php if (mysqli_num_rows($similar_products) > 0): ?>
            <div class="mt-20 border-t border-slate-200 dark:border-white/5 pt-12">
                <div class="flex items-center justify-between mb-8">
                    <h2 class="text-2xl font-black text-slate-900 dark:text-white">Produk Serupa</h2>
                    <a href="<?= BASE_URL ?>market?category=<?= $product['category_slug'] ?>" class="text-xs font-black uppercase tracking-widest text-primary hover:underline">Lihat Semua</a>
                </div>
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
                    <?php while ($sim = mysqli_fetch_assoc($similar_products)): 
                        $sim_category = $sim['category_name'];
                        $sim_isPcs = in_array($sim_category, ['Frozen Food', 'Produk Jadi']);
                        $sim_unit = $sim['unit'] ?: ($sim_isPcs ? 'pcs' : 'kg');
                    ?>
                        <article class="group bg-white dark:bg-card-dark rounded-2xl border border-slate-200 dark:border-white/5 overflow-hidden hover:shadow-xl transition-all duration-300 flex flex-col h-full">
                            <a href="<?= BASE_URL ?>product-detail?id=<?= $sim['id'] ?>" class="block aspect-square overflow-hidden bg-slate-100">
                                <?php
                                $sim_img = $sim['image'];
                                if ($sim_img && !filter_var($sim_img, FILTER_VALIDATE_URL)) {
                                    $sim_img = BASE_URL . $sim_img;
                                }
                                if ($sim_img): ?>
                                    <img src="<?= htmlspecialchars($sim_img) ?>" 
                                         alt="<?= htmlspecialchars($sim['name']) ?>" 
                                         class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                <?php else: ?>
                                    <div class="w-full h-full flex items-center justify-center text-slate-300">
                                        <span class="material-symbols-outlined text-4xl">image_not_supported</span>
                                    </div>
                                <?php endif; ?>
                            </a>
                            <div class="p-4 flex flex-col flex-1">
                                <a href="<?= BASE_URL ?>product-detail?id=<?= $sim['id'] ?>" class="font-bold text-sm text-slate-900 dark:text-white hover:text-primary transition-colors mb-2 block">
                                    <?= htmlspecialchars($sim['name']) ?>
                                </a>
                                <div class="mt-auto">
                                    <span class="text-primary font-black text-sm">Rp <?= number_format($sim['price'], 0, ',', '.') ?> <span class="text-[10px] opacity-50">/ <?= $sim_unit ?></span></span>
                                </div>
                            </div>
                        </article>
                    <?php endwhile; ?>
                </div>
            </div>
        <?php endif; ?>
    </main>

    </main>

    <?php include ROOT_PATH . "includes/public_footer.php"; ?>

    <script>
        // Product Logic (Mirror market.php logic)
        function updateWeight(btn, change) {
            const container = btn.closest('[data-id]');
            const weightDisplay = container.querySelector('.weight-display');
            const totalPriceDisplay = container.querySelector('.total-price-display');
            const unitPrice = parseFloat(container.dataset.price);
            const unit = container.dataset.unit;
            
            const isPcs = (unit == 'pcs' || unit == 'box' || unit == 'porsi');
            const step = isPcs ? 1 : 0.5;
            const min = isPcs ? 1 : 0.5;

            let currentWeight = parseFloat(weightDisplay.innerText);
            let newWeight = currentWeight + change;

            if (newWeight < min) newWeight = min;

            weightDisplay.innerText = isPcs
                ? newWeight
                : newWeight.toFixed(1);

            const total = unitPrice * newWeight;
            updateTotalDisplay();
        }

        function addToCart(btn) {
            const container = btn.closest('[data-id]');
            const id = container.dataset.id;
            const name = container.dataset.name;
            const image = container.dataset.image;
            const category = container.dataset.category;
            const unit = container.dataset.unit;
            const unitPrice = parseFloat(container.dataset.price);
            const weight = parseFloat(container.querySelector('.weight-display').innerText);
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
            
            // Trigger badge update (if header is present)
            if (typeof updateCartBadge === 'function') {
                updateCartBadge();
            }
            
            showToast();
        }

        function showToast() {
            const toast = document.getElementById('toast');
            toast.classList.remove('opacity-0', 'invisible', 'translate-y-4');
            setTimeout(() => {
                toast.classList.add('opacity-0', 'invisible', 'translate-y-4');
            }, 3000);
        }

        // Handle Cart Badge in Header if not already defined (custom-copy from market.php)
        function updateCartBadge() {
            let cart = JSON.parse(localStorage.getItem('cart')) || [];
            const badge = document.getElementById('cart-badge-header');
            if (badge) {
                if (cart.length > 0) {
                    badge.classList.remove('hidden');
                } else {
                    badge.classList.add('hidden');
                }
            }
        }
        
        function updateTotalDisplay() {
            const totalPriceDisplays = document.querySelectorAll('.total-price-display');
            const container = document.querySelector('[data-id]');
            const weightDisplay = container.querySelector('.weight-display');
            const unitPrice = parseFloat(container.dataset.price);
            const currentWeight = parseFloat(weightDisplay.innerText);
            
            const total = unitPrice * currentWeight;
            const formattedTotal = new Intl.NumberFormat('id-ID').format(total);
            
            totalPriceDisplays.forEach(el => el.innerText = formattedTotal);
        }

        // Init
        document.addEventListener('DOMContentLoaded', () => {
            updateCartBadge();
        });
    </script>
    <style>
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</body>
</html>
