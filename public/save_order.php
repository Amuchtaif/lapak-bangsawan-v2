<?php
header('Content-Type: application/json');
require_once dirname(__DIR__) . "/config/init.php";
require_once dirname(__DIR__) . "/includes/cart_helper.php";

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
    exit;
}

// 1. Basic Input Sanitization (Clean Strings)
$name = isset($input['name']) ? trim($input['name']) : '';
$phone = isset($input['phone']) ? trim($input['phone']) : '';
$address = isset($input['address']) ? trim($input['address']) : '';
$order_notes = isset($input['order_notes']) ? trim($input['order_notes']) : '';
$payment_method = isset($input['payment_method']) ? $input['payment_method'] : 'transfer';
$order_token = isset($input['order_token']) ? $input['order_token'] : null;
$email = isset($input['email']) ? trim($input['email']) : '';

// Shipping Data
$courier_company = $input['courier_company'] ?? '';
$courier_type = $input['courier_type'] ?? '';
$courier_price = floatval($input['courier_price'] ?? 0);
$shipping_cost = floatval($input['shipping_cost'] ?? 0);
$destination_area_id = $input['destination_area_id'] ?? '';
$dest_lat = !empty($input['dest_lat']) ? floatval($input['dest_lat']) : null;
$dest_lng = !empty($input['dest_lng']) ? floatval($input['dest_lng']) : null;

// Items Input
$items_input = $input['items'] ?? [];

// Validation: Critical Fields
if (!$name || !$phone || !$address || empty($items_input)) {
    echo json_encode(['success' => false, 'message' => 'Nama, Telepon, Alamat, dan Produk tidak boleh kosong.']);
    exit;
}

// Validation: Shipping Cost
if ($shipping_cost < 0)
    $shipping_cost = 0;

// 2. Idempotency Check (Prevent Double Submit)
if ($order_token) {
    $stmt = $conn->prepare("SELECT id FROM orders WHERE order_token = ? LIMIT 1");
    $stmt->bind_param("s", $order_token);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
        $existing = $res->fetch_assoc();
        echo json_encode([
            'success' => true,
            'order_id' => $existing['id'],
            'message' => 'Order already processed',
            // Ideally return full details, but for now simple success
            'redirect_url' => BASE_URL . "payment?id=" . $existing['id']
        ]);
        exit;
    }
}

