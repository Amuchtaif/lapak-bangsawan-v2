<?php
require_once dirname(__DIR__) . "/config/init.php";
?>
<!DOCTYPE html>
<html class="light" lang="id">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Lapak Bangsawan - Terima Kasih</title>
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
    class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-white font-display antialiased flex flex-col min-h-screen items-center justify-center">

    <main class="w-full max-w-md mx-auto px-4 text-center">
        <div class="bg-white dark:bg-card-dark rounded-2xl shadow-lg border border-slate-200 dark:border-slate-800 p-8">
            <div
                class="size-20 bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 rounded-full flex items-center justify-center mx-auto mb-6">
                <span class="material-symbols-outlined text-4xl">check_circle</span>
            </div>
            <h1 class="text-2xl font-bold mb-4">Terima Kasih!</h1>
            <p class="text-slate-500 dark:text-slate-400 mb-8">
                Pesan Anda telah kami terima. Kami akan terus memperbaiki layanan dan produk kami.
            </p>
            <div class="flex flex-col gap-3">
                <a href="<?= BASE_URL ?>public/home"
                    class="w-full bg-primary hover:bg-blue-600 text-white font-bold py-3 px-6 rounded-lg transition-colors shadow-lg shadow-blue-500/30">
                    Kembali ke Beranda
                </a>
                <a href="<?= BASE_URL ?>public/market"
                    class="w-full bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold py-3 px-6 rounded-lg transition-colors">
                    Lanjut Belanja
                </a>
            </div>
        </div>
    </main>

</body>

</html>