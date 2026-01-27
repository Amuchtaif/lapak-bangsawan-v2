<?php
require("../auth_session.php");
require_once dirname(dirname(__DIR__)) . "/config/init.php";

$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-t');

// 1. Revenue
$revenue_query = "SELECT SUM(total_amount) as total FROM orders WHERE status = 'completed' AND DATE(created_at) BETWEEN '$start_date' AND '$end_date'";
$revenue = $conn->query($revenue_query)->fetch_assoc()['total'] ?? 0;

// 2. COGS (HPP) with Fallback
$hpp_query = "SELECT SUM(oi.weight * COALESCE(NULLIF(oi.buy_price, 0), p.buy_price, 0)) as total 
              FROM order_items oi 
              JOIN products p ON oi.product_name = p.name 
              JOIN orders o ON oi.order_id = o.id 
              WHERE o.status = 'completed' AND DATE(o.created_at) BETWEEN '$start_date' AND '$end_date'";
$hpp = $conn->query($hpp_query)->fetch_assoc()['total'] ?? 0;

// 3. Gross Profit
$gross_profit = $revenue - $hpp;

// 4. Expenses
$expenses_query = "SELECT SUM(amount) as total FROM operational_expenses WHERE expense_date BETWEEN '$start_date' AND '$end_date'";
$total_expenses = $conn->query($expenses_query)->fetch_assoc()['total'] ?? 0;

// 5. Net Profit
$net_profit = $gross_profit - $total_expenses;

// Operational Breakdown
$breakdown_query = "SELECT category, SUM(amount) as total FROM operational_expenses WHERE expense_date BETWEEN '$start_date' AND '$end_date' GROUP BY category ORDER BY total DESC";
$breakdown_res = $conn->query($breakdown_query);
?>
<!DOCTYPE html>
<html class="light" lang="id">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Laporan Laba Rugi - Lapak Bangsawan</title>
    <link rel="icon" href="<?= BASE_URL ?>assets/images/favicon-laba.png" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap"
        rel="stylesheet" />
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet" />
    <script>
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
                    fontFamily: { "display": ["Inter", "sans-serif"] },
                },
            },
        }
    </script>
</head>

