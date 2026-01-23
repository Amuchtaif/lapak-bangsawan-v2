<?php
require("auth_session.php");
require_once dirname(__DIR__) . "/config/init.php";
require(ROOT_PATH . "includes/admin/notification_logic.php");

$current_user_id = $_SESSION['user_id'] ?? 0;
$action = $_GET['action'] ?? 'list';

// Handle Delete (Processing)
if ($action == 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Get image path first
    $stmt = $conn->prepare("SELECT proof_image FROM operational_expenses WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        if (!empty($row['proof_image'])) {
            $file_path = ROOT_PATH . $row['proof_image'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
    }

    if ($conn->query("DELETE FROM operational_expenses WHERE id=$id")) {
        $_SESSION['status_msg'] = "Pengeluaran berhasil dihapus.";
        $_SESSION['status_type'] = "success";
    } else {
        $_SESSION['status_msg'] = "Gagal menghapus data: " . $conn->error;
        $_SESSION['status_type'] = "error";
    }
    header("Location: expenses");
    exit();
}

// Handle Form Submission (Add)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_expense'])) {
    $date = $_POST['expense_date'];
    $category = $_POST['category'];
    $amount = str_replace(['Rp', '.', ' '], '', $_POST['amount']); // Clean currency format
    $amount = floatval($amount);
    $description = mysqli_real_escape_string($conn, $_POST['description']);

    $image_path = "";
    if (isset($_FILES['proof_image']) && $_FILES['proof_image']['error'] == 0) {
        $target_dir = ROOT_PATH . "assets/uploads/receipts/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_ext = strtolower(pathinfo($_FILES["proof_image"]["name"], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'pdf'];

        if (in_array($file_ext, $allowed)) {
            $new_filename = time() . '_' . rand(1000, 9999) . '.' . $file_ext;
            $target_file = $target_dir . $new_filename;

            if (move_uploaded_file($_FILES["proof_image"]["tmp_name"], $target_file)) {
                $image_path = "assets/uploads/receipts/" . $new_filename;
            }
        }
    }

    $stmt = $conn->prepare("INSERT INTO operational_expenses (expense_date, category, description, amount, proof_image, created_by) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssdsi", $date, $category, $description, $amount, $image_path, $current_user_id);

    if ($stmt->execute()) {
        $_SESSION['status_msg'] = "Data pengeluaran berhasil disimpan.";
        $_SESSION['status_type'] = "success";
        header("Location: expenses");
        exit();
    } else {
        $_SESSION['status_msg'] = "Gagal menyimpan data: " . $conn->error;
        $_SESSION['status_type'] = "error";
    }
}

// Pagination Logic
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$start = ($page - 1) * $limit;

// Search Logic
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$where = "";
if ($search) {
    $where = "WHERE description LIKE '%$search%' OR category LIKE '%$search%'";
}

// Total records for pagination
$total_res = $conn->query("SELECT COUNT(*) as count FROM operational_expenses $where");
$total_expenses = mysqli_fetch_assoc($total_res)['count'];
$total_pages = ceil($total_expenses / $limit);

// Fetch Summary Data (Current Month)
$current_month = date('m');
$current_year = date('Y');

// Total This Month
$q_total = "SELECT SUM(amount) as total FROM operational_expenses WHERE MONTH(expense_date) = '$current_month' AND YEAR(expense_date) = '$current_year'";
$total_this_month = mysqli_fetch_assoc($conn->query($q_total))['total'] ?? 0;

// Total Sewa & Utilitas This Month
$q_util = "SELECT SUM(amount) as total FROM operational_expenses WHERE category = 'Sewa & Utilitas' AND MONTH(expense_date) = '$current_month' AND YEAR(expense_date) = '$current_year'";
$total_utilitas = mysqli_fetch_assoc($conn->query($q_util))['total'] ?? 0;

// Kategori Terbesar
$q_lar = "SELECT category, SUM(amount) as total FROM operational_expenses WHERE MONTH(expense_date) = '$current_month' AND YEAR(expense_date) = '$current_year' GROUP BY category ORDER BY total DESC LIMIT 1";
$largest_cat_row = mysqli_fetch_assoc($conn->query($q_lar));
$largest_cat = $largest_cat_row['category'] ?? '-';
$largest_cat_total = $largest_cat_row['total'] ?? 0;

// Fetch Expenses List with Pagination
$query = "SELECT * FROM operational_expenses $where ORDER BY expense_date DESC, created_at DESC LIMIT $start, $limit";
$expenses_result = $conn->query($query);
?>

