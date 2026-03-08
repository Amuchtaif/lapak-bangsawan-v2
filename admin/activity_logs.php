<?php
require("auth_session.php");
require_once dirname(__DIR__) . "/config/init.php";

// Pagination
$limit = 25;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
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

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                    <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between bg-slate-50/30 dark:bg-slate-800/20">
                        <div class="text-xs text-slate-500">
                            Menampilkan <?= $start + 1 ?> ke <?= min($start + $limit, $total_rows) ?> dari <?= $total_rows ?> entri
                        </div>
                        <div class="flex items-center gap-1">
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>" 
                                   class="size-8 flex items-center justify-center rounded-lg text-xs font-bold transition-all <?= $i == $page ? 'bg-primary text-white shadow-lg shadow-blue-500/30' : 'bg-white dark:bg-surface-dark text-slate-500 hover:text-primary' ?>">
                                    <?= $i ?>
                                </a>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <?php include ROOT_PATH . "includes/admin/footer.php"; ?>
            </div>
        </div>
    </main>
</body>

</html>
