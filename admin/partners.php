<?php
require("auth_session.php");
require_once dirname(__DIR__) . "/config/init.php";

// Constants
$base_url = "partners.php";

// Notification Setup
if (isset($_GET['ajax'])) {
    ob_start();
}

// --- Logic ---

// Handle Delete
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    // Check for dependencies (products)
    $check = $conn->query("SELECT counting_products FROM (SELECT COUNT(*) as counting_products FROM products WHERE partner_id=$id) as tmp");
    $count = $check->fetch_assoc()['counting_products'];
    
    if ($count > 0) {
        $_SESSION['status_msg'] = "Gagal menghapus mitra. Masih ada $count produk yang terhubung.";
        $_SESSION['status_type'] = "error";
    } else {
        if ($conn->query("DELETE FROM partners WHERE id=$id")) {
            $_SESSION['status_msg'] = "Mitra berhasil dihapus.";
            $_SESSION['status_type'] = "success";
        } else {
            $_SESSION['status_msg'] = "Gagal menghapus mitra: " . $conn->error;
            $_SESSION['status_type'] = "error";
        }
    }
    header("Location: $base_url");
    exit();
}

// Handle POST (Add/Edit)
$edit_row = null;
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $contact = mysqli_real_escape_string($conn, $_POST['contact']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    if (isset($_POST['add_partner'])) {
        $sql = "INSERT INTO partners (name, contact, address, status) VALUES ('$name', '$contact', '$address', '$status')";
        if ($conn->query($sql)) {
            $_SESSION['status_msg'] = "Mitra baru berhasil ditambahkan.";
            $_SESSION['status_type'] = "success";
            header("Location: $base_url");
            exit();
        } else {
            $error = "Error: " . $conn->error;
        }
    } elseif (isset($_POST['update_partner'])) {
        $id = intval($_POST['id']);
        $sql = "UPDATE partners SET name='$name', contact='$contact', address='$address', status='$status' WHERE id=$id";
        if ($conn->query($sql)) {
            $_SESSION['status_msg'] = "Data mitra berhasil diperbarui.";
            $_SESSION['status_type'] = "success";
            header("Location: $base_url");
            exit();
        } else {
            $error = "Error: " . $conn->error;
        }
    }
}

// Fetch Data for Edit
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $edit_row = $conn->query("SELECT * FROM partners WHERE id=$id")->fetch_assoc();
}

// Fetch All Partners with Stats
// Calculates Debt: Sum of (Products Sold * Buy Price) for this partner
// Note: This relies on matching product names if ID linkage in order_items is missing, 
// OR robustly linking products. Since schema varies, we'll try a join.
$sql_partners = "
    SELECT p.*, 
    (SELECT COUNT(*) FROM products WHERE partner_id = p.id) as product_count,
    (
        SELECT COALESCE(SUM(oi.subtotal), 0) -- This is gross sales, not profit/debt directly without cost
        FROM order_items oi
        JOIN orders o ON oi.order_id = o.id
        JOIN products prod ON oi.product_name = prod.name -- Join by name as per schema
        WHERE prod.partner_id = p.id AND o.status = 'completed'
    ) as total_sales_revenue,
    (
        SELECT COALESCE(SUM(oi.weight * prod.buy_price), 0) -- Approx Debt (weight * modal)
        FROM order_items oi
        JOIN orders o ON oi.order_id = o.id
        JOIN products prod ON oi.product_name = prod.name
        WHERE prod.partner_id = p.id AND o.status = 'completed'
    ) as total_debt_calculated
    FROM partners p
    ORDER BY p.id DESC
";
$result = $conn->query($sql_partners);

