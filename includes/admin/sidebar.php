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
        
        /* Premium Accent Highlight for Active Link */
        .sidebar-active-item {
            background-color: rgb(13 89 242 / 0.1) !important;
            color: #0d59f2 !important;
            font-weight: 700 !important;
            border-left: 4px solid #0d59f2 !important;
            border-top-left-radius: 0px !important;
            border-bottom-left-radius: 0px !important;
            padding-left: 0.5rem !important;
            transition: all 0.2s ease;
        }
        .dark .sidebar-active-item {
            background-color: rgb(13 89 242 / 0.2) !important;
            color: #3b82f6 !important;
            border-left-color: #3b82f6 !important;
        }
    </style>
    <nav id="admin-sidebar-nav" class="flex-1 overflow-y-auto py-6 px-3 space-y-1">
        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg <?php echo $current_page == 'dashboard.php' ? 'sidebar-active-item' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700/50 hover:text-slate-900 dark:hover:text-white transition-colors'; ?>"
            href="<?= BASE_URL ?>admin/dashboard">
            <span class="material-icons-round">dashboard</span>
            Dashboard
        </a>
        <div class="pt-4 pb-2 px-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Inventaris</div>
        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg <?php echo $current_page == 'products.php' ? 'sidebar-active-item' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700/50 hover:text-slate-900 dark:hover:text-white transition-colors'; ?>"
            href="<?= BASE_URL ?>admin/products">
            <span class="material-icons-round">inventory_2</span>
            Produk
        </a>
        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg <?php echo $current_page == 'categories.php' ? 'sidebar-active-item' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700/50 hover:text-slate-900 dark:hover:text-white transition-colors'; ?>"
            href="<?= BASE_URL ?>admin/categories">
            <span class="material-icons-round">category</span>
            Kategori
        </a>
        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg <?php echo $current_page == 'partners.php' ? 'sidebar-active-item' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700/50 hover:text-slate-900 dark:hover:text-white transition-colors'; ?>"
            href="<?= BASE_URL ?>admin/partners">
            <span class="material-icons-round">handshake</span>
            Mitra Laba
        </a>
        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg <?php echo $current_page == 'form_input_target.php' ? 'sidebar-active-item' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700/50 hover:text-slate-900 dark:hover:text-white transition-colors'; ?>"
            href="<?= BASE_URL ?>admin/form_input_target">
            <span class="material-icons-round">edit_calendar</span>
            Input Target
        </a>

        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg <?php echo strpos($_SERVER['PHP_SELF'], 'inventory/adjust.php') !== false ? 'sidebar-active-item' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700/50 hover:text-slate-900 dark:hover:text-white transition-colors'; ?>"
            href="<?= BASE_URL ?>admin/inventory/adjust">
            <span class="material-icons-round">rule</span>
            Penyesuaian Stok
        </a>
        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg <?php echo $current_page == 'expenses.php' ? 'sidebar-active-item' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700/50 hover:text-slate-900 dark:hover:text-white transition-colors'; ?>"
            href="<?= BASE_URL ?>admin/expenses">
            <span class="material-icons-round">payments</span>
            Biaya Operasional
        </a>

        <a class="flex items-center justify-between px-3 py-2.5 rounded-lg <?php echo ($current_page == 'orders.php' || $current_page == 'manual_transaction.php') ? 'sidebar-active-item' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700/50 hover:text-slate-900 dark:hover:text-white transition-colors'; ?>"
            href="<?= BASE_URL ?>admin/orders">
            <div class="flex items-center gap-3">
                <span class="material-icons-round">shopping_bag</span>
                Pesanan
            </div>
        </a>
        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg <?php echo $current_page == 'customers.php' ? 'sidebar-active-item' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700/50 hover:text-slate-900 dark:hover:text-white transition-colors'; ?>"
            href="<?= BASE_URL ?>admin/customers">
            <span class="material-icons-round">people</span>
            Pelanggan
        </a>
        <div class="pt-4 pb-2 px-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Laporan</div>
        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg <?php echo $current_page == 'report_stock.php' ? 'sidebar-active-item' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700/50 hover:text-slate-900 dark:hover:text-white transition-colors'; ?>"
            href="<?= BASE_URL ?>admin/report_stock">
            <span class="material-icons-round">analytics</span>
            Laporan Stok
        </a>
        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg <?php echo strpos($_SERVER['PHP_SELF'], 'reports/profit_loss.php') !== false ? 'sidebar-active-item' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700/50 hover:text-slate-900 dark:hover:text-white transition-colors'; ?>"
            href="<?= BASE_URL ?>admin/reports/profit_loss">
            <span class="material-icons-round">monetization_on</span>
            Laporan Laba Rugi
        </a>
        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg <?php echo strpos($_SERVER['PHP_SELF'], 'reports/consignment_report.php') !== false ? 'sidebar-active-item' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700/50 hover:text-slate-900 dark:hover:text-white transition-colors'; ?>"
            href="<?= BASE_URL ?>admin/reports/consignment_report">
            <span class="material-icons-round">assignment_ind</span>
            Laporan Mitra Laba
        </a>
        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg <?php echo $current_page == 'daily_report.php' ? 'sidebar-active-item' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700/50 hover:text-slate-900 dark:hover:text-white transition-colors'; ?>"
            href="<?= BASE_URL ?>admin/daily_report">
            <span class="material-icons-round">today</span>
            Laporan Harian
        </a>
        <div class="pt-4 pb-2 px-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Manajemen</div>
        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg <?php echo $current_page == 'wholesale_rules.php' ? 'sidebar-active-item' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700/50 hover:text-slate-900 dark:hover:text-white transition-colors'; ?>"
            href="<?= BASE_URL ?>admin/wholesale_rules">
            <span class="material-icons-round">sell</span>
            Aturan Grosir
        </a>
        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg <?php echo $current_page == 'messages.php' ? 'sidebar-active-item' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700/50 hover:text-slate-900 dark:hover:text-white transition-colors'; ?>"
            href="<?= BASE_URL ?>admin/messages">
            <span class="material-icons-round">mail</span>
            Pesan Masuk
        </a>
        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg <?php echo $current_page == 'settings.php' ? 'sidebar-active-item' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700/50 hover:text-slate-900 dark:hover:text-white transition-colors'; ?>"
            href="<?= BASE_URL ?>admin/settings">
            <span class="material-icons-round">settings</span>
            Pengaturan
        </a>
        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg <?php echo $current_page == 'shipping_settings.php' ? 'sidebar-active-item' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700/50 hover:text-slate-900 dark:hover:text-white transition-colors'; ?>"
            href="<?= BASE_URL ?>admin/shipping_settings">
            <span class="material-icons-round">local_shipping</span>
            Pengaturan Kurir
        </a>
        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg <?php echo strpos($_SERVER['PHP_SELF'], 'landingpage') !== false ? 'sidebar-active-item' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700/50 hover:text-slate-900 dark:hover:text-white transition-colors'; ?>"
            href="<?= BASE_URL ?>admin/landingpage">
            <span class="material-icons-round">web</span>
            Kelola Landing Page
        </a>
        <a class="flex items-center gap-3 px-3 py-2.5 rounded-lg <?php echo $current_page == 'activity_logs.php' ? 'sidebar-active-item' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700/50 hover:text-slate-900 dark:hover:text-white transition-colors'; ?>"
            href="<?= BASE_URL ?>admin/activity_logs">
            <span class="material-icons-round">history</span>
            Log Aktivitas
        </a>
    </nav>
    <div class="p-4 border-t border-slate-200 dark:border-slate-800">
        <div
            class="flex items-center gap-3 p-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors cursor-pointer">
            <div class="relative">
                <div
                    class="w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-800 border-2 border-primary/20 flex items-center justify-center text-primary shadow-sm overflow-hidden">
                    <span class="material-icons-round text-2xl">account_circle</span>
                </div>
                <div
                    class="absolute bottom-0 right-0 w-3.5 h-3.5 bg-green-500 border-2 border-white dark:border-surface-dark rounded-full shadow-sm">
                </div>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-slate-900 dark:text-white truncate">
                    <?php
                    // Fetch User Details if possible, otherwise use session
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

<script>
    // Automatic Smooth Scroll to Active Sidebar Link on Page Load
    window.addEventListener('DOMContentLoaded', () => {
        setTimeout(() => {
            const activeSidebarItem = document.querySelector('.sidebar-active-item');
            const sidebarNav = document.getElementById('admin-sidebar-nav');
            if (activeSidebarItem && sidebarNav) {
                // Get active item offset top relative to nav container
                const navRect = sidebarNav.getBoundingClientRect();
                const activeRect = activeSidebarItem.getBoundingClientRect();
                
                // Calculate the scroll position to center the active item inside nav
                const scrollPos = sidebarNav.scrollTop + (activeRect.top - navRect.top) - (navRect.height / 2) + (activeRect.height / 2);
                
                sidebarNav.scrollTo({
                    top: scrollPos,
                    behavior: 'smooth'
                });
            }
        }, 100); // 100ms timeout for stable DOM layout paint
    });
</script>