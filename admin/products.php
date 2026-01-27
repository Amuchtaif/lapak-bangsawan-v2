<?php
require("auth_session.php");
require_once dirname(__DIR__) . "/config/init.php";
require(ROOT_PATH . "includes/admin/notification_logic.php");

if (isset($_GET['ajax'])) {
    ob_start();
}

// Preserve filters/pagination in URLs
$preservable_keys = ['category_id', 'stock_status', 'search', 'sort', 'limit', 'page'];
$filter_params = [];
foreach ($preservable_keys as $key) {
    if (isset($_GET[$key]) && $_GET[$key] !== '') {
        $filter_params[$key] = $_GET[$key];
    }
}
$query_string = !empty($filter_params) ? http_build_query($filter_params) : '';
$filtered_redirect = "products" . ($query_string ? '?' . $query_string : '');

// Base query for pagination (filters without page/limit)
$base_filter_params = $filter_params;
unset($base_filter_params['page']);
unset($base_filter_params['limit']);
$base_query = !empty($base_filter_params) ? http_build_query($base_filter_params) : '';


// Delete
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    if ($conn->query("DELETE FROM products WHERE id=$id")) {
        $_SESSION['status_msg'] = "Product deleted successfully.";
        $_SESSION['status_type'] = "success";
    } else {
        $_SESSION['status_msg'] = "Failed to delete product.";
        $_SESSION['status_type'] = "error";
    }
    header("Location: $filtered_redirect");
    exit();
}

// POST
$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_product']) || isset($_POST['update_product'])) {
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $slug = mysqli_real_escape_string($conn, strtolower(str_replace(' ', '-', $name)));
        $category_id = intval($_POST['category_id']);
        $price = floatval(preg_replace('/[^0-9]/', '', $_POST['price']));
        $buy_price = floatval(preg_replace('/[^0-9]/', '', $_POST['buy_price']));
        $stock = floatval($_POST['stock']);
        $unit = mysqli_real_escape_string($conn, $_POST['unit'] ?? 'kg');
        $description = mysqli_real_escape_string($conn, $_POST['description']);

        $short_code = mysqli_real_escape_string($conn, $_POST['short_code']);
        $image_path = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $target_dir = "../assets/uploads/products/";
            if (!file_exists($target_dir))
                @mkdir($target_dir, 0777, true);
            $new_name = time() . '_' . basename($_FILES["image"]["name"]);
            $target_file = $target_dir . $new_name;
            move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);
            $image_path = "assets/uploads/products/" . $new_name;
        }

        if (isset($_POST['add_product'])) {
            $weight = intval($_POST['weight'] ?? 1000);
            if ($weight <= 0)
                $weight = 1000;

            $sql = "INSERT INTO products (category_id, short_code, name, slug, description, price, buy_price, stock, unit, image, weight) VALUES ($category_id, '$short_code', '$name', '$slug', '$description', $price, $buy_price, $stock, '$unit', '$image_path', $weight)";
            if ($conn->query($sql)) {
                $_SESSION['status_msg'] = "Product added successfully.";
                $_SESSION['status_type'] = "success";
                header("Location: $filtered_redirect");
                exit();
            } else {
                $error = "Error: " . $conn->error;
            }
        } elseif (isset($_POST['update_product'])) {
            $id = intval($_POST['id']);
            $weight = intval($_POST['weight'] ?? 1000);
            if ($weight <= 0)
                $weight = 1000;

            $sql = "UPDATE products SET category_id=$category_id, short_code='$short_code', name='$name', slug='$slug', description='$description', price=$price, buy_price=$buy_price, stock=$stock, unit='$unit', weight=$weight";
            if ($image_path)
                $sql .= ", image='$image_path'";
            $sql .= " WHERE id=$id";
            if ($conn->query($sql)) {
                $_SESSION['status_msg'] = "Product updated successfully.";
                $_SESSION['status_type'] = "success";
                header("Location: $filtered_redirect");
                exit();
            } else {
                $error = "Error: " . $conn->error;
            }
        }
    }
}

// Fetch Products with Pagination
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
if (!in_array($limit, [5, 10, 20]))
    $limit = 10;

$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
if ($page < 1)
    $page = 1;

$offset = ($page - 1) * $limit;

// Filter Logic
$where = "";
$conditions = [];

if (isset($_GET['category_id']) && !empty($_GET['category_id'])) {
    $cat_id = intval($_GET['category_id']);
    $conditions[] = "products.category_id = $cat_id";
}

if (isset($_GET['stock_status']) && !empty($_GET['stock_status'])) {
    if ($_GET['stock_status'] == 'low_stock') {
        $conditions[] = "products.stock > 0 AND products.stock <= 5";
    } elseif ($_GET['stock_status'] == 'out_of_stock') {
        $conditions[] = "products.stock = 0";
    }
}

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $conditions[] = "(products.name LIKE '%$search%' OR products.description LIKE '%$search%')";
}

