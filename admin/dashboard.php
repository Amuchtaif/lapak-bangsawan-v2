<?php
require("auth_session.php");
require("../config/database.php");
require("notification_logic.php");

// Revenue (Completed)
$revenue_query = "SELECT SUM(total_amount) as r FROM orders WHERE status='completed'";
$revenue = mysqli_fetch_assoc(mysqli_query($conn, $revenue_query))['r'] ?? 0;

// Active (Pending)
$pending_query = "SELECT COUNT(*) as c FROM orders WHERE status IN ('pending')";
$pending_orders = mysqli_fetch_assoc(mysqli_query($conn, $pending_query))['c'];

// Total Customers
$customer_query = "SELECT COUNT(DISTINCT customer_phone) as c FROM orders";
$total_customers = mysqli_fetch_assoc(mysqli_query($conn, $customer_query))['c'];

// Total Products
$prod_query = "SELECT COUNT(*) as c FROM products";
$total_products = mysqli_fetch_assoc(mysqli_query($conn, $prod_query))['c'];

// Recent Orders (with Pagination)
$recent_limit = 5;
$recent_page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$recent_start = ($recent_page > 1) ? ($recent_page * $recent_limit) - $recent_limit : 0;

// Filter Recent Orders
$recent_status_filter = isset($_GET['recent_status']) ? mysqli_real_escape_string($conn, $_GET['recent_status']) : '';
$where_clause = "";
if ($recent_status_filter) {
    if ($recent_status_filter !== 'all') {
        $where_clause = "WHERE status = '$recent_status_filter'";
    }
}

$total_orders_result = mysqli_query($conn, "SELECT COUNT(*) as count FROM orders $where_clause");
$total_orders_row = mysqli_fetch_array($total_orders_result);
$total_orders_count = $total_orders_row['count'];
$total_pages = ceil($total_orders_count / $recent_limit);

$query_recent = "SELECT * FROM orders $where_clause ORDER BY created_at DESC LIMIT $recent_start, $recent_limit";
$recent_orders = mysqli_query($conn, $query_recent);

// Monthly Revenue Analytics (Filterable)
$period = isset($_GET['period']) ? $_GET['period'] : 'this_year';
$revenue_data = [];
$labels = [];

if ($period == '7_days') {
    // Last 7 days
    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $q = "SELECT SUM(total_amount) as total FROM orders WHERE status='completed' AND DATE(created_at) = '$date'";
        $res = mysqli_fetch_assoc(mysqli_query($conn, $q));
        $revenue_data[$date] = $res['total'] ?? 0;
        $labels[] = date('d M', strtotime($date));
    }
} elseif ($period == '30_days') {
    // Last 30 days (grouped by roughly 3-day intervals or just simplified to last 30 daily points? 
    // UI fits 12 bars usually. Let's do last 12 days to fit UI or aggregate.
    // Let's simplified: Last 12 days for visual clarity or modify UI to scroll. 
    // Actually requirement says "30 days" filter. Let's do daily for last 30 days but only show chart bars if we adjust width or just 12 chunks.
    // To keep simple and "visual": let's stick to 12 data points. 
    // OR strictly follow "7 hari, 30 hari, tahun ini".
    // Let's do 30 days daily.
    for ($i = 29; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $q = "SELECT SUM(total_amount) as total FROM orders WHERE status='completed' AND DATE(created_at) = '$date'";
        $res = mysqli_fetch_assoc(mysqli_query($conn, $q));
        $revenue_data[$date] = $res['total'] ?? 0;
        // Label sparse: only every 5th day
        $labels[] = ($i % 5 == 0) ? date('d M', strtotime($date)) : '';
    }
} else {
    // This Year (Monthly)
    $revenue_chart_query = "SELECT MONTH(created_at) as m, SUM(total_amount) as total FROM orders WHERE status='completed' AND YEAR(created_at) = YEAR(CURDATE()) GROUP BY MONTH(created_at)";
    $revenue_chart_result = mysqli_query($conn, $revenue_chart_query);
    $monthly_revenue = array_fill(1, 12, 0);
    while ($row = mysqli_fetch_assoc($revenue_chart_result)) {
        $monthly_revenue[$row['m']] = $row['total'];
    }
    $revenue_data = $monthly_revenue;
    $labels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
}

