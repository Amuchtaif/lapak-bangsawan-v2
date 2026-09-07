<?php
require("auth_session.php");
require_once dirname(__DIR__) . "/config/init.php";
require(ROOT_PATH . "includes/admin/notification_logic.php");

// Preserve filters
$preservable_keys = ['search', 'status', 'page'];
$filter_params = [];
foreach ($preservable_keys as $key) {
    if (isset($_GET[$key]) && $_GET[$key] !== '') {
        $filter_params[$key] = $_GET[$key];
    }
}
$query_string = !empty($filter_params) ? http_build_query($filter_params) : '';
$filtered_redirect = "packages.php" . ($query_string ? '?' . $query_string : '');

// Delete package
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    // Since foreign key has ON DELETE CASCADE, deleting a package from products
    // will automatically delete its components mapping in package_items!
    if ($conn->query("DELETE FROM products WHERE id=$id AND is_package=1")) {
        log_activity("DELETE_PACKAGE", "Menghapus produk paket ID #$id");
        $_SESSION['status_msg'] = "Produk paket berhasil dihapus.";
        $_SESSION['status_type'] = "success";
    } else {
        $_SESSION['status_msg'] = "Gagal menghapus produk paket.";
        $_SESSION['status_type'] = "error";
    }
    header("Location: packages.php");
    exit();
}

$error = '';
$success = '';

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_package']) || isset($_POST['update_package'])) {
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $slug = mysqli_real_escape_string($conn, strtolower(str_replace(' ', '-', $name)));
        $category_id = intval($_POST['category_id']);
        $price = floatval(preg_replace('/[^0-9]/', '', $_POST['price']));
        $buy_price = floatval(preg_replace('/[^0-9]/', '', $_POST['buy_price']));
        $unit = mysqli_real_escape_string($conn, $_POST['unit'] ?? 'paket');
        $description = mysqli_real_escape_string($conn, $_POST['description']);
        $status = mysqli_real_escape_string($conn, $_POST['status'] ?? 'active');
        $is_package = 1;
        $stock = 0.00; // Physical stock in products is set to 0.00, calculation is dynamic
        
        $image_path = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $target_dir = "../assets/uploads/products/";
            if (!file_exists($target_dir))
                @mkdir($target_dir, 0777, true);
            $new_name = time() . '_' . basename($_FILES["image"]["name"]);
            $target_file = $target_dir . $new_name;
            if (compress_image($_FILES["image"]["tmp_name"], $target_file, 80)) {
                $image_path = "assets/uploads/products/" . $new_name;
            }
        }

        // Component products
        $components = $_POST['component_id'] ?? [];
        $quantities = $_POST['component_qty'] ?? [];

        if (empty($components)) {
            $error = "Produk paket harus memiliki setidaknya satu produk komponen.";
        } else {
            $conn->begin_transaction();
            try {
                if (isset($_POST['add_package'])) {
                    // Generate short_code
                    $short_code = 'PKG-' . date('ymd') . '-' . rand(100, 999);
                    $sql = "INSERT INTO products (category_id, short_code, name, slug, description, price, buy_price, stock, unit, image, is_package, status, weight) VALUES ($category_id, '$short_code', '$name', '$slug', '$description', $price, $buy_price, $stock, '$unit', '$image_path', $is_package, '$status', 1000)";
                    if (!$conn->query($sql)) {
                        throw new Exception("Gagal menambahkan produk paket: " . $conn->error);
                    }
                    $package_id = $conn->insert_id;
                    log_activity("ADD_PACKAGE", "Menambahkan produk paket baru: $name");
                } else {
                    $package_id = intval($_POST['id']);
                    $sql = "UPDATE products SET category_id=$category_id, name='$name', slug='$slug', description='$description', price=$price, buy_price=$buy_price, unit='$unit', status='$status'";
                    if ($image_path) {
                        $sql .= ", image='$image_path'";
                    }
                    $sql .= " WHERE id=$package_id AND is_package=1";
                    if (!$conn->query($sql)) {
                        throw new Exception("Gagal memperbarui produk paket: " . $conn->error);
                    }
                    // Delete old items mapping
                    $conn->query("DELETE FROM package_items WHERE package_id=$package_id");
                    log_activity("UPDATE_PACKAGE", "Memperbarui produk paket: $name (ID: $package_id)");
                }

                // Insert component products
                $stmt = $conn->prepare("INSERT INTO package_items (package_id, product_id, quantity) VALUES (?, ?, ?)");
                foreach ($components as $index => $prod_id) {
                    $prod_id = intval($prod_id);
                    $qty = floatval($quantities[$index]);
                    if ($prod_id <= 0 || $qty <= 0) continue;
                    $stmt->bind_param("iid", $package_id, $prod_id, $qty);
                    if (!$stmt->execute()) {
                        throw new Exception("Gagal menyimpan komponen paket: " . $stmt->error);
                    }
                }

                $conn->commit();
                $_SESSION['status_msg'] = isset($_POST['add_package']) ? "Produk paket berhasil ditambahkan." : "Produk paket berhasil diperbarui.";
                $_SESSION['status_type'] = "success";
                header("Location: packages.php");
                exit();
            } catch (Exception $e) {
                $conn->rollback();
                $error = "Terjadi kesalahan: " . $e->getMessage();
            }
        }
    }
}

