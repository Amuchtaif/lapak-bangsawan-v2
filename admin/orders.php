<?php
require("auth_session.php");
require_once dirname(__DIR__) . "/config/init.php";

// Update Status
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    if ($conn->query("UPDATE orders SET status='$status' WHERE id=$order_id")) {
        log_activity("UPDATE_ORDER_STATUS", "Memperbarui status pesanan #$order_id menjadi " . ucfirst($status));
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
        log_activity("DELETE_ORDER", "Menghapus pesanan #$order_id");
        $_SESSION['status_msg'] = "Order #$order_id has been deleted.";
        $_SESSION['status_type'] = "success";
    } else {
        $_SESSION['status_msg'] = "Failed to delete order.";
        $_SESSION['status_type'] = "error";
    }
    header("Location: orders");
    exit();
}

// Fetch Orders with Pagination & Filter
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
if (!in_array($limit, [5, 10, 20]))
    $limit = 10;

$filter_status = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';
$where_clause = "";
if (!empty($filter_status)) {
    if ($filter_status == 'completed') {
        $where_clause = "WHERE status IN ('completed', 'delivered')";
    } elseif ($filter_status == 'shipped') {
        $where_clause = "WHERE status IN ('shipped', 'confirmed')";
    } else {
        $where_clause = "WHERE status = '$filter_status'";
    }
}

$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$start = ($page > 1) ? ($page * $limit) - $limit : 0;

// Get total records
$total_result = $conn->query("SELECT COUNT(*) as count FROM orders $where_clause");
$total_row = $total_result->fetch_assoc();
$total_orders = $total_row['count'];
$total_pages = ceil($total_orders / $limit);

// Get counts for each status group
$counts_query = "SELECT 
    COUNT(*) as all_count,
    SUM(CASE WHEN status='pending' THEN 1 ELSE 0 END) as pending_count,
    SUM(CASE WHEN status='ready_to_ship' THEN 1 ELSE 0 END) as ready_count,
    SUM(CASE WHEN status IN ('shipped', 'confirmed') THEN 1 ELSE 0 END) as shipped_count,
    SUM(CASE WHEN status IN ('completed', 'delivered') THEN 1 ELSE 0 END) as completed_count,
    SUM(CASE WHEN status='cancelled' THEN 1 ELSE 0 END) as cancelled_count
FROM orders";
$st_counts = $conn->query($counts_query)->fetch_assoc();

