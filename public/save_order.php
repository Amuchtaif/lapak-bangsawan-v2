<?php
header('Content-Type: application/json');
require_once dirname(__DIR__) . "/config/init.php";

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

$name = mysqli_real_escape_string($conn, $input['name']);
$phone = mysqli_real_escape_string($conn, $input['phone']);
$address = mysqli_real_escape_string($conn, $input['address']);
$order_notes = isset($input['order_notes']) ? mysqli_real_escape_string($conn, $input['order_notes']) : '';
$payment_method = isset($input['payment_method']) ? mysqli_real_escape_string($conn, $input['payment_method']) : 'transfer'; // Default
$order_token = isset($input['order_token']) ? mysqli_real_escape_string($conn, $input['order_token']) : null;
$items = $input['items'];
$total = floatval($input['total']);
$email = isset($input['email']) ? mysqli_real_escape_string($conn, $input['email']) : '';
$courier_company = mysqli_real_escape_string($conn, $input['courier_company'] ?? '');
$courier_type = mysqli_real_escape_string($conn, $input['courier_type'] ?? '');
$courier_price = floatval($input['courier_price'] ?? 0);
$shipping_cost = floatval($input['shipping_cost'] ?? 0);
$destination_area_id = mysqli_real_escape_string($conn, $input['destination_area_id'] ?? '');

$weight_total = getCartTotalWeight($items);

if (!$name || !$phone || empty($items)) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

// 1. Idempotency Check
if ($order_token) {
    $check_token = $conn->query("SELECT id FROM orders WHERE order_token = '$order_token' LIMIT 1");
    if ($check_token->num_rows > 0) {
        // Order already exists, return success immediately with existing data
        $existing_order = $check_token->fetch_assoc();
        $order_id = $existing_order['id'];

        // Construct WhatsApp message (Same logic as below)
        // We need to re-generate the message or just send the link. 
        // Ideally we should store the link or just re-generate it.
        // For simplicity/robustness, let's re-generate.

        $message = "Halo Lapak Bangsawan, saya ingin konfirmasi pesanan saya:\n\n";
        $message .= "*Order ID:* #ORD-" . str_pad($order_id, 5, '0', STR_PAD_LEFT) . "\n";
        $message .= "*Nama:* $name\n";
        $message .= "*Total:* Rp " . number_format($total, 0, ',', '.') . "\n\n";
        $message .= "*Item:*\n";
        foreach ($items as $item) {
            $message .= "- " . $item['name'] . " (" . $item['weight'] . ")\n";
        }
        $message .= "\n*Alamat Pengiriman:*\n$address\n";
        $pm_label = ($payment_method == 'cod') ? 'COD (Bayar di Tempat)' : 'Transfer Bank (BSI)';
        $message .= "\n*Metode Pembayaran:* " . $pm_label . "\n";
        if ($payment_method == 'transfer') {
            $message .= "Bank: BSI\nNo. Rek: 7252428245\nA.n: Shohibudin\n";
        }
        $message .= "\nMohon diproses. Terima kasih!";
        $wa_url = "https://wa.me/62859110022099?text=" . urlencode($message);

        echo json_encode(['success' => true, 'whatsapp_url' => $wa_url, 'message' => 'Order already processed']);
        exit;
    }
}

// Check and Create Customer
$check_customer = $conn->query("SELECT id FROM customers WHERE phone = '$phone' LIMIT 1");
if ($check_customer->num_rows == 0) {
    $insert_customer = "INSERT INTO customers (name, email, phone, address) VALUES ('$name', '$email', '$phone', '$address')";
    if (!$conn->query($insert_customer)) {
        error_log("Failed to create customer: " . $conn->error);
    }
}

// 1. Recalculate Totals on Server for Integrity
$calculation = calculateCartTotal($items);
$total = $calculation['total'];
$total_discount = $calculation['total_discount'];
$discounts_detail = $calculation['discounts_detail'];

// Start Transaction
$conn->begin_transaction();

