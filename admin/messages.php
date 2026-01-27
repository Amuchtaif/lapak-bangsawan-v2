<?php
require("auth_session.php");
require_once dirname(__DIR__) . "/config/init.php";

// Delete
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    if ($conn->query("DELETE FROM messages WHERE id=$id")) {
        $_SESSION['status_msg'] = "Pesan berhasil dihapus.";
        $_SESSION['status_type'] = "success";
    } else {
        $_SESSION['status_msg'] = "Gagal menghapus pesan.";
        $_SESSION['status_type'] = "error";
    }
    header("Location: messages");
    exit();
}

// Fetch Messages
// Pagination
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
if (!in_array($limit, [5, 10, 20]))
    $limit = 10;

$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$start = ($page > 1) ? ($page * $limit) - $limit : 0;

$count_query = "SELECT COUNT(*) as total FROM messages";
$count_result = $conn->query($count_query);
$total_rows = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);

// Fetch Messages
$result = $conn->query("SELECT * FROM messages ORDER BY created_at DESC LIMIT $start, $limit");
?>
<!DOCTYPE html>
<html class="light" lang="id">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Messages - Lapak Bangsawan</title>
    <link rel="icon" href="<?= BASE_URL ?>assets/images/favicon-laba.png" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&amp;display=swap"
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
        <?php $page_title = "Pesan Masuk";
        include ROOT_PATH . "includes/admin/header.php"; ?>

        <div class="flex-1 overflow-y-auto p-6 md:p-8 scroll-smooth">
            <div class="max-w-full mx-auto flex flex-col gap-6">
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

                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Pesan Masuk</h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Kelola pesan dan saran dari
                            pelanggan.</p>
                    </div>
                </div>

                <div
                    class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-6 mb-6">
                    <div class="flex flex-col sm:flex-row justify-between items-center gap-4 mb-4">
                        <form method="GET" class="flex items-center gap-2">
                            <label for="limit" class="text-sm text-slate-500">Tampilkan</label>
                            <select name="limit" id="limit" onchange="this.form.submit()"
                                class="text-sm border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 rounded-lg focus:ring-primary focus:border-primary">
                                <option value="5" <?php echo $limit == 5 ? 'selected' : ''; ?>>5</option>
                                <option value="10" <?php echo $limit == 10 ? 'selected' : ''; ?>>10</option>
                                <option value="20" <?php echo $limit == 20 ? 'selected' : ''; ?>>20</option>
                            </select>
                            <span class="text-sm text-slate-500">entri</span>
                        </form>
                        <span class="text-sm text-slate-500">Menampilkan <?php echo $total_rows > 0 ? $start + 1 : 0; ?>
                            sampai <?php echo min($start + $limit, $total_rows); ?> dari <?php echo $total_rows; ?>
                            entri</span>
                    </div>

                    <div class="overflow-x-auto w-full border rounded-lg border-slate-200 dark:border-slate-700">
                        <table class="w-full text-left text-sm text-slate-500 dark:text-slate-400">
                            <thead
                                class="bg-slate-50 dark:bg-slate-800/50 text-xs uppercase font-semibold text-slate-500">
                                <tr>
                                    <th class="px-6 py-4">No</th>
                                    <th class="px-6 py-4">Nama</th>
                                    <th class="px-6 py-4">Email</th>
                                    <th class="px-6 py-4">Pesan</th>
                                    <th class="px-6 py-4">Tanggal</th>
                                    <th class="px-6 py-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                <?php if ($result->num_rows > 0): ?>
                                    <?php $no = $start + 1; ?>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                                            <td class="px-6 py-4 font-medium text-slate-900 dark:text-white">
                                                <?php echo $no++; ?>
                                            </td>
                                            <td class="px-6 py-4">
                                                <?php echo htmlspecialchars($row['name']); ?>
                                            </td>
                                            <td class="px-6 py-4">
                                                <?php echo htmlspecialchars($row['email']); ?>
                                            </td>
                                            <td class="px-6 py-4">
                                                <p class="line-clamp-2"
                                                    title="<?php echo htmlspecialchars($row['message']); ?>">
                                                    <?php echo htmlspecialchars($row['message']); ?>
                                                </p>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <?php echo date('d M Y H:i', strtotime($row['created_at'])); ?>
                                            </td>
                                            <td class="px-6 py-4 text-right">
                                                <button onclick="openDeleteModal(<?php echo $row['id']; ?>)"
                                                    class="inline-flex items-center justify-center p-2 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                                                    title="Hapus Pesan">
                                                    <span class="material-icons-round text-lg">delete</span>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                            <div class="flex flex-col items-center gap-2">
                                                <span class="material-icons-round text-4xl">inbox</span>
                                                <span>Belum ada pesan masuk</span>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="flex justify-end mt-4 gap-2">
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
            <?php include ROOT_PATH . "includes/admin/footer.php"; ?>
        </div>

        <!-- Delete Confirmation Modal -->
        <div id="deleteModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog"
            aria-modal="true">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity opacity-0"
                id="deleteModalBackdrop"></div>

            <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <!-- Modal Panel -->
                    <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-card-dark text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-md opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        id="deleteModalPanel">
                        <div class="bg-white dark:bg-card-dark px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div
                                    class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30 sm:mx-0 sm:h-10 sm:w-10">
                                    <span class="material-icons-round text-red-600 text-xl">warning</span>
                                </div>
                                <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                                    <h3 class="text-base font-semibold leading-6 text-slate-900 dark:text-white"
                                        id="modal-title">Hapus Pesan</h3>
                                    <div class="mt-2">
                                        <p class="text-sm text-slate-500 dark:text-slate-400">Apakah Anda yakin ingin
                                            menghapus pesan ini? Tindakan ini tidak dapat dibatalkan.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-slate-50 dark:bg-slate-800/50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                            <a id="confirmDeleteBtn" href="#"
                                class="inline-flex w-full justify-center rounded-lg bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto transition-all">Hapus</a>
                            <button type="button" onclick="closeDeleteModal()"
                                class="mt-3 inline-flex w-full justify-center rounded-lg bg-white dark:bg-slate-700 px-3 py-2 text-sm font-semibold text-slate-900 dark:text-white shadow-sm ring-1 ring-inset ring-slate-300 dark:ring-slate-600 hover:bg-slate-50 dark:hover:bg-slate-600 sm:mt-0 sm:w-auto transition-all">Batal</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            const modal = document.getElementById('deleteModal');
            const backdrop = document.getElementById('deleteModalBackdrop');
            const panel = document.getElementById('deleteModalPanel');
            const confirmBtn = document.getElementById('confirmDeleteBtn');

            function openDeleteModal(id) {
                confirmBtn.href = '?action=delete&id=' + id;
                modal.classList.remove('hidden');
                // Slight delay to allow display:block to apply before transitioning opacity
                setTimeout(() => {
                    backdrop.classList.remove('opacity-0');
                    panel.classList.remove('opacity-0', 'translate-y-4', 'sm:translate-y-0', 'sm:scale-95');
                    panel.classList.add('opacity-100', 'translate-y-0', 'sm:scale-100');
                }, 10);
            }

            function closeDeleteModal() {
                backdrop.classList.add('opacity-0');
                panel.classList.remove('opacity-100', 'translate-y-0', 'sm:scale-100');
                panel.classList.add('opacity-0', 'translate-y-4', 'sm:translate-y-0', 'sm:scale-95');
                setTimeout(() => {
                    modal.classList.add('hidden');
                }, 300); // Match transition duration
            }

            // Close on backdrop click
            modal.addEventListener('click', (e) => {
                if (e.target.closest('#deleteModalPanel') === null) {
                    closeDeleteModal();
                }
            });
        </script>

</html>