?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Manajemen Mitra - Lapak Bangsawan</title>
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
        <?php $page_title = "Mitra Laba"; include ROOT_PATH . "includes/admin/header.php"; ?>
        
        <div class="flex-1 overflow-y-auto p-4 md:p-8 scroll-smooth">
            <div class="max-w-7xl mx-auto flex flex-col gap-6">

                <!-- Notifications -->
                <?php if (isset($_SESSION['status_msg'])): ?>
                    <div class="bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-700 rounded-lg p-4 mb-2 flex items-start gap-3 shadow-sm auto-close-alert transition-opacity duration-500">
                        <span class="material-icons-round <?php echo $_SESSION['status_type'] == 'success' ? 'text-green-500' : 'text-red-500'; ?>">
                            <?php echo $_SESSION['status_type'] == 'success' ? 'check_circle' : 'error'; ?>
                        </span>
                        <div>
                            <h3 class="font-medium text-slate-900 dark:text-white">
                                <?php echo $_SESSION['status_type'] == 'success' ? 'Berhasil' : 'Gagal'; ?>
                            </h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400"><?php echo $_SESSION['status_msg']; ?></p>
                        </div>
                    </div>
                    <?php unset($_SESSION['status_msg']); unset($_SESSION['status_type']); ?>
                <?php endif; ?>

                <!-- Form Section -->
                <?php if (isset($_GET['action']) && ($_GET['action'] == 'add' || $_GET['action'] == 'edit')): ?>
                    <div class="bg-surface-light dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm">
                        <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-6">
                            <?php echo isset($edit_row) ? 'Edit Data Mitra' : 'Tambah Mitra Baru'; ?>
                        </h2>
                        <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <?php if (isset($edit_row)): ?>
                                <input type="hidden" name="id" value="<?php echo $edit_row['id']; ?>">
                                <input type="hidden" name="update_partner" value="1">
                            <?php else: ?>
                                <input type="hidden" name="add_partner" value="1">
                            <?php endif; ?>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Nama Mitra</label>
                                <input type="text" name="name" required value="<?php echo $edit_row['name'] ?? ''; ?>"
                                    class="w-full rounded-lg border-slate-200 bg-slate-50 dark:bg-slate-800 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-primary focus:border-primary">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Kontak / HP</label>
                                <input type="text" name="contact" value="<?php echo $edit_row['contact'] ?? ''; ?>"
                                    class="w-full rounded-lg border-slate-200 bg-slate-50 dark:bg-slate-800 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-primary focus:border-primary">
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Alamat Lengkap</label>
                                <textarea name="address" rows="3"
                                    class="w-full rounded-lg border-slate-200 bg-slate-50 dark:bg-slate-800 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-primary focus:border-primary"><?php echo $edit_row['address'] ?? ''; ?></textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Status</label>
                                <select name="status" class="w-full rounded-lg border-slate-200 bg-slate-50 dark:bg-slate-800 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-primary focus:border-primary">
                                    <option value="active" <?php echo (isset($edit_row) && $edit_row['status'] == 'active') ? 'selected' : ''; ?>>Aktif</option>
                                    <option value="inactive" <?php echo (isset($edit_row) && $edit_row['status'] == 'inactive') ? 'selected' : ''; ?>>Non-Aktif</option>
                                </select>
                            </div>

                            <div class="md:col-span-2 flex gap-4 mt-4">
                                <button type="submit" class="bg-primary text-white px-6 py-2.5 rounded-lg hover:bg-blue-600 transition-colors font-medium shadow-lg shadow-blue-500/30">
                                    Simpan Data
                                </button>
                                <a href="<?php echo $base_url; ?>" class="bg-slate-100 text-slate-700 px-6 py-2.5 rounded-lg hover:bg-slate-200 transition-colors font-medium">
                                    Batal
                                </a>
                            </div>
                        </form>
                    </div>
                <?php else: ?>
                    
                    <!-- Header Actions -->
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div>
                            <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Daftar Mitra Konsinyasi</h2>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Kelola data pemiilik produk titipan.</p>
                        </div>
                        <a href="?action=add" class="flex items-center gap-2 bg-primary text-white px-4 py-2.5 rounded-lg hover:bg-blue-600 transition-colors font-medium shadow-md shadow-blue-500/20">
                            <span class="material-icons-round text-sm">add</span> Tambah Mitra
                        </a>
                    </div>

                    <!-- Table -->
                    <div class="bg-surface-light dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm text-slate-500 dark:text-slate-400">
                                <thead class="bg-slate-50 dark:bg-slate-800/50 text-xs uppercase font-semibold text-slate-500">
                                    <tr>
                                        <th class="px-6 py-4">No</th>
                                        <th class="px-6 py-4">Nama Mitra</th>
                                        <th class="px-6 py-4">Kontak & Alamat</th>
                                        <th class="px-6 py-4 text-center">Produk</th>
                                        <th class="px-6 py-4 text-right">Total Hutang (Est)</th>
                                        <th class="px-6 py-4 text-center">Status</th>
                                        <th class="px-6 py-4 text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                                    <?php if ($result && $result->num_rows > 0): ?>
                                        <?php $no = 1; while($row = $result->fetch_assoc()): ?>
                                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                                                <td class="px-6 py-4 font-medium text-slate-900 dark:text-white">
                                                    <?php echo $no++; ?>
                                                </td>
                                                <td class="px-6 py-4 font-medium text-slate-900 dark:text-white">
                                                    <?php echo htmlspecialchars($row['name']); ?>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <div class="flex flex-col">
                                                        <span class="text-slate-900 dark:text-white font-medium"><?php echo htmlspecialchars($row['contact']); ?></span>
                                                        <span class="text-xs text-slate-500 truncate max-w-[200px]" title="<?php echo htmlspecialchars($row['address']); ?>"><?php echo htmlspecialchars($row['address']); ?></span>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 text-center">
                                                    <span class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                                                        <?php echo $row['product_count']; ?> Item
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 text-right font-mono text-slate-900 dark:text-white">
                                                    Rp <?php echo number_format($row['total_debt_calculated'], 0, ',', '.'); ?>
                                                </td>
                                                <td class="px-6 py-4 text-center">
                                                    <?php if($row['status'] == 'active'): ?>
                                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                                                            <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Aktif
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-300">
                                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-500"></span> Non-Aktif
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="px-6 py-4 text-right">
                                                    <div class="flex items-center justify-end gap-2">
                                                        <a href="?action=edit&id=<?php echo $row['id']; ?>" class="p-2 text-slate-400 hover:text-primary hover:bg-slate-100 rounded-lg transition-colors">
                                                            <span class="material-icons-round text-lg">edit</span>
                                                        </a>
                                                        <a href="#" onclick="confirmDelete('?action=delete&id=<?php echo $row['id']; ?>'); return false;" class="p-2 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors">
                                                            <span class="material-icons-round text-lg">delete</span>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="px-6 py-8 text-center text-slate-500">Belum ada data mitra.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </main>
    <?php include ROOT_PATH . "includes/admin/modals.php"; ?>
</body>
</html>