$max_revenue = !empty($revenue_data) && max($revenue_data) > 0 ? max($revenue_data) : 1;

// New Products Count (Last 30 Days)
$new_prod_query = "SELECT COUNT(*) as c FROM products WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
$new_prod_count = mysqli_fetch_assoc(mysqli_query($conn, $new_prod_query))['c'];
$new_prod_percentage = ($total_products > 0) ? round(($new_prod_count / $total_products) * 100, 1) : 0;
?>
<!DOCTYPE html>
<html class="light" lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Dashboard Admin - Lapak Bangsawan</title>
    <link rel="icon" href="../assets/images/favicon-laba.png" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&amp;display=swap"
        rel="stylesheet" />
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet" />
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap"
        rel="stylesheet" />
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#0d59f2",
                        "background-light": "#f5f6f8",
                        "background-dark": "#101622",
                        "surface-light": "#ffffff",
                        "surface-dark": "#1e293b",
                    },
                    fontFamily: {
                        "display": ["Inter", "sans-serif"]
                    },
                    borderRadius: { "DEFAULT": "0.25rem", "lg": "0.5rem", "xl": "0.75rem", "2xl": "1rem", "full": "9999px" },
                },
            },
        }
    </script>
    <style>
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }

        .dark ::-webkit-scrollbar-thumb {
            background: #475569;
        }
    </style>
</head>

