<?php
require("../auth_session.php");
require_once dirname(dirname(__DIR__)) . "/config/init.php";

$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-t');

// Sanitize date inputs
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $start_date)) $start_date = date('Y-m-01');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $end_date)) $end_date = date('Y-m-t');

// ============================================================
// 1. Product Revenue (Omset Produk) — from order_items subtotal
//    This excludes shipping and is the TRUE product revenue.
// ============================================================
$stmt = $conn->prepare("SELECT COALESCE(SUM(oi.subtotal), 0) as total 
                         FROM order_items oi 
                         JOIN orders o ON oi.order_id = o.id 
                         WHERE o.status = 'completed' 
                         AND DATE(o.created_at) BETWEEN ? AND ?");
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$product_revenue = $stmt->get_result()->fetch_assoc()['total'];

// ============================================================
// 2. Shipping Income (Pendapatan Ongkir)
// ============================================================
$stmt = $conn->prepare("SELECT COALESCE(SUM(COALESCE(shipping_cost, 0)), 0) as total 
                         FROM orders 
                         WHERE status = 'completed' 
                         AND DATE(created_at) BETWEEN ? AND ?");
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$shipping_income = $stmt->get_result()->fetch_assoc()['total'];

// ============================================================
// 3. Total Discounts (Diskon yang diberikan)
//    manual_discount from manual transactions
// ============================================================
$stmt = $conn->prepare("SELECT COALESCE(SUM(COALESCE(manual_discount, 0)), 0) as total 
                         FROM orders 
                         WHERE status = 'completed' 
                         AND DATE(created_at) BETWEEN ? AND ?");
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$total_discount = $stmt->get_result()->fetch_assoc()['total'];

// ============================================================
// 4. Gross Revenue = total_amount (what customer actually paid)
//    For cross-validation: should equal product_revenue - discount + shipping
// ============================================================
$stmt = $conn->prepare("SELECT COALESCE(SUM(total_amount), 0) as total 
                         FROM orders 
                         WHERE status = 'completed' 
                         AND DATE(created_at) BETWEEN ? AND ?");
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$gross_revenue = $stmt->get_result()->fetch_assoc()['total'];

// ============================================================
// 5. COGS / HPP (Harga Pokok Penjualan) with fallback
// ============================================================
$stmt = $conn->prepare("SELECT COALESCE(SUM(oi.weight * COALESCE(NULLIF(oi.buy_price, 0), p.buy_price, 0)), 0) as total 
                         FROM order_items oi 
                         LEFT JOIN products p ON oi.product_name = p.name 
                         JOIN orders o ON oi.order_id = o.id 
                         WHERE o.status = 'completed' 
                         AND DATE(o.created_at) BETWEEN ? AND ?");
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$hpp = $stmt->get_result()->fetch_assoc()['total'];

// ============================================================
// 6. Calculations
// ============================================================
// Laba Kotor = Omset Produk - HPP (excluding shipping as it's not product margin)
$gross_profit = $product_revenue - $hpp;

// 7. Operational Expenses
$stmt = $conn->prepare("SELECT COALESCE(SUM(amount), 0) as total 
                         FROM operational_expenses 
                         WHERE expense_date BETWEEN ? AND ?");
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$total_expenses = $stmt->get_result()->fetch_assoc()['total'];

// 8. Net Profit = Laba Kotor + Ongkir - Biaya Operasional
$net_profit = $gross_profit + $shipping_income - $total_expenses;

// 9. Operational Breakdown
$stmt = $conn->prepare("SELECT category, SUM(amount) as total 
                         FROM operational_expenses 
                         WHERE expense_date BETWEEN ? AND ? 
                         GROUP BY category 
                         ORDER BY total DESC");
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$breakdown_res = $stmt->get_result();

// 10. Order Count
$stmt = $conn->prepare("SELECT COUNT(*) as total 
                         FROM orders 
                         WHERE status = 'completed' 
                         AND DATE(created_at) BETWEEN ? AND ?");
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$order_count = $stmt->get_result()->fetch_assoc()['total'];

// 11. Top Selling Products
$stmt = $conn->prepare("SELECT oi.product_name, SUM(oi.weight) as total_weight, SUM(oi.subtotal) as total_revenue,
                         SUM(oi.weight * COALESCE(NULLIF(oi.buy_price, 0), p.buy_price, 0)) as total_hpp
                         FROM order_items oi
                         LEFT JOIN products p ON oi.product_name = p.name
                         JOIN orders o ON oi.order_id = o.id
                         WHERE o.status = 'completed'
                         AND DATE(o.created_at) BETWEEN ? AND ?
                         GROUP BY oi.product_name
                         ORDER BY total_revenue DESC
                         LIMIT 10");
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$top_products_res = $stmt->get_result();
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
                            <input type="date" name="start_date" value="<?= htmlspecialchars($start_date) ?>"
                                class="w-full rounded-xl border-slate-200 bg-slate-50 dark:bg-slate-900 focus:ring-primary focus:border-primary">
                        </div>
                        <div class="flex-1 min-w-[200px]">
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Tanggal Akhir</label>
                            <input type="date" name="end_date" value="<?= htmlspecialchars($end_date) ?>"
                                class="w-full rounded-xl border-slate-200 bg-slate-50 dark:bg-slate-900 focus:ring-primary focus:border-primary">
                        </div>
                        <button type="submit"
                            class="bg-primary text-white px-8 py-2.5 rounded-xl font-bold hover:bg-blue-600 transition-all flex items-center gap-2">
                            <span class="material-icons-round">filter_list</span>
                            Tampilkan
                        </button>
                    </form>
                </div>

                <!-- ============================================ -->
                <!-- REVENUE BREAKDOWN (Rincian Pendapatan) -->
                <!-- ============================================ -->
                <div
                    class="bg-white dark:bg-surface-dark rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                    <div
                        class="p-6 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center">
                        <h3 class="font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <span class="material-icons-round text-blue-500">receipt_long</span>
                            Rincian Pendapatan
                        </h3>
                        <span
                            class="text-xs bg-blue-50 dark:bg-blue-900/30 px-3 py-1 rounded-full text-blue-600 dark:text-blue-400 font-bold">
                            <?= $order_count ?> Pesanan Selesai
                        </span>
                    </div>
                    <div class="p-6">
                        <table class="w-full text-sm">
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                <tr>
                                    <td class="py-3 text-slate-600 dark:text-slate-400">
                                        <span class="flex items-center gap-2">
                                            <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                            Omset Produk (Penjualan Bersih)
                                        </span>
                                    </td>
                                    <td class="py-3 text-right font-bold text-slate-900 dark:text-white">
                                        Rp <?= number_format($product_revenue, 0, ',', '.') ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="py-3 text-slate-600 dark:text-slate-400">
                                        <span class="flex items-center gap-2">
                                            <span class="w-2 h-2 rounded-full bg-cyan-500"></span>
                                            Pendapatan Ongkir
                                        </span>
                                    </td>
                                    <td class="py-3 text-right font-medium text-cyan-600">
                                        + Rp <?= number_format($shipping_income, 0, ',', '.') ?>
                                    </td>
                                </tr>
                                <?php if ($total_discount > 0): ?>
                                    <tr>
                                        <td class="py-3 text-slate-600 dark:text-slate-400">
                                            <span class="flex items-center gap-2">
                                                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                                Total Diskon Diberikan
                                            </span>
                                        </td>
                                        <td class="py-3 text-right font-medium text-amber-600">
                                            - Rp <?= number_format($total_discount, 0, ',', '.') ?>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                                <tr class="border-t-2 border-slate-200 dark:border-slate-700">
                                    <td class="py-3 font-bold text-slate-900 dark:text-white">
                                        Total Diterima (Gross Revenue)
                                    </td>
                                    <td class="py-3 text-right font-black text-lg text-slate-900 dark:text-white">
                                        Rp <?= number_format($gross_revenue, 0, ',', '.') ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- ============================================ -->
                <!-- SCORECARDS -->
                <!-- ============================================ -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Omset Produk -->
                    <div class="bg-white dark:bg-surface-dark p-6 rounded-2xl border-l-4 border-blue-500 shadow-sm">
                        <p class="text-xs font-bold text-slate-500 uppercase mb-1">Omset Produk</p>
                        <h3 class="text-2xl font-black text-slate-900 dark:text-white">Rp
                            <?= number_format($product_revenue, 0, ',', '.') ?>
                        </h3>
                        <p class="text-xs text-slate-400 mt-1">Tanpa ongkir & diskon</p>
                    </div>
                    <!-- HPP -->
                    <div class="bg-white dark:bg-surface-dark p-6 rounded-2xl border-l-4 border-orange-500 shadow-sm">
                        <p class="text-xs font-bold text-slate-500 uppercase mb-1">Total HPP (Modal)</p>
                        <h3 class="text-2xl font-black text-orange-600">Rp
                            <?= number_format($hpp, 0, ',', '.') ?>
                        </h3>
                        <p class="text-xs text-slate-400 mt-1">Harga pokok penjualan</p>
                    </div>
                    <!-- Laba Kotor -->
                    <div
                        class="bg-white dark:bg-surface-dark p-6 rounded-2xl border-l-4 <?= $gross_profit >= 0 ? 'border-emerald-500' : 'border-red-500' ?> shadow-sm">
                        <p class="text-xs font-bold text-slate-500 uppercase mb-1">Laba Kotor</p>
                        <h3
                            class="text-2xl font-black <?= $gross_profit >= 0 ? 'text-emerald-600' : 'text-red-600' ?>">
                            Rp <?= number_format($gross_profit, 0, ',', '.') ?>
                        </h3>
                        <p class="text-xs text-slate-400 mt-1">Omset - HPP</p>
                    </div>
                    <!-- Laba Bersih -->
                    <div
                        class="bg-white dark:bg-surface-dark p-6 rounded-2xl border-l-4 <?= $net_profit >= 0 ? 'border-green-500' : 'border-red-900' ?> shadow-lg">
                        <p class="text-xs font-bold text-slate-500 uppercase mb-1">Laba Bersih</p>
                        <h3 class="text-3xl font-black <?= $net_profit >= 0 ? 'text-green-600' : 'text-red-700' ?>">
                            Rp <?= number_format($net_profit, 0, ',', '.') ?>
                        </h3>
                        <p class="text-xs text-slate-400 mt-1">Laba Kotor + Ongkir - Operasional</p>
                    </div>
                </div>

                <!-- ============================================ -->
                <!-- LAPORAN LABA RUGI FORMAL -->
                <!-- ============================================ -->
                <div
                    class="bg-white dark:bg-surface-dark rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                    <div
                        class="p-6 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center">
                        <h3 class="font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <span class="material-icons-round text-primary">summarize</span>
                            Laporan Laba Rugi
                        </h3>
                        <span
                            class="text-xs bg-slate-100 dark:bg-slate-800 px-3 py-1 rounded-full text-slate-500 font-bold uppercase">
                            <?= date('d M Y', strtotime($start_date)) ?> — <?= date('d M Y', strtotime($end_date)) ?>
                        </span>
                    </div>
                    <div class="p-6">
                        <table class="w-full text-sm">
                            <tbody>
                                <!-- PENDAPATAN -->
                                <tr>
                                    <td colspan="2"
                                        class="pt-2 pb-1 font-bold text-xs uppercase text-slate-500 tracking-wider">
                                        Pendapatan</td>
                                </tr>
                                <tr>
                                    <td class="py-2 pl-4 text-slate-600 dark:text-slate-400">Penjualan Produk</td>
                                    <td class="py-2 text-right font-medium text-slate-900 dark:text-white">Rp
                                        <?= number_format($product_revenue, 0, ',', '.') ?></td>
                                </tr>
                                <tr>
                                    <td class="py-2 pl-4 text-slate-600 dark:text-slate-400">Pendapatan Ongkir</td>
                                    <td class="py-2 text-right font-medium text-slate-900 dark:text-white">Rp
                                        <?= number_format($shipping_income, 0, ',', '.') ?></td>
                                </tr>
                                <?php if ($total_discount > 0): ?>
                                    <tr>
                                        <td class="py-2 pl-4 text-amber-600">Diskon Penjualan</td>
                                        <td class="py-2 text-right font-medium text-amber-600">(Rp
                                            <?= number_format($total_discount, 0, ',', '.') ?>)</td>
                                    </tr>
                                <?php endif; ?>
                                <tr class="border-t border-slate-200 dark:border-slate-700">
                                    <td class="py-2 pl-4 font-semibold text-slate-900 dark:text-white">Total Pendapatan
                                    </td>
                                    <td class="py-2 text-right font-bold text-slate-900 dark:text-white">Rp
                                        <?= number_format($product_revenue + $shipping_income - $total_discount, 0, ',', '.') ?>
                                    </td>
                                </tr>

                                <!-- HPP -->
                                <tr>
                                    <td colspan="2"
                                        class="pt-6 pb-1 font-bold text-xs uppercase text-slate-500 tracking-wider">
                                        Harga Pokok Penjualan</td>
                                </tr>
                                <tr>
                                    <td class="py-2 pl-4 text-slate-600 dark:text-slate-400">HPP (Modal Barang Terjual)
                                    </td>
                                    <td class="py-2 text-right font-medium text-orange-600">(Rp
                                        <?= number_format($hpp, 0, ',', '.') ?>)</td>
                                </tr>
                                <tr class="border-t border-slate-200 dark:border-slate-700">
                                    <td class="py-2 pl-4 font-semibold text-slate-900 dark:text-white">Laba Kotor</td>
                                    <td
                                        class="py-2 text-right font-bold <?= $gross_profit >= 0 ? 'text-emerald-600' : 'text-red-600' ?>">
                                        Rp <?= number_format($gross_profit, 0, ',', '.') ?></td>
                                </tr>

                                <!-- BIAYA OPERASIONAL -->
                                <tr>
                                    <td colspan="2"
                                        class="pt-6 pb-1 font-bold text-xs uppercase text-slate-500 tracking-wider">
                                        Biaya Operasional</td>
                                </tr>
                                <?php
                                // Re-query for the formal statement
                                $stmt2 = $conn->prepare("SELECT category, SUM(amount) as total 
                                                          FROM operational_expenses 
                                                          WHERE expense_date BETWEEN ? AND ? 
                                                          GROUP BY category 
                                                          ORDER BY total DESC");
                                $stmt2->bind_param("ss", $start_date, $end_date);
                                $stmt2->execute();
                                $formal_breakdown = $stmt2->get_result();
                                if ($formal_breakdown->num_rows > 0):
                                    while ($row = $formal_breakdown->fetch_assoc()):
                                ?>
                                        <tr>
                                            <td class="py-2 pl-4 text-slate-600 dark:text-slate-400">
                                                <?= htmlspecialchars($row['category']) ?></td>
                                            <td class="py-2 text-right font-medium text-red-500">(Rp
                                                <?= number_format($row['total'], 0, ',', '.') ?>)</td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td class="py-2 pl-4 text-slate-400 italic" colspan="2">Tidak ada pengeluaran
                                        </td>
                                    </tr>
                                <?php endif; ?>
                                <tr class="border-t border-slate-200 dark:border-slate-700">
                                    <td class="py-2 pl-4 font-semibold text-slate-900 dark:text-white">Total Biaya
                                        Operasional</td>
                                    <td class="py-2 text-right font-bold text-red-600">(Rp
                                        <?= number_format($total_expenses, 0, ',', '.') ?>)</td>
                                </tr>

                                <!-- ONGKIR REVENUE -->
                                <?php if ($shipping_income > 0): ?>
                                    <tr>
                                        <td colspan="2"
                                            class="pt-6 pb-1 font-bold text-xs uppercase text-slate-500 tracking-wider">
                                            Pendapatan Lain</td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 pl-4 text-slate-600 dark:text-slate-400">Pendapatan Ongkos Kirim
                                        </td>
                                        <td class="py-2 text-right font-medium text-cyan-600">Rp
                                            <?= number_format($shipping_income, 0, ',', '.') ?></td>
                                    </tr>
                                <?php endif; ?>

                                <!-- LABA BERSIH -->
                                <tr
                                    class="border-t-2 border-double border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800/50">
                                    <td class="py-4 pl-4 font-black text-slate-900 dark:text-white text-base">LABA
                                        BERSIH</td>
                                    <td
                                        class="py-4 text-right font-black text-lg <?= $net_profit >= 0 ? 'text-green-600' : 'text-red-700' ?>">
                                        Rp <?= number_format($net_profit, 0, ',', '.') ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- ============================================ -->
                    <!-- OPERATIONAL BREAKDOWN TABLE -->
                    <!-- ============================================ -->
                    <div
                        class="lg:col-span-2 bg-white dark:bg-surface-dark rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                        <div
                            class="p-6 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center">
                            <h3 class="font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                <span class="material-icons-round text-amber-500">shopping_bag</span>
                                Produk Terlaris
                            </h3>
                            <span
                                class="text-xs bg-slate-100 dark:bg-slate-800 px-3 py-1 rounded-full text-slate-500 font-bold uppercase">Top
                                10</span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead class="bg-slate-50 dark:bg-slate-900 text-xs font-bold text-slate-500 uppercase">
                                    <tr>
                                        <th class="px-6 py-4">Produk</th>
                                        <th class="px-6 py-4 text-right">Qty (kg)</th>
                                        <th class="px-6 py-4 text-right">Omset</th>
                                        <th class="px-6 py-4 text-right">HPP</th>
                                        <th class="px-6 py-4 text-right">Margin</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                    <?php if ($top_products_res->num_rows > 0): ?>
                                        <?php while ($row = $top_products_res->fetch_assoc()):
                                            $margin_product = $row['total_revenue'] > 0 ? (($row['total_revenue'] - $row['total_hpp']) / $row['total_revenue']) * 100 : 0;
                                        ?>
                                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                                                <td class="px-6 py-4 font-medium text-slate-700 dark:text-slate-300">
                                                    <?= htmlspecialchars($row['product_name']) ?>
                                                </td>
                                                <td class="px-6 py-4 text-right text-slate-600 dark:text-slate-400">
                                                    <?= number_format($row['total_weight'], 1, ',', '.') ?>
                                                </td>
                                                <td class="px-6 py-4 text-right font-bold text-slate-900 dark:text-white">
                                                    Rp <?= number_format($row['total_revenue'], 0, ',', '.') ?>
                                                </td>
                                                <td class="px-6 py-4 text-right text-orange-600">
                                                    Rp <?= number_format($row['total_hpp'], 0, ',', '.') ?>
                                                </td>
                                                <td class="px-6 py-4 text-right">
                                                    <span
                                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold <?= $margin_product >= 20 ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : ($margin_product >= 10 ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400') ?>">
                                                        <?= number_format($margin_product, 1) ?>%
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="px-6 py-8 text-center text-slate-400 italic">Tidak ada
                                                data penjualan di periode ini.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- ============================================ -->
                    <!-- PERFORMANCE ANALYSIS -->
                    <!-- ============================================ -->
                    <div
                        class="bg-primary/5 dark:bg-primary/10 rounded-2xl p-8 border border-primary/20 flex flex-col justify-center text-center">
                        <span class="material-icons-round text-5xl text-primary mb-4">insights</span>
                        <h4 class="font-bold text-slate-900 dark:text-white mb-2 text-lg">Analisa Performa</h4>
                        <p class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
                            <?php
                            if ($net_profit > 0 && $product_revenue > 0) {
                                $margin = ($net_profit / $product_revenue) * 100;
                                $gross_margin = ($gross_profit / $product_revenue) * 100;
                                echo "Margin laba bersih Anda adalah <b>" . number_format($margin, 1) . "%</b>";
                                echo " dengan margin kotor <b>" . number_format($gross_margin, 1) . "%</b>.";
                                echo " Pertahankan efisiensi biaya operasional untuk hasil lebih maksimal.";
                            } elseif ($net_profit < 0) {
                                echo "Operasional Anda sedang mengalami <b class='text-red-600'>defisit (Rugi)</b>. Tinjau kembali kategori pengeluaran terbesar untuk melakukan penghematan.";
                            } else {
                                echo "Belum ada data penjualan atau pengeluaran yang cukup untuk memberikan analisa performa.";
                            }
                            ?>
                        </p>
                        <?php if ($product_revenue > 0): ?>
                            <div class="mt-4 space-y-3 text-left">
                                <div>
                                    <div class="flex justify-between text-xs text-slate-500 mb-1">
                                        <span>Rasio HPP</span>
                                        <span><?= $product_revenue > 0 ? number_format(($hpp / $product_revenue) * 100, 1) : 0 ?>%</span>
                                    </div>
                                    <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-2">
                                        <div class="bg-orange-500 h-2 rounded-full"
                                            style="width: <?= min(($hpp / $product_revenue) * 100, 100) ?>%"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex justify-between text-xs text-slate-500 mb-1">
                                        <span>Rasio Operasional</span>
                                        <span><?= $product_revenue > 0 ? number_format(($total_expenses / $product_revenue) * 100, 1) : 0 ?>%</span>
                                    </div>
                                    <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-2">
                                        <div class="bg-red-500 h-2 rounded-full"
                                            style="width: <?= min(($total_expenses / $product_revenue) * 100, 100) ?>%">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
            <?php include ROOT_PATH . "includes/admin/footer.php"; ?>
        </div>
    </main>
</body>

</html>