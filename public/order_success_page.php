<?php require_once dirname(__DIR__) . '/config/init.php'; ?>
<!DOCTYPE html>

<html class="light" lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Order Success - Lapak Bangsawan</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect" />
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&amp;display=swap"
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
    <!-- Theme Config -->
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
                        "display": ["Inter", "sans-serif"]
                    },
                    borderRadius: { "DEFAULT": "0.25rem", "lg": "0.5rem", "xl": "0.75rem", "full": "9999px" },
                },
            },
        }
    </script>
    <style>
        /* Custom print styles */
        @media print {

            header,
            .no-print {
                display: none !important;
            }

            body {
                background: white;
            }

            .print-container {
                box-shadow: none;
                border: 1px solid #ccc;
            }
        }
    </style>
</head>

<body
    class="bg-background-light dark:bg-background-dark font-display text-slate-900 dark:text-white min-h-screen flex flex-col">
    <!-- Top Navigation -->
    <header class="sticky top-0 z-50 bg-white dark:bg-[#1a202c] border-b border-slate-200 dark:border-slate-800">
        <div class="max-w-[1280px] mx-auto px-4 md:px-10 h-16 flex items-center justify-between">
            <div class="flex items-center gap-8">
                <a class="flex items-center gap-3 text-slate-900 dark:text-white group" href="#">
                    <div class="size-8 text-primary">
                        <span class="material-symbols-outlined !text-[32px]">storefront</span>
                    </div>
                    <h2 class="text-lg font-bold leading-tight tracking-[-0.015em]">Lapak Bangsawan</h2>
                </a>
                <nav class="hidden md:flex items-center gap-6">
                    <a class="text-slate-900 dark:text-slate-200 text-sm font-medium hover:text-primary transition-colors"
                        href="#">Shop</a>
                    <a class="text-slate-900 dark:text-slate-200 text-sm font-medium hover:text-primary transition-colors"
                        href="#">About</a>
                    <a class="text-slate-900 dark:text-slate-200 text-sm font-medium hover:text-primary transition-colors"
                        href="#">Contact</a>
                </nav>
            </div>
            <div class="flex items-center gap-4">
                <button
                    class="hidden md:flex items-center justify-center rounded-lg size-10 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    <span class="material-symbols-outlined">search</span>
                </button>
                <button
                    class="flex items-center justify-center rounded-lg size-10 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors relative">
                    <span class="material-symbols-outlined">shopping_cart</span>
                    <span class="absolute top-2 right-2 size-2 bg-red-500 rounded-full"></span>
                </button>
                <div class="w-8 h-8 rounded-full bg-slate-200 dark:bg-slate-700 overflow-hidden ml-2">
                    <div class="w-full h-full bg-cover bg-center" data-alt="User profile picture"
                        style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuAPB0cddWx-kaC0_OplE1ldhABkXq9b5BPj0Tz4CUovVGYkizo5TYrDrBk8E8o5O-rAqUpdgZtQGidoBMDkbLqslH6MPhO7VFm08DNevk9uKUHSdJN9tDjUGCxDlyZr31rK090fPecY-7-6nVWgT30iBuhHx1iuRrBmYkWbrQUpmbKqjuF6WW0DaV1Kc83O6L-BaG6AQ3M8pEORToUHrh9LR2wDtqXyWOScjU2sc5L5Pi0Zoxez8yxtwJL_yrHLIH_7vmqAQbMMyfc');">
                    </div>
                </div>
            </div>
        </div>
    </header>
    <!-- Main Content -->
    <main class="flex-1 w-full max-w-[960px] mx-auto px-4 md:px-6 py-10 md:py-16 flex flex-col items-center">
        <!-- Success Animation/Hero -->
        <div class="flex flex-col items-center text-center mb-10">
            <div class="size-20 bg-primary/10 rounded-full flex items-center justify-center mb-6 text-primary">
                <span class="material-symbols-outlined text-[48px] font-bold">check_circle</span>
            </div>
            <h1 class="text-3xl md:text-4xl font-bold text-slate-900 dark:text-white tracking-tight mb-3">Order
                Confirmed!</h1>
            <p class="text-slate-500 dark:text-slate-400 max-w-md mx-auto text-base">
                Thank you, Budi. Your order has been received and is now being processed. We've sent a confirmation
                email to <span class="text-slate-900 dark:text-slate-200 font-medium">budi@example.com</span>.
            </p>
        </div>
        <!-- Order Receipt Card -->
        <div
            class="print-container w-full max-w-3xl bg-white dark:bg-[#1A202C] rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden mb-8">
            <!-- Card Header -->
            <div
                class="px-6 py-5 border-b border-slate-100 dark:border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-4 bg-slate-50/50 dark:bg-slate-800/30">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">
                        Order Number</p>
                    <p class="text-lg font-bold text-slate-900 dark:text-white font-mono">#LB-29382</p>
                </div>
                <div class="flex gap-3">
                    <button
                        class="no-print px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg shadow-sm hover:bg-slate-50 dark:hover:bg-slate-600 transition-colors flex items-center gap-2">
                        <span class="material-symbols-outlined text-lg">receipt</span>
                        Download Invoice
                    </button>
                    <button
                        class="no-print px-4 py-2 text-sm font-medium text-primary bg-primary/10 border border-transparent rounded-lg hover:bg-primary/20 transition-colors flex items-center gap-2">
                        <span class="material-symbols-outlined text-lg">local_shipping</span>
                        Track Order
                    </button>
                </div>
            </div>
            <!-- Items Table -->
            <div class="p-6">
                <h3 class="text-slate-900 dark:text-white text-lg font-bold mb-4">Items Ordered</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100 dark:border-slate-800">
                                <th
                                    class="py-3 pr-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-3/5">
                                    Item</th>
                                <th
                                    class="py-3 px-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-center w-1/5">
                                    Qty</th>
                                <th
                                    class="py-3 pl-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-right w-1/5">
                                    Price</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <!-- Item 1 -->
                            <tr class="group">
                                <td class="py-4 pr-4 align-top">
                                    <div class="flex gap-4">
                                        <div class="size-16 rounded-lg bg-slate-100 dark:bg-slate-700 flex-shrink-0 overflow-hidden bg-cover bg-center border border-slate-200 dark:border-slate-600"
                                            data-alt="Premium marbled wagyu beef slices on a dark plate"
                                            style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuC2YACqUZKWafmYHXba5KzMieHbd0sc2x-uDr3ozacyFGaBInvUHVhQ6PFxW5D1R8AOH-HQFneHDy4Ji3zWgt0lbus1m5hgtd20s9K5ET0kQZYZiMmkRTkw4whRvBUGExoUU5Fp3rpx2OKAfCKwfiowa4RU9CfYI2Xuj0th2Mfs4re9cqGUK5vztuk0FMFcNl9M7BU7cVm63MRbXZty0xBYlWOAdONJ7JI4P7GnMEWEipWRdKr7CB3nRI_6kSd4HRIVeswIjMLugog');">
                                        </div>
                                        <div>
                                            <p class="font-medium text-slate-900 dark:text-white line-clamp-1">Premium
                                                Wagyu Beef Slice</p>
                                            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">500g Pack •
                                                Grade A5</p>
                                        </div>
                                    </div>
                                </td>
                                <td
                                    class="py-4 px-4 text-center align-top text-slate-600 dark:text-slate-300 font-medium">
                                    2</td>
                                <td class="py-4 pl-4 text-right align-top text-slate-900 dark:text-white font-medium">
                                    $50.00</td>
                            </tr>
                            <!-- Item 2 -->
                            <tr class="group">
                                <td class="py-4 pr-4 align-top">
                                    <div class="flex gap-4">
                                        <div class="size-16 rounded-lg bg-slate-100 dark:bg-slate-700 flex-shrink-0 overflow-hidden bg-cover bg-center border border-slate-200 dark:border-slate-600"
                                            data-alt="Raw chicken breast on a wooden board"
                                            style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuBFVCesyD9iOQBuJFUKLcPiqxAvdjTls-mKBQilrs_4FTNsbCYPba1gK_jzkBl72rhX1-LJHYgArGU9mSpzVtV1AM-n7tKC-7MrD6Y-lmM4osMoBYIegdxbBqu4DvOB9Gb22J--jaijv92EtZkCAMSRtGble973HlWh3i6AkfR8DR-mVT6Oq2Gzki_ViAOtigm_8XntllpPy2dnZiTKXvb4nIDy3EoRdFZyCjrxwgB0XorKz7cTtDKJOEJON-0gsUS1kK4XOvN1KCc');">
                                        </div>
                                        <div>
                                            <p class="font-medium text-slate-900 dark:text-white line-clamp-1">Free
                                                Range Chicken Breast</p>
                                            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">1kg Pack •
                                                Organic</p>
                                        </div>
                                    </div>
                                </td>
                                <td
                                    class="py-4 px-4 text-center align-top text-slate-600 dark:text-slate-300 font-medium">
                                    1</td>
                                <td class="py-4 pl-4 text-right align-top text-slate-900 dark:text-white font-medium">
                                    $12.00</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- Cost Breakdown -->
            <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-800">
                <div class="flex flex-col gap-3 max-w-xs ml-auto w-full">
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-slate-600 dark:text-slate-400">Subtotal</span>
                        <span class="text-slate-900 dark:text-white font-medium">$62.00</span>
                    </div>
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-slate-600 dark:text-slate-400">Shipping</span>
                        <span class="text-green-600 dark:text-green-400 font-medium">Free</span>
                    </div>
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-slate-600 dark:text-slate-400">Tax</span>
                        <span class="text-slate-900 dark:text-white font-medium">$4.50</span>
                    </div>
                    <div class="h-px bg-slate-200 dark:bg-slate-700 my-1"></div>
                    <div class="flex justify-between items-center text-base font-bold">
                        <span class="text-slate-900 dark:text-white">Total</span>
                        <span class="text-primary text-xl">$66.50</span>
                    </div>
                </div>
            </div>
            <!-- Shipping & Details Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6 border-t border-slate-200 dark:border-slate-800">
                <div>
                    <div class="flex items-center gap-2 mb-3">
                        <span class="material-symbols-outlined text-slate-400">location_on</span>
                        <h4 class="text-sm font-bold uppercase tracking-wider text-slate-900 dark:text-white">Shipping
                            Address</h4>
                    </div>
                    <address class="not-italic text-sm text-slate-600 dark:text-slate-400 pl-8 leading-relaxed">
                        Budi Santoso<br />
                        123 Kuliner Street, Blok A4<br />
                        Jakarta Selatan, 12430<br />
                        Indonesia
                    </address>
                </div>
                <div>
                    <div class="flex items-center gap-2 mb-3">
                        <span class="material-symbols-outlined text-slate-400">schedule</span>
                        <h4 class="text-sm font-bold uppercase tracking-wider text-slate-900 dark:text-white">Estimated
                            Delivery</h4>
                    </div>
                    <p class="text-sm text-slate-600 dark:text-slate-400 pl-8 leading-relaxed">
                        Tomorrow, <span class="font-medium text-slate-900 dark:text-white">March 14th</span><br />
                        Between 10:00 AM - 02:00 PM
                    </p>
                </div>
            </div>
        </div>
        <!-- Action Buttons -->
        <div class="no-print flex flex-col sm:flex-row gap-4 w-full max-w-lg justify-center mb-12">
            <a class="flex-1 flex items-center justify-center h-12 px-6 rounded-lg bg-primary hover:bg-blue-700 text-white font-semibold transition-all shadow-md hover:shadow-lg focus:ring-4 focus:ring-primary/20"
                href="#">
                Back to Home
            </a>
            <a class="flex-1 flex items-center justify-center h-12 px-6 rounded-lg bg-white dark:bg-transparent border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-white font-semibold hover:bg-slate-50 dark:hover:bg-slate-800 transition-all focus:ring-4 focus:ring-slate-200 dark:focus:ring-slate-700"
                href="#">
                Continue Shopping
            </a>
        </div>
        <!-- Help Footer -->
        <div class="no-print text-center">
            <p class="text-sm text-slate-500 dark:text-slate-400">
                Questions about your order?
                <a class="text-primary hover:text-blue-700 hover:underline font-medium ml-1" href="#">Contact
                    Support</a>
            </p>
        </div>
    </main>
</body>

</html>