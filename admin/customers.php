<?php
require("auth_session.php");
require_once dirname(__DIR__) . "/config/init.php";

// Delete Customer
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    if ($conn->query("DELETE FROM customers WHERE id=$id")) {
        $_SESSION['status_msg'] = "Pelanggan berhasil dihapus.";
        $_SESSION['status_type'] = "success";
    } else {
        $_SESSION['status_msg'] = "Gagal menghapus pelanggan: " . $conn->error;
        $_SESSION['status_type'] = "error";
    }
    header("Location: customers");
    exit();
}

// Fetch Customers with Pagination
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
if (!in_array($limit, [5, 10, 20]))
    $limit = 10;

$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$start = ($page > 1) ? ($page * $limit) - $limit : 0;

$count_query = "SELECT COUNT(*) as total FROM customers";
$count_result = $conn->query($count_query);
$total_rows = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);

$query = "SELECT * FROM customers ORDER BY created_at DESC LIMIT $start, $limit";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html class="light" lang="id">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Manajemen Pelanggan - Lapak Bangsawan</title>
    <link rel="icon" href="<?= BASE_URL ?>assets/images/favicon-laba.png" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&amp;display=swap"
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
</head>

<body
    class="bg-background-light dark:bg-background-dark text-slate-600 dark:text-slate-300 font-display antialiased flex h-screen overflow-hidden">
    <?php include ROOT_PATH . "includes/admin/sidebar.php"; ?>

    <main class="flex-1 flex flex-col h-full relative overflow-hidden">
        <!-- Header -->
        <!-- Header -->
        <?php $page_title = "Pelanggan";
        include ROOT_PATH . "includes/admin/header.php"; ?>

        <!-- Content -->
        <div class="flex-1 overflow-auto p-6">
            <div class="max-w-full mx-auto">
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

                <!-- Page Header -->
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                    <div>
                        <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Manajemen Pelanggan
                        </h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Kelola data pelanggan dan riwayat
                            pesanan
                            mereka.</p>
                    </div>
                </div>

                <div
                    class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-6 mb-6">
                    <div class="mb-8 w-full">
                        <div class="overflow-x-auto w-full">
                            <table class="w-full text-left text-sm text-slate-500 dark:text-slate-400">
                                <thead
                                    class="bg-slate-50 dark:bg-slate-800/50 text-xs uppercase font-semibold text-slate-500">
                                    <tr>
                                        <th class="px-6 py-4">No</th>
                                        <th class="px-6 py-4">Nama Pelanggan</th>
                                        <th class="px-6 py-4">Kontak</th>
                                        <th class="px-6 py-4">Alamat</th>
                                        <th class="px-6 py-4">Terdaftar</th>
                                        <th class="px-6 py-4 text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                                    <?php if ($result->num_rows > 0): ?>
                                        <?php $no = $start + 1; ?>
                                        <?php while ($row = $result->fetch_assoc()): ?>
                                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                                                <td class="px-6 py-4 text-slate-500 font-medium">
                                                    <?php echo $no++; ?>
                                                </td>
                                                <td class="px-6 py-4 font-medium text-slate-900 dark:text-white">
                                                    <?php echo htmlspecialchars($row['name']); ?>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <div class="flex flex-col">
                                                        <span><?php echo htmlspecialchars($row['email'] ?? '-'); ?></span>
                                                        <span
                                                            class="text-xs text-slate-400"><?php echo htmlspecialchars($row['phone']); ?></span>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 truncate max-w-xs">
                                                    <?php echo htmlspecialchars($row['address']); ?>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <?php echo date('d M Y', strtotime($row['created_at'])); ?>
                                                </td>
                                                <td class="px-6 py-4 text-right">
                                                    <button
                                                        onclick="confirmDelete('customers.php?action=delete&id=<?php echo $row['id']; ?>')"
                                                        class="text-red-500 hover:text-red-700 transition-colors">
                                                        <span class="material-icons-round text-lg">delete</span>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="px-6 py-8 text-center text-slate-400">Belum ada data
                                                pelanggan.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <?php if ($total_pages > 0):
                        // Smart pagination
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
                        class="border-t border-slate-200 dark:border-slate-800 pt-4 mt-4 flex flex-col sm:flex-row justify-between items-center gap-4">
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
                                <span class="font-semibold text-slate-700 dark:text-slate-300"><?= $start + 1 ?>–<?= min($start + $limit, $total_rows) ?></span>
                                dari
                                <span class="font-semibold text-slate-700 dark:text-slate-300"><?= number_format($total_rows) ?></span>
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
            </div>
            <?php include ROOT_PATH . "includes/admin/footer.php"; ?>
        </div>
    </main>

</body>

</html>