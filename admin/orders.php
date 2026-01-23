<?php
require("auth_session.php");
require_once dirname(__DIR__) . "/config/init.php";

// Update Status
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    if ($conn->query("UPDATE orders SET status='$status' WHERE id=$order_id")) {
        $_SESSION['status_msg'] = "Order status updated successfully to " . ucfirst($status) . ".";
        $_SESSION['status_type'] = "success";
    } else {
        $_SESSION['status_msg'] = "Failed to update order status.";
        $_SESSION['status_type'] = "error";
    }
    header("Location: orders.php?action=view&id=$order_id");
    exit();
}

// Delete Order
// Delete Order
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $order_id = intval($_GET['id']);
    // First delete order items
    $conn->query("DELETE FROM order_items WHERE order_id=$order_id");
    // Then delete order
    if ($conn->query("DELETE FROM orders WHERE id=$order_id")) {
        $_SESSION['status_msg'] = "Order #$order_id has been deleted.";
        $_SESSION['status_type'] = "success";
    } else {
        $_SESSION['status_msg'] = "Failed to delete order.";
        $_SESSION['status_type'] = "error";
    }
    header("Location: orders.php");
    exit();
}

// Fetch Orders with Pagination
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
if (!in_array($limit, [5, 10, 20]))
    $limit = 10;

$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$start = ($page > 1) ? ($page * $limit) - $limit : 0;

// Get total records
$total_result = $conn->query("SELECT COUNT(*) as count FROM orders");
$total_row = $total_result->fetch_assoc();
$total_orders = $total_row['count'];
$total_pages = ceil($total_orders / $limit);

