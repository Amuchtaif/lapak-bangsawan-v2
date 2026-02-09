<?php
require("../auth_session.php");
require_once dirname(dirname(__DIR__)) . "/config/init.php";

$page_title = "Laporan Mitra Laba";
$current_page = "consignment_report.php";

// Default to current month/year
$month = isset($_GET['month']) ? intval($_GET['month']) : date('n');
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

// Array of months for dropdown
$months = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

// Calculations
$grand_total_debt = 0;
$grand_total_profit = 0;
$report_data = [];

// Query
// Note: We join on product name because order_items unfortunately doesn't store product_id in this schema version.
// This assumes product names are unique and don't change often, or accept that history might drift if names change.
$sql = "
    SELECT 
        p.id AS product_id,
        p.name AS product_name,
        p.unit, 
        p.stock AS current_stock,
        p.buy_price AS partner_price,
        p.price AS current_selling_price,
        pt.name AS partner_name,
        SUM(oi.weight) AS total_qty_sold,
        SUM(oi.subtotal) AS total_revenue_real, -- Real revenue from transaction (price at that time)
        -- We calculate debt based on CURRENT buy_price as per standard consignment logic often used in simple systems
        SUM(oi.weight * p.buy_price) AS total_debt
    FROM products p
    JOIN partners pt ON p.partner_id = pt.id
    JOIN order_items oi ON p.name = oi.product_name
    JOIN orders o ON oi.order_id = o.id
    WHERE p.product_type = 'consignment'
      AND o.status = 'completed'
      AND MONTH(o.created_at) = ?
      AND YEAR(o.created_at) = ?
    GROUP BY p.id
    ORDER BY pt.name ASC, p.name ASC
";

$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param("ii", $month, $year);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        // Calculate Internal Profit: (Real Revenue - Calculated Debt)
        // This is more accurate than (Selling Price - Buy Price) * Qty because it respects the price AT TIME OF SALE for revenue.
        $internal_profit = $row['total_revenue_real'] - $row['total_debt'];
        
        $row['internal_profit'] = $internal_profit;
        $report_data[] = $row;
        
        $grand_total_debt += $row['total_debt'];
        $grand_total_profit += $internal_profit;
    }
    $stmt->close();
} else {
    $error_msg = "Database Error: " . $conn->error;
}

?>
<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Laporan Mitra Laba - Lapak Bangsawan</title>
    <link rel="icon" href="<?= BASE_URL ?>assets/images/favicon-laba.png" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
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
                    fontFamily: {
                        "display": ["Inter", "sans-serif"]
                    }
                },
            },
        }
    </script>
</head>

