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

$shipping_cost = (float)($order['shipping_cost'] ?? 0);
$manual_discount = (float)($order['manual_discount'] ?? 0);
// system discount isn't explicitly stored, but total_amount = product_subtotal - system_discount - manual_discount + shipping_cost
// so system_discount = product_subtotal - manual_discount + shipping_cost - total_amount
$system_discount = $product_subtotal - $manual_discount + $shipping_cost - $order['total_amount'];

$order_number = $order['order_number'] ?? str_pad($order['id'], 5, '0', STR_PAD_LEFT);
$order_date = date('d/m/Y H:i', strtotime($order['created_at']));

// Store info
$store_name = get_setting('store_name', 'Lapak Bangsawan');
$store_phone = get_setting('store_phone', '');
$store_address = get_setting('store_address', '');

// Detect manual transaction (check notes OR walk-in customer name pattern)
$is_manual = (stripos($order['order_notes'] ?? '', 'Transaksi Manual') !== false)
          || preg_match('/^Pelanggan\d+$/', $order['customer_name'] ?? '')
          || ($order['customer_name'] ?? '') === 'Walk-in Guest';

// Payment method label
$pm = strtolower($order['payment_method'] ?? 'transfer');
if (in_array($pm, ['cod', 'cash', 'tunai'])) {
    $pm_label = 'Tunai';
} else {
    $pm_label = 'Transfer Bank';
}

// Courier
$courier_label = 'Internal';
if (!empty($order['courier_company'])) {
    $courier_label = strtoupper($order['courier_company']);
    if (!empty($order['courier_type'])) {
        $courier_label .= ' ' . $order['courier_type'];
    }
}

// Status labels
$status_labels = [
    'pending' => 'Pending',
    'ready_to_ship' => 'Siap Kirim',
    'shipped' => 'Dikirim',
    'completed' => 'Selesai',
    'delivered' => 'Diterima',
    'cancelled' => 'Batal'
];

