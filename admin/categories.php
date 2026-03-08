<?php
require("auth_session.php");
require_once dirname(__DIR__) . "/config/init.php";
require(ROOT_PATH . "includes/admin/notification_logic.php");

// Handle Delete
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    if ($conn->query("DELETE FROM categories WHERE id=$id")) {
        log_activity("DELETE_CATEGORY", "Menghapus kategori ID #$id");
        $_SESSION['status_msg'] = "Category deleted successfully.";
        $_SESSION['status_type'] = "success";
    } else {
        $_SESSION['status_msg'] = "Failed to delete category.";
        $_SESSION['status_type'] = "error";
    }
    header("Location: categories");
    exit();
}

// Handle Add/Edit Post
$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_category']) || isset($_POST['update_category'])) {
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $slug = mysqli_real_escape_string($conn, strtolower(str_replace(' ', '-', $name)));

        if (isset($_POST['add_category'])) {
            $sql = "INSERT INTO categories (name, slug) VALUES ('$name', '$slug')";
            if ($conn->query($sql)) {
                log_activity("ADD_CATEGORY", "Menambahkan kategori baru: $name");
                $_SESSION['status_msg'] = "Category added successfully.";
                $_SESSION['status_type'] = "success";
                header("Location: categories");
                exit();
            } else {
                $error = "Error: " . $conn->error;
            }
        } elseif (isset($_POST['update_category'])) {
            $id = intval($_POST['id']);
            $sql = "UPDATE categories SET name='$name', slug='$slug'";
            $sql .= " WHERE id=$id";
            if ($conn->query($sql)) {
                log_activity("UPDATE_CATEGORY", "Memperbarui kategori: $name (ID: $id)");
                $_SESSION['status_msg'] = "Category updated successfully.";
                $_SESSION['status_type'] = "success";
                header("Location: categories");
                exit();
            } else {
                $error = "Error: " . $conn->error;
            }
        }
    }
}

// Fetch Categories
// Fetch Categories with Pagination
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
if (!in_array($limit, [5, 10, 20]))
    $limit = 10;

$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$start = ($page > 1) ? ($page * $limit) - $limit : 0;

// Get total records
$total_result = $conn->query("SELECT COUNT(*) as count FROM categories");
$total_row = $total_result->fetch_assoc();
$total_categories = $total_row['count'];
$total_pages = ceil($total_categories / $limit);

