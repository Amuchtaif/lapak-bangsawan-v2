<?php
require_once dirname(__DIR__) . "/config/init.php";
?>
<!DOCTYPE html>
<html class="light" lang="id">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Lapak Bangsawan - Tentang Kami</title>
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

    <!-- Navigation -->
    <?php include ROOT_PATH . "includes/public_header.php"; ?>

    <main class="flex-grow w-full max-w-[1200px] mx-auto px-4 md:px-10 lg:px-40 pt-12 md:pt-24 pb-12">
        <div class="flex flex-col lg:flex-row gap-12 items-center mb-20">
            <div class="flex-1 space-y-6">
                <span class="text-primary font-bold tracking-wider uppercase text-sm">Cerita Kami</span>
                <h2 class="text-4xl md:text-5xl font-black text-slate-900 dark:text-white leading-tight">
                    Membawa Kesegaran <br>ke Meja Makan Anda
                </h2>
                <p class="text-lg text-slate-600 dark:text-slate-400 leading-relaxed">
                    Lapak Bangsawan didirikan dengan satu misi sederhana: menyediakan protein hewani berkualitas tinggi
                    yang segar, higienis, dan terjangkau bagi setiap keluarga Indonesia.
                </p>
                <div class="pt-4 grid grid-cols-2 gap-8">
                    <div>
                        <h4 class="text-3xl font-bold text-slate-900 dark:text-white mb-1">100%</h4>
                        <p class="text-sm text-slate-500">Halal & Higienis</p>
                    </div>
                    <div>
                        <h4 class="text-3xl font-bold text-slate-900 dark:text-white mb-1">24 Jam</h4>
                        <p class="text-sm text-slate-500">Jaminan Kesegaran</p>
                    </div>
                </div>
            </div>
            <div class="flex-1 relative">
                <div
                    class="absolute inset-0 bg-gradient-to-tr from-primary/20 to-transparent rounded-2xl transform rotate-3 scale-105">
                </div>
                <img src="<?= BASE_URL ?>assets/images/logo.jpeg" sizes="60%" alt="Fresh Produce Market"
                    class="relative rounded-2xl shadow-2xl w-full object-cover h-[400px]">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 py-12 border-t border-slate-200 dark:border-slate-800">

            <div class="bg-white dark:bg-card-dark p-8 rounded-xl border border-slate-200 dark:border-slate-800 hover:border-primary/50 transition-colors group
                text-center md:text-left">

                <div class="size-14 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-xl flex items-center justify-center mb-6
                    mx-auto md:mx-0 group-hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined text-3xl">verified</span>
                </div>

                <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-3">
                    Kualitas Terjamin
                </h3>

                <p class="text-slate-500 dark:text-slate-400 leading-relaxed">
                    Setiap produk yang kami kirim telah melewati proses seleksi ketat untuk memastikan standar kualitas
                    tertinggi.
                </p>
            </div>

            <div class="bg-white dark:bg-card-dark p-8 rounded-xl border border-slate-200 dark:border-slate-800 hover:border-primary/50 transition-colors group
                text-center md:text-left">

                <div class="size-14 bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 rounded-xl flex items-center justify-center mb-6
                    mx-auto md:mx-0 group-hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined text-3xl">local_shipping</span>
                </div>

                <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-3">
                    Pengiriman Cepat
                </h3>

                <p class="text-slate-500 dark:text-slate-400 leading-relaxed">
                    Layanan pengiriman kami yang efisien memastikan produk sampai di depan pintu Anda dalam kondisi
                    segar.
                </p>
            </div>

            <div class="bg-white dark:bg-card-dark p-8 rounded-xl border border-slate-200 dark:border-slate-800 hover:border-primary/50 transition-colors group
                text-center md:text-left">

                <div class="size-14 bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 rounded-xl flex items-center justify-center mb-6
                    mx-auto md:mx-0 group-hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined text-3xl">savings</span>
                </div>

                <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-3">
                    Harga Terbaik
                </h3>

                <p class="text-slate-500 dark:text-slate-400 leading-relaxed">
                    Kami bekerja langsung dengan peternak dan nelayan untuk memberikan harga yang kompetitif bagi Anda.
                </p>
            </div>

        </div>


        <!-- Team Section -->
        <div class="py-12 mb-12 border-t border-slate-200 dark:border-slate-800 pt-16">
            <div class="text-center mb-12">
                <span class="text-primary font-bold tracking-wider uppercase text-sm">Tim Kami</span>
                <h2 class="text-3xl md:text-4xl font-black text-slate-900 dark:text-white mt-2">
                    Team Lapak Bangsawan
                </h2>
                <p class="text-slate-500 dark:text-slate-400 mt-4 max-w-2xl mx-auto">
                    Para profesional berdedikasi yang bekerja keras untuk menghadirkan kualitas terbaik ke meja Anda.
                </p>
            </div>

            <!-- Team Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6">
                <!-- 1. Owner -->
                <div
                    class="group relative bg-white dark:bg-card-dark rounded-2xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-xl hover:-translate-y-2 transition-all duration-300 flex flex-col items-center">
                    <div
                        class="size-16 rounded-full bg-gradient-to-br from-blue-500 to-blue-700 text-white flex items-center justify-center mb-4 shadow-lg group-hover:scale-110 transition-transform duration-300">
                        <span class="material-symbols-outlined text-3xl">diamond</span>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Shohib</h3>
                    <p class="text-sm font-medium text-primary mt-1">Owner</p>
                    <div
                        class="w-12 h-1 bg-slate-100 dark:bg-slate-700 rounded-full mt-4 group-hover:bg-primary transition-colors">
                    </div>
                </div>

                <!-- 2. Web Developer -->
                <div
                    class="group relative bg-white dark:bg-card-dark rounded-2xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-xl hover:-translate-y-2 transition-all duration-300 flex flex-col items-center">
                    <div
                        class="size-16 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 text-white flex items-center justify-center mb-4 shadow-lg group-hover:scale-110 transition-transform duration-300">
                        <span class="material-symbols-outlined text-3xl">terminal</span>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Amuchtaif</h3>
                    <p class="text-sm font-medium text-primary mt-1">Web Developer</p>
                    <div
                        class="w-12 h-1 bg-slate-100 dark:bg-slate-700 rounded-full mt-4 group-hover:bg-primary transition-colors">
                    </div>
                </div>

                <!-- 3. Marketing -->
                <div
                    class="group relative bg-white dark:bg-card-dark rounded-2xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-xl hover:-translate-y-2 transition-all duration-300 flex flex-col items-center">
                    <div
                        class="size-16 rounded-full bg-gradient-to-br from-pink-500 to-rose-600 text-white flex items-center justify-center mb-4 shadow-lg group-hover:scale-110 transition-transform duration-300">
                        <span class="material-symbols-outlined text-3xl">campaign</span>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Dzuki</h3>
                    <p class="text-sm font-medium text-primary mt-1">Marketing</p>
                    <div
                        class="w-12 h-1 bg-slate-100 dark:bg-slate-700 rounded-full mt-4 group-hover:bg-primary transition-colors">
                    </div>
                </div>

                <!-- 4. Produksi -->
                <div
                    class="group relative bg-white dark:bg-card-dark rounded-2xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-xl hover:-translate-y-2 transition-all duration-300 flex flex-col items-center">
                    <div
                        class="size-16 rounded-full bg-gradient-to-br from-amber-500 to-orange-600 text-white flex items-center justify-center mb-4 shadow-lg group-hover:scale-110 transition-transform duration-300">
                        <span class="material-symbols-outlined text-3xl">precision_manufacturing</span>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Ipan</h3>
                    <p class="text-sm font-medium text-primary mt-1">Produksi</p>
                    <div
                        class="w-12 h-1 bg-slate-100 dark:bg-slate-700 rounded-full mt-4 group-hover:bg-primary transition-colors">
                    </div>
                </div>

                <!-- 5. Delivery -->
                <div
                    class="group relative bg-white dark:bg-card-dark rounded-2xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-xl hover:-translate-y-2 transition-all duration-300 flex flex-col items-center">
                    <div
                        class="size-16 rounded-full bg-gradient-to-br from-teal-500 to-emerald-600 text-white flex items-center justify-center mb-4 shadow-lg group-hover:scale-110 transition-transform duration-300">
                        <span class="material-symbols-outlined text-3xl">local_shipping</span>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Atar</h3>
                    <p class="text-sm font-medium text-primary mt-1">Delivery</p>
                    <div
                        class="w-12 h-1 bg-slate-100 dark:bg-slate-700 rounded-full mt-4 group-hover:bg-primary transition-colors">
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Section Merged -->
        <div id="contact" class="py-12 border-t border-slate-200 dark:border-slate-800">
            <div
                class="bg-white dark:bg-card-dark rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-8 md:p-12">
                <div class="text-center mb-12">
                    <h2 class="text-3xl font-bold text-slate-900 dark:text-white mb-4">Hubungi Kami</h2>
                    <p class="text-slate-500 dark:text-slate-400 max-w-lg mx-auto">Kami siap membantu Anda. Silakan
                        hubungi
                        kami untuk pertanyaan, saran, atau bantuan terkait pesanan Anda.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                    <!-- Contact Info -->
                    <div class="space-y-8">
                        <div class="flex gap-4">
                            <div class="size-12 bg-primary/10 rounded-xl flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-primary text-2xl">location_on</span>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-900 dark:text-white mb-1">Alamat Kantor</h3>
                                <p class="text-slate-500 dark:text-slate-400 text-sm leading-relaxed">
                                    Jl. Wanagati, Karyamulya, Kesambi
                                    <br>
                                    Kota Cirebon, Jawa Barat<br>
                                    Indonesia
                                </p>
                            </div>
                        </div>

                        <div class="flex gap-4">
                            <div class="size-12 bg-primary/10 rounded-xl flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-primary text-2xl">mail</span>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-900 dark:text-white mb-1">Email</h3>
                                <p class="text-slate-500 dark:text-slate-400 text-sm">
                                    lapakbangsawan@gmail.com
                                </p>
                            </div>
                        </div>

                        <div class="flex gap-4">
                            <div class="size-12 bg-primary/10 rounded-xl flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-primary text-2xl">call</span>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-900 dark:text-white mb-1">Telepon/WhatsApp</h3>
                                <p class="text-slate-500 dark:text-slate-400 text-sm">
                                    +6285 9110 022099
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Form -->
                    <form action="save_message.php" method="POST" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Nama
                                Lengkap</label>
                            <input type="text" name="name" required
                                class="w-full rounded-lg border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-primary focus:border-primary transition-all"
                                placeholder="Masukkan nama Anda">
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Email</label>
                            <input type="email" name="email" required
                                class="w-full rounded-lg border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-primary focus:border-primary transition-all"
                                placeholder="nama@email.com">
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Pesan</label>
                            <textarea rows="4" name="message" required
                                class="w-full rounded-lg border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-primary focus:border-primary transition-all"
                                placeholder="Tulis pesan Anda di sini..."></textarea>
                        </div>
                        <button type="submit" name="submit_message"
                            class="w-full bg-primary hover:bg-blue-600 text-white font-bold py-3 px-6 rounded-lg transition-colors shadow-lg shadow-blue-500/30">
                            Kirim Pesan
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <?php include ROOT_PATH . "includes/public_footer.php"; ?>

    <script>
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
        updateCartBadge();
    </script>
</body>

</html>