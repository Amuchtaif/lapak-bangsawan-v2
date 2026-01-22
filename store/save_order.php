<?php
header('Content-Type: application/json');
require("../config/database.php");

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

// Start Transaction
$conn->begin_transaction();

try {
    // Determine Next Order ID (Dynamic)
    $max_id_query = $conn->query("SELECT MAX(id) as max_id FROM orders");
    $max_id_row = $max_id_query->fetch_assoc();
    $next_order_id = ($max_id_row['max_id']) ? intval($max_id_row['max_id']) + 1 : 1;

    // Create Order
    $status = 'pending';
    // Include order_token in insert
    $token_val = $order_token ? "'$order_token'" : "NULL";

    $sql = "INSERT INTO orders (id, customer_name, customer_phone, customer_address, total_amount, status, payment_method, order_token, order_notes) 
            VALUES ($next_order_id, '$name', '$phone', '$address', $total, '$status', '$payment_method', $token_val, '$order_notes')";

    if (!$conn->query($sql)) {
        // Double check for duplicate entry error which acts as a second layer of idempotency
        if ($conn->errno == 1062) { // Duplicate entry
            throw new Exception("Duplicate order detected (Concurrent Request).");
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
        $subtotal = floatval($item['total_price']);

        // Check Stock
        $stock_query = $conn->query("SELECT stock FROM products WHERE id = $p_id FOR UPDATE");
        if ($stock_query->num_rows == 0) {
            throw new Exception("Product not found: " . $p_name);
        }
        $current_stock = floatval($stock_query->fetch_assoc()['stock']);

        if ($current_stock < $weight) {
            throw new Exception("Insufficient stock for product: " . $p_name . ". Available: " . $current_stock);
        }

        // Deduct Stock
        $new_stock = $current_stock - $weight;
        $update_stock = $conn->query("UPDATE products SET stock = $new_stock WHERE id = $p_id");
        if (!$update_stock) {
            throw new Exception("Failed to update stock for: " . $p_name);
        }

        // Insert Order Item
        $item_sql = "INSERT INTO order_items (order_id, product_name, weight, price_per_kg, subtotal) 
                     VALUES ($order_id, '$p_name', $weight, $price_per_kg, $subtotal)";
        if (!$conn->query($item_sql)) {
            throw new Exception("Failed to add order item: " . $p_name);
        }
    }

    // Commit Transaction
    $conn->commit();


    // Generate WhatsApp Link
    $message = "Halo Lapak Bangsawan, saya ingin konfirmasi pesanan saya:\n\n";
    $message .= "*Order ID:* #ORD-" . str_pad($order_id, 5, '0', STR_PAD_LEFT) . "\n";
    $message .= "*Nama:* $name\n";
    $message .= "*Total:* Rp " . number_format($total, 0, ',', '.') . "\n\n";
    $message .= "*Item:*\n";
    foreach ($items as $item) {
        $message .= "- " . $item['name'] . " (" . $item['weight'] . ")\n";
    }
    $message .= "\n*Alamat Pengiriman:*\n$address\n";

    if (!empty($order_notes)) {
        $message .= "\n*Catatan Pesanan:*\n$order_notes\n";
    }

    $pm_label = ($payment_method == 'cod') ? 'COD (Bayar di Tempat)' : 'Transfer Bank (BSI)';
    $message .= "\n*Metode Pembayaran:* " . $pm_label . "\n";
    if ($payment_method == 'transfer') {
        $message .= "Bank: BSI\nNo. Rek: 7252428245\nA.n: Shohibudin\n";
    }

    $message .= "\nMohon diproses. Terima kasih!";

    $wa_url = "https://wa.me/62859110022099?text=" . urlencode($message);

    echo json_encode(['success' => true, 'whatsapp_url' => $wa_url]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>