$orders_query = "SELECT * FROM orders ORDER BY created_at DESC LIMIT $start, $limit";
$orders_result = $conn->query($orders_query);
?>
<!DOCTYPE html>
<html class="light" lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Order Management - Lapak Bangsawan</title>
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
        <?php $page_title = "Pesanan";
        include ROOT_PATH . "includes/admin/header.php"; ?>
        <div class="flex-1 overflow-y-auto p-6 md:p-8 scroll-smooth flex flex-col">
            <div class="max-w-7xl mx-auto w-full flex flex-col gap-6 flex-grow">

                <?php if (isset($_GET['action']) && $_GET['action'] == 'view' && isset($_GET['id'])):
                    $oid = intval($_GET['id']);
                    $order_q = "SELECT * FROM orders WHERE id=$oid";
                    $order_data = mysqli_fetch_assoc($conn->query($order_q));

                    // Try to join with products to get image if possible, but purely optional
                    $items_q = "SELECT order_items.*, products.image FROM order_items LEFT JOIN products ON order_items.product_name = products.name WHERE order_id=$oid";
                    $items_res = $conn->query($items_q);
                    ?>
                    <!-- Order Detail View -->
                    <div class="flex flex-col gap-6">
                        <!-- Notification Area -->
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
                                    <p class="text-sm text-slate-500 dark:text-slate-400"><?php echo $_SESSION['status_msg']; ?>
                                    </p>
                                </div>
                            </div>
                            <?php unset($_SESSION['status_msg']);
                            unset($_SESSION['status_type']); ?>
                        <?php endif; ?>
                        <div class="flex items-center justify-between">
                            <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Pesanan
                                #<?php echo str_pad($order_data['id'], 5, '0', STR_PAD_LEFT); ?></h2>
                            <div class="flex gap-2">
                                <button
                                    onclick="confirmDelete('orders.php?action=delete&id=<?php echo $order_data['id']; ?>')"
                                    class="px-4 py-2 bg-red-100 hover:bg-red-200 text-red-700 rounded-lg text-sm font-bold transition-colors flex items-center gap-2">
                                    <span class="material-icons-round text-sm">delete</span> Hapus
                                </button>
                                <a href="orders.php"
                                    class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-sm font-bold transition-colors flex items-center gap-2">
                                    <span class="material-icons-round text-sm">arrow_back</span>
                                    Kembali
                                </a>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Customer Info -->
                            <div
                                class="bg-surface-light dark:bg-surface-dark p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
                                <h3 class="font-bold text-slate-900 dark:text-white mb-4">Detail Pelanggan</h3>
                                <p class="text-sm text-slate-600 dark:text-slate-400 mb-2">Nama: <span
                                        class="font-medium text-slate-900 dark:text-white block"><?php echo htmlspecialchars($order_data['customer_name']); ?></span>
                                </p>
                                <p class="text-sm text-slate-600 dark:text-slate-400 mb-2">Telepon: <span
                                        class="font-medium text-slate-900 dark:text-white block"><?php echo htmlspecialchars($order_data['customer_phone']); ?></span>
                                </p>
                                <p class="text-sm text-slate-600 dark:text-slate-400">Tanggal: <span
                                        class="font-medium text-slate-900 dark:text-white block"><?php echo date('M d, Y H:i', strtotime($order_data['created_at'])); ?></span>
                                </p>
                            </div>
                            <!-- Delivery Info -->
                            <div
                                class="bg-surface-light dark:bg-surface-dark p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
                                <h3 class="font-bold text-slate-900 dark:text-white mb-4">Info Pengiriman</h3>
                                <p class="text-sm text-slate-600 dark:text-slate-400 whitespace-pre-line mb-4">
                                    <?php echo htmlspecialchars($order_data['customer_address']); ?>
                                </p>

                                <?php if (!empty($order_data['order_notes'])): ?>
                                    <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-700">
                                        <h4 class="font-bold text-sm text-slate-900 dark:text-white mb-1">Catatan Pesanan</h4>
                                        <p class="text-sm text-amber-600 dark:text-amber-400 italic">
                                            "<?php echo htmlspecialchars($order_data['order_notes']); ?>"
                                        </p>
                                    </div>
                                <?php endif; ?>

                                <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-700">
                                    <h4 class="font-bold text-sm text-slate-900 dark:text-white mb-1">Metode Pembayaran</h4>
                                    <p class="text-sm text-slate-600 dark:text-slate-400 capitalize">
                                        <?php
                                        $pm = isset($order_data['payment_method']) ? $order_data['payment_method'] : 'transfer';
                                        echo $pm == 'cod' ? 'COD (Bayar di Tempat)' : 'Transfer Bank';
                                        ?>
                                    </p>
                                </div>
                            </div>
                            <!-- Order Status Update -->
                            <div
                                class="bg-surface-light dark:bg-surface-dark p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
                                <h3 class="font-bold text-slate-900 dark:text-white mb-4">Status Pesanan</h3>
                                <form method="POST">
                                    <input type="hidden" name="order_id" value="<?php echo $order_data['id']; ?>">
                                    <input type="hidden" name="update_status" value="1">
                                    <select name="status"
                                        class="w-full rounded-lg border-slate-200 bg-white dark:bg-slate-800 dark:border-slate-700 text-slate-700 dark:text-slate-300 mb-4 px-3 py-2 text-sm focus:ring-primary focus:border-primary">
                                        <option value="pending" <?php echo $order_data['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="completed" <?php echo $order_data['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                        <option value="cancelled" <?php echo $order_data['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                    <button type="submit"
                                        class="w-full bg-primary text-white py-2 rounded-lg font-medium hover:bg-blue-600 transition-colors shadow-sm">Perbarui
                                        Status</button>
                                </form>
                            </div>
                        </div>
                        <!-- Order Items -->
                        <div
                            class="bg-surface-light dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
                            <div class="overflow-x-auto w-full">
                                <table class="w-full text-left text-sm text-slate-500 dark:text-slate-400">
                                    <thead
                                        class="bg-slate-50 dark:bg-slate-800/50 text-xs uppercase font-semibold text-slate-500">
                                        <tr>
                                            <th class="px-6 py-4 whitespace-nowrap">Produk</th>
                                            <th class="px-6 py-4 whitespace-nowrap">Harga/Kg</th>
                                            <th class="px-6 py-4 whitespace-nowrap">Berat (Kg)</th>
                                            <th class="px-6 py-4 text-right whitespace-nowrap">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                                        <?php while ($item = mysqli_fetch_assoc($items_res)): ?>
                                            <tr>
                                                <td class="px-6 py-4">
                                                    <div class="flex items-center gap-3 min-w-[200px]">
                                                        <?php if (isset($item['image']) && $item['image']): ?>
                                                            <img src="../<?php echo $item['image']; ?>"
                                                                class="h-10 w-10 object-cover rounded">
                                                        <?php endif; ?>
                                                        <span
                                                            class="font-medium text-slate-900 dark:text-white"><?php echo htmlspecialchars($item['product_name']); ?></span>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">Rp
                                                    <?php echo number_format($item['price_per_kg'], 0, ',', '.'); ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap"><?php echo $item['weight']; ?> kg</td>
                                                <td
                                                    class="px-6 py-4 text-right font-medium text-slate-900 dark:text-white whitespace-nowrap">
                                                    Rp
                                                    <?php echo number_format($item['subtotal'], 0, ',', '.'); ?>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                        <tr class="bg-slate-50 dark:bg-slate-800/20">
                                            <td colspan="3"
                                                class="px-6 py-4 text-right font-bold text-slate-900 dark:text-white whitespace-nowrap">
                                                Total
                                                Harga</td>
                                            <td
                                                class="px-6 py-4 text-right font-bold text-slate-900 dark:text-white text-lg whitespace-nowrap">
                                                Rp
                                                <?php echo number_format($order_data['total_amount'], 0, ',', '.'); ?>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- List View -->
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div>
                            <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Manajemen Pesanan
                            </h2>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Kelola pesanan, lacak berat, dan
                                proses pengiriman.</p>
                        </div>
                        <a href="manual_transaction.php"
                            class="px-4 py-2 bg-primary hover:bg-blue-600 text-white rounded-lg text-sm font-bold transition-colors flex items-center gap-2 shadow-lg shadow-blue-500/30">
                            <span class="material-icons-round text-sm">add</span> Tambah Pesanan Manual
                        </a>
                    </div>

                    <div
                        class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-6 mb-6">

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
                                    <p class="text-sm text-slate-500 dark:text-slate-400"><?php echo $_SESSION['status_msg']; ?>
                                    </p>
                                </div>
                            </div>
                            <?php unset($_SESSION['status_msg']);
                            unset($_SESSION['status_type']); ?>
                        <?php endif; ?>

                        <div class="mb-8 w-full">
                            <div class="overflow-x-auto w-full">
                                <table class="w-full text-left text-sm text-slate-500 dark:text-slate-400">
                                    <thead
                                        class="bg-slate-50 dark:bg-slate-800/50 text-xs uppercase font-semibold text-slate-500">
                                        <tr>
                                            <th class="px-6 py-4">
                                                ID Pesanan</th>
                                            <th class="px-6 py-4">
                                                Tanggal</th>
                                            <th class="px-6 py-4">
                                                Waktu</th>
                                            <th class="px-6 py-4">
                                                Pelanggan</th>
                                            <th class="px-6 py-4">
                                                Total Harga</th>
                                            <th class="px-6 py-4">
                                                Status</th>
                                            <th class="px-6 py-4 text-right">
                                                Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                                        <?php while ($order = mysqli_fetch_assoc($orders_result)): ?>
                                            <tr class="group hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                                                <td class="px-6 py-4">
                                                    <span
                                                        class="font-medium text-slate-900 dark:text-white">#<?php echo str_pad($order['id'], 5, '0', STR_PAD_LEFT); ?></span>
                                                </td>
                                                <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">
                                                    <?php echo date('d M, Y', strtotime($order['created_at'])); ?>
                                                </td>
                                                <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">
                                                    <?php echo date('H:i', strtotime($order['created_at'])); ?> WIB
                                                </td>
                                                <td class="px-6 py-4">
                                                    <div class="flex flex-col">
                                                        <span
                                                            class="text-sm font-medium text-slate-900 dark:text-white"><?php echo htmlspecialchars($order['customer_name']); ?></span>
                                                        <span
                                                            class="text-xs text-slate-500"><?php echo htmlspecialchars($order['customer_phone']); ?></span>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 text-sm font-medium text-slate-900 dark:text-white">
                                                    Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <?php
                                                    $status_colors = [
                                                        'pending' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400',
                                                        'completed' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
                                                        'cancelled' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
                                                    ];
                                                    $curr_status = $order['status'];
                                                    $color_class = $status_colors[$curr_status] ?? 'bg-slate-100 text-slate-600';
                                                    ?>
                                                    <span
                                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $color_class; ?> capitalize">
                                                        <?php echo $curr_status; ?>
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 text-right">
                                                    <div class="flex flex-col sm:flex-row gap-2 justify-end">
                                                        <a href="orders.php?action=view&id=<?php echo $order['id']; ?>"
                                                            class="inline-flex items-center justify-center rounded-lg size-8 text-xs font-medium bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-700 transition-colors shadow-sm"
                                                            title="Detail Pesanan">
                                                            <span class="material-icons-round text-lg">visibility</span>
                                                        </a>
                                                        <button
                                                            onclick="confirmDelete('orders.php?action=delete&id=<?php echo $order['id']; ?>')"
                                                            class="inline-flex items-center justify-center rounded-lg size-8 text-xs font-medium bg-red-50 border border-red-200 text-red-700 hover:bg-red-100 dark:bg-red-900/20 dark:border-red-800 dark:text-red-400 dark:hover:bg-red-900/30 transition-colors shadow-sm"
                                                            title="Hapus Pesanan">
                                                            <span class="material-icons-round text-lg">delete</span>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                        <?php if (mysqli_num_rows($orders_result) == 0): ?>
                                            <tr>
                                                <td colspan="7"
                                                    class="px-6 py-8 text-center text-slate-500 dark:text-slate-400">
                                                    <div class="flex flex-col items-center justify-center gap-2">
                                                        <span
                                                            class="material-icons-round text-4xl text-slate-300 dark:text-slate-600">inbox</span>
                                                        <p>Belum ada pesanan masuk.</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Pagination Controls -->
                        <?php if (!isset($_GET['action'])): ?>
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
                                    <span class="ml-2 hidden sm:inline"> | Menampilkan <?php echo $start + 1; ?> sampai
                                        <?php echo min($start + $limit, $total_orders); ?> dari <?php echo $total_orders; ?>
                                        entri</span>
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
                        <?php endif; ?>
                    </div>
                <?php endif; ?>





            </div>
            <?php include ROOT_PATH . "includes/admin/footer.php"; ?>
        </div>
    </main>
</body>

</html>