$result = $conn->query("SELECT * FROM categories ORDER BY id DESC LIMIT $start, $limit");
?>
<!DOCTYPE html>
<html class="light" lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Category Management - Lapak Bangsawan</title>
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
    class="bg-background-light dark:bg-background-dark text-slate-600 dark:text-slate-300 font-display transition-colors duration-200 antialiased overflow-hidden h-screen flex">
    <?php include ROOT_PATH . "includes/admin/sidebar.php"; ?>
    <main class="flex-1 flex flex-col h-full relative overflow-hidden">
        <?php $page_title = "Kategori";
        include ROOT_PATH . "includes/admin/header.php"; ?>
        <div class="flex-1 overflow-y-auto p-6 md:p-8 scroll-smooth">
            <div class="max-w-full mx-auto flex flex-col gap-6 w-full h-full">
                <!-- Page Content -->
                <!-- Notification Area -->
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

                <?php if ($success): ?>
                    <div
                        class="bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-700 rounded-lg p-4 mb-6 flex items-start gap-3 shadow-sm auto-close-alert transition-opacity duration-500">
                        <span class="material-icons-round text-green-500">check_circle</span>
                        <div>
                            <h3 class="font-medium text-slate-900 dark:text-white">Berhasil</h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400"><?php echo $success; ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div
                        class="bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-700 rounded-lg p-4 mb-6 flex items-start gap-3 shadow-sm auto-close-alert transition-opacity duration-500">
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
                        $edit_row = mysqli_fetch_assoc($conn->query("SELECT * FROM categories WHERE id=$eid"));
                    }
                    ?>
                    <!-- Form View -->
                    <div
                        class="bg-surface-light dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-slate-800 p-6 max-w-full">
                        <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-6">
                            <?php echo isset($edit_row) ? 'Ubah Kategori' : 'Tambah Kategori Baru'; ?>
                        </h2>
                        <form method="POST" enctype="multipart/form-data" class="flex flex-col gap-6">
                            <?php if (isset($edit_row)): ?>
                                <input type="hidden" name="id" value="<?php echo $edit_row['id']; ?>">
                                <input type="hidden" name="update_category" value="1">
                            <?php else: ?>
                                <input type="hidden" name="add_category" value="1">
                            <?php endif; ?>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Category
                                    Name</label>
                                <input type="text" name="name" required value="<?php echo $edit_row['name'] ?? ''; ?>"
                                    class="w-full rounded-lg border-slate-200 bg-slate-50 dark:bg-slate-800 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-primary focus:border-primary">
                            </div>

                            <div class="flex gap-4 pt-4">
                                <button type="submit"
                                    class="bg-primary text-white px-6 py-2.5 rounded-lg hover:bg-blue-600 transition-colors font-medium">Simpan
                                    Kategori</button>
                                <a href="categories"
                                    class="bg-slate-100 text-slate-700 px-6 py-2.5 rounded-lg hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300 transition-colors font-medium">Batal</a>
                            </div>
                        </form>
                    </div>

                <?php else: ?>
                    <!-- List View -->
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div>
                            <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Manajemen Kategori
                            </h2>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Buat, ubah, dan atur kategori produk
                                Anda.</p>
                        </div>
                        <a href="?action=add"
                            class="flex items-center justify-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-white transition-colors hover:bg-blue-600 shadow-sm shadow-blue-500/30">
                            <span class="material-icons-round text-sm">add</span>
                            <span>Tambah Kategori</span>
                        </a>
                    </div>
                    <div
                        class="bg-surface-light dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-visible">
                        <div class="overflow-x-auto rounded-t-xl">
                            <table
                                class="min-w-full divide-y divide-slate-200 dark:divide-slate-800 text-left text-sm text-slate-500 dark:text-slate-400">
                                <thead
                                    class="bg-slate-50 dark:bg-slate-800/50 text-xs uppercase font-semibold text-slate-500 rounded-t-xl">
                                    <tr>
                                        <th class="px-6 py-4">
                                            Nama</th>
                                        <th class="px-6 py-4">
                                            Slug</th>
                                        <th class="px-6 py-4 text-right">
                                            Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors group">
                                            <td class="whitespace-nowrap px-6 py-4">
                                                <div class="font-medium text-slate-900 dark:text-white">
                                                    <?php echo htmlspecialchars($row['name']); ?>
                                                </div>
                                            </td>
                                            <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-500">
                                                <?php echo htmlspecialchars($row['slug']); ?>
                                            </td>
                                            <td class="whitespace-nowrap px-6 py-4 text-right font-medium">
                                                <div class="flex items-center justify-end gap-2">
                                                    <a href="?action=edit&id=<?php echo $row['id']; ?>"
                                                        class="text-slate-400 hover:text-primary transition-colors p-1 rounded-md hover:bg-slate-100 dark:hover:bg-slate-800">
                                                        <span class="material-icons-round text-xl">edit</span>
                                                    </a>
                                                    <a href="#"
                                                        onclick="confirmDelete('?action=delete&id=<?php echo $row['id']; ?>')"
                                                        class="text-slate-400 hover:text-red-600 transition-colors p-1 rounded-md hover:bg-red-50 dark:hover:bg-red-900/20">
                                                        <span class="material-icons-round text-xl">delete</span>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                    <?php if (mysqli_num_rows($result) == 0): ?>
                                        <tr>
                                            <td colspan="4" class="px-6 py-4 text-center text-slate-500">Tidak ada kategori
                                                ditemukan.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination Controls -->
                        <?php if ($total_pages > 0):
                            $pages_to_show = [];
                            if ($total_pages <= 7) {
                                for ($i = 1; $i <= $total_pages; $i++) $pages_to_show[] = $i;
                            } else {
                                $pages_to_show[] = 1;
                                $range_start = max(2, $page - 1);
                                $range_end = min($total_pages - 1, $page + 1);
                                if ($range_start > 2) $pages_to_show[] = '...';
                                for ($i = $range_start; $i <= $range_end; $i++) $pages_to_show[] = $i;
                                if ($range_end < $total_pages - 1) $pages_to_show[] = '...';
                                $pages_to_show[] = $total_pages;
                            }
                            $base_url = "?limit=$limit";
                        ?>
                        <div
                            class="border-t border-slate-200 dark:border-slate-800 pt-4 px-6 pb-4 flex flex-col sm:flex-row justify-between items-center gap-4">
                            <div class="flex items-center gap-2 text-xs text-slate-500">
                                <span>Tampilkan</span>
                                <select onchange="window.location.href='<?= $base_url ?>&page=1'.replace('limit=<?= $limit ?>','limit='+this.value)"
                                    class="bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-lg text-xs py-1.5 px-3 pr-7 focus:ring-primary focus:border-primary cursor-pointer">
                                    <option value="5" <?= $limit == 5 ? 'selected' : '' ?>>5</option>
                                    <option value="10" <?= $limit == 10 ? 'selected' : '' ?>>10</option>
                                    <option value="20" <?= $limit == 20 ? 'selected' : '' ?>>20</option>
                                </select>
                                <span class="text-slate-400">|</span>
                                <span class="text-slate-500">
                                    <span class="font-semibold text-slate-700 dark:text-slate-300"><?= $start + 1 ?>–<?= min($start + $limit, $total_categories) ?></span>
                                    dari
                                    <span class="font-semibold text-slate-700 dark:text-slate-300"><?= number_format($total_categories) ?></span>
                                </span>
                            </div>

                            <nav class="flex items-center gap-1" aria-label="Pagination">
                                <?php if ($page > 1): ?>
                                    <a href="<?= $base_url ?>&page=<?= $page - 1 ?>"
                                        class="inline-flex items-center justify-center size-8 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-700 hover:text-primary transition-all"
                                        title="Halaman sebelumnya">
                                        <span class="material-icons-round text-base">chevron_left</span>
                                    </a>
                                <?php else: ?>
                                    <span class="inline-flex items-center justify-center size-8 rounded-lg border border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 text-slate-300 dark:text-slate-600 cursor-not-allowed">
                                        <span class="material-icons-round text-base">chevron_left</span>
                                    </span>
                                <?php endif; ?>

                                <?php foreach ($pages_to_show as $pg): ?>
                                    <?php if ($pg === '...'): ?>
                                        <span class="inline-flex items-center justify-center w-8 h-8 text-xs text-slate-400 select-none">•••</span>
                                    <?php elseif ($pg == $page): ?>
                                        <span class="inline-flex items-center justify-center size-8 rounded-lg text-xs font-bold bg-primary text-white shadow-sm shadow-primary/30"><?= $pg ?></span>
                                    <?php else: ?>
                                        <a href="<?= $base_url ?>&page=<?= $pg ?>"
                                            class="inline-flex items-center justify-center size-8 rounded-lg text-xs font-medium border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-primary/5 hover:text-primary hover:border-primary/30 transition-all"><?= $pg ?></a>
                                    <?php endif; ?>
                                <?php endforeach; ?>

                                <?php if ($page < $total_pages): ?>
                                    <a href="<?= $base_url ?>&page=<?= $page + 1 ?>"
                                        class="inline-flex items-center justify-center size-8 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-700 hover:text-primary transition-all"
                                        title="Halaman selanjutnya">
                                        <span class="material-icons-round text-base">chevron_right</span>
                                    </a>
                                <?php else: ?>
                                    <span class="inline-flex items-center justify-center size-8 rounded-lg border border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 text-slate-300 dark:text-slate-600 cursor-not-allowed">
                                        <span class="material-icons-round text-base">chevron_right</span>
                                    </span>
                                <?php endif; ?>
                            </nav>
                        </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            <?php include ROOT_PATH . "includes/admin/footer.php"; ?>
        </div>
    </main>
</body>

</html>