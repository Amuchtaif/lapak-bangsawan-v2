<?php
require("auth_session.php");
require_once dirname(__DIR__) . "/config/init.php";

// Set Date (Default to Today)
$date_param = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$selected_date = mysqli_real_escape_string($conn, $date_param);

// Fetch Daily Sales Report
$sql = "
SELECT 
    oi.product_name,
    SUM(oi.weight) as total_qty,
    SUM(oi.subtotal) as total_sales
FROM order_items oi
JOIN orders o ON oi.order_id = o.id
WHERE DATE(o.created_at) = '$selected_date'
  AND o.status = 'completed'
GROUP BY oi.product_name
ORDER BY total_sales DESC
";

$result = $conn->query($sql);

$report_data = [];
$total_day_sales = 0;
$total_day_qty = 0;

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $report_data[] = $row;
        $total_day_sales += $row['total_sales'];
        $total_day_qty += $row['total_qty'];
    }
}

// Fetch Total Orders for the day
$sql_orders = "SELECT COUNT(*) as total_orders FROM orders WHERE DATE(created_at) = '$selected_date' AND status = 'completed'";
$total_orders = $conn->query($sql_orders)->fetch_assoc()['total_orders'] ?? 0;
?>
<!DOCTYPE html>
<html class="light" lang="id">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Laporan Harian - Admin Lapak Bangsawan</title>
    <link rel="icon" href="<?= BASE_URL ?>assets/images/favicon-laba.png" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap"
        rel="stylesheet" />
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet" />
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
                },
            },
        }
    </script>
</head>