try {
    // Determine Next Order ID (Dynamic)
    $max_id_query = $conn->query("SELECT MAX(id) as max_id FROM orders");
    $max_id_row = $max_id_query->fetch_assoc();
    $next_order_id = ($max_id_row['max_id']) ? intval($max_id_row['max_id']) + 1 : 1;

    // Generate Professional Order Number
    $date_prefix = date('Ymd');
    $random_suffix = strtoupper(substr(md5(uniqid()), 0, 4));
    $order_number = "LB-$date_prefix-$random_suffix";

    // Create Order
    $status = ($payment_method === 'transfer') ? 'unpaid' : 'pending';
    // Include order_token in insert
    $token_val = $order_token ? "'$order_token'" : "NULL";

    $sql = "INSERT INTO orders (id, order_number, customer_name, customer_phone, customer_address, destination_area_id, total_amount, status, payment_method, order_token, order_notes, courier_company, courier_type, weight_total) 
            VALUES ($next_order_id, '$order_number', '$name', '$phone', '$address', '$destination_area_id', $total, '$status', '$payment_method', $token_val, '$order_notes', '$courier_company', '$courier_type', $weight_total)";

    if (!$conn->query($sql)) {
        if ($conn->errno == 1062) {
            throw new Exception("Duplicate order detected.");
        }
        throw new Exception("Failed to create order: " . $conn->error);
    }

    $order_id = $conn->insert_id;

    // Insert Items and Update Stock
    foreach ($items as $item) {
        $p_id = intval($item['product_id']);
        $p_name = mysqli_real_escape_string($conn, $item['name']);
        $weight = floatval($item['weight']);
        $price_per_kg = floatval($item['price']);
        $subtotal = $price_per_kg * $weight;

        // Check Stock and Buy Price
        $prod_query = $conn->query("SELECT stock, buy_price FROM products WHERE id = $p_id FOR UPDATE");
        if ($prod_query->num_rows == 0) {
            throw new Exception("Product not found: " . $p_name);
        }
        $row = $prod_query->fetch_assoc();
        $current_stock = floatval($row['stock']);
        $buy_price = floatval($row['buy_price']);

        if ($current_stock < $weight) {
            throw new Exception("Stok tidak cukup untuk: " . $p_name . ". Sisa: " . $current_stock);
        }

        // Deduct Stock
        $new_stock = $current_stock - $weight;
        $conn->query("UPDATE products SET stock = $new_stock WHERE id = $p_id");

        // Insert Order Item
        $item_sql = "INSERT INTO order_items (order_id, product_name, weight, price_per_kg, buy_price, subtotal) 
                     VALUES ($order_id, '$p_name', $weight, $price_per_kg, $buy_price, $subtotal)";
        $conn->query($item_sql);
    }

    // Commit Transaction
    $conn->commit();

    // Generate Response based on Payment Method
    if ($payment_method === 'transfer') {
        echo json_encode([
            'success' => true,
            'order_id' => $order_id,
            'payment_method' => 'transfer',
            'redirect_url' => BASE_URL . "payment?id=" . $order_id
        ]);
    } else {
        // COD or others: Generate WhatsApp Link
        $message = "Halo Lapak Bangsawan, saya ingin konfirmasi pesanan saya:\n\n";
        $message .= "*Order Number:* $order_number\n";
        $message .= "*Nama:* $name\n";
        $message .= "*Item:*\n";
        foreach ($items as $item) {
            $u = $item['unit'] ?? 'kg';
            $message .= "- " . $item['name'] . " (" . $item['weight'] . " $u)\n";
        }

        if ($total_discount > 0) {
            $message .= "\n*Rincian Diskon Grosir:*\n";
            foreach ($discounts_detail as $d) {
                $message .= "- " . $d['label'] . ": -Rp " . number_format($d['amount'], 0, ',', '.') . "\n";
            }
        }

        $message .= "\n*Total Akhir:* Rp " . number_format($total, 0, ',', '.') . "\n";
        $message .= "\n*Alamat Pengiriman:*\n$address\n";

        if (!empty($order_notes)) {
            $message .= "\n*Catatan Pesanan:*\n$order_notes\n";
        }

        $pm_label = ($payment_method == 'cod') ? 'COD (Bayar di Tempat)' : 'Transfer Bank (BSI)';
        $message .= "\n*Metode Pembayaran:* " . $pm_label . "\n";

        if ($courier_company) {
            $message .= "\n*Pengiriman:* " . strtoupper($courier_company) . " (" . $courier_type . ")\n";
            $message .= "Ongkir: Rp " . number_format($shipping_cost, 0, ',', '.') . "\n";
        }

        if ($payment_method == 'transfer') {
            $message .= "Bank: BSI\nNo. Rek: 7252428245\nA.n: Shohibudin\n";
        }

        $message .= "\nMohon diproses. Terima kasih!";
        $wa_url = "https://wa.me/62859110022099?text=" . urlencode($message);

        echo json_encode([
            'success' => true,
            'order_id' => $order_id,
            'payment_method' => 'cod',
            'whatsapp_url' => $wa_url
        ]);
    }

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>