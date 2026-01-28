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

    <?php include ROOT_PATH . "includes/public_header.php"; ?>

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
                                <input type="hidden" name="destination_area_id" id="destination-area-id">
                                <input type="hidden" name="destination_area_text" id="destination-area-text">
                                <input type="hidden" name="dest_lat" id="dest-lat">
                                <input type="hidden" name="dest_lng" id="dest-lng">

                                <!-- Search Results -->
                                <div id="area-results"
                                    class="hidden absolute z-50 w-full mt-1 bg-white dark:bg-card-dark border border-slate-200 dark:border-slate-800 rounded-lg shadow-xl max-h-60 overflow-y-auto">
                                </div>
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
                            <span id="order-shipping" class="font-medium">Rp 0</span>
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
            window.location.href = 'market.php';
        }

        let total = 0;
        cart.forEach(item => {
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
        fetch('api_calculate_total.php', {
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

        let baseTotal = 0;
        let shippingCost = 0;

        // Handle Submit
        document.getElementById('checkout-form').addEventListener('submit', async (e) => {
            e.preventDefault();

            // Validate Area and Courier
            const areaId = document.getElementById('destination-area-id').value;
            const courier = document.querySelector('input[name="courier_option"]:checked');

            if (!areaId) {
                alert('Silakan pilih Kota/Kecamatan yang valid dari daftar pencarian.');
                return;
            }

            if (!courier) {
                alert('Silakan pilih kurir pengiriman.');
                return;
            }

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
                address: formData.get('address') + ' (' + formData.get('destination_area_text') + ')',
                destination_area_id: formData.get('destination_area_id'),
                courier_company: formData.get('courier_company'),
                courier_type: formData.get('courier_type'),
                courier_price: parseFloat(formData.get('courier_price')),
                order_notes: formData.get('order_notes'),
                payment_method: formData.get('payment_method'),
                order_token: formData.get('order_token'),
                items: cart,
                total: baseTotal + shippingCost,
                shipping_cost: shippingCost
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
                    const res = await fetch(`api/search_area.php?q=${encodeURIComponent(query)}`);
                    const data = await res.json();

                    if (data.success && data.areas.length > 0) {
                        areaResults.innerHTML = data.areas.map(area => {
                            const lat = area.latitude || '';
                            const lng = area.longitude || '';
                            return `
                                <div class="p-3 hover:bg-slate-50 dark:hover:bg-slate-800 cursor-pointer text-sm border-b border-slate-100 dark:border-slate-800 last:border-0"
                                    onclick="selectArea('${area.id}', '${area.name}', '${lat}', '${lng}')">
                                    ${area.name}
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

        window.selectArea = (id, name, lat, lng) => {
            document.getElementById('destination-area-id').value = id;
            document.getElementById('destination-area-text').value = name;
            document.getElementById('dest-lat').value = (lat && lat !== 'null' && lat !== 'undefined') ? lat : '';
            document.getElementById('dest-lng').value = (lng && lng !== 'null' && lng !== 'undefined') ? lng : '';
            areaSearchInput.value = name;
            areaResults.classList.add('hidden');
            checkRates(id, lat, lng);
        };

        async function checkRates(areaId, lat, lng) {
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
                const res = await fetch('api/check_rates.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        area_id: areaId,
                        area_text: document.getElementById('destination-area-text').value,
                        items: cart,
                        couriers: 'jne,jnt,sicepat,gojek,grab,anteraja,borzo,lalamove',
                        dest_lat: destLat,
                        dest_lng: destLng
                    })
                });
                const data = await res.json();

                if (data.success && data.pricing && data.pricing.length > 0) {
                    ratesList.innerHTML = data.pricing.map(rate => `
                        <label class="cursor-pointer relative rounded-xl border border-slate-200 dark:border-slate-700 p-4 flex items-center justify-between hover:bg-slate-50 dark:hover:bg-slate-800 transition-all has-[:checked]:border-primary has-[:checked]:bg-primary/5 has-[:checked]:ring-1 has-[:checked]:ring-primary">
                            <div class="flex items-center gap-3">
                                <input type="radio" name="courier_option" value="${rate.company}|${rate.courier_service_name}|${rate.price}"
                                    class="text-primary focus:ring-primary size-5" 
                                    onchange="updateTotal(${rate.price}, '${rate.courier_name}')">
                                <div class="flex flex-col">
                                    <span class="font-bold text-slate-900 dark:text-white uppercase">${rate.courier_name} - ${rate.courier_service_name}</span>
                                    <span class="text-[10px] text-slate-500">Estimasi: ${rate.duration}</span>
                                </div>
                            </div>
                            <span class="font-bold text-slate-900 dark:text-white">Rp ${new Intl.NumberFormat('id-ID').format(rate.price)}</span>
                            
                            <!-- Hidden inputs for legacy form processing if needed -->
                            <input type="radio" name="courier_company" value="${rate.company}" class="hidden" id="c-${rate.company}-${rate.courier_service_name}">
                            <input type="radio" name="courier_type" value="${rate.courier_service_name}" class="hidden" id="t-${rate.company}-${rate.courier_service_name}">
                            <input type="radio" name="courier_price" value="${rate.price}" class="hidden" id="p-${rate.company}-${rate.courier_service_name}">
                        </label>
                    `).join('');
                } else {
                    ratesList.innerHTML = `
                        <div class="bg-red-50 dark:bg-red-900/10 border border-red-100 dark:border-red-900/30 p-4 rounded-lg text-red-600 text-sm italic">
                            Maaf, tidak ada kurir tersedia untuk rute ini.
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

            const cInput = document.getElementById(`c-${company}-${service}`);
            const tInput = document.getElementById(`t-${company}-${service}`);
            const pInput = document.getElementById(`p-${company}-${service}`);

            if (cInput) cInput.checked = true;
            if (tInput) tInput.checked = true;
            if (pInput) pInput.checked = true;

            updateTotalDisplay();
        };

        function updateTotalDisplay() {
            const shippingEl = document.getElementById('order-shipping');
            const totalDisplay = document.getElementById('order-total');

            const formatter = new Intl.NumberFormat('id-ID');

            shippingEl.innerText = 'Rp ' + formatter.format(shippingCost);
            shippingEl.classList.remove('text-green-600');

            const finalTotal = baseTotal + shippingCost;
            totalDisplay.innerText = 'Rp ' + formatter.format(finalTotal);
        }

        // Close search when clicking outside
        document.addEventListener('click', (e) => {
            if (!document.getElementById('area-search-container').contains(e.target)) {
                areaResults.classList.add('hidden');
            }
        });

        // Initialize
        togglePaymentInfo();
    </script>
</body>

</html>