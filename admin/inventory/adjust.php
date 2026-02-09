<?php
require("../auth_session.php");
require_once dirname(dirname(__DIR__)) . "/config/init.php";
require(ROOT_PATH . "includes/admin/notification_logic.php");

$error = '';
$success = '';

// Handle Post Adjustment
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['adjust_stock'])) {
    $product_id = intval($_POST['product_id']);
    $qty_adjusted = floatval($_POST['qty_adjusted']); // Negative for shrinkage/spoilage
    $reason = mysqli_real_escape_string($conn, $_POST['reason']);
    $admin_id = $_SESSION['user_id'];

    if ($product_id > 0 && $qty_adjusted != 0) {
        $conn->begin_transaction();
        try {
            // Insert into history
            $stmt = $conn->prepare("INSERT INTO stock_adjustments (product_id, qty_adjusted, reason, admin_id) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("idsi", $product_id, $qty_adjusted, $reason, $admin_id);
            $stmt->execute();

            // Update product stock
            $stmt = $conn->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");
            $stmt->bind_param("di", $qty_adjusted, $product_id);
            $stmt->execute();

            $conn->commit();
            $_SESSION['status_msg'] = "Penyesuaian stok berhasil dicatat.";
            $_SESSION['status_type'] = "success";
            header("Location: adjust.php");
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Gagal memproses penyesuaian: " . $e->getMessage();
        }
    } else {
        $error = "Data tidak valid.";
    }
}

// Fetch adjustments history
$q = "SELECT sa.*, p.name as product_name, p.unit, u.full_name as admin_name 
      FROM stock_adjustments sa 
      JOIN products p ON sa.product_id = p.id 
      JOIN users u ON sa.admin_id = u.id 
      ORDER BY sa.date DESC LIMIT 50";
$adjustments = $conn->query($q);

// Fetch products for dropdown
$products_res = $conn->query("SELECT id, name, stock, unit FROM products ORDER BY name ASC");
$products = [];
while ($p = $products_res->fetch_assoc())
    $products[] = $p;
?>
<!DOCTYPE html>
<html class="light" lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Penyesuaian Stok - Lapak Bangsawan</title>
    <link rel="icon" href="<?= BASE_URL ?>assets/images/favicon-laba.png" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&amp;display=swap"
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
                    fontFamily: { "display": ["Inter", "sans-serif"] },
                    borderRadius: { "DEFAULT": "0.25rem", "lg": "0.5rem", "xl": "0.75rem", "2xl": "1rem", "full": "9999px" },
                },
            },
        }
    </script>
</head>

