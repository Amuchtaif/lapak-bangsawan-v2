<?php
require("auth_session.php");
require_once dirname(__DIR__) . "/config/init.php";

// Set Week (Default to Today's Week)
// Format YYYY-Www (ISO-8601 week date)
$week_param = isset($_GET['week']) ? $_GET['week'] : date('Y-\WW');

// Parse Week to get Monday and Sunday
$dto = new DateTime();
// Set ISO week
$parts = explode('-W', $week_param);
$year = isset($parts[0]) ? (int)$parts[0] : (int)date('Y');
$week = isset($parts[1]) ? (int)$parts[1] : (int)date('W');
// Force to Monday of that week
$dto->setISODate($year, $week, 1); 
$monday_date = $dto->format('Y-m-d');
// Calculate Sunday (6 days later)
$dto->modify('+6 days');
$sunday_date = $dto->format('Y-m-d');

$selected_date = $monday_date;

// --- 1. Complex SQL Query ---
$sql = "
SELECT 
    p.id as product_id,
    p.name as product_name,
    p.image as product_image,
    p.price as product_price,
    p.stock as current_stock,
    c.name as category_name,
    c.id as category_id,
    COALESCE(wst.target_qty_kg, 0) as target_qty,
    COALESCE(sales.sold_qty, 0) as realized_qty
FROM products p
LEFT JOIN categories c ON p.category_id = c.id
LEFT JOIN weekly_sales_targets wst ON p.id = wst.product_id AND wst.start_date = '$selected_date'
LEFT JOIN (
    SELECT 
        product_name, 
        SUM(weight) as sold_qty 
    FROM order_items oi
    JOIN orders o ON oi.order_id = o.id
    WHERE DATE(o.created_at) BETWEEN '$monday_date' AND '$sunday_date' 
      AND o.status = 'completed'
    GROUP BY product_name
) sales ON p.name = sales.product_name
ORDER BY wst.target_qty_kg DESC, p.name ASC
";

$result = $conn->query($sql);

$products_data = [];
$summary = [
    'total_stock_value_target' => 0,
    'total_realized_value' => 0,
    'total_target_weight' => 0,
    'total_realized_weight' => 0,
];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $target = (float) $row['target_qty'];
        $realized = (float) $row['realized_qty'];
        $price = (float) $row['product_price'];

        $gap = $target - $realized;
        // Avoid division by zero
        $pct = ($target > 0) ? ($realized / $target) * 100 : 0;

        // Status Progress Target ke Realisasi
        if ($target <= 0) {
            $status_label = 'Menunggu Target';
            $status_class = 'bg-slate-100 text-slate-800';
        } elseif ($pct >= 100) {
            $status_label = 'Target Tercapai';
            $status_class = 'bg-green-100 text-green-800';
        } elseif ($pct >= 50) {
            $status_label = 'Mengejar Target';
            $status_class = 'bg-amber-100 text-amber-800';
        } else {
            $status_label = 'Belum Tercapai';
            $status_class = 'bg-red-100 text-red-800';
        }

        $row['gap'] = $gap;
        $row['percentage'] = $pct;
        $row['revenue_target'] = $target * $price;
        $row['revenue_realized'] = $realized * $price;
        $row['status_label'] = $status_label;
        $row['status_class'] = $status_class;

        $products_data[] = $row;

        // Summary Accumulation
        $summary['total_stock_value_target'] += $row['revenue_target'];
        $summary['total_realized_value'] += $row['revenue_realized'];
        $summary['total_target_weight'] += $target;
        $summary['total_realized_weight'] += $realized;
    }
}

// Efficiency Calculation
$efficiency = ($summary['total_target_weight'] > 0)
    ? ($summary['total_realized_weight'] / $summary['total_target_weight']) * 100
    : 0;
