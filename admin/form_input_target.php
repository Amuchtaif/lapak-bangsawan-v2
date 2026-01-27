<?php
require("auth_session.php");
require_once dirname(__DIR__) . "/config/init.php";

// Set Date (Default to Today)
$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$today = date('Y-m-d');

// Handle Form Submission
$success_msg = "";
$error_msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['targets'])) {
    $target_date = $_POST['target_date'];
    $targets = $_POST['targets']; // Array [product_id => target_qty]

    // Prepare statement for ON DUPLICATE KEY UPDATE
    $stmt = $conn->prepare("INSERT INTO daily_sales_targets (product_id, target_date, target_qty_kg) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE target_qty_kg = VALUES(target_qty_kg)");
    
    $updated_count = 0;
    foreach ($targets as $p_id => $qty) {
        // Validation: Ensure qty is numeric and not negative (0 is fine)
        if (is_numeric($qty) && $qty >= 0) {
            $stmt->bind_param("isd", $p_id, $target_date, $qty);
            if ($stmt->execute()) {
                $updated_count++;
            }
        }
    }
    
    if ($updated_count > 0) {
        $success_msg = "Berhasil menyimpan target untuk $updated_count produk.";
    } else {
        $error_msg = "Tidak ada data yang disimpan atau terjadi kesalahan.";
    }
}

// Fetch Products & Existing Targets
$sql = "
SELECT 
    p.id as product_id,
    p.name as product_name,
    p.image as product_image,
    p.price as product_price,
    p.stock as current_stock,
    c.name as category_name,
    dst.target_qty_kg as existing_target
FROM products p
LEFT JOIN categories c ON p.category_id = c.id
LEFT JOIN daily_sales_targets dst ON p.id = dst.product_id AND dst.target_date = '$date'

