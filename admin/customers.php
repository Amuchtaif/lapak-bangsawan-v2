<?php
require("auth_session.php");
require("../config/database.php");

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
    header("Location: customers.php");
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
    <link rel="icon" href="../assets/images/favicon-laba.png" type="image/x-icon">
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
    <?php include("sidebar.php"); ?>

    <main class="flex-1 flex flex-col h-full relative overflow-hidden">
        <!-- Header -->
        <!-- Header -->
        <?php $page_title = "Pelanggan";
        include("header.php"); ?>

        <!-- Content -->
        <div class="flex-1 overflow-auto p-6">
            <div class="max-w-7xl mx-auto">
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

                    <div
                        class="border-t border-slate-200 dark:border-slate-800 pt-4 mt-4 flex flex-col sm:flex-row justify-between items-center gap-4">
                        <div class="flex items-center gap-2 text-sm text-slate-500">
                            <span>Tampilkan</span>
                            <select onchange="window.location.href='?limit='+this.value+'&page=1'"
                                class="bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded text-xs py-2 px-6 focus:ring-primary focus:border-primary">
                                <option value="5" <?php echo $limit == 5 ? 'selected' : ''; ?>>5</option>
                                <option value="10" <?php echo $limit == 10 ? 'selected' : ''; ?>>10</option>
                                <option value="20" <?php echo $limit == 20 ? 'selected' : ''; ?>>20</option>
                            </select>
                            <span>entri</span>
                            <span class="ml-2 hidden sm:inline"> | Menampilkan <?php echo $start + 1; ?> -
                                <?php echo min($start + $limit, $total_rows); ?> dari <?php echo $total_rows; ?>
                                data</span>
                        </div>

                        <div class="flex gap-2">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?php echo $page - 1; ?>&limit=<?php echo $limit; ?>"
                                    class="px-3 py-1 text-xs border border-slate-200 dark:border-slate-700 rounded bg-white dark:bg-slate-800 text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">Sebelumnya</a>
                            <?php else: ?>
                                <button disabled
                                    class="px-3 py-1 text-xs border border-slate-200 dark:border-slate-700 rounded bg-white dark:bg-slate-800 text-slate-300 cursor-not-allowed">Sebelumnya</button>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <a href="?page=<?php echo $i; ?>&limit=<?php echo $limit; ?>"
                                    class="px-3 py-1 text-xs border <?php echo $i == $page ? 'border-primary bg-primary text-white' : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-700'; ?> rounded transition-colors"><?php echo $i; ?></a>
                            <?php endfor; ?>

                            <?php if ($page < $total_pages): ?>
                                <a href="?page=<?php echo $page + 1; ?>&limit=<?php echo $limit; ?>"
                                    class="px-3 py-1 text-xs border border-slate-200 dark:border-slate-700 rounded bg-white dark:bg-slate-800 text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">Selanjutnya</a>
                            <?php else: ?>
                                <button disabled
                                    class="px-3 py-1 text-xs border border-slate-200 dark:border-slate-700 rounded bg-white dark:bg-slate-800 text-slate-300 cursor-not-allowed">Selanjutnya</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php include("footer.php"); ?>
        </div>
    </main>

</body>

</html>