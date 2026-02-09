<?php require_once __DIR__ . "/notification_logic.php"; ?>
<header
    class="h-16 flex items-center justify-between px-6 bg-surface-light/80 dark:bg-surface-dark/80 backdrop-blur-md border-b border-slate-200 dark:border-slate-800 z-[100] sticky top-0">
    <div class="flex items-center gap-4 lg:hidden">
        <button id="sidebar-toggle"
            class="p-2 -ml-2 rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700">
            <span class="material-icons-round">menu</span>
        </button>
    </div>
    <div class="hidden lg:flex items-center text-sm breadcrumbs text-slate-500">
        <span class="material-icons-round text-lg mr-2">home</span>
        <span class="mx-2">/</span>
        <span class="font-medium text-slate-900 dark:text-white">
            <?php echo isset($page_title) ? $page_title : 'Dashboard'; ?>
        </span>
    </div>
    <div class="flex items-center gap-4">
        <!-- Search Bar -->
        <div class="hidden md:flex relative group">
            <span
                class="absolute left-3 top-1/2 -translate-y-1/2 material-icons-round text-slate-400 text-lg group-focus-within:text-primary transition-colors">search</span>
            <form action="products" method="GET">
                <input
                    class="pl-10 pr-4 py-2 w-64 bg-slate-100 dark:bg-slate-800 border-none rounded-lg text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-primary/50 transition-all"
                    placeholder="Cari produk..." type="text" name="search"
                    value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" />
            </form>
        </div>

        <!-- Notification Dropdown -->
        <div id="notification-container" class="relative">
            <button onclick="toggleNotifications()"
                class="relative p-2 rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors focus:outline-none">
                <span class="material-icons-round">notifications</span>
                <?php
                $total_notif = (isset($empty_stock_count) ? $empty_stock_count : 0) + (isset($low_stock_count) ? $low_stock_count : 0) + (isset($unread_msg_count) ? $unread_msg_count : 0);
                if ($total_notif > 0):
                    ?>
                    <span
                        class="absolute top-2 right-2 w-2 h-2 rounded-full bg-red-500 border-2 border-white dark:border-slate-900 animate-pulse"></span>
                <?php endif; ?>
            </button>

            <!-- Popup Content -->
            <div id="notification-popup"
                class="hidden absolute right-0 mt-2 w-80 bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 z-50 overflow-hidden transform transition-all duration-200 ease-out origin-top-right opacity-0 scale-95">
                <div
                    class="p-3 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center bg-slate-50/50 dark:bg-slate-900/50">
                    <h3 class="font-bold text-sm text-slate-900 dark:text-white">Notifikasi</h3>
                    <?php if ($total_notif > 0): ?>
                        <span
                            class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400">
                            <?php echo $total_notif; ?> Baru
                        </span>
                    <?php endif; ?>
                </div>

                <div class="max-h-[300px] overflow-y-auto">
                    <?php if ($total_notif > 0): ?>

                        <!-- Empty Stock Section -->
                        <?php if (isset($empty_stock_count) && $empty_stock_count > 0): ?>
                            <div
                                class="px-4 py-2 text-xs font-semibold text-red-500 uppercase tracking-wider bg-red-50/50 dark:bg-red-900/10">
                                Stok Habis
                            </div>
                            <?php while ($item = mysqli_fetch_assoc($empty_stock_items_result)): ?>
                                <a href="products?search=<?php echo urlencode($item['name']); ?>"
                                    class="block px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors border-b border-slate-100 dark:border-slate-800/50 last:border-0">
                                    <div class="flex gap-3">
                                        <div class="size-10 rounded-lg bg-slate-100 dark:bg-slate-700 shrink-0 overflow-hidden">
                                            <?php if ($item['image']): ?>
                                                <img src="../<?php echo htmlspecialchars($item['image']); ?>" alt=""
                                                    class="w-full h-full object-cover">
                                            <?php else: ?>
                                                <span
                                                    class="material-icons-round text-slate-400 text-lg flex items-center justify-center w-full h-full">image</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-slate-900 dark:text-white truncate">
                                                <?php echo htmlspecialchars($item['name']); ?>
                                            </p>
                                            <p class="text-xs text-red-600 font-bold mt-0.5">TERJUAL HABIS</p>
                                        </div>
                                    </div>
                                </a>
                            <?php endwhile; ?>
                        <?php endif; ?>

                        <!-- Low Stock Section -->
                        <?php if (isset($low_stock_count) && $low_stock_count > 0): ?>
                            <div
                                class="px-4 py-2 text-xs font-semibold text-amber-500 uppercase tracking-wider bg-amber-50/50 dark:bg-amber-900/10 <?php echo $empty_stock_count > 0 ? 'border-t border-slate-100 dark:border-slate-700' : ''; ?>">
                                Stok Menipis
                            </div>
                            <?php while ($item = mysqli_fetch_assoc($low_stock_items_result)): ?>
                                <a href="products?search=<?php echo urlencode($item['name']); ?>"
                                    class="block px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors border-b border-slate-100 dark:border-slate-800/50 last:border-0">
                                    <div class="flex gap-3">
                                        <div class="size-10 rounded-lg bg-slate-100 dark:bg-slate-700 shrink-0 overflow-hidden">
                                            <?php if ($item['image']): ?>
                                                <img src="../<?php echo htmlspecialchars($item['image']); ?>" alt=""
                                                    class="w-full h-full object-cover">
                                            <?php else: ?>
                                                <span
                                                    class="material-icons-round text-slate-400 text-lg w-full h-full flex items-center justify-center">image</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-slate-900 dark:text-white truncate">
                                                <?php echo htmlspecialchars($item['name']); ?>
                                            </p>
                                            <p class="text-xs text-amber-600 font-medium mt-0.5">Sisa stok:
                                                <?php echo $item['stock']; ?>
                                            </p>
                                        </div>
                                    </div>
                                </a>
                            <?php endwhile; ?>
                        <?php endif; ?>

                        <!-- Unread Messages Section -->
                        <?php if (isset($unread_msg_count) && $unread_msg_count > 0): ?>
                            <div
                                class="px-4 py-2 text-xs font-semibold text-slate-500 uppercase tracking-wider bg-slate-50 dark:bg-slate-900/30 border-t border-slate-100 dark:border-slate-700">
                                Pesan Baru
                            </div>
                            <?php while ($msg = mysqli_fetch_assoc($unread_messages_result)): ?>
                                <a href="messages"
                                    class="block px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors border-b border-slate-100 dark:border-slate-800/50 last:border-0">
                                    <div class="flex flex-col gap-1">
                                        <div class="flex justify-between items-start">
                                            <p class="text-sm font-medium text-slate-900 dark:text-white truncate max-w-[180px]">
                                                <?php echo htmlspecialchars($msg['name']); ?>
                                            </p>
                                            <span class="text-[10px] text-slate-400 whitespace-nowrap">
                                                <?php echo date('d M, H:i', strtotime($msg['created_at'])); ?>
                                            </span>
                                        </div>
                                        <p class="text-xs text-slate-500 dark:text-slate-400 line-clamp-1">
                                            <?php echo htmlspecialchars($msg['message']); ?>
                                        </p>
                                    </div>
                                </a>
                            <?php endwhile; ?>
                        <?php endif; ?>

                    <?php else: ?>
                        <div class="p-8 text-center text-slate-500 dark:text-slate-400">
                            <span class="material-icons-round text-4xl text-slate-300 mb-2">notifications_off</span>
                            <p class="text-sm">Tidak ada notifikasi baru</p>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if ($total_notif > 0): ?>
                    <div
                        class="p-2 border-t border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/50 grid grid-cols-2 divide-x divide-slate-200 dark:divide-slate-700">
                        <a href="products?filter=low_stock"
                            class="block w-full text-center py-2 text-xs font-medium text-primary hover:text-blue-700 transition-colors">
                            Cek Stok
                        </a>
                        <a href="messages"
                            class="block w-full text-center py-2 text-xs font-medium text-primary hover:text-blue-700 transition-colors">
                            Lihat Pesan
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>
<script>
    function toggleNotifications() {
        const popup = document.getElementById('notification-popup');
        if (popup.classList.contains('hidden')) {
            // Show
            popup.classList.remove('hidden');
            // Small delay to allow transition to happen after display block
            requestAnimationFrame(() => {
                popup.classList.remove('opacity-0', 'scale-95');
                popup.classList.add('opacity-100', 'scale-100');
            });
        } else {
            // Hide
            popup.classList.remove('opacity-100', 'scale-100');
            popup.classList.add('opacity-0', 'scale-95');
            setTimeout(() => {
                popup.classList.add('hidden');
            }, 200); // Match transition duration
        }
    }

    // Close when clicking outside
    document.addEventListener('click', function (event) {
        const container = document.getElementById('notification-container');
        const isClickInside = container.contains(event.target);
        const popup = document.getElementById('notification-popup');

        if (!isClickInside && !popup.classList.contains('hidden')) {
            popup.classList.remove('opacity-100', 'scale-100');
            popup.classList.add('opacity-0', 'scale-95');
            setTimeout(() => {
                popup.classList.add('hidden');
            }, 200);
        }
    });
</script>