<!DOCTYPE html>
<html class="light" lang="id">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Biaya Operasional - Lapak Bangsawan</title>
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
    </style>
</head>

<body
    class="bg-background-light dark:bg-background-dark text-slate-600 dark:text-slate-300 font-display antialiased flex h-screen overflow-hidden">
    <?php include ROOT_PATH . "includes/admin/sidebar.php"; ?>

    <main class="flex-1 flex flex-col h-full relative overflow-hidden">
        <?php $page_title = "Biaya Operasional";
        include ROOT_PATH . "includes/admin/header.php"; ?>

        <div class="flex-1 overflow-y-auto p-4 md:p-8 scroll-smooth">
            <div class="max-w-7xl mx-auto space-y-8">

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
                            <p class="text-sm text-slate-500 dark:text-slate-400"><?= $_SESSION['status_msg'] ?></p>
                        </div>
                    </div>
                    <?php unset($_SESSION['status_msg']);
                    unset($_SESSION['status_type']); ?>
                <?php endif; ?>

                <?php if ($action == 'add'): ?>
                    <!-- ADD FORM VIEW -->
                    <div class="mb-6">
                        <a href="expenses"
                            class="inline-flex items-center gap-2 text-slate-500 hover:text-primary transition-colors mb-4">
                            <span class="material-icons-round text-sm">arrow_back</span>
                            Kembali ke Daftar
                        </a>
                        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Form Input Biaya Operasional</h1>
                        <p class="text-slate-500 dark:text-slate-400 mt-1">Catat setiap pengeluaran operasional dengan
                            detail dan akurat.</p>
                    </div>

                    <div
                        class="bg-white dark:bg-surface-dark rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
                        <form action="" method="POST" enctype="multipart/form-data" class="p-6 md:p-8">
                            <input type="hidden" name="add_expense" value="1">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                                <div>
                                    <label
                                        class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Tanggal
                                        Transaksi</label>
                                    <input type="date" name="expense_date" value="<?= date('Y-m-d') ?>" required
                                        class="w-full bg-slate-50 dark:bg-slate-900 border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary/50 transition-all">
                                </div>
                                <div>
                                    <label
                                        class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Kategori
                                        Pengeluaran</label>
                                    <select name="category" required
                                        class="w-full bg-slate-50 dark:bg-slate-900 border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary/50 transition-all">
                                        <option value="" disabled selected>Pilih Kategori</option>
                                        <option value="Pembelian Bahan Baku">Pembelian Bahan Baku</option>
                                        <option value="Sewa & Utilitas">Sewa & Utilitas</option>
                                        <option value="Gaji Karyawan">Gaji Karyawan</option>
                                        <option value="Marketing">Marketing</option>
                                        <option value="Perlengkapan">Perlengkapan</option>
                                        <option value="Lainnya">Lainnya</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-5">
                                <label
                                    class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Deskripsi
                                    Detail</label>
                                <textarea name="description" rows="3" placeholder="Contoh: Tagihan PLN"
                                    class="w-full bg-slate-50 dark:bg-slate-900 border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary/50 transition-all"></textarea>
                            </div>
                            <div class="mb-5">
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Nominal
                                    Biaya</label>
                                <div class="relative">
                                    <span
                                        class="absolute left-4 top-1/2 -translate-y-1/2 font-bold text-slate-400">Rp</span>
                                    <input type="text" id="amount-input" name="amount" required placeholder="0"
                                        class="w-full pl-12 pr-4 py-3 bg-slate-50 dark:bg-slate-900 border-slate-200 dark:border-slate-700 rounded-xl text-lg font-bold text-slate-900 dark:text-white focus:ring-2 focus:ring-primary/50 transition-all">
                                </div>
                                <p class="text-[10px] text-slate-400 mt-1.5">Masukkan angka saja tanpa titik atau koma.</p>
                            </div>
                            <div class="mb-6">
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Upload
                                    Bukti / Nota</label>
                                <div id="drop-zone"
                                    class="relative group cursor-pointer border-2 border-dashed border-slate-200 dark:border-slate-700 hover:border-primary/50 rounded-2xl p-6 transition-all bg-slate-50 dark:bg-slate-900/50">
                                    <input type="file" name="proof_image" id="file-input" accept=".jpg,.jpeg,.png,.pdf"
                                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                                    <div class="text-center">
                                        <div
                                            class="inline-flex p-3 bg-blue-50 dark:bg-blue-900/20 text-blue-600 rounded-full mb-3 group-hover:scale-110 transition-transform">
                                            <span class="material-icons-round">cloud_upload</span>
                                        </div>
                                        <p class="text-sm font-semibold text-slate-900 dark:text-white">Klik untuk upload
                                            atau tarik file ke sini</p>
                                        <p class="text-xs text-slate-500 mt-1">PNG, JPG atau PDF (Maks 5MB)</p>
                                        <p id="file-name" class="text-xs text-primary font-bold mt-2 hidden"></p>
                                    </div>
                                </div>
                            </div>
                            <div class="flex gap-3">
                                <a href="expenses"
                                    class="flex-1 px-6 py-3 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-xl font-bold hover:bg-slate-200 dark:hover:bg-slate-700 transition-all text-center">Batal</a>
                                <button type="submit"
                                    class="flex-2 grow-[2] px-6 py-3 bg-primary hover:bg-blue-700 text-white rounded-xl font-bold shadow-lg shadow-blue-500/20 transition-all">Simpan
                                    Pengeluaran</button>
                            </div>
                        </form>
                    </div>

                <?php else: ?>
                    <!-- LIST VIEW -->
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div>
                            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Daftar Biaya Operasional</h1>
                            <p class="text-slate-500 dark:text-slate-400 mt-1">Kelola dan pantau seluruh pengeluaran
                                operasional.</p>
                        </div>
                        <a href="?action=add"
                            class="inline-flex items-center gap-2 bg-primary hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl font-semibold shadow-lg shadow-blue-500/30 transition-all transform hover:-translate-y-0.5 active:scale-95">
                            <span class="material-icons-round">add</span>
                            Tambah Pengeluaran
                        </a>
                    </div>

                    <!-- Improved Summary Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Total Pengeluaran Card -->
                        <div
                            class="relative overflow-hidden group bg-white dark:bg-surface-dark p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm transition-all hover:shadow-md">
                            <div
                                class="absolute -right-4 -top-4 size-32 bg-primary/5 rounded-full blur-3xl group-hover:bg-primary/10 transition-colors">
                            </div>
                            <div class="flex items-start justify-between relative">
                                <div>
                                    <p class="text-sm font-bold text-slate-400 uppercase tracking-widest mb-1">Total
                                        Pengeluaran Bulan Ini</p>
                                    <h2 class="text-3xl font-black text-slate-900 dark:text-white">Rp
                                        <?= number_format($total_this_month, 0, ',', '.') ?>
                                    </h2>
                                    <div
                                        class="flex items-center gap-2 mt-3 p-1.5 px-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg inline-flex">
                                        <span class="material-icons-round text-primary text-sm">trending_up</span>
                                        <span class="text-xs font-bold text-primary">Rekapitulasi Aktif</span>
                                    </div>
                                </div>
                                <div class="p-4 bg-primary rounded-2xl text-white shadow-lg shadow-blue-500/40">
                                    <span class="material-icons-round text-3xl">account_balance_wallet</span>
                                </div>
                            </div>
                        </div>

                        <!-- Kategori Terbesar / Utilitas Card -->
                        <div
                            class="relative overflow-hidden group bg-white dark:bg-surface-dark p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm transition-all hover:shadow-md">
                            <div
                                class="absolute -right-4 -top-4 size-32 bg-cyan-500/5 rounded-full blur-3xl group-hover:bg-cyan-500/10 transition-colors">
                            </div>
                            <div class="flex items-start justify-between relative">
                                <div>
                                    <p class="text-sm font-bold text-slate-400 uppercase tracking-widest mb-1">Kategori
                                        Tertinggi (Global)</p>
                                    <h2 class="text-3xl font-black text-slate-900 dark:text-white truncate max-w-[200px]">
                                        <?= $largest_cat ?>
                                    </h2>
                                    <p class="text-xs font-medium text-slate-500 mt-2">Total: Rp
                                        <?= number_format($largest_cat_total, 0, ',', '.') ?>
                                    </p>
                                </div>
                                <div class="p-4 bg-cyan-500 rounded-2xl text-white shadow-lg shadow-cyan-500/40">
                                    <span class="material-icons-round text-3xl">insights</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Table Container -->
                    <div
                        class="bg-white dark:bg-surface-dark rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden flex flex-col">
                        <!-- Search Header -->
                        <div
                            class="p-4 md:p-6 border-b border-slate-200 dark:border-slate-800 flex flex-col md:flex-row gap-4 items-center justify-between bg-slate-50/30 dark:bg-slate-800/20">
                            <div class="relative w-full md:w-96 group">
                                <span
                                    class="absolute left-4 top-1/2 -translate-y-1/2 material-icons-round text-slate-400 group-focus-within:text-primary transition-colors">search</span>
                                <form action="" method="GET">
                                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
                                        placeholder="Cari deskripsi pengeluaran..."
                                        class="w-full pl-11 pr-5 py-2.5 bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all shadow-sm">
                                </form>
                            </div>
                        </div>

                        <!-- Table -->
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm border-collapse">
                                <thead>
                                    <tr
                                        class="bg-slate-50/80 dark:bg-slate-800/50 text-slate-500 font-bold uppercase text-[10px] tracking-widest border-b border-slate-200 dark:border-slate-700">
                                        <th class="px-6 py-4 w-16 text-center">No</th>
                                        <th class="px-6 py-4">Tanggal</th>
                                        <th class="px-6 py-4">Kategori</th>
                                        <th class="px-6 py-4">Deskripsi</th>
                                        <th class="px-6 py-4 text-center">Bukti</th>
                                        <th class="px-6 py-4 text-right">Jumlah</th>
                                        <th class="px-6 py-4 text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                    <?php if ($expenses_result->num_rows > 0): ?>
                                        <?php $no = $start + 1;
                                        while ($row = $expenses_result->fetch_assoc()): ?>
                                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors group">
                                                <td class="px-6 py-4 text-center text-slate-400 font-medium"><?= $no++ ?></td>
                                                <td class="px-6 py-4 font-semibold text-slate-700 dark:text-slate-300">
                                                    <?= date('d M Y', strtotime($row['expense_date'])) ?>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <?php
                                                    $cat = $row['category'];
                                                    $bg = 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400';
                                                    $icon = 'category';
                                                    if ($cat == 'Pembelian Bahan Baku') {
                                                        $bg = 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400';
                                                        $icon = 'shopping_cart';
                                                    }
                                                    if ($cat == 'Sewa & Utilitas') {
                                                        $bg = 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400';
                                                        $icon = 'flash_on';
                                                    }
                                                    if ($cat == 'Gaji Karyawan') {
                                                        $bg = 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400';
                                                        $icon = 'badge';
                                                    }
                                                    if ($cat == 'Marketing') {
                                                        $bg = 'bg-pink-100 text-pink-700 dark:bg-pink-900/30 dark:text-pink-400';
                                                        $icon = 'campaign';
                                                    }
                                                    if ($cat == 'Perlengkapan') {
                                                        $bg = 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400';
                                                        $icon = 'inventory_2';
                                                    }
                                                    ?>
                                                    <span
                                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold <?= $bg ?>">
                                                        <span class="material-icons-round text-xs"><?= $icon ?></span>
                                                        <?= $cat ?>
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 text-slate-500 dark:text-slate-400 max-w-xs truncate"
                                                    title="<?= htmlspecialchars($row['description']) ?>">
                                                    <?= htmlspecialchars($row['description']) ?>
                                                </td>
                                                <td class="px-6 py-4 text-center">
                                                    <?php if ($row['proof_image']): ?>
                                                        <a href="<?= BASE_URL . $row['proof_image'] ?>" target="_blank"
                                                            class="size-8 inline-flex items-center justify-center bg-slate-100 dark:bg-slate-800 text-slate-500 hover:text-primary rounded-lg transition-colors shadow-sm">
                                                            <span class="material-icons-round text-sm">visibility</span>
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="text-slate-300 dark:text-slate-700">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td
                                                    class="px-6 py-4 text-right font-black text-slate-900 dark:text-white whitespace-nowrap">
                                                    Rp <?= number_format($row['amount'], 0, ',', '.') ?>
                                                </td>
                                                <td class="px-6 py-4 text-center">
                                                    <button onclick="confirmDelete('expenses?action=delete&id=<?= $row['id'] ?>')"
                                                        class="p-2 text-slate-400 hover:text-red-500 transition-colors opacity-0 group-hover:opacity-100 focus:opacity-100">
                                                        <span class="material-icons-round">delete_outline</span>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="px-6 py-16 text-center">
                                                <div class="flex flex-col items-center gap-2 text-slate-400">
                                                    <span class="material-icons-round text-5xl opacity-20">cloud_off</span>
                                                    <p class="font-medium">Belum ada data pengeluaran ditemukan.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination Controls -->
                        <?php if ($total_pages > 0): ?>
                            <div
                                class="px-6 py-4 border-t border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row justify-between items-center gap-4 bg-slate-50/50 dark:bg-slate-900/10">
                                <div
                                    class="flex items-center gap-3 text-[11px] font-bold text-slate-400 uppercase tracking-widest">
                                    <span class="whitespace-nowrap">Tampilkan</span>
                                    <div class="relative">
                                        <select onchange="window.location.href='?limit='+this.value+'&page=1'"
                                            class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs py-1.5 pl-3 pr-8 focus:ring-2 focus:ring-primary/20 focus:border-primary shadow-sm text-slate-900 dark:text-white cursor-pointer transition-all h-8">
                                            <option value="5" <?= $limit == 5 ? 'selected' : '' ?>>5</option>
                                            <option value="10" <?= $limit == 10 ? 'selected' : '' ?>>10</option>
                                            <option value="20" <?= $limit == 20 ? 'selected' : '' ?>>20</option>
                                        </select>
                                    </div>
                                    <span class="hidden sm:inline opacity-30">|</span>
                                    <span class="hidden sm:inline whitespace-nowrap">HLM <?= $page ?> DARI
                                        <?= $total_pages ?></span>
                                </div>

                                <div class="flex gap-1">
                                    <?php if ($page > 1): ?>
                                        <a href="?page=<?= $page - 1 ?>&limit=<?= $limit ?>&search=<?= urlencode($search) ?>"
                                            class="p-2 border border-slate-200 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-700 transition-all flex items-center justify-center shadow-sm">
                                            <span class="material-icons-round text-sm">chevron_left</span>
                                        </a>
                                    <?php endif; ?>

                                    <div class="flex gap-1">
                                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                            <?php if ($i == 1 || $i == $total_pages || ($i >= $page - 1 && $i <= $page + 1)): ?>
                                                <a href="?page=<?= $i ?>&limit=<?= $limit ?>&search=<?= urlencode($search) ?>"
                                                    class="px-3 py-2 text-xs font-bold rounded-lg border transition-all shadow-sm <?= $i == $page ? 'bg-primary border-primary text-white shadow-primary/20' : 'bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-700' ?>"><?= $i ?></a>
                                            <?php elseif ($i == 2 || $i == $total_pages - 1): ?>
                                                <span class="px-2 py-2 text-slate-300">...</span>
                                            <?php endif; ?>
                                        <?php endfor; ?>
                                    </div>

                                    <?php if ($page < $total_pages): ?>
                                        <a href="?page=<?= $page + 1 ?>&limit=<?= $limit ?>&search=<?= urlencode($search) ?>"
                                            class="p-2 border border-slate-200 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-700 transition-all flex items-center justify-center shadow-sm">
                                            <span class="material-icons-round text-sm">chevron_right</span>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php include ROOT_PATH . "includes/admin/footer.php"; ?>
            </div>
        </div>
    </main>

    <script>
        // Currency Formatting
        const amountInput = document.getElementById('amount-input');
        if (amountInput) {
            amountInput.addEventListener('input', function (e) {
                let val = this.value.replace(/\D/g, '');
                if (val === '') {
                    this.value = '';
                    return;
                }
                this.value = new Intl.NumberFormat('id-ID').format(val);
            });
        }

        // File Selection Feedback & Drag/Drop
        const fileInput = document.getElementById('file-input');
        const fileNameDisplay = document.getElementById('file-name');
        const dropZone = document.getElementById('drop-zone');

        if (fileInput && dropZone) {
            fileInput.addEventListener('change', function () {
                if (this.files && this.files.length > 0) {
                    fileNameDisplay.textContent = 'Terpilih: ' + this.files[0].name;
                    fileNameDisplay.classList.remove('hidden');
                    dropZone.classList.add('border-primary', 'bg-blue-50/50', 'dark:bg-blue-900/10');
                } else {
                    fileNameDisplay.classList.add('hidden');
                    dropZone.classList.remove('border-primary', 'bg-blue-50/50', 'dark:bg-blue-900/10');
                }
            });

            ['dragover', 'dragenter'].forEach(eventName => {
                dropZone.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    dropZone.classList.add('border-primary', 'bg-blue-50/50', 'dark:bg-blue-900/10');
                }, false);
            });

            ['dragleave', 'dragend'].forEach(eventName => {
                dropZone.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    if (fileInput.files.length === 0) {
                        dropZone.classList.remove('border-primary', 'bg-blue-50/50', 'dark:bg-blue-900/10');
                    }
                }, false);
            });

            dropZone.addEventListener('drop', (e) => {
                e.preventDefault();
                if (e.dataTransfer.files.length > 0) {
                    fileInput.files = e.dataTransfer.files;
                    const event = new Event('change');
                    fileInput.dispatchEvent(event);
                }
            }, false);
        }
    </script>
</body>

</html>