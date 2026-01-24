<?php
require("auth_session.php");
require_once dirname(__DIR__) . "/config/init.php";
require(ROOT_PATH . "includes/admin/notification_logic.php");

$action = $_GET['action'] ?? 'list';

// Handle Action
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    if ($conn->query("DELETE FROM wholesale_rules WHERE id=$id")) {
        $_SESSION['status_msg'] = "Aturan grosir berhasil dihapus.";
        $_SESSION['status_type'] = "success";
    }
    header("Location: wholesale_rules");
    exit();
}

if (isset($_GET['toggle_id'])) {
    $id = intval($_GET['toggle_id']);
    $conn->query("UPDATE wholesale_rules SET is_active = 1 - is_active WHERE id=$id");
    header("Location: wholesale_rules");
    exit();
}

// Handle Form POST
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_rule'])) {
    $category_name = mysqli_real_escape_string($conn, $_POST['category_name']);
    $min_weight = floatval($_POST['min_weight_kg']);
    $discount = floatval(str_replace(['Rp', '.', ' '], '', $_POST['discount_per_kg']));
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if (isset($_POST['id']) && !empty($_POST['id'])) {
        $id = intval($_POST['id']);
        $stmt = $conn->prepare("UPDATE wholesale_rules SET category_name=?, min_weight_kg=?, discount_per_kg=? WHERE id=?");
        $stmt->bind_param("sddi", $category_name, $min_weight, $discount, $id);
    } else {
        $stmt = $conn->prepare("INSERT INTO wholesale_rules (category_name, min_weight_kg, discount_per_kg, is_active) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sddi", $category_name, $min_weight, $discount, $is_active);
    }

    if ($stmt->execute()) {
        $_SESSION['status_msg'] = "Aturan grosir berhasil disimpan.";
        $_SESSION['status_type'] = "success";
    } else {
        $_SESSION['status_msg'] = "Gagal menyimpan aturan: " . $conn->error;
        $_SESSION['status_type'] = "error";
    }
    header("Location: wholesale_rules");
    exit();
}

// Fetch Categories from Products (as requested)
$cat_res = $conn->query("SELECT DISTINCT c.name FROM products p JOIN categories c ON p.category_id = c.id ORDER BY c.name ASC");
$available_categories = [];
while ($row = $cat_res->fetch_assoc()) {
    $available_categories[] = $row['name'];
}

// Fetch Rules
$rules_res = $conn->query("SELECT * FROM wholesale_rules ORDER BY category_name ASC, min_weight_kg DESC");

// Edit rule logic
$edit_rule = null;
if (isset($_GET['edit_id'])) {
    $eid = intval($_GET['edit_id']);
    $edit_res = $conn->query("SELECT * FROM wholesale_rules WHERE id=$eid");
    $edit_rule = $edit_res->fetch_assoc();
}
?>