<body
    class="bg-background-light dark:bg-background-dark text-slate-600 dark:text-slate-300 font-display transition-colors duration-200 antialiased lg:overflow-hidden lg:h-screen flex flex-col lg:flex-row">
    <?php include ROOT_PATH . "includes/admin/sidebar.php"; ?>
    <main class="flex-1 flex flex-col lg:h-full relative lg:overflow-hidden">
        <?php $page_title = "Penyesuaian Stok";
        include ROOT_PATH . "includes/admin/header.php"; ?>
        <div class="flex-1 lg:overflow-y-auto p-4 md:p-8 scroll-smooth">
            <div class="max-w-full mx-auto flex flex-col gap-6">

                <?php if (isset($_SESSION['status_msg'])): ?>
                    <div
                        class="bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-700 rounded-lg p-4 mb-2 flex items-start gap-3 shadow-sm auto-close-alert transition-opacity duration-500">
                        <span class="material-icons-round text-green-500">check_circle</span>
                        <div>
                            <h3 class="font-medium text-slate-900 dark:text-white">Berhasil</h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400">
                                <?php echo $_SESSION['status_msg']; ?>
                            </p>
                        </div>
                    </div>
                    <?php unset($_SESSION['status_msg']);
                    unset($_SESSION['status_type']); ?>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div
                        class="bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-700 rounded-lg p-4 mb-2 flex items-start gap-3 shadow-sm auto-close-alert transition-opacity duration-500">
                        <span class="material-icons-round text-red-500">error</span>
                        <div>
                            <h3 class="font-medium text-slate-900 dark:text-white">Gagal</h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400">
                                <?php echo $error; ?>
                            </p>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Adjustment Form -->
                    <div class="lg:col-span-1">
                        <div
                            class="bg-surface-light dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm">
                            <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-6">Input Penyesuaian</h2>
                            <form method="POST" class="flex flex-col gap-4">
                                <input type="hidden" name="adjust_stock" value="1">

                                <div>
                                    <label
                                        class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Produk</label>
                                    <div class="custom-select-wrapper relative">
                                        <select name="product_id" required class="hidden">
                                            <option value="">Pilih Produk</option>
                                            <?php foreach ($products as $p): ?>
                                                <option value="<?= $p['id'] ?>">
                                                    <?= htmlspecialchars($p['name']) ?> (Stok: <?= $p['stock'] ?>
                                                    <?= $p['unit'] ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="button"
                                            class="custom-select-trigger w-full flex items-center justify-between rounded-lg border-slate-200 bg-slate-50 dark:bg-slate-800 dark:border-slate-700 text-slate-900 dark:text-white px-4 py-2.5 text-sm focus:ring-primary focus:border-primary transition-all text-left">
                                            <span class="selected-label truncate">Pilih Produk</span>
                                            <span
                                                class="material-icons-round text-slate-400 selected-icon transition-transform">expand_more</span>
                                        </button>
                                        <div
                                            class="custom-select-options hidden absolute z-[110] w-full mt-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-xl opacity-0 translate-y-2 transition-all duration-200 overflow-hidden">
                                            <!-- Search Input -->
                                            <div class="p-2 border-b border-slate-100 dark:border-slate-700">
                                                <div class="relative">
                                                    <span
                                                        class="absolute left-3 top-1/2 -translate-y-1/2 material-icons-round text-slate-400 text-sm">search</span>
                                                    <input type="text" placeholder="Cari produk..."
                                                        class="custom-select-search w-full pl-9 pr-3 py-2 text-xs bg-slate-50 dark:bg-slate-900 border-none rounded-lg focus:ring-1 focus:ring-primary/30 text-slate-700 dark:text-white"
                                                        onclick="event.stopPropagation()">
                                                </div>
                                            </div>
                                            <div class="max-h-60 overflow-y-auto p-2 dropdown-options-scroll">
                                                <div class="custom-option px-4 py-2.5 rounded-lg hover:bg-primary/5 hover:text-primary cursor-pointer transition-colors text-sm"
                                                    data-value="">Pilih Produk</div>
                                                <?php foreach ($products as $p): ?>
                                                    <div class="custom-option px-4 py-2.5 rounded-lg hover:bg-primary/5 hover:text-primary cursor-pointer transition-colors text-sm"
                                                        data-value="<?= $p['id'] ?>">
                                                        <div class="flex justify-between items-center w-full">
                                                            <span><?= htmlspecialchars($p['name']) ?></span>
                                                            <span
                                                                class="text-[10px] font-bold text-slate-400 bg-slate-100 dark:bg-slate-700 px-1.5 py-0.5 rounded"><?= $p['stock'] ?>
                                                                <?= $p['unit'] ?></span>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label
                                        class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Jumlah
                                        Penyesuaian</label>
                                    <input type="number" step="0.5" name="qty_adjusted" required
                                        placeholder="Misal: -2.5"
                                        class="w-full rounded-lg border-slate-200 bg-slate-50 dark:bg-slate-800 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-primary focus:border-primary">
                                    <p class="text-xs text-slate-500 mt-1">Gunakan tanda (-) untuk pengurangan stok
                                        (mati/susut).</p>
                                </div>

                                <div>
                                    <label
                                        class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Alasan</label>
                                    <div class="custom-select-wrapper relative">
                                        <select name="reason" required class="hidden">
                                            <option value="Shrinkage">Susut Air (Shrinkage)</option>
                                            <option value="Spoilage">Mati/Busuk (Spoilage)</option>
                                            <option value="Opname">Selisih Hitung (Opname)</option>
                                        </select>
                                        <button type="button"
                                            class="custom-select-trigger w-full flex items-center justify-between rounded-lg border-slate-200 bg-slate-50 dark:bg-slate-800 dark:border-slate-700 text-slate-900 dark:text-white px-4 py-2.5 text-sm focus:ring-primary focus:border-primary transition-all text-left">
                                            <span class="selected-label truncate">Susut Air (Shrinkage)</span>
                                            <span
                                                class="material-icons-round text-slate-400 selected-icon transition-transform">expand_more</span>
                                        </button>
                                        <div
                                            class="custom-select-options hidden absolute z-[110] w-full mt-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-xl opacity-0 translate-y-2 transition-all duration-200 overflow-hidden">
                                            <div class="p-2">
                                                <div class="custom-option px-4 py-2.5 rounded-lg hover:bg-primary/5 hover:text-primary cursor-pointer transition-colors text-sm bg-primary/10 text-primary font-bold"
                                                    data-value="Shrinkage">Susut Air (Shrinkage)</div>
                                                <div class="custom-option px-4 py-2.5 rounded-lg hover:bg-primary/5 hover:text-primary cursor-pointer transition-colors text-sm"
                                                    data-value="Spoilage">Mati/Busuk (Spoilage)</div>
                                                <div class="custom-option px-4 py-2.5 rounded-lg hover:bg-primary/5 hover:text-primary cursor-pointer transition-colors text-sm"
                                                    data-value="Opname">Selisih Hitung (Opname)</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <button type="submit"
                                    class="w-full bg-primary text-white px-6 py-2.5 rounded-lg hover:bg-blue-600 transition-colors font-medium mt-2">
                                    Simpan Penyesuaian
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- History Table -->
                    <div class="lg:col-span-2">
                        <div
                            class="bg-surface-light dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                            <div class="p-6 border-b border-slate-200 dark:border-slate-800">
                                <h2 class="text-xl font-bold text-slate-900 dark:text-white">Riwayat Penyesuaian</h2>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-sm text-slate-500 dark:text-slate-400">
                                    <thead
                                        class="bg-slate-50 dark:bg-slate-800/50 text-xs uppercase font-semibold text-slate-500">
                                        <tr>
                                            <th class="px-6 py-4">Tanggal</th>
                                            <th class="px-6 py-4">Produk</th>
                                            <th class="px-6 py-4">Qty</th>
                                            <th class="px-6 py-4">Alasan</th>
                                            <th class="px-6 py-4">Admin</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                                        <?php while ($row = $adjustments->fetch_assoc()): ?>
                                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <?= date('d/m/Y H:i', strtotime($row['date'])) ?>
                                                </td>
                                                <td class="px-6 py-4 font-medium text-slate-900 dark:text-white">
                                                    <?= htmlspecialchars($row['product_name']) ?>
                                                </td>
                                                <td
                                                    class="px-6 py-4 <?= $row['qty_adjusted'] < 0 ? 'text-red-500 font-bold' : 'text-green-500 font-bold' ?>">
                                                    <?= ($row['qty_adjusted'] > 0 ? '+' : '') . $row['qty_adjusted'] ?>
                                                    <?= $row['unit'] ?>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <span
                                                        class="px-2 py-1 rounded text-[10px] font-bold uppercase
                                                        <?= $row['reason'] == 'Shrinkage' ? 'bg-amber-100 text-amber-700' :
                                                            ($row['reason'] == 'Spoilage' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700') ?>">
                                                        <?= $row['reason'] ?>
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <?= htmlspecialchars($row['admin_name']) ?>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                        <?php if ($adjustments->num_rows == 0): ?>
                                            <tr>
                                                <td colspan="5" class="px-6 py-8 text-center text-slate-500">Belum ada
                                                    riwayat penyesuaian.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php include ROOT_PATH . "includes/admin/footer.php"; ?>
        </div>
    </main>
    <script>
        // Custom Select Search Logic
        document.querySelectorAll('.custom-select-search').forEach(search => {
            search.addEventListener('input', function (e) {
                const term = this.value.toLowerCase();
                const wrapper = this.closest('.custom-select-wrapper');
                const options = wrapper.querySelectorAll('.custom-option');

                options.forEach(opt => {
                    const text = opt.innerText.toLowerCase();
                    if (text.includes(term)) {
                        opt.classList.remove('hidden');
                    } else {
                        opt.classList.add('hidden');
                    }
                });
            });

            // Focus search input when dropdown opens
            const wrapper = search.closest('.custom-select-wrapper');
            const trigger = wrapper.querySelector('.custom-select-trigger');
            trigger.addEventListener('click', () => {
                setTimeout(() => {
                    if (!wrapper.querySelector('.custom-select-options').classList.contains('hidden')) {
                        search.focus();
                    }
                }, 100);
            });
        });
    </script>
</body>

</html>