ORDER BY p.stock DESC, p.name ASC
";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Input Target Harian - Admin Lapak Bangsawan</title>
    <link rel="icon" href="<?= BASE_URL ?>assets/images/favicon-laba.png" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
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
<body class="bg-background-light dark:bg-background-dark text-slate-600 dark:text-slate-300 font-display transition-colors duration-200 antialiased overflow-hidden h-screen flex">
    
    <?php include ROOT_PATH . "includes/admin/sidebar.php"; ?>
    
    <main class="flex-1 flex flex-col h-full relative overflow-hidden">
        <?php $page_title = "Input Target Harian"; include ROOT_PATH . "includes/admin/header.php"; ?>
        
        <div class="flex-1 overflow-y-auto scroll-smooth relative">
            <div class="w-full p-6 md:p-8 pb-32"> <!-- Increased bottom padding to avoid button overlap -->
                
                <div class="flex flex-col md:flex-row md:items-start justify-between gap-4 mb-6">
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Bulk Input Target Harian</h1>
                        <p class="text-slate-500 dark:text-slate-400 mt-1">Kelola target penjualan harian berdasarkan stok gudang aktual.</p>
                    </div>
                     <!-- Link to View History/Report -->
                     <a href="report_stock.php?date=<?php echo $date; ?>" class="px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors shadow-sm flex items-center gap-2">
                        <span class="material-icons-round text-sm">history</span>
                        View Report
                    </a>
                </div>

                <!-- Alerts -->
                <!-- Notification Area -->
                <?php if ($success_msg): ?>
                    <div
                        class="bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-700 rounded-lg p-4 mb-6 flex items-start gap-3 shadow-sm auto-close-alert transition-opacity duration-500">
                        <span class="material-icons-round text-green-500">check_circle</span>
                        <div>
                            <h3 class="font-medium text-slate-900 dark:text-white">Berhasil</h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400"><?php echo $success_msg; ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($error_msg): ?>
                    <div
                        class="bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-700 rounded-lg p-4 mb-6 flex items-start gap-3 shadow-sm auto-close-alert transition-opacity duration-500">
                        <span class="material-icons-round text-red-500">error</span>
                        <div>
                            <h3 class="font-medium text-slate-900 dark:text-white">Gagal</h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400"><?php echo $error_msg; ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Filter & Actions Card -->
                <div class="bg-surface-light/95 dark:bg-surface-dark/95 backdrop-blur-md p-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-md mb-6 sticky top-0 z-30">
                    <form id="dateForm" action="" method="GET" class="flex flex-col md:flex-row gap-4 items-end md:items-center justify-between">
                        <div class="w-full md:w-auto">
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Pilih Tanggal Target</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 material-icons-round text-slate-400 text-sm">calendar_today</span>
                                <input type="date" name="date" value="<?php echo $date; ?>" 
                                    class="pl-9 pr-4 py-2 w-full md:w-64 text-sm bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary shadow-sm text-slate-700 dark:text-white"
                                    onchange="document.getElementById('dateForm').submit()">
                            </div>
                        </div>
                        <div class="flex gap-3 w-full md:w-auto">
                            <button type="button" onclick="openModal()" class="flex-1 md:flex-none px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition-colors shadow-sm flex items-center justify-center gap-2">
                                <span class="material-icons-round text-sm">bolt</span>
                                Samakan dengan Stok Aktual
                            </button>
                            <button type="button" onclick="resetInput()" class="flex-1 md:flex-none px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-lg text-sm font-medium transition-colors shadow-sm flex items-center justify-center gap-2">
                                <span class="material-icons-round text-sm">refresh</span>
                                Reset
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Bulk Input Form -->
                <form action="" method="POST" id="bulkForm">
                    <input type="hidden" name="target_date" value="<?php echo $date; ?>">
                    
                    <div class="bg-surface-light dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm text-slate-500 dark:text-slate-400">
                                <thead class="bg-slate-50 dark:bg-slate-800/50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200 dark:border-slate-700">
                                    <tr>
                                        <th class="px-6 py-4 w-12">No</th>
                                        <th class="px-6 py-4 w-1/3">Info Produk</th>
                                        <th class="px-6 py-4 w-1/6">Stok Gudang (Kg)</th>
                                        <th class="px-6 py-4 w-1/4">Target Penjualan (Kg)</th>
                                        <th class="px-6 py-4 w-1/4">Estimasi Pendapatan</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                                    <?php if ($result && $result->num_rows > 0): ?>
                                        <?php $no = 1; ?>
                                        <?php while($row = $result->fetch_assoc()): 
                                            $is_low_stock = $row['current_stock'] < 5;
                                            $default_val = $row['existing_target'] !== null ? $row['existing_target'] : 0;
                                        ?>
                                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                                            <!-- No -->
                                            <td class="px-6 py-4 text-slate-500 font-medium">
                                                <?php echo $no++; ?>
                                            </td>
                                            <!-- Info Produk -->
                                            <td class="px-6 py-4">
                                                <div class="flex items-center gap-3">
                                                    <div class="size-12 rounded-lg bg-slate-100 dark:bg-slate-700 shrink-0 overflow-hidden border border-slate-200 dark:border-slate-600">
                                                        <?php if($row['product_image']): ?>
                                                            <img class="w-full h-full object-cover" src="../<?php echo $row['product_image']; ?>" alt="">
                                                        <?php else: ?>
                                                            <span class="material-icons-round text-slate-400 text-lg w-full h-full flex items-center justify-center">image</span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div>
                                                        <div class="font-medium text-slate-900 dark:text-white text-base"><?php echo htmlspecialchars($row['product_name']); ?></div>
                                                        <div class="text-xs text-slate-500 mt-0.5"><?php echo htmlspecialchars($row['category_name']); ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            
                                            <!-- Stok Gudang -->
                                            <td class="px-6 py-4">
                                                <div class="flex flex-col">
                                                    <span class="text-base font-bold text-slate-900 dark:text-white stock-val" data-stock="<?php echo $row['current_stock']; ?>">
                                                        <?php echo number_format($row['current_stock'], 1, ',', '.'); ?> kg
                                                    </span>
                                                    <?php if($row['current_stock'] <= 0): ?>
                                                        <span class="text-[10px] font-bold text-red-600 uppercase mt-1">Stok Habis</span>
                                                    <?php elseif($row['current_stock'] < 3): ?>
                                                        <span class="text-[10px] font-bold text-amber-500 uppercase mt-1">Stok Menipis</span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            
                                            <!-- Input Target -->
                                            <td class="px-6 py-4">
                                                <input type="number" step="0.5" min="0" 
                                                    name="targets[<?php echo $row['product_id']; ?>]" 
                                                    value="<?php echo $default_val; ?>"
                                                    data-price="<?php echo $row['product_price']; ?>"
                                                    oninput="calcRevenue(this)"
                                                    class="target-input w-full px-4 py-2 text-base font-semibold text-slate-900 dark:text-white bg-white dark:bg-slate-900 border-2 border-slate-200 dark:border-slate-700 rounded-lg focus:ring-4 focus:ring-primary/20 focus:border-primary transition-all shadow-sm">
                                            </td>
                                            
                                            <!-- Estimasi Pendapatan -->
                                            <td class="px-6 py-4">
                                                <div class="text-base font-medium text-slate-600 dark:text-slate-300">
                                                    Rp <span class="revenue-display">0</span>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="px-6 py-12 text-center text-slate-500">Tidak ada produk ditemukan.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Sticky Save Button -->
                    <div class="fixed bottom-6 right-6 z-30">
                        <button type="submit" class="group flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-xl shadow-lg shadow-blue-600/30 hover:shadow-blue-600/50 transition-all transform hover:-translate-y-1 font-semibold text-base">
                            <span class="material-icons-round">save</span>
                            Simpan Semua Target
                        </button>
                    </div>

                </form>

                <?php include ROOT_PATH . "includes/admin/footer.php"; ?>
            </div>
        </div>
    </main>

    <!-- Confirmation Modal -->
    <div id="confirmModal" class="hidden fixed inset-0 z-[200] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:p-0">
            <!-- Background overlay -->
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="closeModal()"></div>

            <!-- Modal panel -->
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="relative inline-block align-middle bg-white dark:bg-surface-dark rounded-2xl text-left shadow-2xl transform transition-all sm:my-8 sm:max-w-lg sm:w-full border border-slate-200 dark:border-slate-700 max-h-[90vh] overflow-y-auto">
                <div class="bg-white dark:bg-surface-dark px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 dark:bg-blue-900/30 sm:mx-0 sm:h-10 sm:w-10">
                            <span class="material-icons-round text-blue-600 dark:text-blue-400">bolt</span>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-slate-900 dark:text-white" id="modal-title">
                                Samakan Target dengan Stok Aktual
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-slate-500 dark:text-slate-400">
                                    Tindakan ini akan mengisi kolom "Target Penjualan" <b>secara otomatis</b> sesuai dengan jumlah Stok Gudang saat ini. Data sebelumnya pada form akan tertimpa.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-slate-50 dark:bg-slate-800/50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                    <button type="button" onclick="confirmAutoFill()" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Ya, Samakan Semua
                    </button>
                    <button type="button" onclick="closeModal()" class="mt-3 w-full inline-flex justify-center rounded-lg border border-slate-300 dark:border-slate-600 shadow-sm px-4 py-2 bg-white dark:bg-surface-dark text-base font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Init Calc on Load & Auto Close Alert
        document.addEventListener('DOMContentLoaded', () => {
            // Calc init
            document.querySelectorAll('.target-input').forEach(input => {
                calcRevenue(input);
            });

            // Auto close alerts
            const alerts = document.querySelectorAll('.auto-close-alert');
            if (alerts.length > 0) {
                setTimeout(() => {
                    alerts.forEach(alert => {
                        alert.classList.add('opacity-0');
                        setTimeout(() => {
                            alert.remove();
                        }, 500); // Wait for transition
                    });
                }, 3000);
            }
        });

        function calcRevenue(input) {
            const qty = parseFloat(input.value) || 0;
            const price = parseFloat(input.dataset.price) || 0;
            const revenue = qty * price;
            
            // Format Rupiah
            const formatted = new Intl.NumberFormat('id-ID').format(revenue);
            
            // Find sibling display
            // Input is in TD, TD sibling is TD containing div containing span
            const row = input.closest('tr');
            row.querySelector('.revenue-display').textContent = formatted;
        }

        // Modal Functions
        function openModal() {
            document.getElementById('confirmModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('confirmModal').classList.add('hidden');
        }

        function confirmAutoFill() {
            const rows = document.querySelectorAll('tbody tr');
            rows.forEach(row => {
                const stockVal = parseFloat(row.querySelector('.stock-val').dataset.stock) || 0;
                const input = row.querySelector('.target-input');
                // Set value
                input.value = stockVal;
                // Trigger calc
                calcRevenue(input);
            });
            closeModal();
            // Optional: Show a nice toast or feedback
        }

        function resetInput() {
            if(confirm('Reset semua input ke 0?')) {
                const inputs = document.querySelectorAll('.target-input');
                inputs.forEach(input => {
                    input.value = 0;
                    calcRevenue(input);
                });
            }
        }
    </script>
</body>
</html>
