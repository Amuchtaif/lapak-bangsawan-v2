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
    header("Location: orders?action=view&id=$order_id");
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
    header("Location: orders");
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body
    class="bg-background-light dark:bg-background-dark text-slate-600 dark:text-slate-300 font-display transition-colors duration-200 antialiased overflow-hidden h-screen flex">
    <?php include ROOT_PATH . "includes/admin/sidebar.php"; ?>
    <main class="flex-1 flex flex-col h-full relative overflow-hidden">
        <?php $page_title = "Pesanan";
        include ROOT_PATH . "includes/admin/header.php"; ?>
        <div class="flex-1 overflow-y-auto p-6 md:p-8 scroll-smooth flex flex-col">
            <div class="max-w-full mx-auto w-full flex flex-col gap-6 flex-grow">

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
                                #<?php echo htmlspecialchars($order_data['order_number'] ?? str_pad($order_data['id'], 5, '0', STR_PAD_LEFT)); ?>
                            </h2>
                            <div class="flex gap-2">
                                <?php if ($order_data['status'] !== 'cancelled' && $order_data['status'] !== 'completed'): ?>
                                    <button onclick="cancelOrder(<?php echo $order_data['id']; ?>)"
                                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-bold transition-colors flex items-center gap-2 shadow-sm shadow-red-500/30">
                                        <span class="material-icons-round text-sm">cancel</span> Batalkan Pesanan
                                    </button>
                                <?php endif; ?>

                                <?php if ($order_data['status'] == 'ready_to_ship' && empty($order_data['biteship_order_id'])): ?>
                                    <button onclick="syncBiteshipId(<?php echo $order_data['id']; ?>)"
                                        class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-sm font-bold transition-colors flex items-center gap-2 shadow-sm shadow-amber-500/30"
                                        title="Sinkronisasi ID Biteship yang hilang">
                                        <span class="material-icons-round text-sm">sync_problem</span> Sync Data Biteship
                                    </button>
                                <?php endif; ?>

                                <button onclick="confirmDelete('orders?action=delete&id=<?php echo $order_data['id']; ?>')"
                                    class="px-4 py-2 bg-red-100 hover:bg-red-200 text-red-700 rounded-lg text-sm font-bold transition-colors flex items-center gap-2">
                                    <span class="material-icons-round text-sm">delete</span> Hapus
                                </button>
                                <a href="orders"
                                    class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-sm font-bold transition-colors flex items-center gap-2">
                                    <span class="material-icons-round text-sm">arrow_back</span>
                                    Kembali
                                </a>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-4 gap-6 mb-6">
                            <!-- Customer & Delivery Info (Merged) -->
                            <div
                                class="bg-surface-light dark:bg-surface-dark p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm h-full flex flex-col gap-6">

                                <!-- Customer Section -->
                                <div>
                                    <h3
                                        class="font-bold text-slate-900 dark:text-white mb-4 border-b pb-2 dark:border-slate-700">
                                        Detail Pelanggan</h3>
                                    <div class="space-y-3">
                                        <p class="text-sm text-slate-600 dark:text-slate-400">
                                            <span class="text-xs uppercase font-bold text-slate-500 block mb-1">Nama</span>
                                            <span
                                                class="font-medium text-slate-900 dark:text-white block text-base"><?php echo htmlspecialchars($order_data['customer_name']); ?></span>
                                        </p>
                                        <p class="text-sm text-slate-600 dark:text-slate-400">
                                            <span
                                                class="text-xs uppercase font-bold text-slate-500 block mb-1">Telepon</span>
                                            <span
                                                class="font-medium text-slate-900 dark:text-white block"><?php echo htmlspecialchars($order_data['customer_phone']); ?></span>
                                        </p>
                                        <p class="text-sm text-slate-600 dark:text-slate-400">
                                            <span class="text-xs uppercase font-bold text-slate-500 block mb-1">Tanggal
                                                Pesanan</span>
                                            <span
                                                class="font-medium text-slate-900 dark:text-white block"><?php echo date('d M Y, H:i', strtotime($order_data['created_at'])); ?>
                                                WIB</span>
                                        </p>
                                    </div>
                                </div>

                                <!-- Delivery Section -->
                                <div class="pt-6 border-t border-slate-100 dark:border-slate-700/50">
                                    <h3
                                        class="font-bold text-slate-900 dark:text-white mb-4 border-b pb-2 dark:border-slate-700">
                                        Info Pengiriman</h3>
                                    <div class="space-y-4">
                                        <div>
                                            <p class="text-[10px] text-slate-500 uppercase font-bold tracking-widest mb-1">
                                                Kurir</p>
                                            <p
                                                class="text-sm font-semibold text-slate-900 dark:text-white flex items-center gap-2">
                                                <span
                                                    class="material-icons-round text-primary text-base">local_shipping</span>
                                                <?php
                                                if (!empty($order_data['courier_company'])) {
                                                    echo strtoupper($order_data['courier_company']) . " (" . ($order_data['courier_type'] ?? 'REG') . ")";
                                                } else {
                                                    echo "Internal / Pickup";
                                                }
                                                ?>
                                            </p>
                                        </div>

                                        <div>
                                            <p class="text-[10px] text-slate-500 uppercase font-bold tracking-widest mb-1">
                                                Alamat Tujuan</p>
                                            <p class="text-sm text-slate-900 dark:text-white flex items-center gap-2">
                                                <?php echo htmlspecialchars($order_data['customer_address']); ?>
                                            </p>
                                        </div>

                                        <?php if (!empty($order_data['order_notes'])): ?>
                                            <div
                                                class="bg-amber-50 dark:bg-amber-900/10 p-3 rounded-lg border border-amber-100 dark:border-amber-900/30">
                                                <p
                                                    class="text-[10px] text-amber-600 dark:text-amber-500 uppercase font-bold tracking-widest mb-1">
                                                    Catatan</p>
                                                <p class="text-sm text-amber-700 dark:text-amber-400 italic">
                                                    "<?php echo htmlspecialchars($order_data['order_notes']); ?>"
                                                </p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Payment Info (New Card) -->
                            <div
                                class="bg-surface-light dark:bg-surface-dark p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm h-full flex flex-col">
                                <h3
                                    class="font-bold text-slate-900 dark:text-white mb-4 border-b pb-2 dark:border-slate-700">
                                    Informasi Pembayaran</h3>

                                <div class="space-y-4 flex-1">
                                    <?php
                                    $pm = isset($order_data['payment_method']) ? $order_data['payment_method'] : 'transfer';
                                    ?>

                                    <?php if (strtolower($pm) == 'cod'): ?>
                                        <!-- COD View -->
                                        <div class="flex flex-col items-center justify-center py-6 text-center h-full">
                                            <div
                                                class="size-16 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center mb-3">
                                                <span class="material-icons-round text-green-600 text-3xl">payments</span>
                                            </div>
                                            <h4 class="font-bold text-lg text-green-700 dark:text-green-400 mb-1">Cash On
                                                Delivery
                                            </h4>
                                            <p class="text-xs text-slate-500">Bayar ditempat saat barang diterima</p>
                                        </div>

                                    <?php else: ?>
                                        <!-- Transfer View -->
                                        <div>
                                            <p class="text-[10px] text-slate-500 uppercase font-bold tracking-widest mb-1">
                                                Metode
                                            </p>
                                            <div class="flex items-center gap-2 mb-3">
                                                <span
                                                    class="material-icons-round text-blue-600 text-base">account_balance</span>
                                                <span class="text-sm font-bold text-slate-900 dark:text-white">Transfer Bank
                                                    (BSI)</span>
                                            </div>
                                        </div>

                                        <div>
                                            <p class="text-[10px] text-slate-500 uppercase font-bold tracking-widest mb-1">Total
                                                Bayar</p>
                                            <p class="text-lg font-black text-primary">
                                                Rp <?php echo number_format($order_data['total_amount'], 0, ',', '.'); ?>
                                            </p>
                                        </div>

                                        <!-- Payment Proof -->
                                        <div class="pt-2 border-t border-slate-100 dark:border-slate-700 mt-2">
                                            <p class="text-[10px] text-slate-500 uppercase font-bold tracking-widest mb-2">Bukti
                                                Transfer</p>
                                            <?php if (!empty($order_data['payment_proof'])): ?>
                                                <div class="relative group cursor-pointer w-24 h-24 rounded-lg overflow-hidden border border-slate-200 dark:border-slate-700"
                                                    onclick="openLightbox('<?php echo BASE_URL . $order_data['payment_proof']; ?>')">
                                                    <img src="<?php echo BASE_URL . $order_data['payment_proof']; ?>"
                                                        alt="Bukti Transfer"
                                                        class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                                                    <div
                                                        class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                                        <span class="material-icons-round text-white">zoom_in</span>
                                                    </div>
                                                </div>
                                            <?php else: ?>
                                                <div
                                                    class="w-full py-3 bg-slate-50 dark:bg-slate-800 rounded-lg flex items-center justify-center text-slate-400 text-xs italic gap-1">
                                                    <span class="material-icons-round text-sm">image_not_supported</span>
                                                    Belum upload
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <!-- Biteship Fulfillment -->
                            <div
                                class="bg-surface-light dark:bg-surface-dark p-6 rounded-xl border border-primary/30 dark:border-primary/20 shadow-sm relative overflow-hidden h-full flex flex-col">
                                <div class="absolute top-0 right-0 p-2 opacity-10">
                                    <span class="material-symbols-outlined text-4xl text-primary">local_shipping</span>
                                </div>
                                <h3 class="font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                                    <span class="material-icons-round text-primary text-xl">local_shipping</span>
                                    Fulfillment Logistics
                                </h3>

                                <?php if (empty($order_data['tracking_id'])): ?>
                                    <div id="fulfillment-pending">
                                        <?php if (empty($order_data['courier_company']) || in_array(strtolower($order_data['courier_company']), ['local', 'internal', 'pickup'])): ?>
                                            <!-- Local Courier View -->
                                            <div
                                                class="bg-blue-50 dark:bg-blue-900/10 p-4 rounded-lg border border-blue-100 dark:border-blue-900/30 mb-4">
                                                <p class="text-sm font-bold text-blue-700 dark:text-blue-400 flex items-center gap-2">
                                                    <span class="material-icons-round text-base">local_shipping</span>
                                                    Kurir Internal / Toko
                                                </p>
                                                <p class="text-xs text-blue-600 dark:text-blue-500 mt-1">
                                                    Pengiriman dilakukan oleh kurir toko. Tidak perlu request pickup ke logistik
                                                    eksternal.
                                                </p>
                                            </div>
                                            <div
                                                class="w-full bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 py-3 rounded-lg font-medium flex justify-center items-center gap-3 border border-dashed border-slate-300 dark:border-slate-600 select-none cursor-default">
                                                <span class="material-icons-round text-lg">check_circle</span>
                                                Pickup Otomatis / Manual
                                            </div>
                                        <?php else: ?>
                                            <!-- External Courier View -->
                                            <p class="text-sm text-slate-600 dark:text-slate-400 mb-6">
                                                Order ini siap diproses untuk pengiriman via kurir pilihan. Klik tombol di bawah
                                                untuk Booking Kurir & Pickup Paket.
                                            </p>
                                            <button onclick="processShipping(<?php echo $order_data['id']; ?>)"
                                                id="btn-process-shipping"
                                                class="w-full bg-primary text-white py-3 rounded-lg font-bold hover:bg-blue-600 transition-all shadow-lg shadow-blue-500/30 flex justify-center items-center gap-2">
                                                <span class="material-icons-round text-lg">local_shipping</span>
                                                Request Pickup
                                                <span id="shipping-spinner"
                                                    class="hidden animate-spin rounded-full h-4 w-4 border-b-2 border-white"></span>
                                            </button>
                                            <p class="text-[10px] text-slate-400 mt-3 text-center italic">*Data akan dikirim ke API
                                                Biteship</p>
                                        <?php endif; ?>
                                <?php else: ?>
                                    <div class="text-center py-2">
                                        <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest mb-1">Nomor
                                            Resi / Waybill</p>
                                        <h4 class="text-2xl font-black text-primary mb-4">
                                            <?php echo $order_data['tracking_id']; ?>
                                        </h4>

                                        <!-- Fulfillment / Resi -->
                                        <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-800">
                                            <div class="flex items-center justify-between mb-2">
                                                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">No.
                                                    Resi / AWB</span>
                                                <button onclick="copyToClipboard('<?php echo $order_data['tracking_id']; ?>')"
                                                    class="text-xs text-primary hover:text-blue-600 font-medium flex items-center gap-1">
                                                    Salin <span class="material-icons-round text-[10px]">content_copy</span>
                                                </button>
                                            </div>
                                            <div
                                                class="bg-slate-50 dark:bg-slate-800 p-3 rounded-lg font-mono text-sm font-bold text-slate-900 dark:text-white text-center tracking-widest border border-slate-200 dark:border-slate-700 mb-3 select-all">
                                                <?php echo $order_data['tracking_id']; ?>
                                            </div>

                                            <div class="grid grid-cols-1 gap-2">
                                                <!-- Logic for Print Label -->
                                                <?php
                                                $has_label = !empty($order_data['shipping_label_url']);
                                                // If URL exists but seems like just a tracking link (not PDF), we might still want to Refetch?
                                                // For now, trust DB unless empty.
                                        
                                                if ($has_label):
                                                    ?>
                                                    <a href="<?php echo $order_data['shipping_label_url']; ?>" target="_blank"
                                                        class="w-full bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold py-2 px-4 rounded-lg flex items-center justify-center gap-2 transition-colors">
                                                        <span class="material-icons-round text-lg">print</span>
                                                        Cetak Label
                                                    </a>
                                                <?php else: ?>
                                                    <!-- Missing Label Button -->
                                                    <a href="../api/refetch_label.php?id=<?php echo $order_data['id']; ?>"
                                                        target="_blank"
                                                        class="w-full bg-yellow-100 hover:bg-yellow-200 text-yellow-800 font-bold py-2 px-4 rounded-lg flex items-center justify-center gap-2 transition-colors border border-yellow-200">
                                                        <span class="material-icons-round text-lg">sync</span>
                                                        Ambil Label
                                                    </a>
                                                <?php endif; ?>

                                                <a href="https://cekresi.com/?noresi=<?php echo $order_data['tracking_id']; ?>"
                                                    target="_blank"
                                                    class="w-full bg-primary/10 hover:bg-primary/20 text-primary font-bold py-2 px-4 rounded-lg flex items-center justify-center gap-2 transition-colors">
                                                    <span class="material-icons-round text-lg">local_shipping</span>
                                                    Lacak Packet
                                                </a>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Order Status Update -->
                            <div class="bg-surface-light dark:bg-surface-dark p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm h-full flex flex-col">
                                <h3 class="font-bold text-slate-900 dark:text-white mb-4">Status Pesanan</h3>
                                <form method="POST">
                                    <input type="hidden" name="order_id" value="<?php echo $order_data['id']; ?>">
                                    <input type="hidden" name="update_status" value="1">
                                    <div class="custom-select-wrapper relative mb-4">
                                        <select name="status" class="hidden">
                                            <option value="pending" <?php echo $order_data['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="ready_to_ship" <?php echo $order_data['status'] == 'ready_to_ship' ? 'selected' : ''; ?>>Siap Dikirim</option>
                                            <option value="shipped" <?php echo $order_data['status'] == 'shipped' ? 'selected' : ''; ?>>Dalam Pengiriman</option>
                                            <option value="completed" <?php echo $order_data['status'] == 'completed' ? 'selected' : ''; ?>>Selesai</option>
                                            <option value="cancelled" <?php echo $order_data['status'] == 'cancelled' ? 'selected' : ''; ?>>Dibatalkan</option>
                                        </select>
                                        <button type="button" class="custom-select-trigger w-full flex items-center justify-between rounded-lg border border-slate-200 bg-white dark:bg-slate-800 dark:border-slate-700 text-slate-700 dark:text-slate-300 px-3 py-2 text-sm focus:ring-primary focus:border-primary transition-all text-left shadow-sm">
                                            <span class="selected-label">
                                                <?php
                                                switch ($order_data['status']) {
                                                    case 'completed':
                                                    case 'delivered':
                                                        echo 'Selesai / Diterima';
                                                        break;
                                                    case 'cancelled':
                                                        echo 'Dibatalkan';
                                                        break;
                                                    case 'ready_to_ship':
                                                        echo 'Siap Dikirim / Menunggu Kurir';
                                                        break;
                                                    case 'confirmed':
                                                        echo 'Dikonfirmasi';
                                                        break;
                                                    case 'shipped':
                                                        echo 'Dalam Pengiriman';
                                                        break;
                                                    default:
                                                        echo ucfirst($order_data['status']);
                                                }
                                                ?>
                                            </span>
                                            <span class="material-icons-round text-slate-400 selected-icon transition-transform">expand_more</span>
                                        </button>
                                        <div class="custom-select-options hidden absolute z-[110] w-full mt-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-xl opacity-0 translate-y-2 transition-all duration-200 overflow-hidden">
                                            <div class="p-1">
                                                <div class="custom-option px-3 py-2 rounded-lg hover:bg-primary/5 hover:text-primary cursor-pointer transition-colors text-sm <?php echo $order_data['status'] == 'pending' ? 'bg-primary/10 text-primary font-bold' : ''; ?>" data-value="pending">Pending</div>
                                                <div class="custom-option px-3 py-2 rounded-lg hover:bg-primary/5 hover:text-primary cursor-pointer transition-colors text-sm <?php echo $order_data['status'] == 'ready_to_ship' ? 'bg-primary/10 text-primary font-bold' : ''; ?>" data-value="ready_to_ship">Siap Dikirim / Menunggu Kurir</div>
                                                <div class="custom-option px-3 py-2 rounded-lg hover:bg-primary/5 hover:text-primary cursor-pointer transition-colors text-sm <?php echo $order_data['status'] == 'shipped' ? 'bg-primary/10 text-primary font-bold' : ''; ?>" data-value="shipped">Dalam Pengiriman</div>
                                                <div class="custom-option px-3 py-2 rounded-lg hover:bg-primary/5 hover:text-primary cursor-pointer transition-colors text-sm <?php echo ($order_data['status'] == 'completed' || $order_data['status'] == 'delivered') ? 'bg-primary/10 text-primary font-bold' : ''; ?>" data-value="completed">Selesai / Diterima</div>
                                                <div class="custom-option px-3 py-2 rounded-lg hover:bg-primary/5 hover:text-primary cursor-pointer transition-colors text-sm <?php echo $order_data['status'] == 'cancelled' ? 'bg-primary/10 text-primary font-bold' : ''; ?>" data-value="cancelled">Dibatalkan</div>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="submit" class="w-full bg-primary text-white py-2 rounded-lg font-medium hover:bg-blue-600 transition-colors shadow-sm">Perbarui Status</button>
                                </form>
                            </div>
                        </div>
                        <!-- Order Items -->
                        <div
                            class="md:col-span-2 lg:col-span-4 bg-surface-light dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
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
                                        <?php $product_subtotal = 0; while ($item = mysqli_fetch_assoc($items_res)): $product_subtotal += $item['subtotal']; ?>
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
                                                <td class="px-6 py-4 whitespace-nowrap"><?php echo $item['weight']; ?> kg
                                                </td>
                                                <td
                                                    class="px-6 py-4 text-right font-medium text-slate-900 dark:text-white whitespace-nowrap">
                                                    Rp
                                                    <?php echo number_format($item['subtotal'], 0, ',', '.'); ?>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                        <!-- Ongkos Kirim Row -->
                                        <tr class="bg-white dark:bg-surface-dark border-t border-b border-slate-200 dark:border-slate-800">
                                            <td colspan="3" class="px-6 py-4 text-right font-bold text-slate-600 dark:text-slate-400 whitespace-nowrap">
                                                Ongkos Kirim
                                            </td>
                                            <td class="px-6 py-4 text-right font-bold text-slate-900 dark:text-white whitespace-nowrap">
                                                Rp <?php echo number_format($order_data['total_amount'] - $product_subtotal, 0, ',', '.'); ?>
                                            </td>
                                        </tr>
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
                            <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Manajemen
                                Pesanan
                            </h2>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Kelola pesanan, lacak berat, dan
                                proses pengiriman.</p>
                        </div>
                        <a href="manual_transaction"
                            class="px-4 py-2 bg-primary hover:bg-blue-600 text-white rounded-lg text-sm font-bold transition-colors flex items-center gap-2 shadow-lg shadow-blue-500/30">
                            <span class="material-icons-round text-sm">add</span> Tambah Pesanan Manual
                        </a>
                    </div>

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
                                <p class="text-sm text-slate-500 dark:text-slate-400">
                                    <?php echo $_SESSION['status_msg']; ?>
                                </p>
                            </div>
                        </div>
                        <?php unset($_SESSION['status_msg']);
                        unset($_SESSION['status_type']); ?>
                    <?php endif; ?>

                    <div
                        class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-6 mb-6">

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
                                                        'ready_to_ship' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
                                                        'shipped' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
                                                        'confirmed' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
                                                        'completed' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
                                                        'delivered' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
                                                        'cancelled' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
                                                    ];
                                                    $curr_status = $order['status'];
                                                    // Map display names for list view
                                                    $status_display = [
                                                        'pending' => 'Pending',
                                                        'ready_to_ship' => 'Siap Dikirim / Menunggu Kurir',
                                                        'shipped' => 'Dalam Pengiriman',
                                                        'confirmed' => 'Dikonfirmasi',
                                                        'completed' => 'Selesai / Diterima',
                                                        'delivered' => 'Selesai / Diterima',
                                                        'cancelled' => 'Dibatalkan'
                                                    ];
                                                    $color_class = $status_colors[$curr_status] ?? 'bg-slate-100 text-slate-600';
                                                    ?>
                                                    <span
                                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $color_class; ?> capitalize">
                                                        <?php echo $status_display[$curr_status] ?? $curr_status; ?>
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 text-right">
                                                    <div class="flex flex-col sm:flex-row gap-2 justify-end">
                                                        <a href="orders?action=view&id=<?php echo $order['id']; ?>"
                                                            class="inline-flex items-center justify-center rounded-lg size-8 text-xs font-medium bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-700 transition-colors shadow-sm"
                                                            title="Detail Pesanan">
                                                            <span class="material-icons-round text-lg">visibility</span>
                                                        </a>
                                                        <button
                                                            onclick="confirmDelete('orders?action=delete&id=<?php echo $order['id']; ?>')"
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
    <!-- Lightbox Modal -->
    <div id="lightbox-modal"
        class="fixed inset-0 z-[200] bg-black/90 hidden flex items-center justify-center opacity-0 transition-opacity duration-300">
        <div class="relative max-w-4xl max-h-[90vh] w-full p-4 flex flex-col items-center">
            <button onclick="closeLightbox()"
                class="absolute -top-12 right-4 text-white hover:text-slate-300 transition-colors">
                <span class="material-icons-round text-4xl">close</span>
            </button>
            <img id="lightbox-image" src="" alt="Bukti Transfer Full"
                class="max-w-full max-h-[85vh] object-contain rounded-lg shadow-2xl">
        </div>
    </div>

    <script>
        function openLightbox(src) {
            const modal = document.getElementById('lightbox-modal');
            const img = document.getElementById('lightbox-image');

            if (!src) return;

            img.src = src;
            modal.classList.remove('hidden');
            // Small delay to allow display block to apply before opacity transition
            setTimeout(() => {
                modal.classList.remove('opacity-0');
            }, 10);
            document.body.style.overflow = 'hidden';
        }

        function closeLightbox() {
            const modal = document.getElementById('lightbox-modal');
            modal.classList.add('opacity-0');

            setTimeout(() => {
                modal.classList.add('hidden');
                document.getElementById('lightbox-image').src = '';
            }, 300);
            document.body.style.overflow = '';
        }

        // Close on clicking outside
        document.getElementById('lightbox-modal').addEventListener('click', function (e) {
            if (e.target === this) {
                closeLightbox();
            }
        });

        function processShipping(orderId) {
            Swal.fire({
                title: 'Konfirmasi Pickup',
                text: "Apakah Anda yakin ingin memproses Booking Kurir & Pickup untuk pesanan ini?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0d59f2',
                cancelButtonColor: '#ef4444',
                confirmButtonText: 'Ya, Proses Sekarang',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    performShippingRequest(orderId);
                }
            });
        }

        function performShippingRequest(orderId) {
            const btn = document.getElementById('btn-process-shipping');
            const spinner = document.getElementById('shipping-spinner');

            if (btn) {
                btn.disabled = true;
                btn.classList.add('opacity-75', 'cursor-not-allowed');
            }
            if (spinner) spinner.classList.remove('hidden');

            const formData = new FormData();
            formData.append('order_id', orderId);

            // Show loading state
            Swal.fire({
                title: 'Memproses...',
                text: 'Sedang menghubungi server logistik',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch('shipping_api/process_shipping.php', {
                method: 'POST',
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Berhasil!',
                            text: 'Nomor Resi: ' + data.tracking_id,
                            icon: 'success',
                            confirmButtonColor: '#0d59f2'
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            title: 'Gagal!',
                            text: data.message || 'Gagal memproses pengiriman',
                            icon: 'error',
                            confirmButtonColor: '#0d59f2'
                        });
                        resetButtonState(btn, spinner);
                    }
                })
                .catch(err => {
                    console.error(err);
                    Swal.fire({
                        title: 'Error!',
                        text: 'Terjadi kesalahan jaringan atau server.',
                        icon: 'error',
                        confirmButtonColor: '#0d59f2'
                    });
                    resetButtonState(btn, spinner);
                });
        }

        function resetButtonState(btn, spinner) {
            if (btn) {
                btn.disabled = false;
                btn.classList.remove('opacity-75', 'cursor-not-allowed');
            }
            if (spinner) spinner.classList.add('hidden');
        }

        function cancelOrder(orderId) {
            Swal.fire({
                title: 'Batalkan Pesanan?',
                text: "Silakan masukkan alasan pembatalan:",
                input: 'text',
                inputPlaceholder: 'Misal: Stok habis, Customer minta cancel',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Batalkan',
                cancelButtonText: 'Kembali',
                inputValidator: (value) => {
                    if (!value) {
                        return 'Alasan pembatalan wajib diisi!'
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    performCancelRequest(orderId, result.value);
                }
            });
        }

        function performCancelRequest(orderId, reason) {
            Swal.fire({
                title: 'Memproses...',
                text: 'Sedang membatalkan pesanan',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            const formData = new FormData();
            formData.append('order_id', orderId);
            formData.append('reason', reason);

            fetch('../api/cancel_order.php', {
                method: 'POST',
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Dibatalkan!',
                            text: data.message,
                            icon: 'success',
                            confirmButtonColor: '#0d59f2'
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            title: 'Gagal Membatalkan',
                            text: data.message,
                            icon: 'error',
                            confirmButtonColor: '#0d59f2'
                        });
                    }
                })
                .catch(err => {
                    console.error(err);
                    Swal.fire({
                        title: 'Error',
                        text: 'Gagal menghubungi server',
                        icon: 'error',
                        confirmButtonColor: '#0d59f2'
                    });
                });
        }

        function syncBiteshipId(orderId) {
            Swal.fire({
                title: 'Sync Biteship ID',
                text: "Sistem akan mencoba mencari ID Biteship berdasarkan Nomor Resi (Waybill) secara otomatis.",
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#f59e0b',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Sinkronisasi',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    performSyncRequest(orderId);
                }
            });
        }

        function performSyncRequest(orderId) {
            Swal.fire({
                title: 'Sedang Sinkronisasi...',
                text: 'Menghubungi Biteship API...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch('../api/sync_biteship_id.php?order_id=' + orderId)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Berhasil!',
                            text: data.message + '\nID: ' + data.biteship_id,
                            icon: 'success',
                            confirmButtonColor: '#0d59f2'
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            title: 'Gagal Sync',
                            text: data.message,
                            icon: 'error',
                            confirmButtonColor: '#0d59f2'
                        });
                    }
                })
                .catch(err => {
                    console.error(err);
                    Swal.fire({
                        title: 'Error',
                        text: 'Terjadi kesalahan jaringan',
                        icon: 'error',
                        confirmButtonColor: '#0d59f2'
                    });
                });
        }
    </script>
</body>

</html>