<!DOCTYPE html>
<html class="light" lang="id">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Manajemen Grosir - Lapak Bangsawan</title>
    <link rel="icon" href="<?= BASE_URL ?>assets/images/favicon-laba.png" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet" />
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet" />
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#0d59f2",
                        "surface-light": "#ffffff",
                        "surface-dark": "#1e293b",
                        "background-light": "#f5f6f8",
                        "background-dark": "#101622",
                    },
                    fontFamily: { "display": ["Inter", "sans-serif"] },
                },
            },
        }
    </script>
    <style>
        ::-webkit-scrollbar {
            width: 5px;
            height: 5px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        .dark ::-webkit-scrollbar-thumb {
            background: #475569;
        }

        .toggle-checkbox:checked+.toggle-label {
            background-color: #10b981;
        }

        .toggle-checkbox:checked+.toggle-label .toggle-dot {
            transform: translateX(100%);
        }
    </style>
</head>

<body
    class="bg-background-light dark:bg-background-dark text-slate-600 dark:text-slate-300 font-display antialiased flex h-screen overflow-hidden">
    <?php include ROOT_PATH . "includes/admin/sidebar.php"; ?>

    <main class="flex-1 flex flex-col h-full relative overflow-hidden">
        <?php $page_title = "Aturan Grosir";
        include ROOT_PATH . "includes/admin/header.php"; ?>

        <div class="flex-1 overflow-y-auto p-4 md:p-8 scroll-smooth flex flex-col gap-8">
            <div class="max-w-7xl mx-auto w-full space-y-8">

                <!-- Notification Area -->
                <?php if (isset($_SESSION['status_msg'])): ?>
                    <div
                        class="bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-700 rounded-lg p-4 flex items-start gap-3 shadow-sm auto-close-alert transition-opacity duration-500">
                        <span
                            class="material-icons-round <?= $_SESSION['status_type'] == 'success' ? 'text-green-500' : 'text-red-500' ?>">
                            <?= $_SESSION['status_type'] == 'success' ? 'check_circle' : 'error' ?>
                        </span>
                        <div>
                            <h3 class="font-medium text-slate-900 dark:text-white">
                                <?= $_SESSION['status_type'] == 'success' ? 'Berhasil' : 'Gagal' ?>
                            </h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400">
                                <?= $_SESSION['status_msg'] ?>
                            </p>
                        </div>
                    </div>
                    <?php unset($_SESSION['status_msg']);
                    unset($_SESSION['status_type']); ?>
                <?php endif; ?>

                <!-- Header Section -->
                <div class="mb-4">
                    <p class="text-xs font-bold text-primary uppercase tracking-widest mb-2">Dashboard / Aturan Grosir
                    </p>
                    <h1 class="text-3xl font-black text-slate-900 dark:text-white">Manajemen Aturan Grosir Dinamis</h1>
                    <p class="text-slate-500 dark:text-slate-400 mt-1">Kelola strategi diskon berbasis volume untuk
                        meningkatkan penjualan.</p>
                </div>

                <!-- Input Card -->
                <div
                    class="bg-white dark:bg-surface-dark rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden p-6 md:p-8">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="p-2 bg-blue-100 dark:bg-blue-900/30 text-primary rounded-lg">
                            <span class="material-icons-round">add_circle</span>
                        </div>
                        <h2 class="text-xl font-bold text-slate-900 dark:text-white">
                            <?= $edit_rule ? 'Edit Aturan' : 'Tambah Aturan Baru' ?>
                        </h2>
                    </div>

                    <form action="" method="POST" class="grid grid-cols-1 md:grid-cols-12 gap-6 items-end">
                        <input type="hidden" name="save_rule" value="1">
                        <?php if ($edit_rule): ?>
                            <input type="hidden" name="id" value="<?= $edit_rule['id'] ?>">
                        <?php endif; ?>

                        <div class="md:col-span-4">
                            <label
                                class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-3">Kategori
                                Produk</label>
                            <select name="category_name" required
                                class="w-full bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm focus:ring-4 focus:ring-primary/10 transition-all">
                                <option value="" disabled selected>Pilih Kategori</option>
                                <?php foreach ($available_categories as $cat): ?>
                                    <option value="<?= $cat ?>" <?= ($edit_rule && $edit_rule['category_name'] == $cat) ? 'selected' : '' ?>>
                                        <?= $cat ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="md:col-span-3">
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-3">Minimal Order</label>
                            <div class="relative">
                                <input type="number" step="0.01" name="min_weight_kg"
                                    value="<?= $edit_rule ? $edit_rule['min_weight_kg'] : '5.00' ?>"
                                    placeholder="Contoh: 10" required
                                    class="w-full pr-12 bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm font-bold text-slate-900 dark:text-white focus:ring-4 focus:ring-primary/10 transition-all">
                                <span
                                    class="absolute right-4 top-1/2 -translate-y-1/2 text-[10px] font-black text-slate-400 uppercase">Kg</span>
                            </div>
                        </div>

                        <div class="md:col-span-3">
                            <label
                                class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-3">Potongan Harga (Rp/Unit)</label>
                            <div class="relative">
                                <span
                                    class="absolute left-4 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400">Rp</span>
                                <input type="text" id="discount-input" name="discount_per_kg"
                                    value="<?= $edit_rule ? number_format($edit_rule['discount_per_kg'], 0, ',', '.') : '' ?>"
                                    placeholder="500" required
                                    class="w-full pl-10 bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm font-bold text-slate-900 dark:text-white focus:ring-4 focus:ring-primary/10 transition-all">
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <button type="submit"
                                class="w-full h-[50px] inline-flex items-center justify-center gap-2 bg-primary hover:bg-blue-700 text-white rounded-xl font-bold shadow-lg shadow-blue-500/30 transition-all transform active:scale-95">
                                <span class="material-icons-round text-sm">save</span>
                                <?= $edit_rule ? 'Update' : 'Simpan' ?>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Table List -->
                <div
                    class="bg-white dark:bg-surface-dark rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden flex flex-col">
                    <div
                        class="p-6 md:p-8 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/30 dark:bg-slate-800/20">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-blue-100 dark:bg-blue-900/30 text-primary rounded-lg">
                                <span class="material-icons-round">format_list_bulleted</span>
                            </div>
                            <h2 class="text-xl font-bold text-slate-900 dark:text-white">Daftar Aturan Aktif</h2>
                        </div>
                        <div class="relative group">
                            <span
                                class="absolute left-4 top-1/2 -translate-y-1/2 material-icons-round text-slate-400 text-sm">search</span>
                            <input type="text" placeholder="Cari aturan..."
                                class="pl-10 pr-4 py-2 bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-4 focus:ring-primary/10 transition-all w-64 shadow-sm">
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm border-collapse">
                            <thead>
                                <tr
                                    class="bg-slate-50/50 dark:bg-slate-800/50 text-slate-400 font-bold uppercase text-[10px] tracking-widest border-b border-slate-100 dark:border-slate-700">
                                    <th class="px-8 py-4">Kategori</th>
                                    <th class="px-8 py-4 text-center">Min. Order</th>
                                    <th class="px-8 py-4 text-center">Potongan (Rp/Kategori)</th>
                                    <th class="px-8 py-4 text-center">Status</th>
                                    <th class="px-8 py-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                <?php if ($rules_res->num_rows > 0): ?>
                                    <?php while ($row = $rules_res->fetch_assoc()): ?>
                                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-all group">
                                            <td class="px-8 py-5 font-bold text-slate-700 dark:text-slate-300">
                                                <?= $row['category_name'] ?>
                                            </td>
                                            <td class="px-8 py-5 text-center font-medium text-slate-500">
                                                <?= number_format($row['min_weight_kg'], 1) ?> kg
                                            </td>
                                            <td class="px-8 py-5 text-center font-black text-primary">Rp
                                                <?= number_format($row['discount_per_kg'], 0, ',', '.') ?>
                                            </td>
                                            <td class="px-8 py-5 text-center">
                                                <div class="flex items-center justify-center gap-2">
                                                    <label class="relative inline-flex items-center cursor-pointer">
                                                        <input type="checkbox"
                                                            onchange="window.location.href='?toggle_id=<?= $row['id'] ?>'"
                                                            class="sr-only toggle-checkbox" <?= $row['is_active'] ? 'checked' : '' ?>>
                                                        <div
                                                            class="w-11 h-6 bg-slate-200 dark:bg-slate-700 rounded-full transition-all toggle-label">
                                                            <div
                                                                class="w-5 h-5 bg-white rounded-full absolute left-0.5 top-0.5 shadow-sm transition-all toggle-dot">
                                                            </div>
                                                        </div>
                                                    </label>
                                                    <span
                                                        class="text-[10px] font-bold uppercase transition-colors <?= $row['is_active'] ? 'text-green-500' : 'text-slate-400' ?>">
                                                        <?= $row['is_active'] ? 'Aktif' : 'Non-Aktif' ?>
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="px-8 py-5 text-right">
                                                <div
                                                    class="flex items-center justify-end gap-2 transition-opacity">
                                                    <a href="?edit_id=<?= $row['id'] ?>"
                                                        class="p-2 text-slate-400 hover:text-primary transition-colors hover:bg-primary/5 rounded-lg">
                                                        <span class="material-icons-round text-lg">edit_note</span>
                                                    </a>
                                                    <button onclick="confirmDelete('?delete_id=<?= $row['id'] ?>')"
                                                        class="p-2 text-slate-400 hover:text-red-500 transition-colors hover:bg-red-500/5 rounded-lg">
                                                        <span class="material-icons-round text-lg">delete_sweep</span>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="px-8 py-16 text-center">
                                            <div class="flex flex-col items-center gap-3 text-slate-400">
                                                <span class="material-icons-round text-5xl opacity-20">rule</span>
                                                <p class="font-medium">Belum ada aturan grosir terdaftar.</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php include ROOT_PATH . "includes/admin/footer.php"; ?>
            </div>
        </div>
    </main>



    <script>
        // Currency Formatting
        const discountInput = document.getElementById('discount-input');
        if (discountInput) {
            discountInput.addEventListener('input', function (e) {
                let val = this.value.replace(/\D/g, '');
                if (val === '') {
                    this.value = '';
                    return;
                }
                this.value = new Intl.NumberFormat('id-ID').format(val);
            });
        }



        // Auto-close Alerts
        setTimeout(() => {
            const alerts = document.querySelectorAll('.auto-close-alert');
            alerts.forEach(alert => {
                alert.classList.add('opacity-0');
                setTimeout(() => alert.remove(), 500);
            });
        }, 3000);
    </script>
</body>

</html>