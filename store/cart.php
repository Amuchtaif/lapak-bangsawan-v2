<!DOCTYPE html>
<html class="light" lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Your Cart - Lapak Bangsawan</title>
    <link rel="icon" href="assets/images/favicon-laba.png" type="image/x-icon">
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

    <header
        class="sticky top-0 z-50 bg-white/90 dark:bg-background-dark/90 backdrop-blur-md border-b border-slate-200 dark:border-slate-800">
        <div class="max-w-[1400px] mx-auto px-4 md:px-8 py-3">
            <div class="flex items-center justify-between gap-4 md:gap-8">
                <a href="index.php" class="flex items-center gap-3 min-w-fit">
                    <div class="size-12 text-primary">
                        <img src="assets/images/logo.jpeg" alt="Logo">
                    </div>
                    <h1 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white hidden sm:block">Lapak
                        Bangsawan</h1>
                </a>

                <div class="flex items-center gap-2 sm:gap-4 ml-auto">
                    <a href="market.php" class="text-sm font-medium hover:text-primary transition-colors">Lanjut
                        Belanja</a>
                </div>
            </div>
        </div>
    </header>

    <main class="flex-grow w-full max-w-[1400px] mx-auto px-4 md:px-8 py-6 md:py-8">
        <h3 class="text-2xl md:text-3xl font-black text-slate-900 dark:text-white mb-8">Keranjang Belanja</h3>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8" id="cart-container">
            <!-- Cart Items -->
            <div class="lg:col-span-2 flex flex-col gap-4">
                <div
                    class="bg-white dark:bg-card-dark rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
                    <div id="cart-items" class="divide-y divide-slate-200 dark:divide-slate-700">
                        <!-- Items will be injected here -->
                    </div>
                </div>

                <div id="empty-cart-message"
                    class="hidden flex flex-col items-center justify-center py-12 bg-white dark:bg-card-dark rounded-xl border border-slate-200 dark:border-slate-800">
                    <span class="material-symbols-outlined text-6xl text-slate-300 mb-4">shopping_cart_off</span>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Keranjang belanja Anda kosong</h3>
                    <br><a href="market.php"
                        class="bg-primary text-white px-6 py-2 rounded-lg font-bold hover:bg-blue-600 transition-colors">Mulai
                        Belanja</a>
                </div>
            </div>

            <!-- Summary -->
            <div class="lg:col-span-1">
                <div
                    class="bg-white dark:bg-card-dark rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-6 sticky top-24">
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-4">Ringkasan Pesanan</h2>

                    <div class="space-y-3 mb-6">
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500 dark:text-slate-400">Subtotal</span>
                            <span class="font-medium text-slate-900 dark:text-white" id="summary-subtotal">Rp 0</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500 dark:text-slate-400">Estimasi Pengiriman</span>
                            <span class="font-medium text-slate-900 dark:text-white" id="summary-shipping">Gratis</span>
                        </div>
                        <div
                            class="border-t border-slate-200 dark:border-slate-700 pt-3 flex justify-between items-end">
                            <span class="font-bold text-slate-900 dark:text-white">Total</span>
                            <span class="text-xl font-black text-primary" id="summary-total">Rp 0</span>
                        </div>
                    </div>

                    <a href="checkout_page.php" id="checkout-btn"
                        class="block w-full bg-primary hover:bg-blue-700 text-white text-center font-bold py-3 px-4 rounded-lg shadow-sm shadow-blue-500/20 active:scale-95 transition-all">
                        Lanjut ke Pembayaran
                    </a>
                </div>
            </div>
        </div>
    </main>

    <?php include("admin/footer.php"); ?>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            renderCart();
        });

        function renderCart() {
            const cartItemsContainer = document.getElementById('cart-items');
            const emptyMessage = document.getElementById('empty-cart-message');
            const cartContainer = document.getElementById('cart-container'); // To potentially hide summary if empty? Actually nice to show empty state.
            const subtotalEl = document.getElementById('summary-subtotal');
            const totalEl = document.getElementById('summary-total');

            let cart = JSON.parse(localStorage.getItem('cart')) || [];

            if (cart.length === 0) {
                cartItemsContainer.innerHTML = '';
                emptyMessage.classList.remove('hidden');
                document.querySelector('.lg\\:col-span-1').style.display = 'none'; // Hide summary
                return;
            } else {
                emptyMessage.classList.add('hidden');
                document.querySelector('.lg\\:col-span-1').style.display = 'block';
            }

            let html = '';
            let total = 0;

            cart.forEach((item, index) => {
                total += item.total_price;

                // Determine units based on category
                // Default: kg
                let unit = 'kg';
                let weightLabel = 'Berat';
                let priceUnit = '/ kg';

                if (item.category && ['Frozen Food', 'Produk Jadi'].includes(item.category)) {
                    unit = 'pcs';
                    weightLabel = 'Qty';
                    priceUnit = '/ pcs';
                }

                html += `
                <div class="p-4 sm:p-6 flex gap-4 sm:gap-6 items-start sm:items-center">
                    <div class="h-20 w-20 sm:h-24 sm:w-24 flex-shrink-0 overflow-hidden rounded-lg bg-slate-100 border border-slate-200 dark:border-slate-700">
                        ${item.image ? `<img src="${item.image}" alt="${item.name}" class="h-full w-full object-cover object-center">` : '<div class="flex h-full items-center justify-center text-slate-400"><span class="material-symbols-outlined">image_not_supported</span></div>'}
                    </div>
                    <div class="flex flex-1 flex-col">
                        <div class="flex justify-between">
                            <h3 class="text-base font-bold text-slate-900 dark:text-white"><a href="#">${item.name}</a></h3>
                            <p class="text-base font-bold text-slate-900 dark:text-white">Rp ${new Intl.NumberFormat('id-ID').format(item.total_price)}</p>
                        </div>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">${weightLabel}: ${item.weight} ${unit}</p>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Harga Satuan: Rp ${new Intl.NumberFormat('id-ID').format(item.price)} ${priceUnit}</p>
                        
                        <div class="flex items-center justify-between mt-4">
                            <button onclick="removeItem(${index})" class="text-sm font-medium text-red-600 hover:text-red-500 flex items-center gap-1">
                                <span class="material-symbols-outlined text-base">delete</span> Hapus
                            </button>
                        </div>
                    </div>
                </div>
                `;
            });

            cartItemsContainer.innerHTML = html;
            subtotalEl.innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(total);
            totalEl.innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(total);
        }

        function removeItem(index) {
            let cart = JSON.parse(localStorage.getItem('cart')) || [];
            cart.splice(index, 1);
            localStorage.setItem('cart', JSON.stringify(cart));
            renderCart();
            // Also update badge in header if we had one here, or dispatch event
        }
    </script>
</body>

</html>