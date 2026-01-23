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
        <div class="w-full bg-white dark:bg-[#1a202c] border-b border-[#f0f1f5] dark:border-[#2d3748]">
            <div class="px-4 md:px-10 lg:px-40 flex justify-center">
                <div class="flex flex-col w-full max-w-[1200px]">
                    <header class="flex items-center justify-between whitespace-nowrap py-3 gap-4">
                        <div class="flex items-center gap-4 lg:gap-8 flex-1">
                            <a class="flex items-center gap-3 text-[#111318] dark:text-white" href="#">
                                <div class="size-12 text-primary">
                                    <img src="<?= BASE_URL ?>assets/images/logo.jpeg" alt="Logo">
                                </div>
                                <h2
                                    class="text-[#111318] dark:text-white text-xl font-bold leading-tight tracking-[-0.015em] hidden sm:block">
                                    Lapak Bangsawan</h2>
                            </a>
                        </div>
                        <div class="flex items-center justify-end gap-4 lg:gap-8">
                            <nav class="hidden lg:flex items-center gap-6">
                                <a class="text-[#111318] dark:text-white text-sm font-medium text-primary transition-colors"
                                    href="<?= BASE_URL ?>public/home">Beranda</a>
                                <a class="text-[#111318] dark:text-white text-sm font-medium hover:text-primary transition-colors"
                                    href="<?= BASE_URL ?>public/market">Belanja</a>
                                <a class="text-[#111318] dark:text-white text-sm font-medium hover:text-primary transition-colors"
                                    href="<?= BASE_URL ?>public/about">Tentang Kami</a>
                            </nav>
                            <div class="flex gap-2">
                                <!-- <button
                                    class="flex items-center justify-center rounded-lg h-10 w-10 bg-[#f0f1f5] dark:bg-[#2d3748] text-[#111318] dark:text-white hover:bg-primary/10 hover:text-primary transition-colors">
                                    <span class="material-symbols-outlined">shopping_cart</span>
                                </button> -->
                                <!-- <a href="admin/index.php"
                                    class="flex items-center justify-center rounded-lg h-10 w-10 bg-[#f0f1f5] dark:bg-[#2d3748] text-[#111318] dark:text-white hover:bg-primary/10 hover:text-primary transition-colors">
                                    <span class="material-symbols-outlined">account_circle</span>
                                </a> -->
                                <button id="mobile-menu-btn"
                                    class="lg:hidden flex items-center justify-center rounded-lg h-10 w-10 bg-[#f0f1f5] dark:bg-[#2d3748] text-[#111318] dark:text-white hover:bg-primary/10 hover:text-primary transition-colors z-50 relative">
                                    <span class="material-symbols-outlined">menu</span>
                                </button>
                            </div>
                        </div>
                    </header>
                    <!-- Mobile Menu Overlay -->
                    <div id="mobile-menu-overlay"
                        class="fixed inset-0 bg-black/50 z-40 hidden opacity-0 transition-opacity duration-300"></div>

                    <!-- Mobile Menu Sidebar -->
                    <div id="mobile-menu"
                        class="fixed inset-y-0 right-0 z-50 w-64 bg-white dark:bg-[#1a202c] shadow-2xl transform translate-x-full transition-transform duration-300 ease-in-out flex flex-col p-6 lg:hidden">
                        <div class="flex items-center justify-between mb-8">
                            <h2 class="text-lg font-bold text-[#111318] dark:text-white">Menu</h2>
                            <button id="close-menu-btn" class="text-[#606e8a] dark:text-[#a0aec0] hover:text-primary">
                                <span class="material-symbols-outlined">close</span>
                            </button>
                        </div>
                        <nav class="flex flex-col gap-4">
                            <a class="text-[#111318] dark:text-white text-base font-medium hover:text-primary transition-colors py-2 border-b border-gray-100 dark:border-gray-800"
                                href="<?= BASE_URL ?>public/home">Beranda</a>
                            <a class="text-[#111318] dark:text-white text-base font-medium hover:text-primary transition-colors py-2 border-b border-gray-100 dark:border-gray-800"
                                href="<?= BASE_URL ?>public/market">Belanja</a>
                            <a class="text-[#111318] dark:text-white text-base font-medium hover:text-primary transition-colors py-2 border-b border-gray-100 dark:border-gray-800"
                                href="<?= BASE_URL ?>public/about">Tentang Kami</a>
                        </nav>
                    </div>

                    <script>
                        document.addEventListener('DOMContentLoaded', () => {
                            const btn = document.getElementById('mobile-menu-btn');
                            const closeBtn = document.getElementById('close-menu-btn');
                            const menu = document.getElementById('mobile-menu');
                            const overlay = document.getElementById('mobile-menu-overlay');
                            const body = document.body;

                            function toggleMenu() {
                                const isClosed = menu.classList.contains('translate-x-full');
                                if (isClosed) {
                                    // Open
                                    menu.classList.remove('translate-x-full');
                                    overlay.classList.remove('hidden');
                                    setTimeout(() => overlay.classList.remove('opacity-0'), 10);
                                    body.style.overflow = 'hidden'; // Prevent scrolling
                                } else {
                                    // Close
                                    menu.classList.add('translate-x-full');
                                    overlay.classList.add('opacity-0');
                                    setTimeout(() => overlay.classList.add('hidden'), 300);
                                    body.style.overflow = '';
                                }
                            }

                            btn.addEventListener('click', toggleMenu);
                            closeBtn.addEventListener('click', toggleMenu);
                            overlay.addEventListener('click', toggleMenu);
                        });
                    </script>
                </div>
            </div>
        </div>
        <!-- Main Content Area -->
        <main class="flex-1 flex flex-col items-center w-full">
            <div class="w-full max-w-[1200px] px-4 md:px-10 lg:px-40 pb-20">
                <!-- Hero Section -->
                <div class="@container mt-10 mb-12">
                    <div class="flex flex-col gap-6 py-2 @[864px]:flex-row @[864px]:items-center">

                        <!-- TEXT -->
                        <div class="w-full @[864px]:w-1/2 flex flex-col gap-5 justify-center">
                            <div class="flex flex-col gap-3 text-left">
                                <span
                                    class="inline-block px-3 py-1 rounded-full bg-primary/10 text-primary text-xs font-bold w-fit">
                                    100% SEGAR & HALAL
                                </span>

                                <h1
                                    class="text-[#111318] dark:text-white text-3xl @[480px]:text-4xl lg:text-6xl font-black leading-tight tracking-[-0.03em]">
                                    Segar Hari Ini,<br class="hidden @[864px]:block"> Dikirim Hari Ini
                                </h1>

                                <p class="text-[#606e8a] dark:text-[#a0aec0] text-base leading-relaxed max-w-[480px]">
                                    Ayam, ikan, dan seafood premium langsung dari sumber terpercaya ke dapur Anda.
                                </p>
                            </div>

                            <!-- CTA -->
                            <div class="flex flex-col sm:flex-row gap-3 mt-2">
                                <a href="<?= BASE_URL ?>public/market"
                                    class="flex w-full sm:w-auto min-w-[160px] items-center justify-center rounded-lg h-12 px-6 bg-primary hover:bg-blue-700 transition-colors text-white text-base font-bold">
                                    Mulai Belanja
                                </a>
                            </div>
                        </div>

                        <!-- IMAGE -->
                        <div
                            class="w-full @[864px]:w-1/2 aspect-[16/9] @[864px]:aspect-[4/3] rounded-2xl overflow-hidden shadow-xl">
                            <div class="w-full h-full bg-cover bg-center"
                                style="background-image: url('<?= BASE_URL ?>assets/images/hero.jpg');">
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Features Section -->
                <div class="flex flex-col gap-10 py-10 mt-8">
                    <div class="text-center max-w-[720px] mx-auto">
                        <h2 class="text-[#111318] dark:text-white text-2xl md:text-3xl font-bold leading-tight mb-3">
                            Mengapa Memilih Lapak Bangsawan?
                        </h2>
                        <p class="text-[#606e8a] dark:text-[#a0aec0]">
                            Kami memprioritaskan kebersihan dan kecepatan untuk memastikan Anda mendapatkan kualitas
                            terbaik.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">

                        <!-- Card -->
                        <div class="flex flex-col gap-4 rounded-xl border border-[#dbdfe6] dark:border-[#2d3748]
                    bg-white dark:bg-[#1a202c] p-6 shadow-sm hover:shadow-md transition-shadow
                    items-center md:items-start text-center md:text-left">

                            <div class="size-12 rounded-full bg-blue-50 dark:bg-blue-900/30
                        flex items-center justify-center text-primary
                        mx-auto md:mx-0">
                                <span class="material-symbols-outlined">local_shipping</span>
                            </div>

                            <div class="flex flex-col gap-2">
                                <h3 class="text-[#111318] dark:text-white text-lg font-bold">
                                    Pengiriman Hari Berikutnya
                                </h3>
                                <p class="text-[#606e8a] dark:text-[#a0aec0] text-sm">
                                    Pesan sebelum pukul 20.00 dan terima produk segar Anda dalam waktu 24 jam, dijamin.
                                </p>
                            </div>
                        </div>

                        <!-- Card -->
                        <div class="flex flex-col gap-4 rounded-xl border border-[#dbdfe6] dark:border-[#2d3748]
                    bg-white dark:bg-[#1a202c] p-6 shadow-sm hover:shadow-md transition-shadow
                    items-center md:items-start text-center md:text-left">

                            <div class="size-12 rounded-full bg-blue-50 dark:bg-blue-900/30
                        flex items-center justify-center text-primary
                        mx-auto md:mx-0">
                                <span class="material-symbols-outlined">ac_unit</span>
                            </div>

                            <div class="flex flex-col gap-2">
                                <h3 class="text-[#111318] dark:text-white text-lg font-bold">
                                    Jaminan Rantai Dingin
                                </h3>
                                <p class="text-[#606e8a] dark:text-[#a0aec0] text-sm">
                                    Pesanan Anda dijaga pada suhu dingin optimal dari gudang kami hingga ke pintu Anda.
                                </p>
                            </div>
                        </div>

                        <!-- Card -->
                        <div class="flex flex-col gap-4 rounded-xl border border-[#dbdfe6] dark:border-[#2d3748]
                    bg-white dark:bg-[#1a202c] p-6 shadow-sm hover:shadow-md transition-shadow
                    items-center md:items-start text-center md:text-left">

                            <div class="size-12 rounded-full bg-blue-50 dark:bg-blue-900/30
                        flex items-center justify-center text-primary
                        mx-auto md:mx-0">
                                <span class="material-symbols-outlined">verified_user</span>
                            </div>

                            <div class="flex flex-col gap-2">
                                <h3 class="text-[#111318] dark:text-white text-lg font-bold">
                                    Tersertifikasi Halal
                                </h3>
                                <p class="text-[#606e8a] dark:text-[#a0aec0] text-sm">
                                    Sumber dan pemrosesan tersertifikasi Halal 100% untuk ketenangan pikiran Anda.
                                </p>
                            </div>
                        </div>

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
                            Kategori Kami</h2>
                        <a class="text-primary text-sm font-semibold hover:underline" href="<?= BASE_URL ?>public/market">Lihat
                            Semua</a>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <a class="group flex flex-col gap-3 rounded-xl border border-[#dbdfe6] dark:border-[#2d3748] bg-white dark:bg-[#1a202c] p-4 items-center hover:border-primary transition-colors cursor-pointer"
                            href="#">
                            <div class="bg-center bg-no-repeat bg-cover rounded-full w-20 h-20 shrink-0 group-hover:scale-105 transition-transform"
                                data-alt="Raw chicken meat icon"
                                style='background-image: url("https://lh3.googleusercontent.com/aida-public/AB6AXuBT7WB-3DzcAiZ0S54FmSH2rr8DFD-p_ccGKfRVR27S4M8RCSWTbsyLWm8fFVCSmHmE4GmMa2Dz4_9891pap4o3wWSzK0Gbu86_S7WMdQjyuGzLoDocPqEptH6i3GjYAB0s9H2Qy50xU14wRFIp7jMxTMCImgf7SSI9A296eAqRDYRnuLe91DMHroSswdzptypuq-SfcD_rYo1UWf_DN8B9ZukZvvIvW_udDIB_5GsdwQU6wLebT6EePNUmYG_BvQF8uOqQwslxHAg");'>
                            </div>
                            <h3 class="text-[#111318] dark:text-white text-base font-bold">Ayam</h3>
                        </a>
                        <a class="group flex flex-col gap-3 rounded-xl border border-[#dbdfe6] dark:border-[#2d3748] bg-white dark:bg-[#1a202c] p-4 items-center hover:border-primary transition-colors cursor-pointer"
                            href="#">
                            <div class="bg-center bg-no-repeat bg-cover rounded-full w-20 h-20 shrink-0 group-hover:scale-105 transition-transform"
                                data-alt="Fresh whole fish icon"
                                style='background-image: url("https://lh3.googleusercontent.com/aida-public/AB6AXuAAnUo5NQYt_ULyTdKlhOMf5E__z1f6Y2EXg5_YUGSL1-4aiYyV17sr2jtsmH3xu7D9IAg71TOgSaLNM9UjH2lBRPlFNZOa3uR7t8ySB0sM764FMWu4j9NAf4NWw4nF8LGxV699IzkqfpU3N-TCHEhge24Y4XOaYlJn4g_mZl8QxRjaV1bh4rVxWyZHw9hDl2be1cts6MnvKPz-veemNVTrsq-2jn5GRMjg19OwKr08rgZJbA90DpWKa-MYwcCHqAtYzA6eaCJWDGE");'>
                            </div>
                            <h3 class="text-[#111318] dark:text-white text-base font-bold">Ikan Segar</h3>
                        </a>
                        <a class="group flex flex-col gap-3 rounded-xl border border-[#dbdfe6] dark:border-[#2d3748] bg-white dark:bg-[#1a202c] p-4 items-center hover:border-primary transition-colors cursor-pointer"
                            href="#">
                            <div class="bg-center bg-no-repeat bg-cover rounded-full w-20 h-20 shrink-0 group-hover:scale-105 transition-transform"
                                data-alt="Frozen meat packaging icon"
                                style='background-image: url("https://lh3.googleusercontent.com/aida-public/AB6AXuBGI9Ig5_vO-qBUVf9ycfwmKJ61Q7lN5GT_uxpEH25-ohX3srnpTgGR7jeLpyVfUDElGIZ2fJ9fyZI-uJkOI-nWtRG58ShQnl6KY9RmVgwrQTRIJO-fLBxJi2sS2vhwqfMf4uObH_oCRhOPNLOt7VqzqCWasxYSZkMPtJfuLdAcroHuFKqA48qRkYUDs6cqt_nEVebqCbrGnnMeYbzqT6O8uSCQh-ihISEL6odtIDPAHJm_8g-AEJOVh-Wu-9C2jJ9dhsJSv3XM01k");'>
                            </div>
                            <h3 class="text-[#111318] dark:text-white text-base font-bold">Makanan Beku</h3>
                        </a>
                        <a class="group flex flex-col gap-3 rounded-xl border border-[#dbdfe6] dark:border-[#2d3748] bg-white dark:bg-[#1a202c] p-4 items-center hover:border-primary transition-colors cursor-pointer"
                            href="#">
                            <div class="bg-center bg-no-repeat bg-cover rounded-full w-20 h-20 shrink-0 group-hover:scale-105 transition-transform"
                                data-alt="Shrimp and seafood icon"
                                style='background-image: url("https://lh3.googleusercontent.com/aida-public/AB6AXuDmDUw7hqwFigqqquMPj9kID8_W09lt_EYvnEntQiV2QGWxJ0f11ZqcInyCxJY0d6-2JeB4OxEzc-afDczrCQm_NW449tU5IwErl3LS4LBMMwXdriWWg6RqQPyIIc_cqTv_RiuwXqPGATKWdjfHZlMdMJd_KsepTLZV2EjPP0okN4DIzbNZXquMAwTUw45E8HRrdI0Qo7LqXU4-14ERgpgZWV2Rmpd-7Lh6gPjOrTtcAArlW9qmAnMz80TjI-uoQgbkfunfCscl5l4");'>
                            </div>
                            <h3 class="text-[#111318] dark:text-white text-base font-bold">Makanan Laut</h3>
                        </a>
                    </div>
                </div>
                <!-- Best Sellers -->
                <div class="py-8">
                    <div class="flex items-center justify-between mb-6">
                        <h2
                            class="text-[#111318] dark:text-white text-[22px] font-bold leading-tight tracking-[-0.015em]">
                            Produk Populer</h2>
                        <a class="text-primary text-sm font-semibold hover:underline" href="<?= BASE_URL ?>public/market">Lihat
                            Semua</a>
                    </div>
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
                        <!-- Product 1 -->
                        <div
                            class="flex flex-col gap-3 rounded-xl border border-[#dbdfe6] dark:border-[#2d3748] bg-white dark:bg-[#1a202c] p-0 overflow-hidden group hover:shadow-lg transition-all">
                            <div class="w-full aspect-square bg-cover bg-center group-hover:scale-105 transition-transform duration-500"
                                data-alt="Fresh organic chicken breast on board"
                                style='background-image: url("<?= BASE_URL ?>assets/images/daging ayam.jpg");'>
                            </div>
                            <div class="p-4 flex flex-col gap-2">
                                <div class="flex justify-between items-start">
                                    <h3
                                        class="text-[#111318] dark:text-white text-base font-bold leading-tight line-clamp-1">
                                        Daging Ayam Segar</h3>
                                </div>
                                <p class="text-[#606e8a] dark:text-[#a0aec0] text-sm">Fresh Ayam</p>
                                <!-- <div class="flex items-center justify-between mt-2">
                                    <span class="text-[#111318] dark:text-white font-bold text-lg">$8.50 <span
                                            class="text-xs font-normal text-[#606e8a] dark:text-[#a0aec0]">/kg</span></span>
                                    <button
                                        class="size-8 rounded-full bg-primary/10 hover:bg-primary text-primary hover:text-white flex items-center justify-center transition-colors">
                                        <span class="material-symbols-outlined !text-[20px]">add</span>
                                    </button>
                                </div> -->
                            </div>
                        </div>
                        <!-- Product 2 -->
                        <div
                            class="flex flex-col gap-3 rounded-xl border border-[#dbdfe6] dark:border-[#2d3748] bg-white dark:bg-[#1a202c] p-0 overflow-hidden group hover:shadow-lg transition-all">
                            <div class="w-full aspect-square bg-cover bg-center group-hover:scale-105 transition-transform duration-500"
                                data-alt="Fresh salmon fillet on ice"
                                style='background-image: url("<?= BASE_URL ?>assets/images/pala-ayam.jpg");'>
                            </div>
                            <div class="p-4 flex flex-col gap-2">
                                <div class="flex justify-between items-start">
                                    <h3
                                        class="text-[#111318] dark:text-white text-base font-bold leading-tight line-clamp-1">
                                        Kepala Ayam</h3>
                                </div>
                                <p class="text-[#606e8a] dark:text-[#a0aec0] text-sm">Potongan Kepala Ayam</p>
                                <!-- <div class="flex items-center justify-between mt-2">
                                    <span class="text-[#111318] dark:text-white font-bold text-lg">$24.00 <span
                                            class="text-xs font-normal text-[#606e8a] dark:text-[#a0aec0]">/kg</span></span>
                                    <button
                                        class="size-8 rounded-full bg-primary/10 hover:bg-primary text-primary hover:text-white flex items-center justify-center transition-colors">
                                        <span class="material-symbols-outlined !text-[20px]">add</span>
                                    </button>
                                </div> -->
                            </div>
                        </div>
                        <!-- Product 3 -->
                        <div
                            class="flex flex-col gap-3 rounded-xl border border-[#dbdfe6] dark:border-[#2d3748] bg-white dark:bg-[#1a202c] p-0 overflow-hidden group hover:shadow-lg transition-all">
                            <div class="w-full aspect-square bg-cover bg-center group-hover:scale-105 transition-transform duration-500"
                                data-alt="Large tiger prawns" style='background-image: url("<?= BASE_URL ?>assets/images/tuna.jpg");'>
                            </div>
                            <div class="p-4 flex flex-col gap-2">
                                <div class="flex justify-between items-start">
                                    <h3
                                        class="text-[#111318] dark:text-white text-base font-bold leading-tight line-clamp-1">
                                        Ikan Tuna</h3>
                                </div>
                                <p class="text-[#606e8a] dark:text-[#a0aec0] text-sm">Fresh Tuna</p>
                                <!-- <div class="flex items-center justify-between mt-2">
                                    <span class="text-[#111318] dark:text-white font-bold text-lg">$18.90 <span
                                            class="text-xs font-normal text-[#606e8a] dark:text-[#a0aec0]">/kg</span></span>
                                    <button
                                        class="size-8 rounded-full bg-primary/10 hover:bg-primary text-primary hover:text-white flex items-center justify-center transition-colors">
                                        <span class="material-symbols-outlined !text-[20px]">add</span>
                                    </button>
                                </div> -->
                            </div>
                        </div>
                        <!-- Product 4 -->
                        <div
                            class="flex flex-col gap-3 rounded-xl border border-[#dbdfe6] dark:border-[#2d3748] bg-white dark:bg-[#1a202c] p-0 overflow-hidden group hover:shadow-lg transition-all">
                            <div class="w-full aspect-square bg-cover bg-center group-hover:scale-105 transition-transform duration-500"
                                data-alt="Raw beef cubes for stew"
                                style='background-image: url("<?= BASE_URL ?>assets/images/seafood.jpg");'>
                            </div>
                            <div class="p-4 flex flex-col gap-2">
                                <div class="flex justify-between items-start">
                                    <h3
                                        class="text-[#111318] dark:text-white text-base font-bold leading-tight line-clamp-1">
                                        Ikan Seafood Premium</h3>
                                </div>
                                <p class="text-[#606e8a] dark:text-[#a0aec0] text-sm">Premium Kualitas Ikan</p>
                                <!-- <div class="flex items-center justify-between mt-2">
                                    <span class="text-[#111318] dark:text-white font-bold text-lg">$15.50 <span
                                            class="text-xs font-normal text-[#606e8a] dark:text-[#a0aec0]">/kg</span></span>
                                    <button
                                        class="size-8 rounded-full bg-primary/10 hover:bg-primary text-primary hover:text-white flex items-center justify-center transition-colors">
                                        <span class="material-symbols-outlined !text-[20px]">add</span>
                                    </button>
                                            class="text-xs font-normal text-[#606e8a] dark:text-[#a0aec0">/kg</span></span>
                                    <button
                                        class="size-8 rounded-full bg-primary/10 hover:bg-primary text-primary hover:text-white flex items-center justify-center transition-colors">
                                        <span class="material-symbols-outlined !text-[20px]">add</span>
                                    </button>
                                </div> -->
                            </div>
                        </div>
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
                            <a href="<?= BASE_URL ?>public/market"
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
        <footer class="w-full border-t border-[#f0f1f5] dark:border-[#2d3748] bg-white dark:bg-[#1a202c] py-12">
            <div class="px-4 md:px-10 lg:px-40 flex justify-center">
                <div class="w-full max-w-[1200px] flex flex-col gap-10">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                        <div class="md:col-span-2 flex flex-col gap-4">
                            <div class="flex items-center gap-2 text-[#111318] dark:text-white">
                                <div class="size-8">
                                    <img src="<?= BASE_URL ?>assets/images/logo.jpeg" alt="Logo">
                                </div>
                                <span class="text-lg font-bold">Lapak Bangsawan</span>
                            </div>
                            <p class="text-[#606e8a] dark:text-[#a0aec0] text-sm max-w-xs">
                                Protein hewani kualitas premium yang diantar langsung ke depan pintu Anda
                                dengan jaminan kesegaran.
                            </p>
                        </div>
                        <div class="flex flex-col gap-4">
                            <h4 class="text-[#111318] dark:text-white font-bold">Belanja</h4>
                            <div class="flex flex-col gap-2 text-sm text-[#606e8a] dark:text-[#a0aec0]">
                                <a class="hover:text-primary transition-colors" href="#">Ayam</a>
                                <a class="hover:text-primary transition-colors" href="#">Ikan</a>
                                <a class="hover:text-primary transition-colors" href="#">Makanan Laut</a>
                                <a class="hover:text-primary transition-colors" href="#">Makanan Beku</a>
                            </div>
                        </div>

                        <div class="flex flex-col gap-4">
                            <h4 class="text-[#111318] dark:text-white font-bold">Kontak</h4>
                            <div class="flex flex-col gap-3 text-sm text-[#606e8a] dark:text-[#a0aec0]">
                                <div class="flex items-center gap-3">
                                    <span class="material-symbols-outlined text-[20px]">mail</span>
                                    <span>lapakbangsawan@gmail.com</span>
                                </div>
                                <a href="https://wa.me/62859110022099"
                                    class="flex items-center gap-3 hover:text-primary transition-colors">
                                    <span class="material-symbols-outlined text-[20px]">chat</span>
                                    <span>+62 859 110 022 099</span>
                                </a>
                                <div class="flex items-start gap-3">
                                    <span class="material-symbols-outlined text-[20px] mt-0.5">location_on</span>
                                    <span>Jl. Wanagati, Karyamulya, Kesambi, Kota Cirebon, Jawa Barat</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div
                        class="border-t border-[#f0f1f5] dark:border-[#2d3748] pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
                        <p class="text-sm text-[#606e8a] dark:text-[#a0aec0]">© 2025 Lapak Bangsawan. Hak cipta
                            dilindungi undang-undang.</p>
                        <div class="flex gap-4">
                            <a class="text-[#606e8a] dark:text-[#a0aec0] hover:text-primary transition-colors"
                                target="_blank" href="https://www.facebook.com/lapakbangsawan">
                                <!-- Facebook -->
                                <svg class="size-6 fill-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.469h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                                </svg>
                            </a>
                            <a class="text-[#606e8a] dark:text-[#a0aec0] hover:text-primary transition-colors"
                                target="_blank" href="https://instagram.com/lapakbangsawan">
                                <!-- Instagram -->
                                <svg class="size-6 fill-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M7.8 2h8.4C19.4 2 22 4.6 22 7.8v8.4a5.8 5.8 0 0 1-5.8 5.8H7.8C4.6 22 2 19.4 2 16.2V7.8A5.8 5.8 0 0 1 7.8 2m-.2 2A3.6 3.6 0 0 0 4 7.6v8.8C4 18.39 5.61 20 7.6 20h8.8a3.6 3.6 0 0 0 3.6-3.6V7.6C20 5.61 18.39 4 16.4 4H7.6m9.65 1.5a1.25 1.25 0 0 1 1.25 1.25A1.25 1.25 0 0 1 17.25 8 1.25 1.25 0 0 1 16 6.75a1.25 1.25 0 0 1 1.25-1.25M12 7a5 5 0 0 1 5 5 5 5 0 0 1-5 5 5 5 0 0 1-5-5 5 5 0 0 1 5-5m0 2a3 3 0 0 0-3 3 3 3 0 0 0 3 3 3 3 0 0 0 3-3 3 3 0 0 0-3-3z" />
                                </svg>
                            </a>
                            <a class="text-[#606e8a] dark:text-[#a0aec0] hover:text-primary transition-colors"
                                target="_blank" href="https://tiktok.com/lapakbangsawan">
                                <!-- TikTok -->
                                <svg class="size-6 fill-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M12.53.02C13.84 0 15.14.01 16.44 0c.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.65-1.58-1.02v6.1c0 4.64-5.27 8.49-10.05 6.36-2.61-1.14-4.23-4.14-3.72-7.06.67-4.05 5.56-6.17 9.17-3.96v4.3c-1.92-1.07-4.14-.15-4.57 2.07-.44 2.29 1.69 4.37 3.96 3.86 1.48-.34 2.45-1.83 2.37-3.35V.02z" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</body>

</html>