// Fetch Categories
$cat_res = $conn->query("SELECT * FROM categories ORDER BY name ASC");
$categories = [];
while ($c = mysqli_fetch_assoc($cat_res)) {
    $categories[] = $c;
}

// Fetch Component Standard Products
$prod_res = $conn->query("SELECT id, name, price, buy_price, unit, stock, short_code FROM products WHERE is_package = 0 AND status = 'active' ORDER BY name ASC");
$standard_products = [];
while ($p = mysqli_fetch_assoc($prod_res)) {
    $standard_products[] = $p;
}

// Pagination
$limit = 10;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// Filter Logic
$where_clauses = ["products.is_package = 1"];
if (isset($_GET['status']) && !empty($_GET['status'])) {
    $status_filter = mysqli_real_escape_string($conn, $_GET['status']);
    $where_clauses[] = "products.status = '$status_filter'";
}
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_filter = mysqli_real_escape_string($conn, $_GET['search']);
    $where_clauses[] = "(products.name LIKE '%$search_filter%' OR products.description LIKE '%$search_filter%')";
}
$where = "WHERE " . implode(" AND ", $where_clauses);

// Count Total Packages
$count_query = "SELECT COUNT(*) as total FROM products $where";
$total_result = $conn->query($count_query);
$total_rows = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);

// Fetch Packages Data
$q = "SELECT products.*, categories.name as category_name 
      FROM products 
      LEFT JOIN categories ON products.category_id = categories.id 
      $where 
      ORDER BY products.id DESC 
      LIMIT $limit OFFSET $offset";
$packages_res = $conn->query($q);
?>
<!DOCTYPE html>
<html class="light" lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Manajemen Produk Paket - Lapak Bangsawan</title>
    <link rel="icon" href="<?= BASE_URL ?>assets/images/favicon-laba.png" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&amp;display=swap"
        rel="stylesheet" />
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet" />
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap"
        rel="stylesheet" />
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
                    borderRadius: { "DEFAULT": "0.25rem", "lg": "0.5rem", "xl": "0.75rem", "2xl": "1rem", "full": "9999px" },
                },
            },
        }
    </script>
    <style>
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }

        .dark ::-webkit-scrollbar-thumb {
            background: #475569;
        }
    </style>
</head>

