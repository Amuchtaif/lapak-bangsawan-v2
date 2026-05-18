<?php require_once dirname(__DIR__) . '/config/init.php'; ?>
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#0d59f2",
                        "primary-dark": "#0b4ecf",
                        "background-light": "#f5f6f8",
                        "background-dark": "#101622",
                        "card-dark": "#1e2736",
                    },
                    boxShadow: {
                        'premium': '0 10px 30px -10px rgba(13, 89, 242, 0.15)',
                    },
                    fontFamily: {
                        "display": ["Inter", "sans-serif"]
                    },
                    animation: {
                        "pulse-subtle": "pulse-subtle 3s ease-in-out infinite",
                        "slide-up": "slide-up 0.4s cubic-bezier(0.16, 1, 0.3, 1)",
                    },
                    keyframes: {
                        "pulse-subtle": {
                            "0%, 100%": { opacity: "1" },
                            "50%": { opacity: "0.8" },
                        },
                        "slide-up": {
                            "0%": { transform: "translateY(100%)" },
                            "100%": { transform: "translateY(0)" },
                        }
                    }
                },
            },
        }
    </script>
    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        .dark .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #475569;
        }

        @media (max-width: 768px) {
            .mobile-bottom-bar {
                box-shadow: 0 -10px 20px -5px rgba(0, 0, 0, 0.1);
            }
        }

        .step-active {
            @apply ring-4 ring-primary/20 scale-110;
        }

        input:focus,
        textarea:focus {
            @apply ring-2 ring-primary/20 border-primary !important;
        }

        .rate-card-selected {
            @apply border-primary bg-primary/5 ring-1 ring-primary shadow-sm;
        }
    </style>
</head>

