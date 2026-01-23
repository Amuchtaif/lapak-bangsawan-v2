<!DOCTYPE html>
<?php
// Generate Order Token for Idempotency
$order_token = bin2hex(random_bytes(16));
?>
<html class="light" lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Pembayaran - Lapak Bangsawan</title>
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
                },
            },
        }
    </script>
</head>

<body
    class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-white font-display antialiased flex flex-col min-h-screen">

    <header class="bg-white dark:bg-background-dark border-b border-slate-200 dark:border-slate-800 py-4">
        <div class="max-w-[1400px] mx-auto px-4 flex justify-between items-center">
            <a href="<?= BASE_URL ?>public/home" class="flex items-center gap-2 font-bold text-xl">
                <div class="size-10 text-primary">
                    <img src="<?= BASE_URL ?>assets/images/logo.jpeg" alt="Logo">
                </div>
            </a>
            <a href="<?= BASE_URL ?>public/cart" class="text-sm font-medium hover:text-primary">Kembali ke Keranjang</a>
        </div>
    </header>

    <main class="flex-grow w-full max-w-4xl lg:max-w-7xl mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold mb-6">Pembayaran</h1>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-8 lg:gap-12">
            <!-- Form -->
            <div class="lg:col-span-8 space-y-8">
                <!-- Contact & Shipping -->
                <div
                    class="bg-white dark:bg-card-dark p-6 lg:p-8 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
                    <h2 class="text-xl font-bold mb-6 flex items-center gap-2">
                        <span
                            class="flex items-center justify-center size-8 rounded-full bg-primary/10 text-primary text-sm">1</span>
                        Informasi Kontak
                    </h2>
                    <form id="checkout-form" class="space-y-6">
                        <!-- Idempotency Token -->
                        <input type="hidden" name="order_token" value="<?php echo $order_token; ?>">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">
                                    Nama Lengkap</label>
                                <input type="text" name="name" required
                                    class="w-full rounded-lg border-slate-200 bg-slate-50 dark:bg-slate-800 dark:border-slate-700 focus:ring-primary focus:border-primary transition-all py-2.5">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Email
                                    (Opsional)</label>
                                <input type="email" name="email"
                                    class="w-full rounded-lg border-slate-200 bg-slate-50 dark:bg-slate-800 dark:border-slate-700 focus:ring-primary focus:border-primary transition-all py-2.5">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">
                                    Nomor WhatsApp</label>
                                <input type="tel" name="phone" required placeholder="e.g. 08123456789"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                    class="w-full rounded-lg border-slate-200 bg-slate-50 dark:bg-slate-800 dark:border-slate-700 focus:ring-primary focus:border-primary transition-all py-2.5">
                                <p class="text-xs text-slate-500 mt-1.5">Kami akan mengirimkan update pesanan ke sini.
                                </p>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">
                                Alamat Pengiriman</label>
                            <textarea name="address" required rows="3"
                                class="w-full rounded-lg border-slate-200 bg-slate-50 dark:bg-slate-800 dark:border-slate-700 focus:ring-primary focus:border-primary transition-all p-3"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">
                                Catatan Pesanan (Opsional)</label>
                            <textarea name="order_notes" rows="2"
                                placeholder="Contoh: Tolong dikirim sore hari, atau kemasan jangan dipress"
                                class="w-full rounded-lg border-slate-200 bg-slate-50 dark:bg-slate-800 dark:border-slate-700 focus:ring-primary focus:border-primary transition-all p-3"></textarea>
                        </div>
                </div>

                <!-- Payment Method -->
                <div
                    class="bg-white dark:bg-card-dark p-6 lg:p-8 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
                    <h2 class="text-xl font-bold mb-6 flex items-center gap-2">
                        <span
                            class="flex items-center justify-center size-8 rounded-full bg-primary/10 text-primary text-sm">2</span>
                        Metode Pembayaran
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <label
                            class="cursor-pointer relative rounded-xl border p-4 flex flex-col gap-2 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all has-[:checked]:border-primary has-[:checked]:bg-primary/5 has-[:checked]:ring-1 has-[:checked]:ring-primary">
                            <div class="flex items-center gap-3">
                                <input type="radio" name="payment_method" value="transfer"
                                    class="text-primary focus:ring-primary size-5" checked
                                    onchange="togglePaymentInfo()">
                                <span class="font-bold text-slate-900 dark:text-white">Transfer Bank</span>
                            </div>
                            <div class="text-xs text-slate-500 pl-8">Bayar melalui transfer BSI</div>
                        </label>
                        <label
                            class="cursor-pointer relative rounded-xl border p-4 flex flex-col gap-2 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all has-[:checked]:border-primary has-[:checked]:bg-primary/5 has-[:checked]:ring-1 has-[:checked]:ring-primary">
                            <div class="flex items-center gap-3">
                                <input type="radio" name="payment_method" value="cod"
                                    class="text-primary focus:ring-primary size-5" onchange="togglePaymentInfo()">
                                <span class="font-bold text-slate-900 dark:text-white">COD</span>
                            </div>
                            <div class="text-xs text-slate-500 pl-8">Bayar di tempat saat barang sampai</div>
                        </label>
                    </div>

                    <!-- Bank Info (Conditional) -->
                    <div id="bank-info"
                        class="bg-blue-50 dark:bg-blue-900/20 border-blue-100 dark:border-blue-900 rounded-lg flex flex-col gap-2 transition-all duration-300 ease-in-out overflow-hidden max-h-0 opacity-0 p-0 border-0">
                        <p class="text-xs font-bold uppercase text-blue-500 mb-2">Silakan transfer ke:</p>
                        <div
                            class="flex justify-between items-center py-1 border-b border-blue-100 dark:border-blue-800/50 last:border-0">
                            <span class="text-sm text-slate-600 dark:text-slate-300">Bank</span>
                            <span class="font-bold text-slate-900 dark:text-white">BSI</span>
                        </div>
                        <div
                            class="flex justify-between items-center py-1 border-b border-blue-100 dark:border-blue-800/50 last:border-0">
                            <span class="text-sm text-slate-600 dark:text-slate-300">No. Rekening</span>
                            <span
                                class="font-bold text-slate-900 dark:text-white font-mono tracking-wide text-lg">7252428245</span>
                        </div>
                        <div
                            class="flex justify-between items-center py-1 border-b border-blue-100 dark:border-blue-800/50 last:border-0">
                            <span class="text-sm text-slate-600 dark:text-slate-300">Nama</span>
                            <span class="font-bold text-slate-900 dark:text-white">Shohibudin</span>
                        </div>
                    </div>
                </div>


                </form>
            </div>

            <!-- Order Preview -->
            <div class="lg:col-span-4">
                <div
                    class="bg-slate-50 dark:bg-slate-800 p-6 rounded-xl border border-slate-200 dark:border-slate-700 sticky top-24">
                    <h2 class="text-lg font-bold mb-4">Ringkasan Pesanan</h2>
                    <div id="order-items" class="space-y-4 mb-6 max-h-[60vh] overflow-y-auto pr-2 custom-scrollbar">
                    </div>

                    <div class="border-t border-slate-200 dark:border-slate-600 pt-4 space-y-3">
                        <div class="flex justify-between text-slate-600 dark:text-slate-400">
                            <span>Subtotal</span>
                            <span id="order-subtotal" class="font-medium">Rp 0</span>
                        </div>
                        <div class="flex justify-between text-slate-600 dark:text-slate-400">
                            <span>Biaya Pengiriman</span>
                            <span class="font-medium text-green-600">Gratis</span>
                        </div>
                        <div class="border-t border-slate-200 dark:border-slate-600 my-2"></div>
                        <div class="flex justify-between items-end">
                            <span class="font-bold text-lg">Total</span>
                            <span id="order-total" class="font-black text-2xl">Rp 0</span>
                        </div>
                    </div>

                    <div class="mt-8">
                        <button form="checkout-form" type="submit" id="submit-btn"
                            class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3.5 px-4 rounded-xl shadow-lg shadow-green-500/20 hover:-translate-y-0.5 transition-all flex justify-center items-center gap-2">
                            <span id="btn-text">Selesaikan Pesanan</span>
                            <span id="btn-spinner"
                                class="hidden animate-spin rounded-full h-5 w-5 border-b-2 border-white"></span>
                        </button>
                        <p class="text-xs text-center text-slate-500 mt-3">Anda akan diarahkan ke WhatsApp untuk
                            menyelesaikan pesanan.</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Load Cart
        const cart = JSON.parse(localStorage.getItem('cart')) || [];
        const itemsContainer = document.getElementById('order-items');
        const totalEl = document.getElementById('order-total');
        const subtotalEl = document.getElementById('order-subtotal');

        if (cart.length === 0) {
            alert("Keranjang Anda kosong!");
            window.location.href = 'market.php';
        }

        let total = 0;
        cart.forEach(item => {
            total += item.total_price;
            itemsContainer.innerHTML += `
               <div class="flex gap-4">
                    <div class="size-16 shrink-0 bg-white dark:bg-slate-700 rounded-lg border border-slate-200 dark:border-slate-600 overflow-hidden flex items-center justify-center">
                        ${item.image ? `<img src="${item.image}" alt="${item.name}" class="w-full h-full object-cover">` : '<span class="material-symbols-outlined text-slate-400">image</span>'}
                    </div>
                    <div class="flex-1 min-w-0">
                        <h4 class="font-bold text-sm text-slate-900 dark:text-white line-clamp-2 leading-tight mb-1">${item.name}</h4>
                        <div class="flex justify-between items-end">
                             <p class="text-xs text-slate-500">${item.weight} <span class="lowercase">${['Frozen Food', 'Produk Jadi'].includes(item.category) ? 'pcs' : 'kg'}</span></p>
                             <span class="font-bold text-sm">Rp ${new Intl.NumberFormat('id-ID').format(item.total_price)}</span>
                        </div>
                    </div>
                </div>
            `;
        });
        const formattedTotal = 'Rp ' + new Intl.NumberFormat('id-ID').format(total);
        totalEl.innerText = formattedTotal;
        if (subtotalEl) subtotalEl.innerText = formattedTotal;

        // Payment Toggle
        function togglePaymentInfo() {
            const method = document.querySelector('input[name="payment_method"]:checked').value;
            const info = document.getElementById('bank-info');
            if (method === 'transfer') {
                info.classList.remove('max-h-0', 'opacity-0', 'p-0', 'border-0');
                info.classList.add('max-h-96', 'opacity-100', 'p-5', 'border');
            } else {
                info.classList.remove('max-h-96', 'opacity-100', 'p-5', 'border');
                info.classList.add('max-h-0', 'opacity-0', 'p-0', 'border-0');
            }
        }
        // Initialize
        togglePaymentInfo();

        // Handle Submit
        document.getElementById('checkout-form').addEventListener('submit', async (e) => {
            e.preventDefault();

            // UI Protections
            const submitBtn = document.getElementById('submit-btn');
            const btnText = document.getElementById('btn-text');
            const btnSpinner = document.getElementById('btn-spinner');

            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
            btnText.innerText = 'Memproses...';
            btnSpinner.classList.remove('hidden');

            const formData = new FormData(e.target);
            const data = {
                name: formData.get('name'),
                email: formData.get('email'),
                phone: formData.get('phone'),
                phone: formData.get('phone'),
                address: formData.get('address'),
                order_notes: formData.get('order_notes'), // Capture Notes
                payment_method: formData.get('payment_method'), // Captured
                order_token: formData.get('order_token'), // Send Token
                items: cart,
                total: total
            };

            try {
                const response = await fetch('save_order.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                const result = await response.json();

                if (result.success) {
                    localStorage.removeItem('cart');
                    window.location.href = result.whatsapp_url;
                } else {
                    alert('Gagal: ' + result.message);
                    // Reset button on failure
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('opacity-75', 'cursor-not-allowed');
                    btnText.innerText = 'Selesaikan Pesanan';
                    btnSpinner.classList.add('hidden');
                }
            } catch (err) {
                console.error(err);
                alert('Terjadi kesalahan. Silakan coba lagi.');
                // Reset button on error
                submitBtn.disabled = false;
                submitBtn.classList.remove('opacity-75', 'cursor-not-allowed');
                btnText.innerText = 'Selesaikan Pesanan';
                btnSpinner.classList.add('hidden');
            }
        });
    </script>
</body>

</html>