<body
    class="bg-background-light dark:bg-background-dark text-slate-600 dark:text-slate-300 font-display transition-colors duration-200 antialiased lg:overflow-hidden lg:h-screen flex flex-col lg:flex-row">
    <?php include ROOT_PATH . "includes/admin/sidebar.php"; ?>
    <main class="flex-1 flex flex-col lg:h-full relative lg:overflow-hidden">
        <?php $page_title = "Produk Paket";
        include ROOT_PATH . "includes/admin/header.php"; ?>
        <div class="flex-1 lg:overflow-y-auto p-4 md:p-8 scroll-smooth">
            <div class="max-w-full mx-auto flex flex-col gap-6">

                <!-- Alert Messages -->
                <?php if (isset($_SESSION['status_msg'])): ?>
                    <div
                        class="bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-700 rounded-lg p-4 mb-6 flex items-start gap-3 shadow-sm auto-close-alert transition-opacity duration-500">
                        <span
                            class="material-icons-round <?php echo $_SESSION['status_type'] == 'success' ? 'text-green-500' : 'text-red-500'; ?>">
                            <?php echo $_SESSION['status_type'] == 'success' ? 'check_circle' : 'error'; ?>
                        </span>
                        <div>
                            <h3 class="font-medium text-slate-900 dark:text-white">
                                <?php echo $_SESSION['status_type'] == 'success' ? 'Berhasil' : 'Gagal'; ?>
                            </h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400"><?php echo $_SESSION['status_msg']; ?></p>
                        </div>
                    </div>
                    <?php unset($_SESSION['status_msg']);
                    unset($_SESSION['status_type']); ?>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div
                        class="bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-700 rounded-lg p-4 mb-6 flex items-start gap-3 shadow-sm transition-opacity duration-500">
                        <span class="material-icons-round text-red-500">error</span>
                        <div>
                            <h3 class="font-medium text-slate-900 dark:text-white">Gagal</h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400"><?php echo $error; ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Form View (Add/Edit) -->
                <?php if (isset($_GET['action']) && ($_GET['action'] == 'add' || $_GET['action'] == 'edit')):
                    $edit_row = null;
                    $edit_items = [];
                    if ($_GET['action'] == 'edit' && isset($_GET['id'])) {
                        $eid = intval($_GET['id']);
                        $edit_res = $conn->query("SELECT * FROM products WHERE id=$eid AND is_package=1");
                        if ($edit_res && $edit_res->num_rows > 0) {
                            $edit_row = $edit_res->fetch_assoc();
                            $edit_items = AppHelper::getPackageItems($conn, $eid);
                        }
                    }
                    ?>
                    <div class="bg-surface-light dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-slate-800 p-6">
                        <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-6">
                            <?php echo isset($edit_row) ? 'Ubah Produk Paket' : 'Tambah Produk Paket Baru'; ?>
                        </h2>
                        <form method="POST" enctype="multipart/form-data" class="flex flex-col gap-6" onsubmit="return validateForm();">
                            <?php if (isset($edit_row)): ?>
                                <input type="hidden" name="id" value="<?php echo $edit_row['id']; ?>">
                                <input type="hidden" name="update_package" value="1">
                            <?php else: ?>
                                <input type="hidden" name="add_package" value="1">
                            <?php endif; ?>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Nama Paket</label>
                                    <input type="text" name="name" required value="<?php echo $edit_row['name'] ?? ''; ?>"
                                        class="w-full rounded-lg border-slate-200 bg-slate-50 dark:bg-slate-800 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-primary focus:border-primary">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Kategori</label>
                                    <select name="category_id" required
                                        class="w-full rounded-lg border-slate-200 bg-slate-50 dark:bg-slate-800 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-primary focus:border-primary">
                                        <option value="">Pilih Kategori</option>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?php echo $cat['id']; ?>"
                                                <?php 
                                                if (isset($edit_row) && $edit_row['category_id'] == $cat['id']) {
                                                    echo 'selected';
                                                } elseif (!isset($edit_row) && $cat['slug'] == 'paket-promo') {
                                                    echo 'selected'; // Default select Paket Promo
                                                }
                                                ?>>
                                                <?php echo htmlspecialchars($cat['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Harga Jual Paket (Rp)</label>
                                    <input type="text" name="price" id="price-input" required
                                        value="<?php echo isset($edit_row['price']) ? number_format($edit_row['price'], 0, ',', '.') : ''; ?>"
                                        class="currency-input w-full rounded-lg border-slate-200 bg-slate-50 dark:bg-slate-800 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-primary focus:border-primary font-bold">
                                </div>
                                <div>
                                    <div class="flex justify-between items-center mb-2">
                                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Harga Beli / Modal Paket (Rp)</label>
                                        <button type="button" onclick="autoCalcModal()" class="text-xs text-primary hover:underline font-bold flex items-center gap-1">
                                            <span class="material-icons-round text-xs">calculate</span> Hitung Otomatis
                                        </button>
                                    </div>
                                    <input type="text" name="buy_price" id="buy-price-input" required
                                        value="<?php echo isset($edit_row['buy_price']) ? number_format($edit_row['buy_price'], 0, ',', '.') : ''; ?>"
                                        class="currency-input w-full rounded-lg border-slate-200 bg-slate-50 dark:bg-slate-800 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-primary focus:border-primary font-bold">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Unit Jual</label>
                                    <select name="unit" required
                                        class="w-full rounded-lg border-slate-200 bg-slate-50 dark:bg-slate-800 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-primary focus:border-primary">
                                        <option value="paket" <?php echo (isset($edit_row) && $edit_row['unit'] == 'paket') || !isset($edit_row) ? 'selected' : ''; ?>>paket</option>
                                        <option value="pcs" <?php echo (isset($edit_row) && $edit_row['unit'] == 'pcs') ? 'selected' : ''; ?>>pcs</option>
                                        <option value="box" <?php echo (isset($edit_row) && $edit_row['unit'] == 'box') ? 'selected' : ''; ?>>box</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Status Aktif</label>
                                    <select name="status" required
                                        class="w-full rounded-lg border-slate-200 bg-slate-50 dark:bg-slate-800 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-primary focus:border-primary">
                                        <option value="active" <?php echo (isset($edit_row) && $edit_row['status'] == 'active') || !isset($edit_row) ? 'selected' : ''; ?>>Aktif (Muncul di Toko)</option>
                                        <option value="inactive" <?php echo (isset($edit_row) && $edit_row['status'] == 'inactive') ? 'selected' : ''; ?>>Tidak Aktif (Disembunyikan)</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Deskripsi Paket</label>
                                <textarea name="description" rows="3"
                                    class="w-full rounded-lg border-slate-200 bg-slate-50 dark:bg-slate-800 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-primary focus:border-primary"><?php echo $edit_row['description'] ?? ''; ?></textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Gambar Paket</label>
                                <input type="file" name="image"
                                    class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20">
                                <?php if (isset($edit_row['image']) && $edit_row['image']): ?>
                                    <div class="mt-2 text-xs text-slate-500">Gambar Saat Ini:</div>
                                    <img src="../<?php echo $edit_row['image']; ?>"
                                        class="h-16 w-16 object-cover rounded mt-1 border border-slate-200">
                                <?php endif; ?>
                            </div>

                            <!-- Component Products Section -->
                            <div class="border-t border-slate-200 dark:border-slate-800 pt-6">
                                <div class="flex justify-between items-center mb-4">
                                    <h3 class="text-md font-bold text-slate-900 dark:text-white">Isi Komponen Paket</h3>
                                    <button type="button" onclick="addComponentRow()"
                                        class="px-4 py-2 bg-primary/10 text-primary hover:bg-primary hover:text-white rounded-lg text-xs font-bold transition-all flex items-center gap-1">
                                        <span class="material-icons-round text-sm">add</span> Tambah Komponen
                                    </button>
                                </div>

                                <div class="overflow-x-auto">
                                    <table class="w-full text-left text-sm text-slate-500 dark:text-slate-400">
                                        <thead class="bg-slate-50 dark:bg-slate-800/50 text-xs font-bold uppercase text-slate-500">
                                            <tr>
                                                <th class="px-4 py-3">Produk Komponen</th>
                                                <th class="px-4 py-3 w-40 text-center">Jumlah / Berat</th>
                                                <th class="px-4 py-3 w-20 text-center">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody id="components-container" class="divide-y divide-slate-100 dark:divide-slate-800">
                                            <!-- Dynamic component rows go here -->
                                        </tbody>
                                    </table>
                                </div>
                                <div id="empty-components-warn" class="text-center py-6 text-sm text-slate-400 hidden">
                                    Belum ada komponen produk ditambahkan. Klik "Tambah Komponen".
                                </div>
                            </div>

                            <div class="flex gap-4 pt-4 border-t border-slate-100 dark:border-slate-800">
                                <button type="submit"
                                    class="bg-primary text-white px-6 py-2.5 rounded-lg hover:bg-blue-600 transition-colors font-medium">Simpan Paket</button>
                                <a href="<?php echo $filtered_redirect; ?>"
                                    class="bg-slate-100 text-slate-700 px-6 py-2.5 rounded-lg hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300 transition-colors font-medium">Batal</a>
                            </div>
                        </form>
                    </div>

                <!-- Index View (List Packages) -->
                <?php else: ?>
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mt-2">
                        <div>
                            <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Manajemen Produk Paket</h2>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Kelola paket promo dan bundel produk toko Anda.</p>
                        </div>
                        <a href="?action=add<?php echo $query_string ? '&' . $query_string : ''; ?>"
                            class="flex items-center justify-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-white transition-colors hover:bg-blue-600 shadow-sm shadow-blue-500/30">
                            <span class="material-icons-round text-sm">add</span>
                            <span>Tambah Produk Paket</span>
                        </a>
                    </div>

                    <!-- Filter Bar -->
                    <div class="bg-surface-light dark:bg-surface-dark p-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
                        <form action="" method="GET" class="flex flex-col md:flex-row gap-4">
                            <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <!-- Search Input -->
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1 ml-1">Cari Paket</label>
                                    <input type="text" name="search" placeholder="Nama paket atau deskripsi..."
                                        value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>"
                                        class="w-full rounded-lg border-slate-200 bg-slate-50 dark:bg-slate-800/50 dark:border-slate-700 px-4 py-2 text-sm focus:ring-primary focus:border-primary">
                                </div>

                                <!-- Status Filter -->
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1 ml-1">Status Keaktifan</label>
                                    <select name="status" onchange="this.form.submit()"
                                        class="w-full rounded-lg border-slate-200 bg-slate-50 dark:bg-slate-800/50 dark:border-slate-700 px-4 py-2 text-sm focus:ring-primary focus:border-primary text-slate-700 dark:text-slate-300">
                                        <option value="">Semua Status</option>
                                        <option value="active" <?php echo (isset($_GET['status']) && $_GET['status'] == 'active') ? 'selected' : ''; ?>>Aktif</option>
                                        <option value="inactive" <?php echo (isset($_GET['status']) && $_GET['status'] == 'inactive') ? 'selected' : ''; ?>>Tidak Aktif</option>
                                    </select>
                                </div>
                            </div>
                            <div class="flex items-end gap-2">
                                <button type="submit"
                                    class="bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 px-6 py-2 rounded-lg text-sm font-medium transition-colors">
                                    Filter
                                </button>
                                <?php if (!empty($filter_params)): ?>
                                    <a href="packages.php"
                                        class="bg-red-50 hover:bg-red-100 text-red-600 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                        Reset
                                    </a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>

                    <!-- Table List -->
                    <div class="bg-surface-light dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm text-slate-500 dark:text-slate-400">
                                <thead class="bg-slate-50 dark:bg-slate-800/50 text-xs uppercase font-semibold text-slate-500">
                                    <tr>
                                        <th class="px-6 py-4 w-12 text-center">No</th>
                                        <th class="px-6 py-4">Nama Paket</th>
                                        <th class="px-6 py-4">Kategori</th>
                                        <th class="px-6 py-4">Isi Komponen Paket</th>
                                        <th class="px-6 py-4 text-right">Harga Jual</th>
                                        <th class="px-6 py-4 text-center">Stok (Dinamis)</th>
                                        <th class="px-6 py-4 text-center">Status</th>
                                        <th class="px-6 py-4 text-center w-28">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                                    <?php 
                                    $no = $offset + 1;
                                    if ($packages_res && $packages_res->num_rows > 0):
                                        while ($row = $packages_res->fetch_assoc()):
                                            $dyn_stock = AppHelper::getPackageStock($conn, $row['id']);
                                            $items = AppHelper::getPackageItems($conn, $row['id']);
                                            ?>
                                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                                                <td class="px-6 py-4 text-center font-medium"><?php echo $no++; ?></td>
                                                <td class="px-6 py-4">
                                                    <div class="flex items-center gap-3">
                                                        <div class="w-10 h-10 rounded-lg bg-slate-100 dark:bg-slate-700 shrink-0 overflow-hidden border border-slate-200 dark:border-slate-800">
                                                            <?php if ($row['image']): ?>
                                                                <img class="w-full h-full object-cover" src="../<?php echo htmlspecialchars($row['image']); ?>" alt="">
                                                            <?php else: ?>
                                                                <span class="material-icons-round text-slate-400 text-lg w-full h-full flex items-center justify-center">image</span>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div>
                                                            <div class="font-medium text-slate-900 dark:text-white"><?php echo htmlspecialchars($row['name']); ?></div>
                                                            <div class="text-xs text-slate-400"><?php echo htmlspecialchars($row['short_code']); ?></div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4"><?php echo htmlspecialchars($row['category_name']); ?></td>
                                                <td class="px-6 py-4">
                                                    <ul class="list-disc pl-4 space-y-0.5 text-xs text-slate-500 dark:text-slate-400">
                                                        <?php foreach ($items as $item): ?>
                                                            <li>
                                                                <strong><?php echo htmlspecialchars($item['name']); ?></strong>
                                                                (<?php echo floatval($item['quantity']) . ' ' . $item['unit']; ?>)
                                                            </li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                </td>
                                                <td class="px-6 py-4 text-right font-bold text-slate-900 dark:text-white">
                                                    Rp <?php echo number_format($row['price'], 0, ',', '.'); ?>
                                                    <div class="text-[10px] font-normal text-slate-400">Modal: Rp <?php echo number_format($row['buy_price'], 0, ',', '.'); ?></div>
                                                </td>
                                                <td class="px-6 py-4 text-center font-semibold">
                                                    <span class="<?php echo $dyn_stock > 0 ? 'text-green-600 dark:text-green-400' : 'text-red-500'; ?>">
                                                        <?php echo $dyn_stock; ?> <?php echo htmlspecialchars($row['unit']); ?>
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 text-center">
                                                    <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-bold <?php echo $row['status'] == 'active' ? 'bg-green-100 text-green-800' : 'bg-slate-100 text-slate-800'; ?>">
                                                        <?php echo $row['status'] == 'active' ? 'Aktif' : 'Nonaktif'; ?>
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 text-center">
                                                    <div class="flex justify-center gap-2">
                                                        <a href="?action=edit&id=<?php echo $row['id']; ?><?php echo $query_string ? '&' . $query_string : ''; ?>"
                                                            class="p-1 text-slate-400 hover:text-primary transition-colors" title="Ubah">
                                                            <span class="material-icons-round text-lg">edit</span>
                                                        </a>
                                                        <a href="?action=delete&id=<?php echo $row['id']; ?>"
                                                            onclick="return confirm('Apakah Anda yakin ingin menghapus produk paket ini?');"
                                                            class="p-1 text-slate-400 hover:text-red-500 transition-colors" title="Hapus">
                                                            <span class="material-icons-round text-lg">delete</span>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="px-6 py-10 text-center text-slate-500 dark:text-slate-400">
                                                <span class="material-icons-round text-4xl text-slate-300 mb-2">inventory_2</span>
                                                <p>Belum ada produk paket terdaftar.</p>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination Navigation -->
                        <?php if ($total_pages > 1): ?>
                            <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 flex justify-between items-center">
                                <span class="text-xs text-slate-500">Halaman <?php echo $page; ?> dari <?php echo $total_pages; ?></span>
                                <div class="flex gap-1">
                                    <?php if ($page > 1): ?>
                                        <a href="?page=<?php echo $page-1; ?><?php echo $query_string ? '&' . $query_string : ''; ?>"
                                            class="p-2 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 rounded text-slate-500 hover:bg-slate-50">&laquo;</a>
                                    <?php endif; ?>
                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <a href="?page=<?php echo $i; ?><?php echo $query_string ? '&' . $query_string : ''; ?>"
                                            class="px-3 py-2 border rounded <?php echo $i == $page ? 'bg-primary text-white border-primary' : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-500 hover:bg-slate-50'; ?>"><?php echo $i; ?></a>
                                    <?php endfor; ?>
                                    <?php if ($page < $total_pages): ?>
                                        <a href="?page=<?php echo $page+1; ?><?php echo $query_string ? '&' . $query_string : ''; ?>"
                                            class="p-2 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 rounded text-slate-500 hover:bg-slate-50">&raquo;</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

            </div>
            <?php include ROOT_PATH . "includes/admin/footer.php"; ?>
        </div>
    </main>

    <!-- Component Row Template -->
    <template id="component-row-template">
        <tr class="component-row">
            <td class="px-4 py-3 align-middle">
                <select name="component_id[]" required onchange="calculateDefaultModal()"
                    class="component-select w-full rounded-lg border-slate-200 bg-slate-50 dark:bg-slate-800 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-primary focus:border-primary text-sm">
                    <option value="" data-buy-price="0">-- Pilih Produk Komponen --</option>
                    <?php foreach ($standard_products as $sp): ?>
                        <option value="<?php echo $sp['id']; ?>" data-buy-price="<?php echo $sp['buy_price']; ?>" data-unit="<?php echo htmlspecialchars($sp['unit']); ?>">
                            <?php echo htmlspecialchars($sp['name']); ?> (Harga Modal: Rp <?php echo number_format($sp['buy_price'], 0, ',', '.'); ?>/<?php echo $sp['unit']; ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
            <td class="px-4 py-3 align-middle text-center">
                <div class="inline-flex items-center gap-2">
                    <input type="number" name="component_qty[]" step="0.01" min="0.01" required value="1.00"
                        oninput="calculateDefaultModal()"
                        class="w-24 rounded-lg border-slate-200 bg-slate-50 dark:bg-slate-800 dark:border-slate-700 text-slate-900 dark:text-white text-sm text-center focus:ring-primary focus:border-primary">
                    <span class="component-unit text-xs text-slate-500 font-bold uppercase w-10">kg</span>
                </div>
            </td>
            <td class="px-4 py-3 align-middle text-center">
                <button type="button" onclick="removeComponentRow(this)"
                    class="text-slate-400 hover:text-red-500 transition-colors p-1">
                    <span class="material-icons-round">cancel</span>
                </button>
            </td>
        </tr>
    </template>

    <!-- JS Form & Component Select Helper -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Setup Currency Formatter for Inputs
            setupCurrencyInputs();

            // Load components if editing
            <?php if (isset($edit_row) && !empty($edit_items)): ?>
                <?php foreach ($edit_items as $ei): ?>
                    addComponentRow(<?php echo $ei['product_id']; ?>, <?php echo $ei['quantity']; ?>);
                <?php endforeach; ?>
            <?php else: ?>
                // Default add 1 empty row on new form
                if (document.getElementById('components-container')) {
                    addComponentRow();
                }
            <?php endif; ?>
            
            checkEmptyState();
        });

        function setupCurrencyInputs() {
            document.querySelectorAll('.currency-input').forEach(input => {
                input.addEventListener('input', function(e) {
                    let value = this.value.replace(/[^0-9]/g, '');
                    if (value) {
                        this.value = new Intl.NumberFormat('id-ID').format(value);
                    } else {
                        this.value = '';
                    }
                });
            });
        }

        function addComponentRow(selectedProductId = null, selectedQty = null) {
            const container = document.getElementById('components-container');
            const template = document.getElementById('component-row-template');
            if (!container || !template) return;

            const clone = template.content.cloneNode(true);
            const row = clone.querySelector('.component-row');
            
            container.appendChild(row);

            const select = row.querySelector('.component-select');
            const qtyInput = row.querySelector('input[name="component_qty[]"]');
            const unitLabel = row.querySelector('.component-unit');

            // Set select change event to update unit label
            select.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                if (selectedOption) {
                    const unit = selectedOption.getAttribute('data-unit') || 'kg';
                    unitLabel.innerText = unit;
                } else {
                    unitLabel.innerText = 'kg';
                }
            });

            // Set initial selected values if editing
            if (selectedProductId) {
                select.value = selectedProductId;
                // trigger change event to populate unit label
                select.dispatchEvent(new Event('change'));
            }
            if (selectedQty) {
                qtyInput.value = selectedQty;
            }

            checkEmptyState();
            calculateDefaultModal();
        }

        function removeComponentRow(btn) {
            const row = btn.closest('.component-row');
            if (row) {
                row.remove();
            }
            checkEmptyState();
            calculateDefaultModal();
        }

        function checkEmptyState() {
            const container = document.getElementById('components-container');
            const warn = document.getElementById('empty-components-warn');
            if (!container || !warn) return;

            if (container.querySelectorAll('.component-row').length === 0) {
                warn.classList.remove('hidden');
            } else {
                warn.classList.add('hidden');
            }
        }

        function calculateDefaultModal() {
            const container = document.getElementById('components-container');
            if (!container) return 0;

            let totalModal = 0;
            container.querySelectorAll('.component-row').forEach(row => {
                const select = row.querySelector('.component-select');
                const qtyInput = row.querySelector('input[name="component_qty[]"]');
                
                const selectedOption = select.options[select.selectedIndex];
                if (selectedOption && selectedOption.value) {
                    const buyPrice = parseFloat(selectedOption.getAttribute('data-buy-price')) || 0;
                    const qty = parseFloat(qtyInput.value) || 0;
                    totalModal += buyPrice * qty;
                }
            });

            return totalModal;
        }

        function autoCalcModal() {
            const calculated = calculateDefaultModal();
            const modalInput = document.getElementById('buy-price-input');
            if (modalInput) {
                modalInput.value = new Intl.NumberFormat('id-ID').format(calculated);
            }
        }

        function validateForm() {
            const container = document.getElementById('components-container');
            if (!container) return true;

            const rows = container.querySelectorAll('.component-row');
            if (rows.length === 0) {
                alert('Silakan tambahkan setidaknya satu produk komponen!');
                return false;
            }

            // Ensure no duplicate products are selected
            const selectedProductIds = [];
            let isValid = true;

            rows.forEach(row => {
                const select = row.querySelector('.component-select');
                const val = select.value;
                if (!val) {
                    alert('Harap pilih produk komponen pada semua baris!');
                    isValid = false;
                    return false;
                }
                if (selectedProductIds.includes(val)) {
                    alert('Produk komponen tidak boleh duplikat!');
                    isValid = false;
                    return false;
                }
                selectedProductIds.push(val);
            });

            return isValid;
        }
    </script>
</body>

</html>
