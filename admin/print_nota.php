<?php
require("auth_session.php");
require_once dirname(__DIR__) . "/config/init.php";

if (!isset($_GET['id'])) {
    echo "Order ID is required.";
    exit;
}

$oid = intval($_GET['id']);
$order_q = "SELECT * FROM orders WHERE id=$oid";
$order_result = $conn->query($order_q);

if (!$order_result || $order_result->num_rows == 0) {
    echo "Order not found.";
    exit;
}

$order = $order_result->fetch_assoc();

// Fetch order items
$items_q = "SELECT * FROM order_items WHERE order_id=$oid";
$items_result = $conn->query($items_q);

// Calculate product subtotal
$product_subtotal = 0;
$items_arr = [];
while ($item = $items_result->fetch_assoc()) {
    $product_subtotal += $item['subtotal'];
    $items_arr[] = $item;
}

$shipping_cost = $order['total_amount'] - $product_subtotal;
$order_number = $order['order_number'] ?? str_pad($order['id'], 5, '0', STR_PAD_LEFT);
$order_date = date('d/m/Y H:i', strtotime($order['created_at']));

// Store info
$store_name = get_setting('store_name', 'Lapak Bangsawan');
$store_phone = get_setting('store_phone', '');
$store_address = get_setting('store_address', '');

// Payment method
$pm = strtolower($order['payment_method'] ?? 'transfer');
$pm_label = ($pm === 'cod') ? 'COD (Bayar di Tempat)' : 'Transfer Bank';

// Courier
$courier_label = 'Internal / Pickup';
if (!empty($order['courier_company'])) {
    $courier_label = strtoupper($order['courier_company']);
    if (!empty($order['courier_type'])) {
        $courier_label .= ' (' . $order['courier_type'] . ')';
    }
}