<body
    class="bg-background-light dark:bg-background-dark text-slate-600 dark:text-slate-300 font-display transition-colors duration-200 antialiased overflow-hidden h-screen flex">

    <?php include ROOT_PATH . "includes/admin/sidebar.php"; ?>

    <main class="flex-1 flex flex-col h-full relative overflow-hidden">
        <?php $page_title = "Laporan Penjualan Harian";
        include ROOT_PATH . "includes/admin/header.php"; ?>

        <div class="flex-1 overflow-y-auto p-6 md:p-8 scroll-smooth">
            <div class="max-w-full mx-auto">

                <!-- Filter Header -->
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Laporan Penjualan Harian</h1>
                        <p class="text-slate-500 dark:text-slate-400 mt-1">Laporan detail penjualan produk per hari.</p>
                        <p class="text-xs text-primary mt-1 font-bold italic bg-primary/10 px-2 py-1 rounded inline-block">
                            <?php echo format_date_id($selected_date); ?>
                        </p>
                    </div>
                    <div class="flex items-center gap-3">
                        <form action="" method="GET" class="flex items-center gap-2">
                            <div class="relative">
                                <span
                                    class="absolute left-3 top-1/2 -translate-y-1/2 material-icons-round text-slate-400 text-sm">calendar_today</span>
                                <input type="date" name="date" value="<?php echo $date_param; ?>"
                                    class="pl-9 pr-4 py-2 text-sm bg-white dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary shadow-sm text-slate-700 dark:text-white"
                                    onchange="this.form.submit()">
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <!-- Card 1: Total Sales Revenue -->
                    <div class="bg-gradient-to-br from-primary to-blue-700 text-white p-6 rounded-2xl shadow-lg relative overflow-hidden group hover:shadow-primary/30 transition-all duration-300">
                        <div class="relative z-10">
                            <p class="text-blue-100/80 text-xs font-bold uppercase tracking-wider">Total Penjualan</p>
                            <h3 class="text-3xl font-black mt-2 tracking-tight">Rp <?php echo number_format($total_day_sales, 0, ',', '.'); ?></h3>
                        </div>
                        <span class="material-icons-round absolute -right-4 -bottom-4 text-8xl text-white opacity-10 group-hover:scale-110 transition-transform duration-500">payments</span>
                    </div>

                    <!-- Card 2: Total Quantity Sold -->
                    <div class="bg-surface-light dark:bg-surface-dark p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm relative overflow-hidden group hover:shadow-md transition-all">
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Berat Terjual</p>
                        <h3 class="text-3xl font-black text-slate-900 dark:text-white mt-2 tracking-tight"><?php echo number_format($total_day_qty, 1, ',', '.'); ?> <span class="text-xs font-bold text-slate-400">kg</span></h3>
                        <span class="material-icons-round absolute -right-4 -bottom-4 text-8xl text-slate-100 dark:text-slate-800/40 group-hover:scale-110 transition-transform duration-500">scale</span>
                    </div>

                    <!-- Card 3: Total Products Sold -->
                    <div class="bg-surface-light dark:bg-surface-dark p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm relative overflow-hidden group hover:shadow-md transition-all">
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Jenis Produk</p>
                        <h3 class="text-3xl font-black text-slate-900 dark:text-white mt-2 tracking-tight"><?php echo count($report_data); ?></h3>
                        <span class="material-icons-round absolute -right-4 -bottom-4 text-8xl text-slate-100 dark:text-slate-800/40 group-hover:scale-110 transition-transform duration-500">category</span>
                    </div>

                    <!-- Card 4: Total Orders -->
                    <div class="bg-surface-light dark:bg-surface-dark p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm relative overflow-hidden group hover:shadow-md transition-all">
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Pesanan</p>
                        <h3 class="text-3xl font-black text-slate-900 dark:text-white mt-2 tracking-tight"><?php echo number_format($total_orders); ?></h3>
                        <span class="material-icons-round absolute -right-4 -bottom-4 text-8xl text-slate-100 dark:text-slate-800/40 group-hover:scale-110 transition-transform duration-500">shopping_bag</span>
                    </div>
                </div>

                <!-- Report Table -->
                <div class="bg-surface-light dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center bg-slate-50/50 dark:bg-slate-800/50">
                        <h2 class="text-lg font-bold text-slate-900 dark:text-white">Rincian Penjualan Produk</h2>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-slate-500 dark:text-slate-400">
                            <thead class="bg-slate-50 dark:bg-slate-800/50 text-xs uppercase font-semibold text-slate-500">
                                <tr>
                                    <th class="px-6 py-4">Nama Produk</th>
                                    <th class="px-6 py-4 text-center">Total Berat (kg)</th>
                                    <th class="px-6 py-4 text-right">Total Penjualan (Rp)</th>
                                    <th class="px-6 py-4 text-right">Rata-rata Harga / kg</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                                <?php if (empty($report_data)): ?>
                                    <tr>
                                        <td colspan="4" class="px-6 py-12 text-center">
                                            <span class="material-icons-round text-4xl text-slate-300 mb-2">find_in_page</span>
                                            <p>Tidak ada data penjualan untuk tanggal ini.</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($report_data as $row): ?>
                                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                                            <td class="px-6 py-4 font-medium text-slate-900 dark:text-white">
                                                <?php echo htmlspecialchars($row['product_name']); ?>
                                            </td>
                                            <td class="px-6 py-4 text-center font-bold">
                                                <?php echo number_format($row['total_qty'], 1, ',', '.'); ?>
                                            </td>
                                            <td class="px-6 py-4 text-right font-black text-primary">
                                                Rp <?php echo number_format($row['total_sales'], 0, ',', '.'); ?>
                                            </td>
                                            <td class="px-6 py-4 text-right text-slate-400">
                                                Rp <?php echo number_format($row['total_sales'] / $row['total_qty'], 0, ',', '.'); ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                            <?php if (!empty($report_data)): ?>
                                <tfoot class="bg-slate-50 dark:bg-slate-800/50 font-bold text-slate-900 dark:text-white">
                                    <tr>
                                        <td class="px-6 py-4 uppercase">Total Keseluruhan</td>
                                        <td class="px-6 py-4 text-center"><?php echo number_format($total_day_qty, 1, ',', '.'); ?> kg</td>
                                        <td class="px-6 py-4 text-right">Rp <?php echo number_format($total_day_sales, 0, ',', '.'); ?></td>
                                        <td class="px-6 py-4"></td>
                                    </tr>
                                </tfoot>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>

                <?php include ROOT_PATH . "includes/admin/footer.php"; ?>
            </div>
        </div>
    </main>
</body>

</html>