// Size param
$size = $_GET['size'] ?? '58mm';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota #<?= htmlspecialchars($order_number) ?></title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Courier New', Courier, monospace;
            background: #f1f5f9;
            color: #000;
            font-weight: 700;
            font-size: 12px;
            line-height: 1.3;
        }

        /* ===== PRINT CONTROLS (screen only) ===== */
        .print-controls {
            position: fixed;
            top: 0; left: 0; right: 0;
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
        .print-controls h3 { font-weight: 700; font-size: 14px; font-family: Arial, sans-serif; }

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
            font-family: Arial, sans-serif;
        }
        .btn-print { background: white; color: #0d59f2; }
        .btn-print:hover { background: #e0e7ff; }
        .btn-back { background: rgba(255,255,255,0.15); color: white; }
        .btn-back:hover { background: rgba(255,255,255,0.25); }
        .btn-size {
            background: rgba(255,255,255,0.1);
            color: rgba(255,255,255,0.7);
            padding: 6px 12px;
            font-size: 11px;
        }
        .btn-size:hover { background: rgba(255,255,255,0.2); color: white; }
        .btn-size.active { background: white; color: #0d59f2; }

        /* ===== RECEIPT WRAPPER (screen) ===== */
        .receipt-wrapper {
            display: flex;
            justify-content: center;
            padding: 80px 16px 40px;
        }

        .receipt {
            background: white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08), 0 4px 12px rgba(0,0,0,0.04);
        }

        /* Size variants for SCREEN preview */
        .receipt.size-58mm { width: 48mm; padding: 2mm; }
        .receipt.size-80mm { width: 72mm; padding: 3mm; }
        .receipt.size-a4   { width: 700px; padding: 40px; font-size: 13px; }

        /* ===== RECEIPT CONTENT ===== */
        .r-center { text-align: center; }
        .r-bold { font-weight: 900; }
        .r-line { border-bottom: 1px dashed #000; margin: 4px 0; }
        .r-line-thick { border-bottom: 2px dashed #000; margin: 6px 0; }
        .r-row { display: block; margin-bottom: 1px; }
        .r-row-flex { display: flex; justify-content: space-between; margin-bottom: 1px; word-break: break-all; }
        .r-right { text-align: right; }
        .r-section { margin-bottom: 6px; }

        .store-name {
            font-weight: 900;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .store-info { font-size: 9px; margin-top: 2px; }

        .r-label { font-size: 10px; }
        .r-val { font-size: 10px; font-weight: 900; }

        .item-block { margin-bottom: 4px; }
        .item-name { font-weight: 900; font-size: 11px; }
        .item-qty { font-size: 10px; }
        .item-price { font-size: 10px; text-align: right; font-weight: 900; }

        .total-label { font-size: 11px; }
        .total-val { font-size: 11px; font-weight: 900; }
        .grand-label { font-size: 13px; font-weight: 900; }
        .grand-val { font-size: 13px; font-weight: 900; }

        .footer-text { font-size: 9px; text-align: center; margin-top: 4px; }

        /* A4 overrides */
        .size-a4 .store-name { font-size: 22px; }
        .size-a4 .store-info { font-size: 12px; }
        .size-a4 .r-label, .size-a4 .r-val { font-size: 13px; }
        .size-a4 .item-name { font-size: 14px; }
        .size-a4 .item-qty, .size-a4 .item-price { font-size: 13px; }
        .size-a4 .total-label, .size-a4 .total-val { font-size: 14px; }
        .size-a4 .grand-label, .size-a4 .grand-val { font-size: 18px; }
        .size-a4 .footer-text { font-size: 12px; }
        .size-a4 .r-line { margin: 8px 0; }
        .size-a4 .r-line-thick { margin: 10px 0; }
        .size-a4 .r-section { margin-bottom: 12px; }

        /* ===== PRINT STYLES ===== */
        @media print {
            html, body {
                width: 48mm;
                margin: 0;
                padding: 0;
                background: white !important;
            }
            .print-controls { display: none !important; }
            .receipt-wrapper {
                padding: 0 !important;
                display: block;
            }
            .receipt {
                box-shadow: none !important;
                width: 100% !important;
                max-width: 48mm !important;
                padding: 1mm !important;
                margin: 0 !important;
            }
            @page {
                size: 48mm auto;
                margin: 0;
            }
        }
        /* 80mm print */
        .size-80mm ~ .print-80mm-override { display: none; }
        @media print {
            body.print-80mm {
                width: 72mm;
            }
            body.print-80mm .receipt {
                max-width: 72mm !important;
            }
            body.print-80mm ~ style @page {
                size: 72mm auto;
            }
        }
    </style>
    <?php if ($size === '80mm'): ?>
    <style>
        @media print {
            html, body { width: 72mm; }
            .receipt { max-width: 72mm !important; }
            @page { size: 72mm auto; margin: 0; }
        }
    </style>
    <?php elseif ($size === 'a4'): ?>
    <style>
        @media print {
            html, body { width: 210mm; }
            .receipt { max-width: 210mm !important; width: 100% !important; padding: 10mm !important; }
            @page { size: A4; margin: 10mm; }
        }
    </style>
    <?php endif; ?>
</head>
<body>
    <!-- Print Controls Bar -->
    <div class="print-controls" id="print-controls">
        <div class="left">
            <a href="javascript:void(0)" onclick="window.close()" class="btn btn-back">
                <span class="material-icons-round" style="font-size:18px">close</span>
                Tutup
            </a>
            <h3>Cetak Nota #<?= htmlspecialchars($order_number) ?></h3>
        </div>
        <div class="right">
            <span style="font-size:11px;opacity:0.7;margin-right:4px;font-family:Arial;">Ukuran:</span>
            <a href="?id=<?= $oid ?>&size=58mm" class="btn btn-size <?= $size === '58mm' ? 'active' : '' ?>">58mm</a>
            <a href="?id=<?= $oid ?>&size=80mm" class="btn btn-size <?= $size === '80mm' ? 'active' : '' ?>">80mm</a>
            <a href="?id=<?= $oid ?>&size=a4" class="btn btn-size <?= $size === 'a4' ? 'active' : '' ?>">A4</a>
            <button onclick="window.print(); window.onafterprint = () => window.close();" class="btn btn-print">
                <span class="material-icons-round" style="font-size:18px">print</span>
                Cetak
            </button>
        </div>
    </div>

    <!-- Receipt -->
    <div class="receipt-wrapper">
        <div class="receipt size-<?= htmlspecialchars($size) ?>">

            <!-- Store Header -->
            <div class="r-center r-section">
                <div class="store-name"><?= htmlspecialchars($store_name) ?></div>
                <?php if ($store_address || $store_phone): ?>
                <div class="store-info">
                    <?php if ($store_address): ?><?= htmlspecialchars($store_address) ?><?php endif; ?>
                    <?php if ($store_phone): ?><br>Telp: <?= htmlspecialchars($store_phone) ?><?php endif; ?>
                </div>
                <?php endif; ?>
            </div>

            <div class="r-line-thick"></div>

            <!-- Order Info (stacked, not side-by-side) -->
            <div class="r-section">
                <div class="r-row">
                    <span class="r-label">No:</span>
                    <span class="r-val">#<?= htmlspecialchars($order_number) ?></span>
                </div>
                <div class="r-row">
                    <span class="r-label">Tgl:</span>
                    <span class="r-val"><?= $order_date ?></span>
                </div>
                <div class="r-row">
                    <span class="r-label">Bayar:</span>
                    <span class="r-val"><?= $pm_label ?></span>
                </div>
            </div>

            <div class="r-line"></div>

            <!-- Customer -->
            <?php if (!$is_manual): ?>
            <div class="r-section">
                <div class="r-row r-bold"><?= htmlspecialchars($order['customer_name']) ?></div>
                <?php if (!empty($order['customer_phone'])): ?>
                <div class="r-row" style="font-size:10px;"><?= htmlspecialchars($order['customer_phone']) ?></div>
                <?php endif; ?>
                <?php if (!empty($order['customer_address'])): ?>
                <div class="r-row" style="font-size:9px;"><?= htmlspecialchars($order['customer_address']) ?></div>
                <?php endif; ?>
                <?php if (!empty($order['courier_company'])): ?>
                <div class="r-row" style="font-size:10px;">Kurir: <?= htmlspecialchars($courier_label) ?></div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <div class="r-line-thick"></div>

            <!-- Items Header -->
            <div class="r-row-flex" style="border-bottom:1px solid #000;padding-bottom:2px;margin-bottom:4px;">
                <span class="r-bold" style="font-size:10px;">PRODUK</span>
                <span class="r-bold" style="font-size:10px;">HARGA</span>
            </div>

            <!-- Items -->
            <?php foreach ($items_arr as $item): ?>
            <div class="item-block">
                <div class="item-name"><?= htmlspecialchars($item['product_name']) ?></div>
                <div class="r-row-flex">
                    <span class="item-qty"><?= $item['weight'] ?> <?= ($item['unit'] ?? 'kg') ?> x Rp<?= number_format($item['price_per_kg'], 0, ',', '.') ?></span>
                    <span class="item-price">Rp<?= number_format($item['subtotal'], 0, ',', '.') ?></span>
                </div>
            </div>
            <?php endforeach; ?>

            <div class="r-line-thick"></div>

            <!-- Totals -->
            <div class="r-section">
                <div class="r-row-flex">
                    <span class="total-label">Subtotal</span>
                    <span class="total-val">Rp<?= number_format($product_subtotal, 0, ',', '.') ?></span>
                </div>
                <?php if ($system_discount > 1): ?>
                <div class="r-row-flex">
                    <span class="total-label">Diskon Grosir</span>
                    <span class="total-val">-Rp<?= number_format($system_discount, 0, ',', '.') ?></span>
                </div>
                <?php endif; ?>

                <?php if ($manual_discount > 0): ?>
                <div class="r-row-flex">
                    <span class="total-label">Diskon Manual</span>
                    <span class="total-val">-Rp<?= number_format($manual_discount, 0, ',', '.') ?></span>
                </div>
                <?php endif; ?>

                <?php if ($shipping_cost > 0): ?>
                <div class="r-row-flex">
                    <span class="total-label">Ongkir</span>
                    <span class="total-val">+Rp<?= number_format($shipping_cost, 0, ',', '.') ?></span>
                </div>
                <?php elseif ($shipping_cost == 0 && !empty($order['courier_company'])): ?>
                <div class="r-row-flex">
                    <span class="total-label">Ongkir</span>
                    <span class="total-val">GRATIS</span>
                </div>
                <?php endif; ?>

                <div class="r-line" style="margin:4px 0;"></div>

                <div class="r-row-flex">
                    <span class="grand-label">TOTAL</span>
                    <span class="grand-val">Rp<?= number_format($order['total_amount'], 0, ',', '.') ?></span>
                </div>
            </div>

            <?php if (!empty($order['order_notes'])): ?>
            <div class="r-line"></div>
            <div class="r-section">
                <div class="r-row r-bold" style="font-size:10px;">CATATAN:</div>
                <div class="r-row" style="font-size:9px;font-style:italic;">"<?= htmlspecialchars($order['order_notes']) ?>"</div>
            </div>
            <?php endif; ?>

            <div class="r-line-thick"></div>

            <!-- Footer -->
            <div class="footer-text">
                <div class="r-bold">Terima kasih!</div>
                <div class="r-bold">Syukron, Jazaakumullah Khoiron</div>
            </div>

        </div>
    </div>

    <script>
        <?php if (isset($_GET['auto_print'])): ?>
        window.addEventListener('load', () => {
            setTimeout(() => {
                window.print();
                // Attempt to close the window after printing
                window.onafterprint = () => {
                    window.close();
                };
                // Fallback for browsers that don't support onafterprint or if dialog is cancelled
                setTimeout(() => {
                    // We don't want to close too early if they are slow with the dialog
                    // but usually 1-2 seconds after print command is enough for the dialog to take focus
                }, 1000);
            }, 500);
        });
        <?php endif; ?>
    </script>
</body>
</html>
