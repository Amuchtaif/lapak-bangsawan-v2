<?php
require("auth_session.php");
require_once dirname(__DIR__) . "/config/init.php";

$type = $_GET['type'] ?? 'excel';

if ($type == 'excel') {
    // Export to CSV
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="dashboard_export_' . date('Y-m-d') . '.csv"');

    $output = fopen('php://output', 'w');
    fputcsv($output, array('ID Order', 'Tanggal', 'Nama Pelanggan', 'No. HP', 'Alamat', 'Total (Rp)', 'Status', 'Metode Pembayaran'));

    $query = "SELECT * FROM orders ORDER BY created_at DESC LIMIT 100"; // Limit for quick export from dashboard
    $result = mysqli_query($conn, $query);

    while ($row = mysqli_fetch_assoc($result)) {
        fputcsv($output, array(
            $row['id'],
            $row['created_at'],
            $row['customer_name'],
            $row['customer_phone'],
            $row['customer_address'],
            $row['total_amount'],
            $row['status'],
            $row['payment_method'] ?? '-'
        ));
    }
    fclose($output);
    exit();

} elseif ($type == 'pdf') {
    // Print View
    $query = "SELECT * FROM orders ORDER BY created_at DESC LIMIT 50";
    $result = mysqli_query($conn, $query);
    ?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <title>Order Report</title>
        <style>
            body {
                font-family: sans-serif;
                padding: 20px;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 20px;
            }

            th,
            td {
                border: 1px solid #ddd;
                padding: 8px;
                text-align: left;
                font-size: 12px;
            }

            th {
                background-color: #f2f2f2;
            }

            .header {
                margin-bottom: 20px;
            }

            .header h1 {
                margin: 0;
                font-size: 24px;
            }

            .header p {
                margin: 5px 0 0;
                color: #666;
            }

            @media print {
                .no-print {
                    display: none;
                }
            }
        </style>
    </head>

    <body onload="window.print()">
        <div class="header">
            <h1>Laporan Pesanan Terbaru</h1>
            <p>Dicetak pada:
                <?php echo date('d F Y H:i'); ?>
            </p>
        </div>
        <button class="no-print" onclick="window.print()">Print / Save as PDF</button>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tanggal</th>
                    <th>Pelanggan</th>
                    <th>Total</th>
                    <th>Metode</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td>#
                            <?php echo $row['id']; ?>
                        </td>
                        <td>
                            <?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?>
                        </td>
                        <td>
                            <strong>
                                <?php echo htmlspecialchars($row['customer_name']); ?>
                            </strong><br>
                            <?php echo htmlspecialchars($row['customer_phone']); ?>
                        </td>
                        <td>Rp
                            <?php echo number_format($row['total_amount'], 0, ',', '.'); ?>
                        </td>
                        <td style="text-transform: capitalize;">
                            <?php echo $row['payment_method'] ?? '-'; ?>
                        </td>
                        <td style="text-transform: capitalize;">
                            <?php echo $row['status']; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </body>

    </html>
    <?php
    exit();
}
?>