$orders_query = "SELECT * FROM orders $where_clause ORDER BY created_at DESC LIMIT $start, $limit";
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
                        <div class="flex items-center justify-between">
                            <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Pesanan
                                #<?php echo htmlspecialchars($order_data['order_number'] ?? str_pad($order_data['id'], 5, '0', STR_PAD_LEFT)); ?>
                            </h2>
                            <div class="flex gap-2 flex-wrap">
                                <!-- Cetak Nota -->
                                <a href="print_nota?id=<?php echo $order_data['id']; ?>&size=58mm&auto_print=1" target="_blank"
                                    class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-bold transition-colors flex items-center gap-2 shadow-sm shadow-emerald-500/30">
                                    <span class="material-icons-round text-sm">receipt_long</span> Cetak Nota
                                </a>

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

                                        <?php if (!empty($order_data['destination_latitude']) && !empty($order_data['destination_longitude'])): ?>
                                        <div>
                                            <p class="text-[10px] text-slate-500 uppercase font-bold tracking-widest mb-1">
                                                Titik Lokasi (Pinned)</p>
                                            <div class="flex flex-col gap-2">
                                                <a href="https://www.google.com/maps?q=<?php echo $order_data['destination_latitude']; ?>,<?php echo $order_data['destination_longitude']; ?>" target="_blank" class="text-xs bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg flex items-center justify-center gap-2 w-fit transition-colors shadow-sm">
                                                    <span class="material-icons-round text-sm">place</span>
                                                    Lihat di Google Maps
                                                </a>
                                                <a href="https://wa.me/?text=<?php echo urlencode("Lokasi Pengiriman Order #" . ($order_data['order_number'] ?? str_pad($order_data['id'], 5, '0', STR_PAD_LEFT)) . ": https://www.google.com/maps?q=" . $order_data['destination_latitude'] . "," . $order_data['destination_longitude']); ?>" target="_blank" class="text-xs bg-green-500 hover:bg-green-600 text-white px-3 py-2 rounded-lg flex items-center justify-center gap-2 w-fit transition-colors shadow-sm">
                                                    <span class="material-icons-round text-sm">share</span>
                                                    Share ke WhatsApp
                                                </a>
                                            </div>
                                        </div>
                                        <?php endif; ?>

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

                    <!-- Compact Filter Card -->
                    <div class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-4 mb-2">
                        <div class="flex flex-col md:flex-row md:items-center gap-4">
                            <div class="flex items-center gap-2 text-slate-900 dark:text-white shrink-0">
                                <span class="material-icons-round text-primary text-xl">filter_list</span>
                                <h3 class="font-bold text-xs uppercase tracking-wider">Filter</h3>
                            </div>
                            
                            <!-- Compact Status Filter Tabs -->
                            <div class="flex flex-wrap items-center gap-2">
                                <?php
                                $filter_items = [
                                    '' => ['label' => 'Semua', 'count' => $st_counts['all_count'], 'icon' => 'list_alt', 'color' => 'blue'],
                                    'pending' => ['label' => 'Pending', 'count' => $st_counts['pending_count'] ?? 0, 'icon' => 'schedule', 'color' => 'amber'],
                                    'ready_to_ship' => ['label' => 'Siap', 'count' => $st_counts['ready_count'] ?? 0, 'icon' => 'inventory_2', 'color' => 'yellow'],
                                    'shipped' => ['label' => 'Kirim', 'count' => $st_counts['shipped_count'] ?? 0, 'icon' => 'local_shipping', 'color' => 'indigo'],
                                    'completed' => ['label' => 'Selesai', 'count' => $st_counts['completed_count'] ?? 0, 'icon' => 'check_circle', 'color' => 'green'],
                                    'cancelled' => ['label' => 'Batal', 'count' => $st_counts['cancelled_count'] ?? 0, 'icon' => 'cancel', 'color' => 'red']
                                ];

                                foreach ($filter_items as $val => $info):
                                    $isActive = ($filter_status === $val);
                                    $color = $info['color'];
                                    
                                    $bgClass = $isActive ? "bg-$color-50 border-$color-200 text-$color-700 dark:bg-$color-900/10 dark:border-$color-800 dark:text-$color-400 ring-1 ring-$color-500/10" : "bg-white border-slate-200 text-slate-500 hover:border-slate-300 hover:bg-slate-50 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-400 dark:hover:bg-slate-700/50";
                                    $iconClass = $isActive ? "text-$color-600" : "text-slate-400 group-hover:text-slate-500";
                                    $countClass = $isActive ? "text-$color-700 dark:text-$color-300" : "text-slate-600 dark:text-slate-200";
                                ?>
                                <a href="?status=<?php echo $val; ?>&limit=<?php echo $limit; ?>" 
                                   class="group flex items-center gap-2 px-3 py-1.5 rounded-lg transition-all duration-300 border <?php echo $bgClass; ?>">
                                    <span class="material-icons-round text-base <?php echo $iconClass; ?>">
                                        <?php echo $info['icon']; ?>
                                    </span>
                                    <div class="flex items-center gap-1.5 leading-none">
                                        <span class="text-[10px] font-bold uppercase tracking-tight"><?php echo $info['label']; ?></span>
                                        <span class="text-[10px] font-black opacity-60 <?php echo $countClass; ?>">
                                            (<?php echo number_format($info['count'], 0); ?>)
                                        </span>
                                    </div>
                                </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Order List Card -->
                    <div class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-6 mb-6">

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
                                                        <?php if (!empty($order['customer_phone']) && $order['customer_phone'] !== '0' && $order['customer_phone'] !== '-'): ?>
                                                            <span
                                                                class="text-xs text-slate-500"><?php echo htmlspecialchars($order['customer_phone']); ?></span>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 text-sm font-medium text-slate-900 dark:text-white">
                                                    Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <?php
                                                    $status_colors = [
                                                        'pending' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-900/10 dark:text-amber-400 dark:border-amber-800',
                                                        'ready_to_ship' => 'bg-yellow-50 text-yellow-700 border-yellow-200 dark:bg-yellow-900/10 dark:text-yellow-400 dark:border-yellow-800',
                                                        'shipped' => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-900/10 dark:text-blue-400 dark:border-blue-800',
                                                        'confirmed' => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-900/10 dark:text-blue-400 dark:border-blue-800',
                                                        'completed' => 'bg-green-50 text-green-700 border-green-200 dark:bg-green-900/10 dark:text-green-400 dark:border-green-800',
                                                        'delivered' => 'bg-green-50 text-green-700 border-green-200 dark:bg-green-900/10 dark:text-green-400 dark:border-green-800',
                                                        'cancelled' => 'bg-red-50 text-red-700 border-red-200 dark:bg-red-900/10 dark:text-red-400 dark:border-red-800',
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
                                                        class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold border <?php echo $color_class; ?> uppercase tracking-wider">
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
                                                        <a href="print_nota?id=<?php echo $order['id']; ?>&size=58mm&auto_print=1" target="_blank"
                                                            class="inline-flex items-center justify-center rounded-lg size-8 text-xs font-medium bg-emerald-50 border border-emerald-200 text-emerald-700 hover:bg-emerald-100 dark:bg-emerald-900/20 dark:border-emerald-800 dark:text-emerald-400 dark:hover:bg-emerald-900/30 transition-colors shadow-sm"
                                                            title="Cetak Nota 58mm">
                                                            <span class="material-icons-round text-lg">print</span>
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
                        <?php if (!isset($_GET['action']) && $total_pages > 0): ?>
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

                            $base_url = "?status=" . urlencode($filter_status) . "&limit=$limit";
                            ?>
                            <div
                                class="border-t border-slate-200 dark:border-slate-800 pt-4 mt-4 flex flex-col sm:flex-row justify-between items-center gap-4">
                                <!-- Left: Per page & info -->
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
                                        <span class="font-semibold text-slate-700 dark:text-slate-300"><?= $start + 1 ?>–<?= min($start + $limit, $total_orders) ?></span>
                                        dari
                                        <span class="font-semibold text-slate-700 dark:text-slate-300"><?= number_format($total_orders) ?></span>
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