<body
    class="bg-background-light dark:bg-background-dark text-slate-600 dark:text-slate-300 font-display transition-colors duration-200 antialiased overflow-hidden h-screen flex">
    <?php include("sidebar.php"); ?>
    <main class="flex-1 flex flex-col h-full relative overflow-hidden">
        <?php $page_title = "Dashboard";
        include("header.php"); ?>
        <div class="flex-1 overflow-y-auto p-6 md:p-8 scroll-smooth">
            <div class="max-w-7xl mx-auto">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Dashboard</h1>
                        <p class="text-slate-500 dark:text-slate-400 mt-1">Pantau kinerja toko dan kelola tugas harian.
                        </p>
                    </div>
                    <div class="flex gap-3">
                        <!-- <button
                            class="px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors shadow-sm">
                            Download Report
                        </button> -->
                        <a href="products.php?action=add"
                            class="px-4 py-2 bg-primary hover:bg-blue-600 text-white rounded-lg text-sm font-medium transition-colors shadow-sm shadow-blue-500/30 flex items-center gap-2">
                            <span class="material-icons-round text-sm">add</span>
                            Tambah Produk
                        </a>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-10">
                    <div
                        class="bg-surface-light dark:bg-surface-dark p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm relative overflow-hidden group md:col-span-1 lg:col-span-1">
                        <div class="absolute right-0 top-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                            <span class="material-icons-round text-6xl text-primary">payments</span>
                        </div>
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Total Pendapatan</p>
                        <div class="mt-2 flex items-baseline gap-2">
                            <span class="text-3xl font-bold text-slate-900 dark:text-white">Rp
                                <?php echo number_format($revenue, 0, ',', '.'); ?></span>
                        </div>
                        <div class="mt-4 flex items-center text-sm">
                            <!-- <span class="text-green-500 font-medium flex items-center">
                                <span class="material-icons-round text-sm">trending_up</span>
                                12.5%
                            </span>
                            <span class="text-slate-400 ml-2">vs last month</span> -->
                        </div>
                    </div>
                    <div
                        class="bg-surface-light dark:bg-surface-dark p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm relative overflow-hidden group">
                        <div class="absolute right-0 top-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                            <span class="material-icons-round text-6xl text-amber-500">local_shipping</span>
                        </div>
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Pesanan Aktif</p>
                        <div class="mt-2 flex items-baseline gap-2">
                            <span
                                class="text-3xl font-bold text-slate-900 dark:text-white"><?php echo $pending_orders; ?></span>
                            <span
                                class="text-xs font-bold px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">Pending</span>
                        </div>
                        <div class="mt-4 flex items-center text-sm">
                            <!-- <span class="text-slate-500 dark:text-slate-400">4 awaiting shipment</span> -->
                        </div>
                    </div>
                    <div
                        class="bg-surface-light dark:bg-surface-dark p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm relative overflow-hidden group">
                        <div class="absolute right-0 top-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                            <span class="material-icons-round text-6xl text-blue-500">groups</span>
                        </div>
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Total Pelanggan</p>
                        <div class="mt-2 flex items-baseline gap-2">
                            <span
                                class="text-3xl font-bold text-slate-900 dark:text-white"><?php echo $total_customers; ?></span>
                            <span
                                class="text-xs font-bold px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">Aktif</span>
                        </div>
                        <div class="mt-4 flex items-center text-sm">
                            <a href="customers.php"
                                class="text-blue-500 font-medium hover:underline cursor-pointer">Lihat detail</a>
                        </div>
                    </div>
                    <div
                        class="bg-surface-light dark:bg-surface-dark p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm relative overflow-hidden group">
                        <div class="absolute right-0 top-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                            <span class="material-icons-round text-6xl text-purple-500">inventory_2</span>
                        </div>
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Total Produk</p>
                        <div class="mt-2 flex items-baseline gap-2">
                            <span
                                class="text-3xl font-bold text-slate-900 dark:text-white"><?php echo $total_products; ?></span>
                        </div>
                        <div class="mt-4 flex items-center text-sm">
                            <span class="text-green-500 font-medium flex items-center">
                                <span class="material-icons-round text-sm">trending_up</span>
                                <?php echo $new_prod_percentage; ?>%
                            </span>
                            <span class="text-slate-400 ml-2">produk baru</span>
                        </div>
                    </div>

                    <!-- Low Stock Card -->
                    <div
                        class="bg-surface-light dark:bg-surface-dark p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm relative overflow-hidden group">
                        <div class="absolute right-0 top-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                            <span class="material-icons-round text-6xl text-amber-500">warning</span>
                        </div>
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Stok Menipis</p>
                        <div class="mt-2 flex items-baseline gap-2">
                            <span
                                class="text-3xl font-bold text-slate-900 dark:text-white"><?php echo $low_stock_count; ?></span>
                            <?php if ($low_stock_count > 0): ?>
                                <span
                                    class="text-xs font-bold px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">Pantau
                                    Stok</span>
                            <?php endif; ?>
                        </div>
                        <div class="mt-4 flex items-center text-sm">
                            <a href="products.php"
                                class="text-amber-500 font-medium hover:underline cursor-pointer">Lihat
                                detail</a>
                            <span class="text-slate-400 ml-2">(1-5 items)</span>
                        </div>
                    </div>

                    <!-- Empty Stock Card -->
                    <div
                        class="bg-surface-light dark:bg-surface-dark p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm relative overflow-hidden group">
                        <div class="absolute right-0 top-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                            <span class="material-icons-round text-6xl text-red-500">report_problem</span>
                        </div>
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Stok Habis</p>
                        <div class="mt-2 flex items-baseline gap-2">
                            <span class="text-3xl font-bold text-slate-900 dark:text-white">
                                <?php echo isset($empty_stock_count) ? $empty_stock_count : 0; ?>
                            </span>
                            <?php if (isset($empty_stock_count) && $empty_stock_count > 0): ?>
                                <span
                                    class="text-xs font-bold px-2 py-0.5 rounded-full bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">Segera
                                    Restock</span>
                            <?php endif; ?>
                        </div>
                        <div class="mt-4 flex items-center text-sm">
                            <a href="products.php" class="text-red-500 font-medium hover:underline cursor-pointer">Lihat
                                detail</a>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-10">
                    <div
                        class="lg:col-span-4 bg-surface-light dark:bg-surface-dark p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-lg font-bold text-slate-900 dark:text-white">Analisis Pendapatan</h2>
                            <select onchange="window.location.href='?period='+this.value"
                                class="text-sm border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 rounded-lg text-slate-600 dark:text-slate-400 focus:ring-primary focus:border-primary">
                                <option value="this_year" <?php echo $period == 'this_year' ? 'selected' : ''; ?>>Tahun
                                    Ini
                                </option>
                                <option value="30_days" <?php echo $period == '30_days' ? 'selected' : ''; ?>>30 Hari
                                    Terakhir
                                </option>
                                <option value="7_days" <?php echo $period == '7_days' ? 'selected' : ''; ?>>7 Hari
                                    Terakhir
                                </option>
                            </select>
                        </div>
                        <div class="relative h-64 w-full flex items-end justify-between gap-2 pt-8">
                            <div
                                class="absolute inset-0 flex flex-col justify-between text-xs text-slate-400 pointer-events-none pb-6">
                                <div class="border-b border-slate-100 dark:border-slate-700/50 w-full h-0"></div>
                                <div class="border-b border-slate-100 dark:border-slate-700/50 w-full h-0"></div>
                                <div class="border-b border-slate-100 dark:border-slate-700/50 w-full h-0"></div>
                                <div class="border-b border-slate-100 dark:border-slate-700/50 w-full h-0"></div>
                                <div class="border-b border-slate-100 dark:border-slate-700/50 w-full h-0"></div>
                            </div>
                            <?php
                            // Calculate chart bars
                            
                            // We use $revenue_data array which is Key=>Value
                            // Since array keys might be date strings or month numbers, we iterate carefully.
                            // For display, we just iterate $revenue_data as $key => $val. 
                            // But wait, $revenue_data is index based for month? 
                            
                            // Fix for iteration:
                            // If period is 'this_year', it is 1..12 keys.
                            // If periods are days, it is date string keys.
                            
                            // We need distinct visual mapping.
                            // Let's just iterate $revenue_data.
                            
                            // NOTE: To ensure bar width is correct we might need to know count.
                            // Tailwind w-full in flex container handles distribution? No, flex justify-between handles spacing.
                            // The bars need width.
                            
                            foreach ($revenue_data as $key => $total):
                                $height_percentage = ($max_revenue > 0) ? ($total / $max_revenue) * 100 : 0;
                                // Ensure at least a sliver is shown if 0, or just 0
                                $height_percentage = $height_percentage == 0 ? 0 : max($height_percentage, 5);
                                $formatted_total = ($total >= 1000) ? number_format($total / 1000, 1) . 'k' : $total;

                                // Tooltip Value
                                $tooltip_val = number_format($total, 0, ',', '.');
                                ?>
                                <div style="height: <?php echo $height_percentage; ?>%"
                                    class="w-full bg-primary/20 rounded-t-sm relative group cursor-pointer hover:bg-primary transition-all duration-300">
                                    <div
                                        class="hidden group-hover:block absolute -top-8 left-1/2 -translate-x-1/2 bg-slate-900 text-white text-xs py-1 px-2 rounded shadow-lg z-10 whitespace-nowrap">
                                        Rp <?php echo number_format($total, 0, ',', '.'); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="flex justify-between text-xs text-slate-400 mt-2">
                            <?php foreach ($labels as $lbl): ?>
                                <span class="w-full text-center truncate"><?php echo $lbl; ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <div
                    class="bg-surface-light dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                    <div
                        class="px-6 py-5 border-b border-slate-200 dark:border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <h2 class="text-lg font-bold text-slate-900 dark:text-white">Pesanan Terbaru</h2>
                        <div class="flex gap-2">
                            <select onchange="window.location.href='?recent_status='+this.value"
                                class="px-7 py-1.5 text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors focus:ring-primary focus:border-primary">
                                <option value="all">Semua Status</option>
                                <option value="pending" <?php echo (isset($_GET['recent_status']) && $_GET['recent_status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                                <option value="completed" <?php echo (isset($_GET['recent_status']) && $_GET['recent_status'] == 'completed') ? 'selected' : ''; ?>>Selesai</option>
                                <option value="cancelled" <?php echo (isset($_GET['recent_status']) && $_GET['recent_status'] == 'cancelled') ? 'selected' : ''; ?>>Batal</option>
                            </select>

                            <select onchange="if(this.value) window.open(this.value, '_blank')"
                                class="px-6 py-1.5 text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors focus:ring-primary focus:border-primary">
                                <option value="">Export</option>
                                <option value="export_dashboard.php?type=excel">Excel (CSV)</option>
                                <option value="export_dashboard.php?type=pdf">Print / PDF</option>
                            </select>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-slate-500 dark:text-slate-400">
                            <thead
                                class="bg-slate-50 dark:bg-slate-800/50 text-xs uppercase font-semibold text-slate-500">
                                <tr>
                                    <th class="px-6 py-4">ID Pesanan</th>
                                    <th class="px-6 py-4">Pelanggan</th>
                                    <th class="px-6 py-4">Produk</th>
                                    <th class="px-6 py-4">Tanggal</th>
                                    <th class="px-6 py-4">Total</th>
                                    <th class="px-6 py-4">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                                <?php if (mysqli_num_rows($recent_orders) > 0): ?>
                                    <?php while ($row = mysqli_fetch_assoc($recent_orders)):
                                        $status_color = 'bg-slate-100 text-slate-800';
                                        if ($row['status'] == 'completed')
                                            $status_color = 'bg-green-100 text-green-800';
                                        if ($row['status'] == 'pending')
                                            $status_color = 'bg-amber-100 text-amber-800';
                                        if ($row['status'] == 'cancelled')
                                            $status_color = 'bg-red-100 text-red-800';
                                        ?>
                                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors group">
                                            <td class="px-6 py-4 font-medium text-slate-900 dark:text-white">
                                                #<?php echo $row['id']; ?></td>
                                            <td class="px-6 py-4">
                                                <div class="flex items-center gap-3">
                                                    <div
                                                        class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold text-xs uppercase">
                                                        <?php echo substr($row['customer_name'], 0, 2); ?>
                                                    </div>
                                                    <span
                                                        class="font-medium text-slate-700 dark:text-slate-200"><?php echo htmlspecialchars($row['customer_name']); ?></span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 truncate max-w-xs text-xs text-slate-500">
                                                (Lihat detail)
                                            </td>
                                            <td class="px-6 py-4"><?php echo date('M d, Y', strtotime($row['created_at'])); ?>
                                            </td>
                                            <td class="px-6 py-4 font-medium text-slate-900 dark:text-white">Rp
                                                <?php echo number_format($row['total_amount'], 0, ',', '.'); ?>
                                            </td>
                                            <td class="px-6 py-4">
                                                <span
                                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium <?php echo $status_color; ?> dark:bg-opacity-30">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                                    <?php echo ucfirst($row['status']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="px-6 py-4 text-center text-slate-500">Tidak ada pesanan
                                            ditemukan.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div
                        class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50 flex justify-between items-center">
                        <span class="text-xs text-slate-500">Menampilkan <?php echo $recent_start + 1; ?> -
                            <?php echo min($recent_start + $recent_limit, $total_orders_count); ?> dari
                            <?php echo $total_orders_count; ?> entri</span>
                        <div class="flex gap-2">
                            <?php if ($recent_page > 1): ?>
                                <a href="?page=<?php echo $recent_page - 1; ?><?php echo $recent_status_filter ? '&recent_status=' . $recent_status_filter : ''; ?><?php echo $period ? '&period=' . $period : ''; ?>"
                                    class="px-3 py-1 text-xs border border-slate-200 dark:border-slate-700 rounded bg-white dark:bg-slate-800 text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">Sebelumnya</a>
                            <?php else: ?>
                                <button
                                    class="px-3 py-1 text-xs border border-slate-200 dark:border-slate-700 rounded bg-white dark:bg-slate-800 text-slate-500 disabled:opacity-50"
                                    disabled>Sebelumnya</button>
                            <?php endif; ?>

                            <?php if ($recent_page < $total_pages): ?>
                                <a href="?page=<?php echo $recent_page + 1; ?><?php echo $recent_status_filter ? '&recent_status=' . $recent_status_filter : ''; ?><?php echo $period ? '&period=' . $period : ''; ?>"
                                    class="px-3 py-1 text-xs border border-slate-200 dark:border-slate-700 rounded bg-white dark:bg-slate-800 text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">Selanjutnya</a>
                            <?php else: ?>
                                <button
                                    class="px-3 py-1 text-xs border border-slate-200 dark:border-slate-700 rounded bg-white dark:bg-slate-800 text-slate-500 disabled:opacity-50"
                                    disabled>Selanjutnya</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php include("footer.php"); ?>
            </div>
        </div>
    </main>
</body>

</html>