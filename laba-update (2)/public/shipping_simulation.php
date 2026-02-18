<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lapak Bangsawan - Simulasi Pengiriman Hybrid</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .glass-panel {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">

    <!-- Main Container -->
    <div class="w-full max-w-md glass-panel rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
        
        <!-- Header -->
        <div class="bg-indigo-600 p-6 text-white relative overflow-hidden">
            <div class="absolute top-0 right-0 -mt-4 -mr-4 bg-white opacity-10 w-24 h-24 rounded-full"></div>
            <h2 class="text-xl font-bold relative z-10">Pengiriman</h2>
            <p class="text-indigo-100 text-sm relative z-10">Pilih opsi pengiriman terbaik untuk Anda</p>
        </div>

        <!-- Content -->
        <div class="p-6 space-y-6">

            <!-- Location Section -->
            <div class="space-y-3">
                <label class="block text-sm font-medium text-gray-700">Lokasi Pengiriman</label>
                
                <div class="flex flex-col gap-3">
                    <button id="btn-get-location" class="flex items-center justify-center w-full py-3 px-4 bg-indigo-50 text-indigo-700 font-semibold rounded-xl border border-indigo-100 hover:bg-indigo-100 transition-colors focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                        </svg>
                        Gunakan Lokasi Saat Ini
                    </button>
                    
                    <div id="location-status" class="text-xs text-gray-500 text-center hidden">
                        Mendeteksi lokasi...
                    </div>
                </div>

                <!-- Coordinates Display (Hidden or Subtle) -->
                <div class="grid grid-cols-2 gap-4 text-xs text-gray-400 mt-2 bg-gray-50 p-2 rounded-lg border border-gray-100 hidden" id="coords-display">
                    <div>Latency: <span id="lat-val">-</span></div>
                    <div>Longitude: <span id="lng-val">-</span></div>
                </div>
            </div>

            <!-- Shipping Options -->
            <div id="shipping-results" class="space-y-3 hidden">
                <h3 class="text-sm font-medium text-gray-900 pb-2 border-b border-gray-100">Opsi Tersedia</h3>
                
                <div id="options-container" class="space-y-3">
                    <!-- Dynamic Options will be injected here -->
                </div>
            </div>

            <!-- Empty State / Placeholder -->
            <div id="empty-state" class="text-center py-8">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-50 mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                    </svg>
                </div>
                <p class="text-sm text-gray-400">Silakan tentukan lokasi untuk melihat ongkir</p>
            </div>

        </div>

    </div>

    <script>
        const btnGetLocation = document.getElementById('btn-get-location');
        const locationStatus = document.getElementById('location-status');
        const coordsDisplay = document.getElementById('coords-display');
        const latVal = document.getElementById('lat-val');
        const lngVal = document.getElementById('lng-val');
        const shippingResults = document.getElementById('shipping-results');
        const optionsContainer = document.getElementById('options-container');
        const emptyState = document.getElementById('empty-state');

        // Customer ID simulation (replace with actual session ID in production)
        const CUSTOMER_ID = 3; 

        btnGetLocation.addEventListener('click', () => {
            if (!navigator.geolocation) {
                alert("Geolocation tidak didukung oleh browser ini.");
                return;
            }

            locationStatus.textContent = "Mencari koordinat...";
            locationStatus.classList.remove('hidden');
            btnGetLocation.disabled = true;
            btnGetLocation.classList.add('opacity-75', 'cursor-not-allowed');

            navigator.geolocation.getCurrentPosition(success, error);
        });

        function success(position) {
            const latitude  = position.coords.latitude;
            const longitude = position.coords.longitude;

            latVal.textContent = latitude.toFixed(6);
            lngVal.textContent = longitude.toFixed(6);
            coordsDisplay.classList.remove('hidden');
            locationStatus.textContent = "Lokasi ditemukan! Menghitung biaya...";

            fetchShippingOptions(latitude, longitude);
        }

        function error() {
            locationStatus.textContent = "Gagal mengambil lokasi. Pastikan GPS aktif.";
            btnGetLocation.disabled = false;
            btnGetLocation.classList.remove('opacity-75', 'cursor-not-allowed');
        }

        async function fetchShippingOptions(lat, lng) {
            try {
                const response = await fetch('../api/calculate_shipping.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        latitude: lat,
                        longitude: lng,
                        customer_id: CUSTOMER_ID 
                    })
                });

                if (!response.ok) throw new Error('Network response was not ok');
                
                const data = await response.json();
                
                if(data.status === 'success') {
                    renderOptions(data.options, data.distance);
                } else {
                    alert('Error: ' + data.message);
                }

            } catch (error) {
                console.error('Error:', error);
                locationStatus.textContent = "Terjadi kesalahan koneksi.";
            } finally {
                btnGetLocation.disabled = false;
                btnGetLocation.classList.remove('opacity-75', 'cursor-not-allowed');
            }
        }

        function renderOptions(options, distance) {
            optionsContainer.innerHTML = '';
            
            // Show Distance Info
            locationStatus.innerHTML = `Jarak ke store: <strong>${distance} km</strong>`;

            emptyState.classList.add('hidden');
            shippingResults.classList.remove('hidden');

            options.forEach(opt => {
                const isInstant = opt.code === 'LOCAL_INSTANT';
                
                const card = document.createElement('div');
                card.className = `cursor-pointer relative flex items-center justify-between p-4 rounded-xl border-2 transition-all duration-200 hover:border-indigo-500 hover:shadow-md ${isInstant ? 'bg-indigo-50 border-indigo-200' : 'bg-white border-gray-100'}`;
                
                card.innerHTML = `
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center ${isInstant ? 'bg-indigo-100 text-indigo-600' : 'bg-gray-100 text-gray-500'}">
                            ${isInstant 
                                ? '<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>' 
                                : '<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>'
                            }
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900 text-sm">${opt.name}</h4>
                            <p class="text-xs text-gray-500">Estimasi: ${opt.etd}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="font-bold text-indigo-700 text-sm">Rp ${new Intl.NumberFormat('id-ID').format(opt.price)}</div>
                        ${isInstant ? '<span class="text-[10px] uppercase font-bold text-indigo-500 bg-indigo-100 px-2 py-0.5 rounded-full">Recommended</span>' : ''}
                    </div>
                `;
                
                // Selection Logic (Visual only for demo)
                card.onclick = () => {
                   document.querySelectorAll('#options-container > div').forEach(el => {
                       el.classList.remove('border-indigo-600', 'ring-2', 'ring-indigo-100');
                       if(!el.classList.contains('bg-indigo-50')) el.classList.add('border-gray-100');
                   });
                   card.classList.remove('border-gray-100', 'border-indigo-200');
                   card.classList.add('border-indigo-600', 'ring-2', 'ring-indigo-100');
                };

                optionsContainer.appendChild(card);
            });
        }
    </script>
</body>
</html>