<body class="bg-background-light dark:bg-background-dark text-slate-600 dark:text-slate-300 font-display transition-colors duration-200 antialiased lg:h-screen flex flex-col lg:flex-row">
    <?php include ROOT_PATH . "includes/admin/sidebar.php"; ?>
    <main class="flex-1 flex flex-col lg:h-full relative overflow-hidden">
        <?php include ROOT_PATH . "includes/admin/header.php"; ?>
        
        <div class="flex-1 overflow-y-auto p-4 md:p-8 scroll-smooth">
            <div class="max-w-7xl mx-auto flex flex-col gap-6">

                <!-- Header & Filter -->
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Laporan Bulanan Mitra Laba</h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Rekapitulasi penjualan konsinyasi dan bagi hasil.</p>
                    </div>
                    
                    <form action="" method="GET" class="flex flex-wrap gap-2 items-center bg-white dark:bg-surface-dark p-2 rounded-lg border border-slate-200 dark:border-slate-800 shadow-sm">
                        
                        <!-- Month Select -->
                        <div class="custom-select-wrapper relative min-w-[140px]" data-input-name="month">
                            <input type="hidden" name="month" value="<?= $month ?>">
                            <button type="button" class="custom-select-trigger w-full flex items-center justify-between px-3 py-2 text-sm bg-transparent border-0 focus:ring-0 text-slate-700 dark:text-slate-300 font-medium cursor-pointer">
                                <span class="selected-label"><?= $months[$month] ?></span>
                                <span class="material-icons-round text-slate-400 text-lg transition-transform ml-2">expand_more</span>
                            </button>
                            <div class="custom-select-options hidden absolute z-10 w-full mt-1 bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-800 rounded-lg shadow-xl max-h-60 overflow-y-auto">
                                <?php foreach ($months as $m_num => $m_name): ?>
                                    <div class="custom-option px-4 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 cursor-pointer text-sm <?= $month == $m_num ? 'bg-primary/5 text-primary font-bold' : 'text-slate-600 dark:text-slate-400' ?>" 
                                         data-value="<?= $m_num ?>">
                                        <?= $m_name ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <div class="h-6 w-px bg-slate-200 dark:bg-slate-700"></div>

                        <!-- Year Select -->
                        <div class="custom-select-wrapper relative min-w-[100px]" data-input-name="year">
                            <input type="hidden" name="year" value="<?= $year ?>">
                            <button type="button" class="custom-select-trigger w-full flex items-center justify-between px-3 py-2 text-sm bg-transparent border-0 focus:ring-0 text-slate-700 dark:text-slate-300 font-medium cursor-pointer">
                                <span class="selected-label"><?= $year ?></span>
                                <span class="material-icons-round text-slate-400 text-lg transition-transform ml-2">expand_more</span>
                            </button>
                            <div class="custom-select-options hidden absolute z-10 w-full mt-1 bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-800 rounded-lg shadow-xl max-h-60 overflow-y-auto">
                                <?php for ($y = date('Y'); $y >= 2024; $y--): ?>
                                    <div class="custom-option px-4 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 cursor-pointer text-sm <?= $year == $y ? 'bg-primary/5 text-primary font-bold' : 'text-slate-600 dark:text-slate-400' ?>" 
                                         data-value="<?= $y ?>">
                                        <?= $y ?>
                                    </div>
                                <?php endfor; ?>
                            </div>
                        </div>

                        <button type="submit" class="p-2 ml-1 bg-primary text-white rounded-md hover:bg-blue-600 transition-colors shadow-sm shadow-primary/30">
                            <span class="material-icons-round text-lg block">filter_list</span>
                        </button>
                    </form>
                </div>

                <!-- Report Table -->
                <div class="bg-surface-light dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                    <?php if (isset($error_msg)): ?>
                        <div class="p-8 text-center text-red-500">
                            <?= $error_msg ?>
                        </div>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm text-slate-500 dark:text-slate-400">
                                <thead class="bg-slate-50 dark:bg-slate-800/50 text-xs uppercase font-semibold text-slate-500">
                                    <tr>
                                        <th class="px-6 py-4 w-16 text-center">No</th>
                                        <th class="px-6 py-4">Nama Mitra</th>
                                        <th class="px-6 py-4">Produk</th>
                                        <th class="px-6 py-4 text-center">Sisa Stok</th>
                                        <th class="px-6 py-4 text-center">Terjual</th>
                                        <th class="px-6 py-4 text-right">Harga Modal (Mitra)</th>
                                        <th class="px-6 py-4 text-right">Total Hutang (Mitra)</th>
                                        <th class="px-6 py-4 text-right">Laba Internal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                                    <?php if (count($report_data) > 0): ?>
                                        <?php $no = 1; foreach ($report_data as $row): ?>
                                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                                                <td class="px-6 py-4 text-center font-mono text-xs font-bold text-slate-400">
                                                    <?= $no++ ?>
                                                </td>
                                                <td class="px-6 py-4 font-medium text-slate-900 dark:text-white">
                                                    <?= htmlspecialchars($row['partner_name']) ?>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <div class="flex flex-col">
                                                        <span class="font-medium"><?= htmlspecialchars($row['product_name']) ?></span>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 text-center">
                                                    <?php 
                                                        $stock_class = $row['current_stock'] <= 5 ? 'text-amber-500 font-bold' : '';
                                                        if ($row['current_stock'] == 0) $stock_class = 'text-red-500 font-bold';
                                                    ?>
                                                    <span class="<?= $stock_class ?>"><?= $row['current_stock'] ?></span>
                                                </td>
                                                <td class="px-6 py-4 text-center font-bold text-slate-700 dark:text-slate-300">
                                                    <?= $row['total_qty_sold'] ?>
                                                </td>
                                                <td class="px-6 py-4 text-right">
                                                    Rp <?= number_format($row['partner_price'], 0, ',', '.') ?>
                                                </td>
                                                <td class="px-6 py-4 text-right font-medium text-red-600 dark:text-red-400 bg-red-50/50 dark:bg-red-900/10">
                                                    Rp <?= number_format($row['total_debt'], 0, ',', '.') ?>
                                                </td>
                                                <td class="px-6 py-4 text-right font-bold text-green-600 dark:text-green-400 bg-green-50/50 dark:bg-green-900/10">
                                                    Rp <?= number_format($row['internal_profit'], 0, ',', '.') ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                                                Tidak ada data penjualan konsinyasi pada periode ini.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                                <tfoot class="bg-slate-50 dark:bg-slate-800/50 font-bold text-slate-900 dark:text-white border-t-2 border-slate-200 dark:border-slate-700">
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 text-right uppercase text-xs tracking-wider">Total Keseluruhan</td>
                                        <td class="px-6 py-4 text-right text-red-600 dark:text-red-400 text-base">
                                            Rp <?= number_format($grand_total_debt, 0, ',', '.') ?>
                                        </td>
                                        <td class="px-6 py-4 text-right text-green-600 dark:text-green-400 text-base">
                                            Rp <?= number_format($grand_total_profit, 0, ',', '.') ?>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Print Action (Optional) -->
                <div class="flex justify-end">
                    <button onclick="window.print()" class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-300 bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-800 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors shadow-sm no-print">
                        <span class="material-icons-round text-lg">print</span> Cetak Laporan
                    </button>
                </div>

            </div>
        </div>
    </main>

    <style>
        @media print {
            .no-print, aside, header { display: none !important; }
            main { margin: 0; padding: 0; overflow: visible !important; height: auto !important; }
            body { background: white !important; -webkit-print-color-adjust: exact; }
            table th, table td { color: black !important; }
        }
    </style>
    <script>
        // Custom Select Logic
        document.querySelectorAll('.custom-select-wrapper').forEach(wrapper => {
            const button = wrapper.querySelector('.custom-select-trigger');
            const options = wrapper.querySelector('.custom-select-options');
            const hiddenInput = wrapper.querySelector('input[type="hidden"]');
            const label = wrapper.querySelector('.selected-label');
            const icon = wrapper.querySelector('.material-icons-round');

            // Toggle Dropdown
            button.addEventListener('click', (e) => {
                e.stopPropagation();
                // Close other dropdowns
                document.querySelectorAll('.custom-select-options').forEach(opt => {
                    if (opt !== options) opt.classList.add('hidden');
                });
                options.classList.toggle('hidden');
                icon.classList.toggle('rotate-180');
            });

            // Select Option
            wrapper.querySelectorAll('.custom-option').forEach(option => {
                option.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const value = option.dataset.value;
                    const text = option.textContent.trim();
                    
                    const hiddenInput = wrapper.querySelector('input[type="hidden"]');
                    const label = wrapper.querySelector('.selected-label');
                    const icon = wrapper.querySelector('.material-icons-round');

                    hiddenInput.value = value;
                    label.textContent = text;
                    
                    // Visual feedback
                    wrapper.querySelectorAll('.custom-option').forEach(opt => {
                        opt.classList.remove('bg-primary/5', 'text-primary', 'font-bold');
                        opt.classList.add('text-slate-600', 'dark:text-slate-400');
                    });
                    option.classList.remove('text-slate-600', 'dark:text-slate-400');
                    option.classList.add('bg-primary/5', 'text-primary', 'font-bold');

                    wrapper.querySelector('.custom-select-options').classList.add('hidden');
                    icon.classList.remove('rotate-180');
                });
            });
        });

        // Close dropdowns when clicking outside
        document.addEventListener('click', () => {
            document.querySelectorAll('.custom-select-options').forEach(opt => opt.classList.add('hidden'));
            document.querySelectorAll('.custom-select-trigger .material-icons-round').forEach(icon => icon.classList.remove('rotate-180'));
        });
    </script>
</body>
</html>