if (count($conditions) > 0) {
    $where = "WHERE " . implode(' AND ', $conditions);
}

// Sorting Logic
$order_by = "products.id DESC"; // Default
if (isset($_GET['sort']) && $_GET['sort'] == 'stock_desc') {
    $order_by = "products.stock DESC, products.name ASC";
}

// Count Total
$count_query = "SELECT COUNT(*) as total FROM products $where";
$total_result = $conn->query($count_query);
$total_rows = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);

// Fetch Data
$q = "SELECT products.*, categories.name as category_name 
      FROM products 
      LEFT JOIN categories ON products.category_id = categories.id 
      $where 
      ORDER BY $order_by 
      LIMIT $limit OFFSET $offset";
$result = $conn->query($q);

// Fetch Categories
$cat_res = $conn->query("SELECT * FROM categories ORDER BY name ASC");
$categories = [];
while ($c = mysqli_fetch_assoc($cat_res))
    $categories[] = $c;

// Statistics for Overview Cards
$stat_total = $conn->query("SELECT COUNT(*) as c FROM products")->fetch_assoc()['c'];
$stat_low = $conn->query("SELECT COUNT(*) as c FROM products WHERE stock > 0 AND stock <= 5")->fetch_assoc()['c'];
$stat_out = $conn->query("SELECT COUNT(*) as c FROM products WHERE stock = 0")->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html class="light" lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Product Management - Lapak Bangsawan</title>
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
        <?php $page_title = "Produk";
        include ROOT_PATH . "includes/admin/header.php"; ?>
        <div class="flex-1 lg:overflow-y-auto p-4 md:p-8 scroll-smooth">
            <div class="max-w-full mx-auto flex flex-col gap-6">
                <!-- Page Content -->
                <!-- Notification Area -->
                <?php if (isset($_SESSION['status_msg'])): ?>
                    <div
                        class="bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-700 rounded-lg p-4 mb-2 flex items-start gap-3 shadow-sm auto-close-alert transition-opacity duration-500">
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

                <?php if ($success): ?>
                    <div
                        class="bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-700 rounded-lg p-4 mb-2 flex items-start gap-3 shadow-sm auto-close-alert transition-opacity duration-500">
                        <span class="material-icons-round text-green-500">check_circle</span>
                        <div>
                            <h3 class="font-medium text-slate-900 dark:text-white">Berhasil</h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400"><?php echo $success; ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div
                        class="bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-700 rounded-lg p-4 mb-2 flex items-start gap-3 shadow-sm auto-close-alert transition-opacity duration-500">
                        <span class="material-icons-round text-red-500">error</span>
                        <div>
                            <h3 class="font-medium text-slate-900 dark:text-white">Gagal</h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400"><?php echo $error; ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['action']) && ($_GET['action'] == 'add' || $_GET['action'] == 'edit')):
                    $edit_row = null;
                    if ($_GET['action'] == 'edit' && isset($_GET['id'])) {
                        $eid = intval($_GET['id']);
                        $edit_row = mysqli_fetch_assoc($conn->query("SELECT * FROM products WHERE id=$eid"));
                    }
                    ?>
                    <!-- Form View -->
                    <div
                        class="bg-surface-light dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-slate-800 p-6 max-w-full overflow-visible">
                        <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-6">
                            <?php echo isset($edit_row) ? 'Ubah Produk' : 'Tambah Produk Baru'; ?>
                        </h2>
                        <form method="POST" enctype="multipart/form-data" class="flex flex-col gap-6">
                            <?php if (isset($edit_row)): ?>
                                <input type="hidden" name="id" value="<?php echo $edit_row['id']; ?>">
                                <input type="hidden" name="update_product" value="1">
                            <?php else: ?>
                                <input type="hidden" name="add_product" value="1">
                            <?php endif; ?>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Short
                                        Code (Unique)</label>
                                    <input type="text" name="short_code"
                                        value="<?php echo $edit_row['short_code'] ?? ''; ?>"
                                        class="w-full rounded-lg border-slate-200 bg-slate-50 dark:bg-slate-800 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-primary focus:border-primary">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Nama
                                        Produk</label>
                                    <input type="text" name="name" required value="<?php echo $edit_row['name'] ?? ''; ?>"
                                        class="w-full rounded-lg border-slate-200 bg-slate-50 dark:bg-slate-800 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-primary focus:border-primary">
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Kategori</label>
                                    <div class="custom-select-wrapper relative">
                                        <select name="category_id" required class="hidden">
                                            <option value="">Pilih Kategori</option>
                                            <?php foreach ($categories as $cat): ?>
                                                <option value="<?php echo $cat['id']; ?>"
                                                    data-name="<?php echo htmlspecialchars($cat['name']); ?>" <?php echo (isset($edit_row) && $edit_row['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($cat['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="button"
                                            class="custom-select-trigger w-full flex items-center justify-between rounded-lg border-slate-200 bg-slate-50 dark:bg-slate-800 dark:border-slate-700 text-slate-900 dark:text-white px-4 py-2.5 text-sm focus:ring-primary focus:border-primary transition-all text-left">
                                            <span class="selected-label truncate">
                                                <?php
                                                $current_cat_name = 'Pilih Kategori';
                                                if (isset($edit_row)) {
                                                    foreach ($categories as $cat) {
                                                        if ($cat['id'] == $edit_row['category_id']) {
                                                            $current_cat_name = $cat['name'];
                                                            break;
                                                        }
                                                    }
                                                }
                                                echo htmlspecialchars($current_cat_name);
                                                ?>
                                            </span>
                                            <span
                                                class="material-icons-round text-slate-400 selected-icon transition-transform">expand_more</span>
                                        </button>
                                        <div
                                            class="custom-select-options hidden absolute z-[110] w-full mt-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-xl opacity-0 translate-y-2 transition-all duration-200 overflow-hidden">
                                            <div class="max-h-60 overflow-y-auto p-2 dropdown-options-scroll">
                                                <div class="custom-option px-4 py-2.5 rounded-lg hover:bg-primary/5 hover:text-primary cursor-pointer transition-colors text-sm"
                                                    data-value="">Pilih Kategori</div>
                                                <?php foreach ($categories as $cat): ?>
                                                    <div class="custom-option px-4 py-2.5 rounded-lg hover:bg-primary/5 hover:text-primary cursor-pointer transition-colors text-sm <?php echo (isset($edit_row) && $edit_row['category_id'] == $cat['id']) ? 'bg-primary/10 text-primary font-bold' : ''; ?>"
                                                        data-value="<?php echo $cat['id']; ?>">
                                                        <?php echo htmlspecialchars($cat['name']); ?>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <div class="grid grid-cols-2 gap-4 mt-2">
                                        <div>
                                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Harga
                                                Beli</label>
                                            <div class="relative flex items-center group">
                                                <span
                                                    class="absolute left-4 text-sm font-bold text-slate-400 group-focus-within:text-primary transition-colors">Rp</span>
                                                <input type="text" name="buy_price" id="buy-price-input"
                                                    value="<?php echo isset($edit_row['buy_price']) ? number_format($edit_row['buy_price'], 0, ',', '.') : ''; ?>"
                                                    required
                                                    class="currency-input w-full pl-11 rounded-xl border-slate-200 bg-slate-50 dark:bg-slate-800 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-4 focus:ring-primary/10 focus:border-primary font-bold transition-all">
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Harga
                                                Jual</label>
                                            <div class="relative flex items-center group">
                                                <span
                                                    class="absolute left-4 text-sm font-bold text-slate-400 group-focus-within:text-primary transition-colors">Rp</span>
                                                <input type="text" name="price" id="price-input"
                                                    value="<?php echo isset($edit_row['price']) ? number_format($edit_row['price'], 0, ',', '.') : ''; ?>"
                                                    required
                                                    class="currency-input w-full pl-11 rounded-xl border-slate-200 bg-slate-50 dark:bg-slate-800 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-4 focus:ring-primary/10 focus:border-primary font-bold transition-all">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Stok</label>
                                    <input type="number" step="0.01" name="stock" id="stock-input" required
                                        value="<?php echo $edit_row['stock'] ?? ''; ?>"
                                        class="w-full rounded-lg border-slate-200 bg-slate-50 dark:bg-slate-800 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-primary focus:border-primary">
                                    <p id="stock-hint" class="text-xs text-slate-500 mt-1 hidden">For Frozen Food, stock
                                        must be an integer.</p>
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Unit</label>
                                    <div class="custom-select-wrapper relative">
                                        <select name="unit" required class="hidden">
                                            <option value="kg" <?php echo (isset($edit_row) && $edit_row['unit'] == 'kg') || !isset($edit_row) ? 'selected' : ''; ?>>kg</option>
                                            <option value="pcs" <?php echo (isset($edit_row) && $edit_row['unit'] == 'pcs') ? 'selected' : ''; ?>>pcs</option>
                                            <option value="box" <?php echo (isset($edit_row) && $edit_row['unit'] == 'box') ? 'selected' : ''; ?>>box</option>
                                        </select>
                                        <button type="button"
                                            class="custom-select-trigger w-full flex items-center justify-between rounded-lg border-slate-200 bg-slate-50 dark:bg-slate-800 dark:border-slate-700 text-slate-900 dark:text-white px-4 py-2.5 text-sm focus:ring-primary focus:border-primary transition-all text-left">
                                            <span class="selected-label truncate">
                                                <?php echo $edit_row['unit'] ?? 'kg'; ?>
                                            </span>
                                            <span
                                                class="material-icons-round text-slate-400 selected-icon transition-transform">expand_more</span>
                                        </button>
                                        <div
                                            class="custom-select-options hidden absolute z-[110] w-full mt-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-xl opacity-0 translate-y-2 transition-all duration-200 overflow-hidden">
                                            <div class="max-h-60 overflow-y-auto p-2 dropdown-options-scroll">
                                                <div class="custom-option px-4 py-2.5 rounded-lg hover:bg-primary/5 hover:text-primary cursor-pointer transition-colors text-sm <?php echo (!isset($edit_row) || $edit_row['unit'] == 'kg') ? 'bg-primary/10 text-primary font-bold' : ''; ?>"
                                                    data-value="kg">kg</div>
                                                <div class="custom-option px-4 py-2.5 rounded-lg hover:bg-primary/5 hover:text-primary cursor-pointer transition-colors text-sm <?php echo (isset($edit_row) && $edit_row['unit'] == 'pcs') ? 'bg-primary/10 text-primary font-bold' : ''; ?>"
                                                    data-value="pcs">pcs</div>
                                                <div class="custom-option px-4 py-2.5 rounded-lg hover:bg-primary/5 hover:text-primary cursor-pointer transition-colors text-sm <?php echo (isset($edit_row) && $edit_row['unit'] == 'box') ? 'bg-primary/10 text-primary font-bold' : ''; ?>"
                                                    data-value="box">box</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Berat
                                        Produk (Gram)</label>
                                    <input type="number" name="weight" required
                                        value="<?php echo $edit_row['weight'] ?? '1000'; ?>"
                                        class="w-full rounded-lg border-slate-200 bg-slate-50 dark:bg-slate-800 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-primary focus:border-primary">
                                    <p class="text-xs text-slate-500 mt-1">Wajib diisi dalam satuan Gram. Contoh: 1kg = 1000
                                    </p>
                                </div>
                            </div>

                            <div>
                                <label
                                    class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Deskripsi</label>
                                <textarea name="description" rows="4"
                                    class="w-full rounded-lg border-slate-200 bg-slate-50 dark:bg-slate-800 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-primary focus:border-primary"><?php echo $edit_row['description'] ?? ''; ?></textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Gambar
                                    Produk</label>
                                <input type="file" name="image"
                                    class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20">
                                <?php if (isset($edit_row['image']) && $edit_row['image']): ?>
                                    <div class="mt-2 text-xs text-slate-500">Gambar Saat Ini:</div>
                                    <img src="../<?php echo $edit_row['image']; ?>"
                                        class="h-16 w-16 object-cover rounded mt-1 border border-slate-200">
                                <?php endif; ?>
                            </div>

                            <div class="flex gap-4 pt-4">
                                <button type="submit"
                                    class="bg-primary text-white px-6 py-2.5 rounded-lg hover:bg-blue-600 transition-colors font-medium">Simpan
                                    Produk</button>
                                <a href="<?php echo $filtered_redirect; ?>"
                                    class="bg-slate-100 text-slate-700 px-6 py-2.5 rounded-lg hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300 transition-colors font-medium">Batal</a>
                            </div>
                        </form>
                    </div>
                <?php else: ?>

                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mt-2">
                        <div>
                            <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Manajemen Produk
                            </h2>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Kelola inventaris dan stok produk
                                Anda.</p>
                        </div>
                        <a href="?action=add<?php echo $query_string ? '&' . $query_string : ''; ?>"
                            class="flex items-center justify-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-white transition-colors hover:bg-blue-600 shadow-sm shadow-blue-500/30">
                            <span class="material-icons-round text-sm">add</span>
                            <span>Tambah Produk</span>
                        </a>
                    </div>



                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div
                            class="bg-surface-light dark:bg-surface-dark p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm flex items-center gap-4">
                            <div class="p-3 bg-blue-50 dark:bg-blue-900/30 rounded-full text-primary">
                                <span class="material-icons-round text-2xl">inventory_2</span>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Total Produk</p>
                                <h3 class="text-2xl font-bold text-slate-900 dark:text-white"><?php echo $stat_total; ?>
                                </h3>
                            </div>
                        </div>
                        <div
                            class="bg-surface-light dark:bg-surface-dark p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm flex items-center gap-4">
                            <div class="p-3 bg-orange-50 dark:bg-orange-900/30 rounded-full text-orange-500">
                                <span class="material-icons-round text-2xl">warning</span>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Stok Rendah</p>
                                <h3 class="text-2xl font-bold text-slate-900 dark:text-white"><?php echo $stat_low; ?></h3>
                            </div>
                        </div>
                        <div
                            class="bg-surface-light dark:bg-surface-dark p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm flex items-center gap-4">
                            <div class="p-3 bg-red-50 dark:bg-red-900/30 rounded-full text-red-500">
                                <span class="material-icons-round text-2xl">remove_shopping_cart</span>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Stok Habis</p>
                                <h3 class="text-2xl font-bold text-slate-900 dark:text-white"><?php echo $stat_out; ?></h3>
                            </div>
                        </div>
                    </div>

                    <!-- Filter Bar -->
                    <div
                        class="bg-surface-light dark:bg-surface-dark p-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
                        <form action="" method="GET" class="flex flex-col md:flex-row gap-4">
                            <!-- Preserve Search if any -->
                            <?php if (isset($_GET['search'])): ?>
                                <input type="hidden" name="search" value="<?php echo htmlspecialchars($_GET['search']); ?>">
                            <?php endif; ?>

                            <div class="flex-1 grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <!-- Category Filter -->
                                <div>
                                    <label
                                        class="block text-[10px] font-bold text-slate-400 uppercase mb-1 ml-1">Kategori</label>
                                    <div class="custom-select-wrapper relative"
                                        data-onchange="this.closest('form').submit()">
                                        <select name="category_id" class="hidden">
                                            <option value="">Semua Kategori</option>
                                            <?php foreach ($categories as $cat): ?>
                                                <option value="<?php echo $cat['id']; ?>" <?php echo (isset($_GET['category_id']) && $_GET['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($cat['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="button"
                                            class="custom-select-trigger w-full flex items-center justify-between rounded-lg border-slate-200 bg-slate-50 dark:bg-slate-800/50 dark:border-slate-700 px-4 py-2 text-sm focus:ring-primary focus:border-primary transition-all text-left">
                                            <span class="selected-label">
                                                <?php
                                                $current_filter_cat = 'Semua Kategori';
                                                if (isset($_GET['category_id']) && !empty($_GET['category_id'])) {
                                                    foreach ($categories as $cat) {
                                                        if ($cat['id'] == $_GET['category_id']) {
                                                            $current_filter_cat = $cat['name'];
                                                            break;
                                                        }
                                                    }
                                                }
                                                echo htmlspecialchars($current_filter_cat);
                                                ?>
                                            </span>
                                            <span
                                                class="material-icons-round text-slate-400 selected-icon transition-transform">expand_more</span>
                                        </button>
                                        <div
                                            class="custom-select-options hidden absolute z-[110] w-full mt-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-xl opacity-0 translate-y-2 transition-all duration-200 overflow-hidden">
                                            <div class="max-h-60 overflow-y-auto p-2 dropdown-options-scroll">
                                                <div class="custom-option px-4 py-2 rounded-lg hover:bg-primary/5 hover:text-primary cursor-pointer transition-colors text-sm"
                                                    data-value="">Semua Kategori</div>
                                                <?php foreach ($categories as $cat): ?>
                                                    <div class="custom-option px-4 py-2 rounded-lg hover:bg-primary/5 hover:text-primary cursor-pointer transition-colors text-sm <?php echo (isset($_GET['category_id']) && $_GET['category_id'] == $cat['id']) ? 'bg-primary/10 text-primary font-bold' : ''; ?>"
                                                        data-value="<?php echo $cat['id']; ?>">
                                                        <?php echo htmlspecialchars($cat['name']); ?>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Stock Status Filter -->
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1 ml-1">Status
                                        Stok</label>
                                    <div class="custom-select-wrapper relative"
                                        data-onchange="this.closest('form').submit()">
                                        <select name="stock_status" class="hidden">
                                            <option value="">Semua Status</option>
                                            <option value="low_stock" <?php echo (isset($_GET['stock_status']) && $_GET['stock_status'] == 'low_stock') ? 'selected' : ''; ?>>Stok Rendah (≤ 5)
                                            </option>
                                            <option value="out_of_stock" <?php echo (isset($_GET['stock_status']) && $_GET['stock_status'] == 'out_of_stock') ? 'selected' : ''; ?>>Stok Habis (0)
                                            </option>
                                        </select>
                                        <button type="button"
                                            class="custom-select-trigger w-full flex items-center justify-between rounded-lg border-slate-200 bg-slate-50 dark:bg-slate-800/50 dark:border-slate-700 px-4 py-2 text-sm focus:ring-primary focus:border-primary transition-all text-left">
                                            <span class="selected-label">
                                                <?php
                                                $status = $_GET['stock_status'] ?? '';
                                                if ($status == 'low_stock')
                                                    echo 'Stok Rendah (≤ 5)';
                                                else if ($status == 'out_of_stock')
                                                    echo 'Stok Habis (0)';
                                                else
                                                    echo 'Semua Status';
                                                ?>
                                            </span>
                                            <span
                                                class="material-icons-round text-slate-400 selected-icon transition-transform">expand_more</span>
                                        </button>
                                        <div
                                            class="custom-select-options hidden absolute z-[110] w-full mt-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-xl opacity-0 translate-y-2 transition-all duration-200 overflow-hidden">
                                            <div class="max-h-60 overflow-y-auto p-2 dropdown-options-scroll">
                                                <div class="custom-option px-4 py-2 rounded-lg hover:bg-primary/5 hover:text-primary cursor-pointer transition-colors text-sm <?= $status == '' ? 'bg-primary/10 text-primary font-bold' : '' ?>"
                                                    data-value="">Semua Status</div>
                                                <div class="custom-option px-4 py-2 rounded-lg hover:bg-primary/5 hover:text-primary cursor-pointer transition-colors text-sm <?= $status == 'low_stock' ? 'bg-primary/10 text-primary font-bold' : '' ?>"
                                                    data-value="low_stock">Stok Rendah (≤ 5)</div>
                                                <div class="custom-option px-4 py-2 rounded-lg hover:bg-primary/5 hover:text-primary cursor-pointer transition-colors text-sm <?= $status == 'out_of_stock' ? 'bg-primary/10 text-primary font-bold' : '' ?>"
                                                    data-value="out_of_stock">Stok Habis (0)</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Sort Order -->
                                <div>
                                    <label
                                        class="block text-[10px] font-bold text-slate-400 uppercase mb-1 ml-1">Urutan</label>
                                    <div class="custom-select-wrapper relative"
                                        data-onchange="this.closest('form').submit()">
                                        <select name="sort" class="hidden">
                                            <option value="">Terbaru (Default)</option>
                                            <option value="stock_desc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'stock_desc') ? 'selected' : ''; ?>>Stok Tertinggi</option>
                                        </select>
                                        <button type="button"
                                            class="custom-select-trigger w-full flex items-center justify-between rounded-lg border-slate-200 bg-slate-50 dark:bg-slate-800/50 dark:border-slate-700 px-4 py-2 text-sm focus:ring-primary focus:border-primary transition-all text-left">
                                            <span class="selected-label">
                                                <?php
                                                $sort = $_GET['sort'] ?? '';
                                                if ($sort == 'stock_desc')
                                                    echo 'Stok Tertinggi';
                                                else
                                                    echo 'Terbaru (Default)';
                                                ?>
                                            </span>
                                            <span
                                                class="material-icons-round text-slate-400 selected-icon transition-transform">expand_more</span>
                                        </button>
                                        <div
                                            class="custom-select-options hidden absolute z-[110] w-full mt-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-xl opacity-0 translate-y-2 transition-all duration-200 overflow-hidden">
                                            <div class="max-h-60 overflow-y-auto p-2 dropdown-options-scroll">
                                                <div class="custom-option px-4 py-2 rounded-lg hover:bg-primary/5 hover:text-primary cursor-pointer transition-colors text-sm <?= $sort == '' ? 'bg-primary/10 text-primary font-bold' : '' ?>"
                                                    data-value="">Terbaru (Default)</div>
                                                <div class="custom-option px-4 py-2 rounded-lg hover:bg-primary/5 hover:text-primary cursor-pointer transition-colors text-sm <?= $sort == 'stock_desc' ? 'bg-primary/10 text-primary font-bold' : '' ?>"
                                                    data-value="stock_desc">Stok Tertinggi</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-end">
                                <a href="products.php"
                                    class="flex items-center justify-center gap-2 px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-300 bg-slate-50 dark:bg-slate-800/50 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg transition-all shadow-sm">
                                    <span class="material-icons-round text-sm">restart_alt</span>
                                    <span>Reset Filter</span>
                                </a>
                            </div>
                        </form>
                    </div>

                    <!-- List View Container for AJAX -->
                    <div id="product-list-container" class="flex flex-col gap-6">
                        <?php if (isset($_GET['ajax'])):
                            ob_clean();
                        endif; ?>

                        <div
                            class="bg-surface-light dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-visible">
                            <div class="overflow-x-auto rounded-t-xl">
                                <table class="w-full text-left text-sm text-slate-500 dark:text-slate-400">
                                    <thead
                                        class="bg-slate-50 dark:bg-slate-800/50 text-xs uppercase font-semibold text-slate-500">
                                        <tr>
                                            <th class="px-6 py-4">No</th>
                                            <th class="px-6 py-4">Gambar</th>
                                            <th class="px-6 py-4">Short Code</th>
                                            <th class="px-6 py-4">Nama Produk</th>
                                            <th class="px-6 py-4">Kategori</th>
                                            <th class="px-6 py-4">Harga/Unit</th>
                                            <th class="px-6 py-4">Stok</th>
                                            <th class="px-6 py-4 text-right">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                                        <?php
                                        $no = $offset + 1;
                                        while ($row = mysqli_fetch_assoc($result)):
                                            ?>
                                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors group">
                                                <td class="px-6 py-4"><?php echo $no++; ?></td>
                                                <td class="px-6 py-4">
                                                    <div
                                                        class="h-10 w-10 flex-shrink-0 overflow-hidden rounded-lg bg-slate-100 flex items-center justify-center dark:bg-slate-800">
                                                        <?php if ($row['image']): ?>
                                                            <img src="../<?php echo $row['image']; ?>"
                                                                class="h-full w-full object-cover">
                                                        <?php else: ?>
                                                            <span class="material-icons-round text-slate-400">image</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 font-mono text-xs text-primary font-bold">
                                                    <?php echo htmlspecialchars($row['short_code'] ?? '-'); ?>
                                                </td>
                                                <td class="px-6 py-4 font-medium text-slate-900 dark:text-white">
                                                    <?php echo htmlspecialchars($row['name']); ?>
                                                </td>
                                                <td class="px-6 py-4"><?php echo htmlspecialchars($row['category_name']); ?>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <div class="text-xs font-bold text-slate-900 dark:text-white">Rp
                                                        <?php echo number_format($row['price'], 0, ',', '.'); ?> /
                                                        <?php echo $row['unit']; ?>
                                                    </div>
                                                    <div class="text-[10px] text-slate-400 italic">Modal: Rp
                                                        <?php echo number_format($row['buy_price'], 0, ',', '.'); ?>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 <?php
                                                if ($row['stock'] == 0) {
                                                    echo 'text-red-600 font-bold dark:text-red-400';
                                                } elseif ($row['stock'] <= 5) {
                                                    echo 'text-amber-500 font-bold dark:text-amber-400';
                                                }
                                                ?>">
                                                    <?php
                                                    echo $row['stock'] . ' ' . $row['unit'];
                                                    ?>
                                                </td>
                                                <td class="px-6 py-4 text-right">
                                                    <div class="flex items-center justify-end gap-2">
                                                        <a href="?action=edit&id=<?php echo $row['id'] . ($query_string ? '&' . $query_string : ''); ?>"
                                                            class="text-slate-400 hover:text-primary transition-colors p-1 rounded-md hover:bg-slate-100 dark:hover:bg-slate-800">
                                                            <span class="material-icons-round text-xl">edit</span>
                                                        </a>
                                                        <a href="#"
                                                            onclick="confirmDelete('?action=delete&id=<?php echo $row['id'] . ($query_string ? '&' . $query_string : ''); ?>')"
                                                            class="text-slate-400 hover:text-red-600 transition-colors p-1 rounded-md hover:bg-red-50 dark:hover:bg-red-900/20">
                                                            <span class="material-icons-round text-xl">delete</span>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                        <?php if (mysqli_num_rows($result) == 0): ?>
                                            <tr>
                                                <td colspan="6" class="px-6 py-4 text-center">Tidak ada produk ditemukan.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination Controls -->
                            <div
                                class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50 flex flex-col sm:flex-row justify-between items-center gap-4">
                                <div class="flex items-center gap-2 text-sm text-slate-500">
                                    <span>Tampilkan</span>
                                    <select
                                        onchange="const url = '?limit='+this.value+'&page=1<?php echo $base_query ? '&' . $base_query : ''; ?>'; if(typeof loadProducts === 'function'){ loadProducts(url); } else { window.location.href=url; }"
                                        class="bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded text-xs py-2 px-6 focus:ring-primary focus:border-primary">
                                        <option value="5" <?php echo $limit == 5 ? 'selected' : ''; ?>>5</option>
                                        <option value="10" <?php echo $limit == 10 ? 'selected' : ''; ?>>10</option>
                                        <option value="20" <?php echo $limit == 20 ? 'selected' : ''; ?>>20</option>
                                    </select>
                                    <span>entri</span>
                                    <span class="ml-2 hidden sm:inline"> | Menampilkan
                                        <?php echo $total_rows > 0 ? $offset + 1 : 0; ?> sampai
                                        <?php echo min($offset + $limit, $total_rows); ?> dari <?php echo $total_rows; ?>
                                        entri</span>
                                </div>

                                <div class="flex gap-2">
                                    <?php if ($page > 1): ?>
                                        <a href="?page=<?php echo $page - 1; ?>&limit=<?php echo $limit; ?><?php echo $base_query ? '&' . $base_query : ''; ?>"
                                            class="px-3 py-1 text-xs border border-slate-200 dark:border-slate-700 rounded bg-white dark:bg-slate-800 text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">Sebelumnya</a>
                                    <?php else: ?>
                                        <button disabled
                                            class="px-3 py-1 text-xs border border-slate-200 dark:border-slate-700 rounded bg-white dark:bg-slate-800 text-slate-300 cursor-not-allowed">Sebelumnya</button>
                                    <?php endif; ?>

                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <a href="?page=<?php echo $i; ?>&limit=<?php echo $limit; ?><?php echo $base_query ? '&' . $base_query : ''; ?>"
                                            class="px-3 py-1 text-xs border <?php echo $i == $page ? 'border-primary bg-primary text-white' : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-700'; ?> rounded transition-colors"><?php echo $i; ?></a>
                                    <?php endfor; ?>

                                    <?php if ($page < $total_pages): ?>
                                        <a href="?page=<?php echo $page + 1; ?>&limit=<?php echo $limit; ?><?php echo $base_query ? '&' . $base_query : ''; ?>"
                                            class="px-3 py-1 text-xs border border-slate-200 dark:border-slate-700 rounded bg-white dark:bg-slate-800 text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">Selanjutnya</a>
                                    <?php else: ?>
                                        <button disabled
                                            class="px-3 py-1 text-xs border border-slate-200 dark:border-slate-700 rounded bg-white dark:bg-slate-800 text-slate-300 cursor-not-allowed">Selanjutnya</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php if (isset($_GET['ajax'])):
                            echo ob_get_clean();
                            exit;
                        endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            <?php include ROOT_PATH . "includes/admin/footer.php"; ?>
        </div>
    </main>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const catSelect = document.querySelector('select[name="category_id"]');
            const stockInput = document.getElementById('stock-input');
            const stockHint = document.getElementById('stock-hint');
            // Currency Input Logic
            const currencyInputs = document.querySelectorAll('.currency-input');

            function formatRupiah(angka) {
                var number_string = angka.replace(/[^0-9]/g, "").toString(),
                    sisa = number_string.length % 3,
                    rupiah = number_string.substr(0, sisa),
                    ribuan = number_string.substr(sisa).match(/\d{3}/gi);

                if (ribuan) {
                    var separator = sisa ? "." : "";
                    rupiah += separator + ribuan.join(".");
                }
                return rupiah;
            }

            currencyInputs.forEach(input => {
                const update = () => {
                    let raw = input.value.replace(/[^0-9]/g, '');
                    if (raw) {
                        let cursorSource = input.selectionStart;
                        let oldLen = input.value.length;

                        input.value = formatRupiah(raw);

                        // Fix cursor position after formatting
                        let newLen = input.value.length;
                        input.setSelectionRange(cursorSource + (newLen - oldLen), cursorSource + (newLen - oldLen));
                    }
                };

                input.addEventListener('input', update);
                // Run on load for edit form
                if (input.value) update();
            });

            function checkFrozen() {
                if (!catSelect || !stockInput) return;
                const selectedOption = catSelect.options[catSelect.selectedIndex];
                const catName = selectedOption ? selectedOption.getAttribute('data-name') : '';

                if (catName === 'Frozen Food') {
                    stockInput.setAttribute('step', '1');
                    stockHint.classList.remove('hidden');
                } else {
                    stockInput.setAttribute('step', '0.01');
                    stockHint.classList.add('hidden');
                }
            }

            if (catSelect) {
                catSelect.addEventListener('change', checkFrozen);
                checkFrozen();
            }

            // AJAX Pagination Logic
            const listContainer = document.getElementById('product-list-container');

            async function loadProducts(url) {
                if (!listContainer) return;

                // Show loading state
                listContainer.style.opacity = '0.5';
                listContainer.style.pointerEvents = 'none';

                try {
                    const ajaxUrl = new URL(url, window.location.href);
                    ajaxUrl.searchParams.set('ajax', '1');
                    ajaxUrl.searchParams.set('_t', Date.now());

                    const response = await fetch(ajaxUrl);
                    if (!response.ok) throw new Error('Network response was not ok');

                    const html = await response.text();
                    listContainer.innerHTML = html;

                    // Update URL
                    window.history.pushState({}, '', url);

                    // Re-run initialization for new elements if needed
                    // (In this case, we just need to scroll to top of table)
                    listContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
                } catch (error) {
                    console.error('Fetch error:', error);
                    window.location.href = url; // Fallback to normal load
                } finally {
                    listContainer.style.opacity = '1';
                    listContainer.style.pointerEvents = 'auto';
                }
            }

            if (listContainer) {
                listContainer.addEventListener('click', function (e) {
                    const link = e.target.closest('a');
                    const h = link.getAttribute('href');
                    const isPagination = h && (h.includes('page=') || h.includes('limit='));
                    const isAction = h && (h.includes('action='));

                    if (link && link.href && isPagination && !isAction && !link.onclick) {
                        e.preventDefault();
                        loadProducts(link.href);
                    }
                });

                // Handle select change for limit
                listContainer.addEventListener('change', function (e) {
                    if (e.target.tagName === 'SELECT' && (e.target.onchange || '').toString().includes('window.location.href')) {
                        // The existing onchange is hardcoded as: onchange="window.location.href='?limit='+this.value+'&page=1...'"
                        // We need to override it or intercept it.
                        // Since it's inline onchange, it fires before this. 
                        // Let's replace the inline onchange logic with AJAX if possible, 
                        // or just intercept the triggered change.
                    }
                });
            }

            // Simple fix for the select onchange: 
            // We'll replace the inline onchange if it exists during AJAX payload load
            // or just rely on the fact that we can re-query the select.
        });
    </script>
</body>

</html>