<body
    class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-white font-display antialiased flex flex-col min-h-screen">

    <?php include ROOT_PATH . "includes/public_header.php"; ?>

    <main class="flex-grow w-full max-w-4xl lg:max-w-7xl mx-auto px-4 py-6 md:py-10 pb-32 md:pb-10">
        <div class="flex items-center gap-2 mb-6 md:mb-8">
            <a href="cart" class="text-slate-500 hover:text-primary transition-colors">
                <span class="material-symbols-outlined">arrow_back</span>
            </a>
            <h1 class="text-2xl md:text-3xl font-black tracking-tight">Checkout</h1>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 md:gap-8 lg:gap-12">
            <!-- Form -->
            <div class="lg:col-span-8 space-y-8">
                <!-- Contact & Shipping -->
                <div
                    class="bg-white dark:bg-card-dark p-5 md:p-8 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm transition-all hover:shadow-premium group">
                    <h2 class="text-lg md:text-xl font-bold mb-6 flex items-center gap-3">
                        <span
                            class="flex items-center justify-center size-8 md:size-9 rounded-xl bg-primary text-white text-sm shadow-lg shadow-primary/30">1</span>
                        Informasi Pengiriman
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
                                <input type="tel" name="phone" required placeholder="e.g. 08123456789" minlength="11"
                                    maxlength="15" oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                    class="w-full rounded-lg border-slate-200 bg-slate-50 dark:bg-slate-800 dark:border-slate-700 focus:ring-primary focus:border-primary transition-all py-2.5">
                                <p class="text-xs text-slate-500 mt-1.5">Masukkan nomor WhatsApp aktif (contoh:
                                    08123456789).
                                </p>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">
                                    Alamat Lengkap</label>
                                <textarea name="address" required rows="2" placeholder="Nama Jalan, No. Rumah, RT/RW"
                                    class="w-full rounded-lg border-slate-200 bg-slate-50 dark:bg-slate-800 dark:border-slate-700 focus:ring-primary focus:border-primary transition-all p-3"></textarea>
                            </div>

                            <div class="relative" id="area-search-container">
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">
                                    Kota / Kecamatan</label>
                                <input type="text" id="area-search-input" required autocomplete="off"
                                    placeholder="Ketik minimal 3 karakter untuk mencari..."
                                    class="w-full rounded-lg border-slate-200 bg-slate-50 dark:bg-slate-800 dark:border-slate-700 focus:ring-primary focus:border-primary transition-all py-2.5">

                                <button type="button" id="btn-geolocation"
                                    class="mt-2.5 text-xs md:text-sm text-primary font-bold flex items-center gap-2 px-3 py-2 -ml-3 rounded-xl hover:bg-primary/5 transition-all active:scale-95 group/geo w-fit">
                                    <span
                                        class="material-symbols-outlined text-[20px] group-hover/geo:rotate-90 transition-transform duration-500">my_location</span>
                                    <span>Gunakan Lokasi Saya</span>
                                </button>

                                <input type="hidden" name="destination_area_id" id="destination-area-id">
                                <input type="hidden" name="destination_area_text" id="destination-area-text">
                                <input type="hidden" name="dest_lat" id="dest-lat">
                                <input type="hidden" name="dest_lng" id="dest-lng">

                                <!-- Search Results -->
                                <div id="area-results"
                                    class="hidden absolute z-50 w-full mt-1 bg-white dark:bg-card-dark border border-slate-200 dark:border-slate-800 rounded-lg shadow-xl max-h-60 overflow-y-auto">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">
                                    Kode Pos</label>
                                <input type="text" name="postal_code" id="postal-code" maxlength="5" pattern="\d{5}"
                                    placeholder="Ex: 45132" oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                    class="w-full md:w-1/3 rounded-lg border-slate-200 bg-slate-50 dark:bg-slate-800 dark:border-slate-700 focus:ring-primary focus:border-primary transition-all py-2.5">
                            </div>
                        </div>

                        <!-- Shipping Rates Container -->
                        <div id="shipping-rates-section"
                            class="hidden space-y-4 pt-4 border-t border-slate-100 dark:border-slate-800">
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300">
                                Pilih Kurir Pengiriman</label>
                            <div id="shipping-rates-list" class="grid grid-cols-1 gap-3">
                                <!-- Rates will be injected here -->
                            </div>
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
                    class="bg-white dark:bg-card-dark p-5 md:p-8 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm transition-all hover:shadow-premium">
                    <h2 class="text-lg md:text-xl font-bold mb-6 flex items-center gap-3">
                        <span
                            class="flex items-center justify-center size-8 md:size-9 rounded-xl bg-primary text-white text-sm shadow-lg shadow-primary/30">2</span>
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
                </div>


                </form>
            </div>

            <!-- Order Preview -->
            <div class="lg:col-span-4 h-fit">
                <div
                    class="bg-white dark:bg-card-dark p-6 rounded-2xl border border-slate-200 dark:border-slate-800 sticky top-24 shadow-sm">
                    <h2 class="text-lg font-bold mb-5 flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">shopping_basket</span>
                        Ringkasan Pesanan
                    </h2>
                    <div id="order-items" class="space-y-4 mb-6 max-h-[40vh] overflow-y-auto pr-2 custom-scrollbar">
                    </div>

                    <div class="bg-slate-50 dark:bg-slate-900/50 p-4 rounded-xl space-y-3 mb-6">
                        <div class="flex justify-between text-sm text-slate-600 dark:text-slate-400">
                            <span>Subtotal</span>
                            <span id="order-subtotal" class="font-semibold text-slate-900 dark:text-white">Rp 0</span>
                        </div>
                        <div class="flex justify-between text-sm text-slate-600 dark:text-slate-400">
                            <span>Biaya Pengiriman</span>
                            <span id="order-shipping" class="font-semibold text-slate-900 dark:text-white">Rp 0</span>
                        </div>
                        <div class="border-t border-slate-200 dark:border-slate-700 mt-2 pt-2">
                            <div class="flex justify-between items-end">
                                <span class="text-sm font-medium">Total Tagihan</span>
                                <span id="order-total" class="font-black text-2xl text-primary">Rp 0</span>
                            </div>
                        </div>
                    </div>

                    <div class="hidden md:block">
                        <button form="checkout-form" type="submit" id="submit-btn"
                            class="w-full bg-primary hover:bg-primary-dark text-white font-bold py-4 px-6 rounded-xl shadow-xl shadow-primary/20 hover:-translate-y-0.5 active:translate-y-0 transition-all flex justify-center items-center gap-2 group">
                            <span id="btn-text">Bayar Sekarang</span>
                            <span
                                class="material-symbols-outlined group-hover:translate-x-1 transition-transform">arrow_forward</span>
                            <span id="btn-spinner"
                                class="hidden animate-spin rounded-full h-5 w-5 border-b-2 border-white"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Mobile Sticky Bottom Bar -->
    <div class="md:hidden fixed bottom-0 left-0 right-0 z-[60] animate-slide-up">
        <div
            class="bg-white dark:bg-card-dark border-t border-slate-200 dark:border-slate-800 p-4 pb-safe-area flex items-center justify-between gap-4 mobile-bottom-bar rounded-t-3xl">
            <div class="flex flex-col">
                <span class="text-xs text-slate-500 font-medium">Total Tagihan</span>
                <span id="mobile-total" class="text-xl font-black text-primary">Rp 0</span>
            </div>
            <button form="checkout-form" type="submit" id="mobile-submit-btn"
                class="flex-1 bg-primary hover:bg-primary-dark text-white font-bold py-3.5 px-6 rounded-xl shadow-xl shadow-primary/20 flex justify-center items-center gap-2">
                <span id="mobile-btn-text">Bayar</span>
                <span id="mobile-btn-spinner"
                    class="hidden animate-spin rounded-full h-5 w-5 border-b-2 border-white"></span>
            </button>
        </div>
    </div>

    <script>
        const BASE_URL = '<?= BASE_URL ?>';
        const getImageUrl = (path) => {
            if (!path) return '';
            if (path.startsWith('http') || path.startsWith('//')) return path;
            return BASE_URL + path;
        };

        // Load Cart and Elements
        const cart = JSON.parse(localStorage.getItem('cart')) || [];
        const itemsContainer = document.getElementById('order-items');
        const totalEl = document.getElementById('order-total');
        const subtotalEl = document.getElementById('order-subtotal');

        if (cart.length === 0) {
            alert("Keranjang Anda kosong!");
            window.location.href = 'market';
        }

        let baseTotal = 0;
        let shippingCost = 0;
        let total = 0; // Local var for subtotal display reference if needed, or just use baseTotal

        cart.forEach(item => {
            baseTotal += item.total_price;
            total += item.total_price;
            const itemImg = getImageUrl(item.image);
            itemsContainer.innerHTML += `
               <div class="flex gap-4">
                    <div class="size-16 shrink-0 bg-white dark:bg-slate-700 rounded-lg border border-slate-200 dark:border-slate-600 overflow-hidden flex items-center justify-center">
                        ${itemImg ? `<img src="${itemImg}" alt="${item.name}" class="w-full h-full object-cover">` : '<span class="material-symbols-outlined text-slate-400">image</span>'}
                    </div>
                    <div class="flex-1 min-w-0">
                        <h4 class="font-bold text-sm text-slate-900 dark:text-white line-clamp-2 leading-tight mb-1">${item.name}</h4>
                        <div class="flex justify-between items-end">
                             <p class="text-xs text-slate-500">${item.weight} <span class="lowercase">${item.unit || 'kg'}</span></p>
                             <span class="font-bold text-sm">Rp ${new Intl.NumberFormat('id-ID').format(item.total_price)}</span>
                        </div>
                    </div>
                </div>
            `;
        });
        const formattedSubtotal = 'Rp ' + new Intl.NumberFormat('id-ID').format(total);
        if (subtotalEl) subtotalEl.innerText = formattedSubtotal;

        // Fetch dynamic totals for discounts
        fetch(BASE_URL + 'public/api_calculate_total.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ items: cart })
        })
            .then(res => res.json())
            .then(data => {
                if (data.total !== undefined) {
                    totalEl.innerText = data.total_formatted;
                    baseTotal = data.total; // Correctly initialize baseTotal here

                    // Show discounts if any
                    let discountHtml = '';
                    if (data.total_discount > 0) {
                        data.discounts_detail.forEach(d => {
                            discountHtml += `
                            <div class="flex justify-between text-green-600 font-bold text-sm">
                                <span>${d.label}</span>
                                <span>${d.amount_formatted}</span>
                            </div>
                        `;
                        });
                    }

                    // Inject before the divider
                    const divider = document.querySelector('.border-t.border-slate-200.dark\\:border-slate-600.my-2');
                    if (divider) {
                        // Remove old ones
                        divider.parentElement.querySelectorAll('.text-green-600').forEach(el => el.remove());
                        divider.insertAdjacentHTML('beforebegin', discountHtml);
                    }
                }
            });

        // Payment Toggle
        function togglePaymentInfo() {
            const method = document.querySelector('input[name="payment_method"]:checked').value;
            const submitBtnText = document.getElementById('btn-text');
            const mobileBtnText = document.getElementById('mobile-btn-text');

            if (method === 'transfer') {
                if (submitBtnText) submitBtnText.innerText = 'Bayar Sekarang';
                if (mobileBtnText) mobileBtnText.innerText = 'Bayar';
            } else {
                if (submitBtnText) submitBtnText.innerText = 'Buat Pesanan (COD)';
                if (mobileBtnText) mobileBtnText.innerText = 'Pesan COD';
            }
        }
        // Initialize
        togglePaymentInfo();

        // Removed late declarations

        // Handle Submit
        document.getElementById('checkout-form').addEventListener('submit', async (e) => {
            e.preventDefault();

            // Validate WhatsApp Number Format
            const phoneInput = document.querySelector('input[name="phone"]');
            const rawPhone = phoneInput.value.replace(/[^0-9]/g, '');
            const isIndonesianWA = /^(08|628|8)\d{7,12}$/.test(rawPhone);
            if (!isIndonesianWA) {
                Swal.fire({
                    title: 'Nomor WhatsApp Tidak Valid!',
                    text: 'Harap masukkan nomor WhatsApp Indonesia yang valid (contoh: 08123456789) dengan panjang antara 9 hingga 14 angka.',
                    icon: 'warning',
                    confirmButtonColor: '#0d59f2',
                    confirmButtonText: 'OK'
                });
                phoneInput.focus();
                return;
            }

            // Validate Area and Courier
            const areaId = document.getElementById('destination-area-id').value;
            const courier = document.querySelector('input[name="courier_option"]:checked');

            if (!areaId) {
                const lat = document.getElementById('dest-lat').value;
                const lng = document.getElementById('dest-lng').value;
                if (!lat || !lng) {
                    alert('Silakan pilih Kota/Kecamatan yang valid dari daftar pencarian atau gunakan lokasi saya.');
                    return;
                }
            }

            if (!courier) {
                alert('Silakan pilih kurir pengiriman.');
                return;
            }

            // UI Protections
            const submitBtn = document.getElementById('submit-btn');
            const btnText = document.getElementById('btn-text');
            const btnSpinner = document.getElementById('btn-spinner');

            const mobileSubmitBtn = document.getElementById('mobile-submit-btn');
            const mobileBtnText = document.getElementById('mobile-btn-text');
            const mobileBtnSpinner = document.getElementById('mobile-btn-spinner');

            [submitBtn, mobileSubmitBtn].forEach(btn => {
                if (btn) {
                    btn.disabled = true;
                    btn.classList.add('opacity-75', 'cursor-not-allowed');
                }
            });

            if (btnText) btnText.innerText = 'Memproses...';
            if (btnSpinner) btnSpinner.classList.remove('hidden');
            if (mobileBtnText) mobileBtnText.innerText = '...';
            if (mobileBtnSpinner) mobileBtnSpinner.classList.remove('hidden');

            const formData = new FormData(e.target);
            const data = {
                name: formData.get('name'),
                email: formData.get('email'),
                phone: formData.get('phone'),
                address: formData.get('address') + ' (' + formData.get('destination_area_text') + ') [Kode Pos: ' + formData.get('postal_code') + ']',
                destination_area_id: formData.get('destination_area_id'),
                courier_company: formData.get('courier_company'),
                courier_type: formData.get('courier_type'),
                courier_price: parseFloat(formData.get('courier_price')),
                order_notes: formData.get('order_notes'),
                payment_method: formData.get('payment_method'),
                order_token: formData.get('order_token'),
                items: cart,
                total: baseTotal + shippingCost,
                shipping_cost: shippingCost,
                dest_lat: document.getElementById('dest-lat').value,
                dest_lng: document.getElementById('dest-lng').value
            };

            try {
                const response = await fetch('<?= BASE_URL ?>public/save_order.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                const result = await response.json();

                if (result.success) {
                    localStorage.removeItem('cart');

                    if (result.payment_method === 'cod') {
                        Swal.fire({
                            title: 'Pesanan COD Berhasil!',
                            text: 'Admin akan segera memproses pesanan Anda.',
                            icon: 'success',
                            confirmButtonColor: '#0d59f2',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            // Redirect to success page for COD
                            window.location.href = BASE_URL + 'success?order_id=' + result.order_id;
                        });
                    } else if (result.payment_method === 'transfer') {
                        Swal.fire({
                            title: 'Pesanan Dibuat!',
                            text: 'Silakan lakukan pembayaran untuk menyelesaikan pesanan.',
                            icon: 'info',
                            confirmButtonColor: '#0d59f2',
                            confirmButtonText: 'Bayar Sekarang'
                        }).then(() => {
                            window.location.href = result.redirect_url;
                        });
                    }
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

        // --- Biteship Logic ---

        const areaSearchInput = document.getElementById('area-search-input');
        const areaResults = document.getElementById('area-results');
        let searchTimeout;

        areaSearchInput.addEventListener('input', () => {
            const query = areaSearchInput.value.trim();
            clearTimeout(searchTimeout);

            if (query.length < 3) {
                areaResults.classList.add('hidden');
                return;
            }

            searchTimeout = setTimeout(async () => {
                try {
                    const res = await fetch('<?= BASE_URL ?>public/api/search_area.php?q=' + encodeURIComponent(query));
                    const data = await res.json();

                    if (data.success && data.areas.length > 0) {
                        areaResults.innerHTML = data.areas.map(area => {
                            const lat = area.latitude || '';
                            const lng = area.longitude || '';
                            return `
                                <div class="p-4 hover:bg-primary/5 cursor-pointer text-sm border-b border-slate-100 dark:border-slate-800 last:border-0 flex items-center gap-3 group/item transition-colors"
                                    onclick="selectArea('${area.id}', '${area.name}', '${lat}', '${lng}')">
                                    <div class="size-8 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 group-hover/item:text-primary group-hover/item:bg-primary/10 transition-colors">
                                        <span class="material-symbols-outlined text-xl">location_on</span>
                                    </div>
                                    <span class="font-medium text-slate-700 dark:text-slate-300 group-hover/item:text-primary transition-colors">${area.name}</span>
                                </div>
                            `;
                        }).join('');
                        areaResults.classList.remove('hidden');
                    } else {
                        areaResults.innerHTML = '<div class="p-4 text-center text-slate-500 text-sm">Tidak ditemukan.</div>';
                        areaResults.classList.remove('hidden');
                    }
                } catch (err) {
                    console.error('Search error:', err);
                }
            }, 500);
        });

        const postalInput = document.getElementById('postal-code');

        // Listener for Postal Code
        postalInput.addEventListener('input', () => {
            const areaId = document.getElementById('destination-area-id').value;
            const lat = document.getElementById('dest-lat').value;
            const lng = document.getElementById('dest-lng').value;

            // Only re-check rates if postal code is valid (5 digits) AND we have location data
            if (postalInput.value.length === 5 && (areaId || (lat && lng))) {
                checkRates(areaId, lat, lng);
            }
        });

        window.selectArea = async (id, name, lat, lng) => {
            document.getElementById('destination-area-id').value = id;
            document.getElementById('destination-area-text').value = name;
            areaSearchInput.value = name;
            areaResults.classList.add('hidden');

            // If Biteship didn't provide coordinates, try geocoding via Nominatim
            let finalLat = (lat && lat !== 'null' && lat !== 'undefined' && lat !== '') ? lat : '';
            let finalLng = (lng && lng !== 'null' && lng !== 'undefined' && lng !== '') ? lng : '';

            if (!finalLat || !finalLng) {
                try {
                    // Clean the area name: remove postal code suffix
                    const cleanName = name.replace(/\.\s*\d{5}$/, '').trim();
                    const res = await fetch(`https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(cleanName)}&format=json&limit=1&countrycodes=id`);
                    const data = await res.json();
                    if (data && data.length > 0 && data[0].lat && data[0].lon) {
                        finalLat = data[0].lat;
                        finalLng = data[0].lon;
                        console.log('Geocoded coordinates:', finalLat, finalLng, 'from:', cleanName);
                    }
                } catch (err) {
                    console.warn('Frontend geocoding failed:', err);
                }
            }

            document.getElementById('dest-lat').value = finalLat;
            document.getElementById('dest-lng').value = finalLng;

            // Trigger checkRates immediately!
            checkRates(id, finalLat, finalLng);
        };

        async function checkRates(areaId, lat, lng) {
            const postalCode = document.getElementById('postal-code').value;
            // No longer blocking if postal code is missing

            const ratesSection = document.getElementById('shipping-rates-section');
            const ratesList = document.getElementById('shipping-rates-list');
            const destLat = (lat && lat !== 'null' && lat !== 'undefined') ? lat : document.getElementById('dest-lat').value;
            const destLng = (lng && lng !== 'null' && lng !== 'undefined') ? lng : document.getElementById('dest-lng').value;

            ratesSection.classList.remove('hidden');
            ratesList.innerHTML = `
                <div class="col-span-full py-8 flex flex-col items-center justify-center text-slate-500">
                    <span class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary mb-3"></span>
                    <p class="text-sm">Mencari kurir tersedia...</p>
                </div>
            `;

            try {
                const res = await fetch('<?= BASE_URL ?>public/api/check_rates.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        area_id: areaId,
                        area_text: document.getElementById('destination-area-text').value,
                        postal_code: postalCode,
                        items: cart,
                        couriers: 'paxel,jne,jnt,sicepat,gojek,grab,anteraja,borzo,lalamove',
                        dest_lat: destLat,
                        dest_lng: destLng
                    })
                });
                const data = await res.json();

                let html = '';
                if (data.recommendation) {
                    const rec = data.recommendation;
                    const bgClass = rec.type === 'instant' ? 'bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-900/50' :
                        (rec.type === 'cold' ? 'bg-cyan-50 dark:bg-cyan-900/20 border-cyan-200 dark:border-cyan-900/50' :
                            'bg-purple-50 dark:bg-purple-900/20 border-purple-200 dark:border-purple-900/50');
                    const textClass = rec.type === 'instant' ? 'text-blue-700 dark:text-blue-400' :
                        (rec.type === 'cold' ? 'text-cyan-700 dark:text-cyan-400' :
                            'text-purple-700 dark:text-purple-400');
                    const icon = rec.type === 'instant' ? 'bolt' : (rec.type === 'cold' ? 'ac_unit' : 'local_shipping');

                    html += `
                        <div class="${bgClass} border p-4 rounded-xl flex items-start gap-3 mb-4 animate-pulse-subtle">
                            <span class="material-symbols-outlined ${textClass}">${icon}</span>
                            <div>
                                <h4 class="font-bold text-sm ${textClass} mb-0.5">${rec.title}</h4>
                                <p class="text-[11px] ${textClass} opacity-90 leading-relaxed">${rec.message}</p>
                            </div>
                        </div>
                    `;
                }

                if (!data.success) {
                    html += `
                        <div class="bg-red-50 text-red-600 p-3 rounded-lg text-sm mb-3">
                            ${data.message || 'Gagal memuat ongkir.'}
                        </div>
                    `;
                    ratesList.innerHTML = html;
                    return;
                }

                if (data.warning_msg) {
                    html += `
                        <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-900/50 p-3 rounded-lg flex items-start gap-2 mb-2">
                            <span class="material-symbols-outlined text-amber-500 text-sm mt-0.5">warning</span>
                            <p class="text-[11px] text-amber-700 dark:text-amber-400 font-medium leading-tight">${data.warning_msg}</p>
                        </div>
                    `;
                }

                if (data.pricing && data.pricing.length > 0) {
                    const hasLocal = data.pricing.some(r => r.company === 'local');

                    if (hasLocal) {
                        html += `
                        <div class="mb-3 p-4 bg-primary/5 border border-primary/10 rounded-xl flex items-start gap-3 shadow-sm border-l-4">
                            <span class="material-symbols-outlined text-primary text-xl">info</span>
                            <div class="text-[11px] md:text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                                <p class="font-bold text-primary mb-1 text-sm">Jadwal Pengiriman Kurir Lokal:</p>
                                <p>• Pesanan lewat jam <strong>17:00 (5 sore)</strong> akan dikirim <strong>besok jam 09:00</strong>.</p>
                                <p>• Pesanan lewat jam <strong>09:00 (9 pagi)</strong> akan dikirim <strong>hari ini jam 17:00</strong>.</p>
                            </div>
                        </div>
                        `;
                    }

                    html += data.pricing.map(rate => {
                        const isInternal = rate.company === 'local';
                        const icon = isInternal ? 'local_shipping' : 'package_2';
                        const iconBg = isInternal ? 'bg-primary/10 text-primary' : 'bg-slate-100 text-slate-500';

                        return `
                        <label class="cursor-pointer relative rounded-2xl border border-slate-200 dark:border-slate-800 p-4 grid grid-cols-[auto_1fr_auto] items-center gap-4 hover:bg-slate-50 dark:hover:bg-slate-900 transition-all group has-[:checked]:border-primary has-[:checked]:bg-primary/[0.03] has-[:checked]:ring-1 has-[:checked]:ring-primary/50">
                            <div class="flex items-center justify-center size-10 rounded-xl ${iconBg} transition-colors group-hover:scale-110">
                                <span class="material-symbols-outlined text-[22px] font-variation-fill">${icon}</span>
                            </div>
                            <div class="flex flex-col min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-slate-900 dark:text-white uppercase text-xs md:text-sm truncate">${rate.company} ${rate.courier_service_name}</span>
                                </div>
                                <span class="text-[10px] md:text-xs text-slate-500 font-medium">${rate.duration}</span>
                            </div>
                            <div class="flex flex-col items-end gap-1">
                                <span class="font-black text-slate-900 dark:text-white text-sm md:text-base whitespace-nowrap">
                                    ${rate.price === 0 ? '<span class="text-green-600">GRATIS</span>' : 'Rp ' + new Intl.NumberFormat('id-ID').format(rate.price)}
                                </span>
                                <input type="radio" name="courier_option" value="${rate.company}|${rate.courier_service_name}|${rate.price}"
                                    class="text-primary focus:ring-primary size-5 rounded-full border-slate-300" 
                                    onchange="updateTotal(${rate.price}, '${rate.courier_name}')">
                            </div>
                            
                            <!-- Hidden inputs for legacy form processing -->
                            <input type="radio" name="courier_company" value="${rate.company}" class="hidden">
                            <input type="radio" name="courier_type" value="${rate.type || rate.courier_service_code || rate.courier_service_name}" class="hidden">
                            <input type="radio" name="courier_price" value="${rate.price}" class="hidden">
                        </label>
                        `;
                    }).join('');

                    ratesList.innerHTML = html;
                } else {
                    ratesList.innerHTML = `
                        <div class="bg-red-50 dark:bg-red-900/10 border border-red-100 dark:border-red-900/30 p-4 rounded-lg text-red-600 text-sm italic">
                            Maaf, tidak ada kurir tersedia untuk rute ini. Coba cek kembali Kode Pos Anda.
                        </div>
                    `;
                }
            } catch (err) {
                console.error('Rates error:', err);
                ratesList.innerHTML = '<p class="text-sm text-red-500">Gagal memuat kurir. Silakan coba lagi.</p>';
            }
        }

        window.updateTotal = (cost, courierName) => {
            shippingCost = parseInt(cost);

            // Sync hidden legacy inputs
            const courierOption = document.querySelector('input[name="courier_option"]:checked').value;
            const [company, service, price] = courierOption.split('|');

            document.querySelectorAll('input[name="courier_company"]').forEach(i => i.checked = false);
            document.querySelectorAll('input[name="courier_type"]').forEach(i => i.checked = false);
            document.querySelectorAll('input[name="courier_price"]').forEach(i => i.checked = false);

            // Try to match safely by escaping or finding partial
            // In API response construction above, IDs are sanitized? Ideally yes.
            // Simplified here: Just rely on name attributes if IDs fail, but let's try IDs:
            const safeService = service.replace(/[^a-zA-Z0-9-_]/g, '');
            const safeCompany = company.replace(/[^a-zA-Z0-9-_]/g, '');
            // Actually the ID construction in map loop was: id="c-${rate.company}-${rate.courier_service_name}"
            // Service name might have spaces.

            // Better approach: Find inputs by value matching
            const cInput = document.querySelector(`input[name="courier_company"][value="${company}"]`);
            // Type/Service matching is harder as value might be code or name.
            // But we set them specific to this loop instance.

            // Re-finding based on the clicked radio's parent label context is safest?
            // No, let's just use the hidden inputs next to the radio.
            const parentLabel = document.querySelector(`input[name="courier_option"][value="${courierOption}"]`).closest('label');
            parentLabel.querySelector('input[name="courier_company"]').checked = true;
            parentLabel.querySelector('input[name="courier_type"]').checked = true;
            parentLabel.querySelector('input[name="courier_price"]').checked = true;

            updateTotalDisplay();
        };

        function updateTotalDisplay() {
            const shippingEl = document.getElementById('order-shipping');
            const totalDisplay = document.getElementById('order-total');
            const mobileTotalDisplay = document.getElementById('mobile-total');

            const formatter = new Intl.NumberFormat('id-ID');

            if (shippingEl) {
                shippingEl.innerText = shippingCost === 0 ? 'Gratis' : 'Rp ' + formatter.format(shippingCost);
                if (shippingCost === 0 && shippingCost !== null) shippingEl.classList.add('text-green-600');
                else shippingEl.classList.remove('text-green-600');
            }

            const finalTotal = baseTotal + shippingCost;
            const formattedTotal = 'Rp ' + formatter.format(finalTotal);

            if (totalDisplay) totalDisplay.innerText = formattedTotal;
            if (mobileTotalDisplay) mobileTotalDisplay.innerText = formattedTotal;
        }

        // Initialize total display
        updateTotalDisplay();


        // Geolocation Logic
        const btnGeo = document.getElementById('btn-geolocation');
        btnGeo.addEventListener('click', () => {
            if (!navigator.geolocation) {
                alert('Browser Anda tidak mendukung Geolocation.');
                return;
            }

            const originalContent = btnGeo.innerHTML;
            btnGeo.innerHTML = '<span class="animate-spin rounded-full h-3 w-3 border-2 border-primary border-t-transparent"></span><span class="text-[9px]">Mencari...</span>';
            btnGeo.disabled = true;

            navigator.geolocation.getCurrentPosition(
                (position) => {
                    btnGeo.innerHTML = originalContent;
                    btnGeo.disabled = false;
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;

                    document.getElementById('dest-lat').value = lat;
                    document.getElementById('dest-lng').value = lng;

                    // Optional: Reverse Geocoding could go here if needed to fill the text input
                    areaSearchInput.value = "Lokasi Saya (" + lat.toFixed(4) + ", " + lng.toFixed(4) + ")";
                    document.getElementById('destination-area-text').value = "Pinned Location";
                    // For area_id, we might leave it empty or set a flag. 
                    // But check_rates.php needs area_id usually for Biteship fallback. 
                    // If we have coords, we prioritize coords in our logic.
                    // However, Biteship might still need an area_id. 
                    // For now, let's assume we proceed with empty area_id if we have coords, 
                    // OR we force user to still select area for Biteship accuracy if needed.
                    // But let's try to just trigger checkRates with what we have.

                    checkRates('', lat, lng);

                    btnGeo.innerHTML = '<span class="material-symbols-outlined text-lg animate-bounce">check</span><span>Lokasi ditemukan</span>';
                    btnGeo.classList.remove('text-primary');
                    btnGeo.classList.add('text-green-600');
                    setTimeout(() => {
                        btnGeo.innerHTML = originalContent;
                        btnGeo.classList.add('text-primary');
                        btnGeo.classList.remove('text-green-600');
                        btnGeo.disabled = false;
                    }, 3000);
                },
                (error) => {
                    console.error(error);
                    alert('Gagal mengambil lokasi: ' + error.message);
                    btnGeo.innerHTML = originalContent;
                    btnGeo.disabled = false;
                }
            );
        });

        // Close search when clicking outside
        document.addEventListener('click', (e) => {
            if (!document.getElementById('area-search-container').contains(e.target)) {
                areaResults.classList.add('hidden');
            }
        });

        // --- Persistence Logic ---
        const PERSISTENCE_KEY = 'customer_biodata';

        function saveCustomerData() {
            const formData = new FormData(document.getElementById('checkout-form'));
            const dataToSave = {
                name: formData.get('name'),
                email: formData.get('email'),
                phone: formData.get('phone'),
                address: formData.get('address'),
                postal_code: formData.get('postal_code'),
                area_id: document.getElementById('destination-area-id').value,
                area_text: document.getElementById('destination-area-text').value,
                area_search: document.getElementById('area-search-input').value,
                lat: document.getElementById('dest-lat').value,
                lng: document.getElementById('dest-lng').value
            };
            localStorage.setItem(PERSISTENCE_KEY, JSON.stringify(dataToSave));
        }

        function loadCustomerData() {
            try {
                const saved = localStorage.getItem(PERSISTENCE_KEY);
                if (!saved) return;
                const data = JSON.parse(saved);

                // Fill standard inputs
                const form = document.getElementById('checkout-form');
                if (data.name) form.querySelector('[name="name"]').value = data.name;
                if (data.email) form.querySelector('[name="email"]').value = data.email;
                if (data.phone) form.querySelector('[name="phone"]').value = data.phone;
                if (data.address) form.querySelector('[name="address"]').value = data.address;
                if (data.postal_code) form.querySelector('[name="postal_code"]').value = data.postal_code;

                // Fill hidden inputs
                if (data.area_id) document.getElementById('destination-area-id').value = data.area_id;
                if (data.area_text) document.getElementById('destination-area-text').value = data.area_text;
                if (data.area_search) document.getElementById('area-search-input').value = data.area_search;
                if (data.lat) document.getElementById('dest-lat').value = data.lat;
                if (data.lng) document.getElementById('dest-lng').value = data.lng;

                // Trigger rates if we have location info
                if (data.area_id || (data.lat && data.lng)) {
                    checkRates(data.area_id, data.lat, data.lng);
                }
            } catch (e) {
                console.error('Failed to load saved biodata:', e);
            }
        }

        // Attach listeners to all relevant inputs
        const formFields = ['name', 'email', 'phone', 'address', 'postal_code'];
        formFields.forEach(fieldName => {
            const el = document.querySelector(`[name="${fieldName}"]`);
            if (el) el.addEventListener('change', saveCustomerData);
        });

        // Search input also needs saving on manual clear or change
        document.getElementById('area-search-input').addEventListener('change', saveCustomerData);

        // Also save when an area is selected (add to window.selectArea)
        const originalSelectArea = window.selectArea;
        window.selectArea = async (id, name, lat, lng) => {
            await originalSelectArea(id, name, lat, lng);
            saveCustomerData();
        };

        // Also save when geolocation is used
        const originalCheckRates = window.checkRates;
        window.checkRates = async (areaId, lat, lng) => {
            await originalCheckRates(areaId, lat, lng);
            // Save after potentially getting new coordinates/area
            saveCustomerData();
        };

        // Initialize
        loadCustomerData();
        togglePaymentInfo();
    </script>
</body>

</html>