?>
<!DOCTYPE html>
<html class="light" lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Laporan Stok Mingguan - Admin Lapak Bangsawan</title>
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
        <?php $page_title = "Laporan Stok & Realisasi Mingguan";
        include ROOT_PATH . "includes/admin/header.php"; ?>

        <div class="flex-1 overflow-y-auto p-6 md:p-8 scroll-smooth">
            <div class="max-w-full mx-auto">

                <!-- Filter Header -->
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Laporan Nilai Stok Mingguan</h1>
                        <p class="text-slate-500 dark:text-slate-400 mt-1">Ringkasan nilai aset stok dan realisasi
                            penjualan mingguan.</p>
                        <p class="text-xs text-primary mt-1 font-bold">
                            Periode: <?php echo date('d M Y', strtotime($monday_date)); ?> s/d <?php echo date('d M Y', strtotime($sunday_date)); ?>
                        </p>
                    </div>
                    <div class="flex items-center gap-3">
                        <form action="" method="GET" class="flex items-center gap-2">
                            <div class="relative">
                                <span
                                    class="absolute left-3 top-1/2 -translate-y-1/2 material-icons-round text-slate-400 text-sm">date_range</span>
                                <input type="week" name="week" value="<?php echo $week_param; ?>"
                                    class="pl-9 pr-4 py-2 text-sm bg-white dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary shadow-sm text-slate-700 dark:text-white"
                                    onchange="this.form.submit()">
                            </div>
                        </form>
                        <button
                            class="px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors shadow-sm flex items-center gap-2">
                            <span class="material-icons-round text-sm">download</span>
                            Export
                        </button>
                    </div>
                </div>

                <!-- A. Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">

                    <!-- Card 1: Total Stock Value -->
                    <div
                        class="bg-green-700 text-white p-6 rounded-xl border border-green-800 shadow-sm relative overflow-hidden">
                        <div class="relative z-10">
                            <p class="text-green-100 text-sm font-medium">Total Nilai Stok (Target)</p>
                            <h3 class="text-2xl font-bold mt-2">Rp
                                <?php echo number_format($summary['total_stock_value_target'], 0, ',', '.'); ?>
                            </h3>
                            <div
                                class="mt-4 inline-flex items-center px-2.5 py-0.5 rounded-full bg-green-800 bg-opacity-50 text-xs text-green-50 border border-green-600">
                                Estimasi Aset
                            </div>
                        </div>
                        <div
                            class="absolute right-0 top-0 h-full w-24 bg-gradient-to-l from-green-600 to-transparent opacity-20">
                        </div>
                        <span
                            class="material-icons-round absolute right-4 bottom-4 text-6xl text-green-600 opacity-50">account_balance_wallet</span>
                    </div>

                    <!-- Card 2: Realization Value -->
                    <div
                        class="bg-surface-light dark:bg-surface-dark p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm relative overflow-hidden group">
                        <div class="absolute right-0 top-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                            <span class="material-icons-round text-6xl text-blue-500">payments</span>
                        </div>
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Total Realisasi Penjualan</p>
                        <div class="mt-2 flex items-baseline gap-2">
                            <span class="text-2xl font-bold text-slate-900 dark:text-white">Rp
                                <?php echo number_format($summary['total_realized_value'], 0, ',', '.'); ?></span>
                        </div>
                        <div class="mt-4 flex items-center text-sm">
                            <?php if ($efficiency >= 100): ?>
                                <span class="text-green-500 font-medium flex items-center">
                                    <span class="material-icons-round text-sm">trending_up</span>
                                    Outstanding
                                </span>
                            <?php else: ?>
                                <span class="text-slate-400 font-medium">On Progress</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Card 3: Weight Target -->
                    <div
                        class="bg-surface-light dark:bg-surface-dark p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm relative overflow-hidden group">
                        <div class="absolute right-0 top-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                            <span class="material-icons-round text-6xl text-purple-500">scale</span>
                        </div>
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Target Berat Mingguan</p>
                        <div class="mt-2 flex items-baseline gap-2">
                            <span
                                class="text-2xl font-bold text-slate-900 dark:text-white"><?php echo number_format($summary['total_target_weight'], 1, ',', '.'); ?></span>
                            <span class="text-sm text-slate-500 font-medium">kg</span>
                        </div>
                        <div class="mt-4 flex items-center text-sm">
                            <span class="text-slate-500 dark:text-slate-400">
                                Terjual: <span
                                    class="text-slate-900 dark:text-white font-semibold"><?php echo number_format($summary['total_realized_weight'], 1, ',', '.'); ?>
                                    kg</span>
                            </span>
                        </div>
                    </div>

                    <!-- Card 4: Total Stok Terjual -->
                    <div
                        class="bg-surface-light dark:bg-surface-dark p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm relative overflow-hidden group">
                        <div class="absolute right-0 top-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                            <span class="material-icons-round text-6xl text-emerald-500">check_circle</span>
                        </div>
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Total Stok Terjual</p>
                        <div class="mt-2 flex items-baseline gap-2">
                            <span
                                class="text-2xl font-bold text-slate-900 dark:text-white"><?php echo number_format($summary['total_realized_weight'], 1, ',', '.'); ?></span>
                            <span class="text-sm text-slate-500 font-medium">kg</span>
                        </div>
                        <div class="mt-4 flex items-center text-sm">
                            <span class="text-slate-400 font-medium">Realisasi Pekan Ini</span>
                        </div>
                    </div>

                </div>

                <!-- B. Detail Table -->
                <div
                    class="bg-surface-light dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                    <div
                        class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center bg-slate-50/50 dark:bg-slate-800/50">
                        <h2 class="text-lg font-bold text-slate-900 dark:text-white">Detail Produk & Nilai</h2>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-slate-500 dark:text-slate-400">
                            <thead
                                class="bg-slate-50 dark:bg-slate-800/50 text-xs uppercase font-semibold text-slate-500">
                                <tr>
                                    <th class="px-6 py-4">Produk</th>
                                    <th class="px-6 py-4">Kategori</th>
                                    <th class="px-6 py-4 text-center">Stok Terkini</th>
                                    <th class="px-6 py-4 text-right">Target (Kg)</th>
                                    <th class="px-6 py-4 pl-10">Realisasi (Kg)</th>
                                    <th class="px-6 py-4 text-right">Selisih</th>
                                    <th class="px-6 py-4 text-center">Status Progress</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                                <?php if (empty($products_data)): ?>
                                    <tr>
                                        <td colspan="7" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                                            <span
                                                class="material-icons-round text-4xl text-slate-300 mb-2">assignment_late</span>
                                            <p>Belum ada target stok untuk tanggal ini.</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($products_data as $item): ?>
                                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                                            <!-- Product -->
                                            <td class="px-6 py-4">
                                                <div class="flex items-center gap-3">
                                                    <div
                                                        class="size-10 rounded-lg bg-slate-100 dark:bg-slate-700 shrink-0 overflow-hidden">
                                                        <?php if ($item['product_image']): ?>
                                                            <img class="w-full h-full object-cover"
                                                                src="../<?php echo $item['product_image']; ?>" alt="">
                                                        <?php else: ?>
                                                            <span
                                                                class="material-icons-round text-slate-400 text-lg w-full h-full flex items-center justify-center">image</span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div>
                                                        <div class="font-medium text-slate-900 dark:text-white">
                                                            <?php echo htmlspecialchars($item['product_name']); ?>
                                                        </div>
                                                        <div class="text-xs text-slate-500">ID:
                                                            <?php echo $item['product_id']; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <!-- Category -->
                                            <td class="px-6 py-4 text-slate-500 dark:text-slate-400">
                                                <?php echo htmlspecialchars($item['category_name']); ?>
                                            </td>
                                            <!-- Current Stock -->
                                            <td class="px-6 py-4 text-center font-medium text-slate-700 dark:text-slate-300">
                                                <?php echo number_format($item['current_stock'], 0, ',', '.'); ?>
                                            </td>
                                            <!-- Target -->
                                            <td class="px-6 py-4 text-right font-medium text-slate-900 dark:text-white">
                                                <?php echo number_format($item['target_qty'], 1, ',', '.'); ?>
                                            </td>
                                            <!-- Realization & Progress -->
                                            <td class="px-6 py-4 align-middle">
                                                <div class="w-full max-w-xs">
                                                    <div class="flex items-center justify-between mb-1">
                                                        <span
                                                            class="text-sm font-bold text-slate-900 dark:text-white"><?php echo number_format($item['realized_qty'], 1, ',', '.'); ?>
                                                            kg</span>
                                                        <span
                                                            class="text-xs font-semibold <?php echo ($item['percentage'] >= 100) ? 'text-green-600' : 'text-slate-500'; ?>">
                                                            <?php echo number_format($item['percentage'], 1); ?>%
                                                        </span>
                                                    </div>
                                                    <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-2">
                                                        <div class="<?php
                                                        if ($item['percentage'] >= 100)
                                                            echo 'bg-green-500';
                                                        elseif ($item['percentage'] >= 50)
                                                            echo 'bg-amber-500';
                                                        else
                                                            echo 'bg-red-500';
                                                        ?> h-2 rounded-full transition-all duration-500"
                                                            style="width: <?php echo min($item['percentage'], 100); ?>%"></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <!-- Gap -->
                                            <td class="px-6 py-4 text-right font-medium">
                                                <?php $text_color = ($item['gap'] > 0) ? 'text-red-500' : 'text-green-500'; ?>
                                                <span class="<?php echo $text_color; ?>">
                                                    <?php echo ($item['gap'] > 0 ? '-' : '+') . number_format(abs($item['gap']), 1, ',', '.'); ?>
                                                </span>
                                            </td>
                                            <!-- Status -->
                                            <td class="px-6 py-4 text-center">
                                                <span
                                                    class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium <?php echo $item['status_class']; ?> dark:bg-opacity-20">
                                                    <?php echo $item['status_label']; ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <?php include ROOT_PATH . "includes/admin/footer.php"; ?>
            </div>
        </div>
    </main>
</body>

</html>