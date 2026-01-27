<?php
$current_page = basename($_SERVER['PHP_SELF']);
$username = $_SESSION['username'] ?? 'Admin User';
?>
<aside id="admin-sidebar"
    class="fixed inset-y-0 left-0 z-50 w-64 bg-surface-light dark:bg-surface-dark border-r border-slate-200 dark:border-slate-800 flex flex-col h-full transform -translate-x-full lg:translate-x-0 lg:static lg:flex transition-transform duration-300 ease-in-out">
    <a href="dashboard" class="h-16 flex items-center px-6 border-b border-slate-100 dark:border-slate-800">
        <div class="flex items-center gap-2 font-bold text-xl text-slate-800 dark:text-white">
            <div class="w-8 h-8 rounded-lg bg-primary flex items-center justify-center text-white overflow-hidden">
                <img src="<?= BASE_URL ?>assets/images/logo.jpeg" alt="Logo" class="w-full h-full object-cover">
            </div>
            <span>Lapak<span class="text-primary">Bangsawan</span></span>
        </div>
    </a>
    <style>
        /* Hide scrollbar for Chrome, Safari and Opera */
        #admin-sidebar-nav::-webkit-scrollbar {
            display: none;
        }

        /* Hide scrollbar for IE, Edge and Firefox */
        #admin-sidebar-nav {
            -ms-overflow-style: none;
            /* IE and Edge */
            scrollbar-width: none;
            /* Firefox */
        }
    </style>
    <nav id="admin-sidebar-nav" class="flex-1 overflow-y-auto py-6 px-3 space-y-1">
        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg <?php echo $current_page == 'dashboard.php' ? 'bg-primary/10 text-primary font-medium' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700/50 hover:text-slate-900 dark:hover:text-white transition-colors'; ?>"
            href="<?= BASE_URL ?>admin/dashboard">
            <span class="material-icons-round">dashboard</span>
            Dashboard
        </a>
        <div class="pt-4 pb-2 px-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Inventaris</div>
        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg <?php echo $current_page == 'products.php' ? 'bg-primary/10 text-primary font-medium' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700/50 hover:text-slate-900 dark:hover:text-white transition-colors'; ?>"
            href="<?= BASE_URL ?>admin/products">
            <span class="material-icons-round">inventory_2</span>
            Produk
        </a>
        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg <?php echo $current_page == 'categories.php' ? 'bg-primary/10 text-primary font-medium' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700/50 hover:text-slate-900 dark:hover:text-white transition-colors'; ?>"
            href="<?= BASE_URL ?>admin/categories">
            <span class="material-icons-round">category</span>
            Kategori
        </a>
        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg <?php echo $current_page == 'form_input_target.php' ? 'bg-primary/10 text-primary font-medium' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700/50 hover:text-slate-900 dark:hover:text-white transition-colors'; ?>"
            href="<?= BASE_URL ?>admin/form_input_target">
            <span class="material-icons-round">edit_calendar</span>
            Input Target Harian
        </a>

        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg <?php echo strpos($_SERVER['PHP_SELF'], 'inventory/adjust.php') !== false ? 'bg-primary/10 text-primary font-medium' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700/50 hover:text-slate-900 dark:hover:text-white transition-colors'; ?>"
            href="<?= BASE_URL ?>admin/inventory/adjust">
            <span class="material-icons-round">rule</span>
            Penyesuaian Stok
        </a>
        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg <?php echo $current_page == 'expenses.php' ? 'bg-primary/10 text-primary font-medium' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700/50 hover:text-slate-900 dark:hover:text-white transition-colors'; ?>"
            href="<?= BASE_URL ?>admin/expenses">
            <span class="material-icons-round">payments</span>
            Biaya Operasional
        </a>

        <a class="flex items-center justify-between px-3 py-2.5 rounded-lg <?php echo ($current_page == 'orders.php' || $current_page == 'manual_transaction.php') ? 'bg-primary/10 text-primary font-medium' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700/50 hover:text-slate-900 dark:hover:text-white transition-colors'; ?>"
            href="<?= BASE_URL ?>admin/orders">
            <div class="flex items-center gap-3">
                <span class="material-icons-round">shopping_bag</span>
                Pesanan
            </div>
        </a>
        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg <?php echo $current_page == 'customers.php' ? 'bg-primary/10 text-primary font-medium' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700/50 hover:text-slate-900 dark:hover:text-white transition-colors'; ?>"
            href="<?= BASE_URL ?>admin/customers">
            <span class="material-icons-round">people</span>
            Pelanggan
        </a>
        <div class="pt-4 pb-2 px-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Laporan</div>
        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg <?php echo $current_page == 'report_stock.php' ? 'bg-primary/10 text-primary font-medium' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700/50 hover:text-slate-900 dark:hover:text-white transition-colors'; ?>"
            href="<?= BASE_URL ?>admin/report_stock">
            <span class="material-icons-round">analytics</span>
            Laporan Stok
        </a>
        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg <?php echo strpos($_SERVER['PHP_SELF'], 'reports/profit_loss.php') !== false ? 'bg-primary/10 text-primary font-medium' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700/50 hover:text-slate-900 dark:hover:text-white transition-colors'; ?>"
            href="<?= BASE_URL ?>admin/reports/profit_loss">
            <span class="material-icons-round">monetization_on</span>
            Laporan Laba Rugi
        </a>
        <div class="pt-4 pb-2 px-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Manajemen</div>
        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg <?php echo $current_page == 'wholesale_rules.php' ? 'bg-primary/10 text-primary font-medium' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700/50 hover:text-slate-900 dark:hover:text-white transition-colors'; ?>"
            href="<?= BASE_URL ?>admin/wholesale_rules">
            <span class="material-icons-round">sell</span>
            Aturan Grosir
        </a>
        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg <?php echo $current_page == 'messages.php' ? 'bg-primary/10 text-primary font-medium' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700/50 hover:text-slate-900 dark:hover:text-white transition-colors'; ?>"
            href="<?= BASE_URL ?>admin/messages">
            <span class="material-icons-round">mail</span>
            Pesan Masuk
        </a>
        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg <?php echo $current_page == 'settings.php' ? 'bg-primary/10 text-primary font-medium' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700/50 hover:text-slate-900 dark:hover:text-white transition-colors'; ?>"
            href="<?= BASE_URL ?>admin/settings">
            <span class="material-icons-round">settings</span>
            Pengaturan
        </a>
    </nav>
    <div class="p-4 border-t border-slate-200 dark:border-slate-800">
        <div
            class="flex items-center gap-3 p-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors cursor-pointer">
            <img alt="Profile picture of current admin user"
                class="w-10 h-10 rounded-full object-cover border-2 border-slate-100 dark:border-slate-700"
                data-alt="Admin user profile picture showing a smiling professional"
                src="<?= BASE_URL ?>assets/images/profile.png" />
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-slate-900 dark:text-white truncate">
                    <?php
                    // Fetch User Details if possible, otherwise use session
                    // We'll rely on session or query if needed, but for now let's assume session username
                    // If we want full_name we should update auth_session.php or login.php to store it, OR fetch it here.
                    // For simplicity and robustness, let's fetch it if $conn is available (which it usually is in admin pages)
                    if (isset($conn) && isset($_SESSION['user_id'])) {
                        $uid = $_SESSION['user_id'];
                        $u_res = $conn->query("SELECT full_name FROM users WHERE id=$uid");
                        if ($u_res && $u_row = $u_res->fetch_assoc()) {
                            echo htmlspecialchars($u_row['full_name'] ? $u_row['full_name'] : $username);
                        } else {
                            echo htmlspecialchars($username);
                        }
                    } else {
                        echo htmlspecialchars($username);
                    }
                    ?>
                </p>
                <p class="text-xs text-slate-500 truncate">Manajer Toko</p>
            </div>
            <a href="<?= BASE_URL ?>admin/logout"
                class="material-icons-round text-slate-400 hover:text-red-500 transition-colors">logout</a>
        </div>
    </div>
</aside>