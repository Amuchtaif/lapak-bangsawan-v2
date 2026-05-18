<?php
require("auth_session.php");
require_once dirname(__DIR__) . "/config/init.php";

// Pagination
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 25;
if (!in_array($limit, [10, 25, 50, 100])) {
    $limit = 25;
}
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$start = ($page - 1) * $limit;

// Filter Search
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$where = "";
if ($search) {
    $where = " WHERE admin_name LIKE '%$search%' OR action LIKE '%$search%' OR details LIKE '%$search%' ";
}

// Fetch Logs
$sql = "SELECT * FROM activity_logs $where ORDER BY created_at DESC LIMIT $start, $limit";
$result = $conn->query($sql);

// Count Total for Pagination
$total_res = $conn->query("SELECT COUNT(id) as total FROM activity_logs $where");
$total_rows = $total_res->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);
?>
<!DOCTYPE html>
<html class="light" lang="id">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Log Aktivitas Admin - Lapak Bangsawan</title>
    <link rel="icon" href="<?= BASE_URL ?>assets/images/favicon-laba.png" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap"
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
                    fontFamily: {
                        "display": ["Inter", "sans-serif"]
                    },
                },
            },
        }
    </script>
</head>

<body
    class="bg-background-light dark:bg-background-dark text-slate-600 dark:text-slate-300 font-display transition-colors duration-200 antialiased overflow-hidden h-screen flex">

    <?php include ROOT_PATH . "includes/admin/sidebar.php"; ?>

    <main class="flex-1 flex flex-col h-full relative overflow-hidden">
        <?php $page_title = "Log Aktivitas Admin";
        include ROOT_PATH . "includes/admin/header.php"; ?>

        <div class="flex-1 overflow-y-auto p-6 md:p-8 scroll-smooth">
            <div class="max-w-full mx-auto">

                <!-- Header & Search -->
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Log Aktivitas</h1>
                        <p class="text-slate-500 dark:text-slate-400 mt-1">Riwayat semua tindakan yang dilakukan admin di sistem.</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <form action="" method="GET" class="flex items-center gap-2">
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 material-icons-round text-slate-400 text-sm">search</span>
                                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Cari admin, aksi, atau detail..."
                                    class="pl-9 pr-4 py-2 text-sm bg-white dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary shadow-sm text-slate-700 dark:text-white w-64 md:w-80">
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Table Container -->
                <div class="bg-surface-light dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-slate-500 dark:text-slate-400">
                            <thead class="bg-slate-50 dark:bg-slate-800/50 text-xs uppercase font-semibold text-slate-500">
                                <tr>
                                    <th class="px-6 py-4">Waktu</th>
                                    <th class="px-6 py-4">Admin</th>
                                    <th class="px-6 py-4 text-center">Tindakan/Aksi</th>
                                    <th class="px-6 py-4">Informasi Detail</th>
                                    <th class="px-6 py-4 text-center">IP Address</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                                <?php if (!$result || $result->num_rows == 0): ?>
                                    <tr>
                                        <td colspan="5" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                                            <span class="material-icons-round text-4xl text-slate-300 mb-2">history</span>
                                            <p>Belum ada log aktivitas yang tercatat.</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap text-xs font-medium">
                                                <?= date('d M Y, H:i', strtotime($row['created_at'])) ?>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="flex items-center gap-2">
                                                    <div class="size-8 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center">
                                                        <span class="material-icons-round text-slate-400 text-sm">person</span>
                                                    </div>
                                                    <span class="font-bold text-slate-900 dark:text-white"><?= htmlspecialchars($row['admin_name']) ?></span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 text-center">
                                                <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider <?php
                                                    $action = strtoupper($row['action']);
                                                    if(strpos($action, 'LOGIN') !== false) echo 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400';
                                                    elseif(strpos($action, 'CREATE') !== false || strpos($action, 'TAMBAH') !== false) echo 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400';
                                                    elseif(strpos($action, 'UPDATE') !== false || strpos($action, 'EDIT') !== false) echo 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400';
                                                    elseif(strpos($action, 'DELETE') !== false || strpos($action, 'HAPUS') !== false) echo 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400';
                                                    else echo 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-400';
                                                ?>">
                                                    <?= htmlspecialchars($row['action']) ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4">
                                                <p class="text-xs break-all"><?= htmlspecialchars($row['details']) ?></p>
                                            </td>
                                            <td class="px-6 py-4 text-center text-[10px] text-slate-400">
                                                <?= $row['ip_address'] ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination Controls -->
                    <?php if ($total_pages > 0): ?>
                        <?php
                        // Smart pagination: build array of pages to show
                        $pages_to_show = [];
                        $adjacents = 1; // pages on each side of current

                        if ($total_pages <= 7) {
                            // Show all pages if 7 or less
                            for ($i = 1; $i <= $total_pages; $i++) $pages_to_show[] = $i;
                        } else {
                            // Always show first page
                            $pages_to_show[] = 1;

                            // Calculate range around current page
                            $range_start = max(2, $page - $adjacents);
                            $range_end = min($total_pages - 1, $page + $adjacents);

                            // Add ellipsis before range if needed
                            if ($range_start > 2) $pages_to_show[] = '...';

                            // Add range pages
                            for ($i = $range_start; $i <= $range_end; $i++) $pages_to_show[] = $i;

                            // Add ellipsis after range if needed
                            if ($range_end < $total_pages - 1) $pages_to_show[] = '...';

                            // Always show last page
                            $pages_to_show[] = $total_pages;
                        }

                        $base_url = "?search=" . urlencode($search) . "&limit=$limit";
                        ?>
                        <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row justify-between items-center gap-4 bg-slate-50/30 dark:bg-slate-800/20">
                            <!-- Left: Per page & info -->
                            <div class="flex items-center gap-2 text-xs text-slate-500">
                                <span>Tampilkan</span>
                                <select onchange="window.location.href='<?= $base_url ?>&page=1'.replace('limit=<?= $limit ?>','limit='+this.value)"
                                    class="bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-lg text-xs py-1.5 px-3 pr-7 focus:ring-primary focus:border-primary cursor-pointer text-slate-700 dark:text-slate-300">
                                    <option value="10" <?= $limit == 10 ? 'selected' : '' ?>>10</option>
                                    <option value="25" <?= $limit == 25 ? 'selected' : '' ?>>25</option>
                                    <option value="50" <?= $limit == 50 ? 'selected' : '' ?>>50</option>
                                    <option value="100" <?= $limit == 100 ? 'selected' : '' ?>>100</option>
                                </select>
                                <span class="text-slate-400">|</span>
                                <span class="text-slate-500">
                                    <span class="font-semibold text-slate-700 dark:text-slate-300"><?= $total_rows > 0 ? $start + 1 : 0 ?>–<?= min($start + $limit, $total_rows) ?></span>
                                    dari
                                    <span class="font-semibold text-slate-700 dark:text-slate-300"><?= number_format($total_rows) ?></span>
                                </span>
                            </div>

                            <!-- Right: Page buttons -->
                            <nav class="flex items-center gap-1" aria-label="Pagination">
                                <!-- Prev button -->
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

                                <!-- Page numbers with ellipsis -->
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

                                <!-- Next button -->
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

                <?php include ROOT_PATH . "includes/admin/footer.php"; ?>
            </div>
        </div>
    </main>
</body>

</html>