<body
    class="bg-background-light dark:bg-background-dark text-slate-600 dark:text-slate-300 font-display antialiased flex h-screen overflow-hidden">
    <?php include ROOT_PATH . "includes/admin/sidebar.php"; ?>
    <main class="flex-1 flex flex-col h-full relative overflow-hidden">
        <?php $page_title = "Laporan Laba Rugi";
        include ROOT_PATH . "includes/admin/header.php"; ?>

        <div class="flex-1 overflow-y-auto p-4 md:p-8 scroll-smooth">
            <div class="max-w-full mx-auto space-y-8">

                <!-- Filter Section -->
                <div
                    class="bg-white dark:bg-surface-dark p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
                    <form method="GET" class="flex flex-wrap items-end gap-4">
                        <div class="flex-1 min-w-[200px]">
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Tanggal Mulai</label>
                            <input type="date" name="start_date" value="<?= $start_date ?>"
                                class="w-full rounded-xl border-slate-200 bg-slate-50 dark:bg-slate-900 focus:ring-primary focus:border-primary">
                        </div>
                        <div class="flex-1 min-w-[200px]">
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Tanggal Akhir</label>
                            <input type="date" name="end_date" value="<?= $end_date ?>"
                                class="w-full rounded-xl border-slate-200 bg-slate-50 dark:bg-slate-900 focus:ring-primary focus:border-primary">
                        </div>
                        <button type="submit"
                            class="bg-primary text-white px-8 py-2.5 rounded-xl font-bold hover:bg-blue-600 transition-all flex items-center gap-2">
                            <span class="material-icons-round">filter_list</span>
                            Tampilkan
                        </button>
                    </form>
                </div>

                <!-- Scorecards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Revenue -->
                    <div class="bg-white dark:bg-surface-dark p-6 rounded-2xl border-l-4 border-blue-500 shadow-sm">
                        <p class="text-xs font-bold text-slate-500 uppercase mb-1">Total Omset</p>
                        <h3 class="text-2xl font-black text-slate-900 dark:text-white">Rp
                            <?= number_format($revenue, 0, ',', '.') ?>
                        </h3>
                    </div>
                    <!-- HPP -->
                    <div class="bg-white dark:bg-surface-dark p-6 rounded-2xl border-l-4 border-orange-500 shadow-sm">
                        <p class="text-xs font-bold text-slate-500 uppercase mb-1">Total HPP (Modal)</p>
                        <h3 class="text-2xl font-black text-slate-900 dark:text-white text-orange-600">Rp
                            <?= number_format($hpp, 0, ',', '.') ?>
                        </h3>
                    </div>
                    <!-- Expenses -->
                    <div class="bg-white dark:bg-surface-dark p-6 rounded-2xl border-l-4 border-red-500 shadow-sm">
                        <p class="text-xs font-bold text-slate-500 uppercase mb-1">Operasional</p>
                        <h3 class="text-2xl font-black text-slate-900 dark:text-white text-red-600">Rp
                            <?= number_format($total_expenses, 0, ',', '.') ?>
                        </h3>
                    </div>
                    <!-- Net Profit -->
                    <div
                        class="bg-white dark:bg-surface-dark p-6 rounded-2xl border-l-4 <?= $net_profit >= 0 ? 'border-green-500' : 'border-red-900' ?> shadow-lg">
                        <p class="text-xs font-bold text-slate-500 uppercase mb-1">Laba Bersih</p>
                        <h3 class="text-3xl font-black <?= $net_profit >= 0 ? 'text-green-600' : 'text-red-700' ?>">
                            Rp
                            <?= number_format($net_profit, 0, ',', '.') ?>
                        </h3>
                    </div>
                </div>

                <!-- Operational Breakdown -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div
                        class="lg:col-span-2 bg-white dark:bg-surface-dark rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                        <div
                            class="p-6 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center">
                            <h3 class="font-bold text-slate-900 dark:text-white">Rincian Biaya Operasional</h3>
                            <span
                                class="text-xs bg-slate-100 dark:bg-slate-800 px-3 py-1 rounded-full text-slate-500 font-bold uppercase">Breakdown</span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead class="bg-slate-50 dark:bg-slate-900 text-xs font-bold text-slate-500 uppercase">
                                    <tr>
                                        <th class="px-6 py-4">Kategori Pengeluaran</th>
                                        <th class="px-6 py-4 text-right">Total Nominal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                    <?php if ($breakdown_res->num_rows > 0): ?>
                                        <?php while ($row = $breakdown_res->fetch_assoc()): ?>
                                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                                                <td class="px-6 py-4 font-medium text-slate-700 dark:text-slate-300">
                                                    <?= htmlspecialchars($row['category']) ?>
                                                </td>
                                                <td class="px-6 py-4 text-right font-bold text-slate-900 dark:text-white">Rp
                                                    <?= number_format($row['total'], 0, ',', '.') ?>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="2" class="px-6 py-8 text-center text-slate-400 italic">Tidak ada
                                                pengeluaran di periode ini.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div
                        class="bg-primary/5 dark:bg-primary/10 rounded-2xl p-8 border border-primary/20 flex flex-col justify-center text-center">
                        <span class="material-icons-round text-5xl text-primary mb-4">insights</span>
                        <h4 class="font-bold text-slate-900 dark:text-white mb-2 text-lg">Analisa Performa</h4>
                        <p class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
                            <?php
                            if ($net_profit > 0 && $revenue > 0) {
                                $margin = ($net_profit / $revenue) * 100;
                                echo "Margin laba bersih Anda adalah <b>" . number_format($margin, 1) . "%</b>. Pertahankan efisiensi biaya operasional untuk hasil lebih maksimal.";
                            } elseif ($net_profit < 0) {
                                echo "Operasional Anda sedang mengalami defisit (Rugi). Tinjau kembali kategori pengeluaran terbesar untuk melakukan penghematan.";
                            } else {
                                echo "Belum ada data penjualan atau pengeluaran yang cukup untuk memberikan analisa performa.";
                            }
                            ?>
                        </p>
                    </div>
                </div>

            </div>
            <?php include ROOT_PATH . "includes/admin/footer.php"; ?>
        </div>
    </main>
</body>

</html>