// Determine if nota size should be thermal (58mm/80mm) or A4
$size = $_GET['size'] ?? '80mm'; // Options: '58mm', '80mm', 'a4'
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota #<?= htmlspecialchars($order_number) ?> - <?= htmlspecialchars($store_name) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
    <style>
        /* Reset & Base */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        
        body {
            font-family: 'Inter', 'Segoe UI', sans-serif;
            background: #f1f5f9;
            color: #1e293b;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* Print Controls Bar */
        .print-controls {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 100;
            background: linear-gradient(135deg, #0d59f2 0%, #0b4ecf 100%);
            color: white;
            padding: 12px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            box-shadow: 0 4px 20px rgba(13, 89, 242, 0.3);
        }
        .print-controls .left { display: flex; align-items: center; gap: 12px; }
        .print-controls .right { display: flex; align-items: center; gap: 8px; }
        .print-controls h3 { font-weight: 700; font-size: 14px; }
        
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all 0.15s;
            text-decoration: none;
        }
        .btn-print {
            background: white;
            color: #0d59f2;
        }
        .btn-print:hover { background: #e0e7ff; }
        .btn-back {
            background: rgba(255,255,255,0.15);
            color: white;
        }
        .btn-back:hover { background: rgba(255,255,255,0.25); }
        .btn-size {
            background: rgba(255,255,255,0.1);
            color: rgba(255,255,255,0.7);
            padding: 6px 12px;
            font-size: 11px;
        }
        .btn-size:hover { background: rgba(255,255,255,0.2); color: white; }
        .btn-size.active { background: white; color: #0d59f2; }

        /* Receipt Container */
        .receipt-wrapper {
            display: flex;
            justify-content: center;
            padding: 80px 16px 40px;
        }

        .receipt {
            background: white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08), 0 4px 12px rgba(0,0,0,0.04);
            position: relative;
        }

        /* Size variants */
        .receipt.size-58mm { width: 220px; padding: 12px; font-size: 10px; }
        .receipt.size-80mm { width: 302px; padding: 16px; font-size: 11px; }
        .receipt.size-a4 { width: 700px; padding: 40px; font-size: 13px; }

        /* Header */
        .receipt-header {
            text-align: center;
            padding-bottom: 12px;
            border-bottom: 2px dashed #cbd5e1;
            margin-bottom: 12px;
        }
        .store-name {
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .size-58mm .store-name { font-size: 13px; }
        .size-80mm .store-name { font-size: 16px; }
        .size-a4 .store-name { font-size: 22px; }
        
        .store-info {
            color: #64748b;
            margin-top: 4px;
            line-height: 1.5;
        }
        .size-58mm .store-info { font-size: 8px; }
        .size-80mm .store-info { font-size: 9px; }
        .size-a4 .store-info { font-size: 11px; }

        /* Order Meta */
        .order-meta {
            padding: 10px 0;
            border-bottom: 1px dashed #e2e8f0;
            margin-bottom: 10px;
        }
        .meta-row {
            display: flex;
            justify-content: space-between;
            padding: 2px 0;
        }
        .meta-label { color: #64748b; font-weight: 500; }
        .meta-value { font-weight: 600; text-align: right; }

        /* Items */
        .items-header {
            font-weight: 700;
            text-transform: uppercase;
            color: #64748b;
            letter-spacing: 0.5px;
            padding: 6px 0;
            border-bottom: 1px solid #e2e8f0;
            margin-bottom: 6px;
            display: flex;
            justify-content: space-between;
        }

        .item-row {
            padding: 5px 0;
            border-bottom: 1px dotted #f1f5f9;
        }
        .item-name {
            font-weight: 600;
            margin-bottom: 2px;
        }
        .item-detail {
            display: flex;
            justify-content: space-between;
            color: #64748b;
        }
        .item-subtotal { 
            font-weight: 600;
            color: #1e293b;
        }

        /* Totals */
        .totals {
            border-top: 2px dashed #cbd5e1;
            margin-top: 10px;
            padding-top: 10px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 3px 0;
        }
        .total-row.grand {
            border-top: 1px solid #e2e8f0;
            margin-top: 6px;
            padding-top: 8px;
            font-weight: 800;
        }
        .size-58mm .total-row.grand { font-size: 13px; }
        .size-80mm .total-row.grand { font-size: 15px; }
        .size-a4 .total-row.grand { font-size: 18px; }

        /* Footer */
        .receipt-footer {
            text-align: center;
            padding-top: 12px;
            margin-top: 12px;
            border-top: 2px dashed #cbd5e1;
            color: #94a3b8;
            line-height: 1.6;
        }
        .size-58mm .receipt-footer { font-size: 8px; }
        .size-80mm .receipt-footer { font-size: 9px; }
        .size-a4 .receipt-footer { font-size: 11px; }

        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .badge-cod { background: #dcfce7; color: #166534; }
        .badge-transfer { background: #dbeafe; color: #1e40af; }

        /* Customer Info Section */
        .customer-section {
            padding: 10px 0;
            border-bottom: 1px dashed #e2e8f0;
            margin-bottom: 10px;
        }
        .section-title {
            font-weight: 700;
            text-transform: uppercase;
            color: #64748b;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }
        .size-58mm .section-title { font-size: 8px; }
        .size-80mm .section-title { font-size: 9px; }
        .size-a4 .section-title { font-size: 10px; }

        .customer-name { font-weight: 700; margin-bottom: 2px; }
        .customer-detail { color: #64748b; line-height: 1.5; word-wrap: break-word; }

        /* A4 Specific Styles */
        .size-a4 .receipt-header { padding-bottom: 20px; margin-bottom: 20px; border-bottom-width: 2px; }
        .size-a4 .order-meta { padding: 16px 0; margin-bottom: 16px; }
        .size-a4 .meta-row { padding: 4px 0; }
        .size-a4 .item-row { padding: 8px 0; }
        .size-a4 .totals { margin-top: 16px; padding-top: 16px; }
        .size-a4 .total-row { padding: 5px 0; }
        .size-a4 .receipt-footer { padding-top: 20px; margin-top: 20px; }

        /* Status Badge */
        .status-badge {
            display: inline-block;
            padding: 1px 6px;
            border-radius: 3px;
            font-weight: 600;
            font-size: inherit;
        }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-ready_to_ship { background: #fef9c3; color: #854d0e; }
        .status-shipped { background: #dbeafe; color: #1e40af; }
        .status-completed, .status-delivered { background: #dcfce7; color: #166534; }
        .status-cancelled { background: #fee2e2; color: #991b1b; }

        /* Print Styles */
        @media print {
            body { background: white !important; }
            .print-controls { display: none !important; }
            .receipt-wrapper { padding: 0 !important; }
            .receipt {
                box-shadow: none !important;
                width: 100% !important;
                max-width: none !important;
            }
            /* For thermal printers, you might adjust margins */
            @page { margin: 5mm; }
        }
    </style>
</head>
<body>
    <!-- Print Controls Bar -->
    <div class="print-controls" id="print-controls">
        <div class="left">
            <a href="orders?action=view&id=<?= $oid ?>" class="btn btn-back">
                <span class="material-icons-round" style="font-size:18px">arrow_back</span>
                Kembali
            </a>
            <h3>Cetak Nota #<?= htmlspecialchars($order_number) ?></h3>
        </div>
        <div class="right">
            <span style="font-size:11px;opacity:0.7;margin-right:4px;">Ukuran:</span>
            <a href="?id=<?= $oid ?>&size=58mm" class="btn btn-size <?= $size === '58mm' ? 'active' : '' ?>">58mm</a>
            <a href="?id=<?= $oid ?>&size=80mm" class="btn btn-size <?= $size === '80mm' ? 'active' : '' ?>">80mm</a>
            <a href="?id=<?= $oid ?>&size=a4" class="btn btn-size <?= $size === 'a4' ? 'active' : '' ?>">A4</a>
            <button onclick="window.print()" class="btn btn-print">
                <span class="material-icons-round" style="font-size:18px">print</span>
                Cetak Nota
            </button>
        </div>
    </div>

    <!-- Receipt -->
    <div class="receipt-wrapper">
        <div class="receipt size-<?= htmlspecialchars($size) ?>">
            <!-- Header -->
            <div class="receipt-header">
                <div class="store-name"><?= htmlspecialchars($store_name) ?></div>
                <?php if ($store_address || $store_phone): ?>
                <div class="store-info">
                    <?php if ($store_address): ?><?= htmlspecialchars($store_address) ?><br><?php endif; ?>
                    <?php if ($store_phone): ?>Telp: <?= htmlspecialchars($store_phone) ?><?php endif; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Order Meta -->
            <div class="order-meta">
                <div class="meta-row">
                    <span class="meta-label">No. Pesanan</span>
                    <span class="meta-value">#<?= htmlspecialchars($order_number) ?></span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">Tanggal</span>
                    <span class="meta-value"><?= $order_date ?></span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">Kasir</span>
                    <span class="meta-value"><?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?></span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">Pembayaran</span>
                    <span class="meta-value">
                        <span class="badge <?= $pm === 'cod' ? 'badge-cod' : 'badge-transfer' ?>"><?= $pm_label ?></span>
                    </span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">Status</span>
                    <span class="meta-value">
                        <?php
                        $status_labels = [
                            'pending' => 'Pending',
                            'ready_to_ship' => 'Siap Kirim',
                            'shipped' => 'Dikirim',
                            'completed' => 'Selesai',
                            'delivered' => 'Diterima',
                            'cancelled' => 'Dibatalkan'
                        ];
                        $sl = $status_labels[$order['status']] ?? ucfirst($order['status']);
                        ?>
                        <span class="status-badge status-<?= $order['status'] ?>"><?= $sl ?></span>
                    </span>
                </div>
            </div>

            <!-- Customer Section -->
            <div class="customer-section">
                <div class="section-title">Pelanggan</div>
                <div class="customer-name"><?= htmlspecialchars($order['customer_name']) ?></div>
                <div class="customer-detail">
                    <?php if (!empty($order['customer_phone'])): ?>
                        <?= htmlspecialchars($order['customer_phone']) ?><br>
                    <?php endif; ?>
                    <?php if (!empty($order['customer_address'])): ?>
                        <?= htmlspecialchars($order['customer_address']) ?>
                    <?php endif; ?>
                </div>
                <?php if (!empty($order['courier_company'])): ?>
                <div style="margin-top:6px;">
                    <span class="meta-label">Kurir: </span>
                    <span class="meta-value"><?= htmlspecialchars($courier_label) ?></span>
                </div>
                <?php endif; ?>
            </div>

            <!-- Items -->
            <div class="items-header">
                <span>Produk</span>
                <span>Subtotal</span>
            </div>

            <?php foreach ($items_arr as $item): ?>
            <div class="item-row">
                <div class="item-name"><?= htmlspecialchars($item['product_name']) ?></div>
                <div class="item-detail">
                    <span><?= $item['weight'] ?> <?= ($item['unit'] ?? 'kg') ?> × Rp <?= number_format($item['price_per_kg'], 0, ',', '.') ?></span>
                    <span class="item-subtotal">Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></span>
                </div>
            </div>
            <?php endforeach; ?>

            <!-- Totals -->
            <div class="totals">
                <div class="total-row">
                    <span>Subtotal Produk</span>
                    <span style="font-weight:600">Rp <?= number_format($product_subtotal, 0, ',', '.') ?></span>
                </div>
                <?php if ($shipping_cost > 0): ?>
                <div class="total-row">
                    <span>Ongkos Kirim</span>
                    <span style="font-weight:600">Rp <?= number_format($shipping_cost, 0, ',', '.') ?></span>
                </div>
                <?php elseif ($shipping_cost == 0 && !empty($order['courier_company'])): ?>
                <div class="total-row">
                    <span>Ongkos Kirim</span>
                    <span style="font-weight:600;color:#16a34a">GRATIS</span>
                </div>
                <?php endif; ?>
                <div class="total-row grand">
                    <span>TOTAL</span>
                    <span>Rp <?= number_format($order['total_amount'], 0, ',', '.') ?></span>
                </div>
            </div>

            <?php if (!empty($order['order_notes'])): ?>
            <div style="margin-top:10px;padding:6px 8px;background:#fffbeb;border-radius:4px;border:1px solid #fde68a;">
                <div class="section-title" style="color:#92400e;margin-bottom:2px;">Catatan</div>
                <div style="color:#78350f;font-style:italic;">"<?= htmlspecialchars($order['order_notes']) ?>"</div>
            </div>
            <?php endif; ?>

            <!-- Footer -->
            <div class="receipt-footer">
                <div style="font-weight:700;margin-bottom:2px;">Terima Kasih!</div>
                <div>Barang yang sudah dibeli tidak dapat dikembalikan.</div>
                <div style="margin-top:6px;font-size:inherit;opacity:0.7">
                    Dicetak: <?= date('d/m/Y H:i') ?> WIB
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto print on page load if requested via URL param
        <?php if (isset($_GET['auto_print'])): ?>
        window.addEventListener('load', () => {
            setTimeout(() => window.print(), 500);
        });
        <?php endif; ?>
    </script>
</body>
</html>