// 3. Customer Creation / Update
// We use phone as unique identifier for simplicity here
$stmt = $conn->prepare("SELECT id FROM customers WHERE phone = ? LIMIT 1");
$stmt->bind_param("s", $phone);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows == 0) {
    // New Customer
    $stmt = $conn->prepare("INSERT INTO customers (name, email, phone, address) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $phone, $address);
    $stmt->execute();
    $cust_id = $conn->insert_id;
} else {
    // Existing Customer - Update address? Optional. For now just get ID.
    $row = $res->fetch_assoc();
    $cust_id = $row['id'];
}

// 4. Start Transaction (CRITICAL)
$conn->begin_transaction();

try {
    // 5. Item Validation & Stock Check & Price Verification (SECURITY)
    $verified_items = [];

    foreach ($items_input as $item) {
        $pid = intval($item['product_id']);
        $reqWeight = floatval($item['weight']);
        $reqUnit = $item['unit'] ?? 'kg';

        if ($reqWeight <= 0)
            continue; // Skip invalid weights

        // Lock Product Row !
        $stmt = $conn->prepare("SELECT id, name, price, buy_price, stock, category_id, unit FROM products WHERE id = ? FOR UPDATE");
        $stmt->bind_param("i", $pid);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows === 0) {
            throw new Exception("Produk dengan ID $pid tidak ditemukan (mungkin sudah dihapus).");
        }

        $product = $res->fetch_assoc();
        $current_stock = floatval($product['stock']);
        $real_price = floatval($product['price']);
        $real_buy_price = floatval($product['buy_price']);

        // Check Stock
        if ($current_stock < $reqWeight) {
            throw new Exception("Stok tidak mencukupi untuk item: " . $product['name'] . ". Sisa stok: " . $current_stock);
        }

        // Fetch Category Name for Cart Calculation Helper
        $cat_name = 'Uncategorized';
        if ($product['category_id']) {
            $cRes = $conn->query("SELECT name FROM categories WHERE id = " . $product['category_id']);
            if ($cRes->num_rows > 0)
                $cat_name = $cRes->fetch_assoc()['name'];
        }

        $verified_items[] = [
            'product_id' => $pid,
            'name' => $product['name'], // Trust DB name
            'price' => $real_price,     // Trust DB price
            'buy_price' => $real_buy_price,
            'weight' => $reqWeight,
            'stock' => $current_stock,
            'category' => $cat_name,
            'unit' => $reqUnit
        ];
    }

    if (empty($verified_items)) {
        throw new Exception("Tidak ada item yang valid dalam pesanan.");
    }

    // 6. Calculate Totals (Using Verified Data)
    // We reuse calculateCartTotal because it handles Wholesale Logic
    $calculation = calculateCartTotal($verified_items); // Now safe because $items has real DB prices

    $subtotal = $calculation['subtotal'];
    $total_discount = $calculation['total_discount'];
    $discounts_detail = $calculation['discounts_detail'];
    $final_product_total = $calculation['total'];

    $grand_total = $final_product_total + $shipping_cost;
    $weight_total_grams = getCartTotalWeight($verified_items); // Logic uses verified units

    // 7. Generate Order ID & Number
    // Use manual ID increment to be consistent with old code preference? Or Auto Increment?
    // Old code typically did manual MAX(id)+1. We can stick to that or let DB handle it.
    // For safety in transaction, let DB Auto Increment handle ID usually.
    // But if you rely on specific ID logic:
    $max_res = $conn->query("SELECT MAX(id) as m FROM orders");
    $max_row = $max_res->fetch_assoc();
    $next_id = ($max_row['m']) ? intval($max_row['m']) + 1 : 1;

    $date_prefix = date('Ymd');
    $random_suffix = strtoupper(substr(md5(uniqid()), 0, 4));
    $order_number = "LB-$date_prefix-$random_suffix";

    // 8. Insert Order Header
    $status = 'pending';

    $stmt = $conn->prepare("INSERT INTO orders (id, order_number, customer_name, customer_phone, customer_address, destination_area_id, destination_latitude, destination_longitude, total_amount, status, payment_method, order_token, order_notes, courier_company, courier_type, weight_total) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param(
        "isssssddsssssssi",
        $next_id,
        $order_number,
        $name,
        $phone,
        $address,
        $destination_area_id,
        $dest_lat,
        $dest_lng,
        $grand_total,
        $status,
        $payment_method,
        $order_token,
        $order_notes,
        $courier_company,
        $courier_type,
        $weight_total_grams
    );

    if (!$stmt->execute()) {
        throw new Exception("Gagal membuat pesanan: " . $stmt->error);
    }

    $order_id = $next_id; // or $conn->insert_id if we used auto increment

    // 9. Insert Order Items & Deduct Stock
    foreach ($verified_items as $item) {
        $p_id = $item['product_id'];
        $qty = $item['weight'];
        $price = $item['price'];
        $buy_price = $item['buy_price'];
        $subtotal_item = $price * $qty;

        // Deduct Stock
        $new_stock = $item['stock'] - $qty;
        $upd = $conn->prepare("UPDATE products SET stock = ? WHERE id = ?");
        $upd->bind_param("di", $new_stock, $p_id);
        $upd->execute();

        // Insert Item
        $ins = $conn->prepare("INSERT INTO order_items (order_id, product_name, weight, price_per_kg, buy_price, subtotal) VALUES (?, ?, ?, ?, ?, ?)");
        $ins->bind_param("isdddd", $order_id, $item['name'], $qty, $price, $buy_price, $subtotal_item);
        $ins->execute();
    }

    // 10. Success! Commit.
    $conn->commit();

    // 11. Cleanup Session (If used)
    if (isset($_SESSION['cart'])) {
        unset($_SESSION['cart']);
    }

    // 12. Response
    if ($payment_method === 'transfer') {
        echo json_encode([
            'success' => true,
            'order_id' => $order_id,
            'payment_method' => 'transfer',
            'redirect_url' => BASE_URL . "payment?id=" . $order_id
        ]);
    } else {
        // WhatsApp Message Construction
        $msg = "Halo Lapak Bangsawan, saya ingin konfirmasi pesanan saya:\n\n";
        $msg .= "*Order ID:* #$order_number\n";
        $msg .= "*Nama:* $name\n";
        $msg .= "*Total:* Rp " . number_format($grand_total, 0, ',', '.') . "\n\n";
        $msg .= "*Detail Item:*\n";
        foreach ($verified_items as $vi) {
            $msg .= "- " . $vi['name'] . " (" . $vi['weight'] . " " . $vi['unit'] . ")\n";
        }

        if ($total_discount > 0) {
            $msg .= "\n*Info Diskon:*\n";
            foreach ($discounts_detail as $dd) {
                $msg .= "- " . $dd['label'] . ": -Rp " . number_format($dd['amount'], 0, ',', '.') . "\n";
            }
        }

        $msg .= "\n*Alamat:* " . $address . "\n";
        $msg .= "*Pembayaran:* " . (($payment_method == 'cod') ? 'COD (Bayar Ditempat)' : $payment_method) . "\n";

        if ($courier_company) {
            $msg .= "*Kurir:* " . strtoupper($courier_company) . " ($courier_type) - Rp " . number_format($shipping_cost, 0, ',', '.') . "\n";
        }

        $msg .= "\nMohon diproses. Terima kasih!";

        echo json_encode([
            'success' => true,
            'order_id' => $order_id,
            'payment_method' => 'cod',
            'whatsapp_url' => "https://wa.me/62859110022099?text=" . urlencode($msg)
        ]);
    }

} catch (Exception $e) {
    $conn->rollback();
    error_log("Checkout Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>