<?php
require("auth_session.php");
require_once dirname(__DIR__) . "/config/init.php";

$success_msg = "";
$error_msg = "";

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_transaction'])) {
    $conn->begin_transaction();
    try {
        // 1. Handle Customer
        $customer_id = NULL;
        $customer_name = "Walk-in Guest";
        $customer_phone = "-";
        $customer_address = "-";

        $customer_type = $_POST['customer_type'] ?? 'walk-in';

        if ($customer_type === 'existing') {
            $customer_id = intval($_POST['existing_customer_id']);
            $cust_q = $conn->query("SELECT name, phone, address FROM customers WHERE id=$customer_id");
            if ($cust_q->num_rows == 0)
                throw new Exception("Pelanggan tidak ditemukan.");
            $cust_data = $cust_q->fetch_assoc();
            $customer_name = $cust_data['name'];
            $customer_phone = $cust_data['phone'];
            $customer_address = $cust_data['address'];
        } elseif ($customer_type === 'new') {
            $customer_name = mysqli_real_escape_string($conn, $_POST['new_customer_name']);
            $customer_phone = mysqli_real_escape_string($conn, $_POST['new_customer_phone']);
            $customer_address = mysqli_real_escape_string($conn, $_POST['new_customer_address']);

            if (empty($customer_name) || empty($customer_phone))
                throw new Exception("Nama dan Telepon pelanggan wajib diisi.");

            $check_phone = $conn->query("SELECT id FROM customers WHERE phone='$customer_phone'");
            if ($check_phone->num_rows > 0) {
                $customer_id = $check_phone->fetch_assoc()['id'];
            } else {
                $stmt_cust = $conn->prepare("INSERT INTO customers (name, phone, address, email) VALUES (?, ?, ?, '')");
                $stmt_cust->bind_param("sss", $customer_name, $customer_phone, $customer_address);
                if (!$stmt_cust->execute())
                    throw new Exception("Gagal membuat data pelanggan baru.");
                $customer_id = $conn->insert_id;
            }
        } else {
            // Walk-in
            $customer_name = !empty($_POST['walkin_name']) ? mysqli_real_escape_string($conn, $_POST['walkin_name']) : "Walk-in Guest";
        }

        // 2. Process Items & Calculate Total
        $items = $_POST['items']; // Array of pointers
        $subtotal_gross = 0;
        $order_items_data = [];
        $category_weights = [];

        foreach ($items as $idx => $item_raw) {
            $p_id = intval($_POST["product_id"][$idx]);
            $qty = floatval($_POST["qty"][$idx]);

            if ($p_id == 0 || $qty <= 0)
                continue;

            $prod_q = $conn->query("SELECT p.name, p.price, p.buy_price, p.stock, c.name as category_name 
                                    FROM products p 
                                    LEFT JOIN categories c ON p.category_id = c.id 
                                    WHERE p.id=$p_id FOR UPDATE");
            if ($prod_q->num_rows == 0)
                throw new Exception("Produk ID $p_id tidak ditemukan.");
            $prod_data = $prod_q->fetch_assoc();

            if ($prod_data['stock'] < $qty) {
                throw new Exception("Stok untuk {$prod_data['name']} tidak mencukupi (Sisa: {$prod_data['stock']}).");
            }

            $line_subtotal = $prod_data['price'] * $qty;
            $subtotal_gross += $line_subtotal;

            $cat_name = $prod_data['category_name'] ?? 'Uncategorized';
            if (!isset($category_weights[$cat_name]))
                $category_weights[$cat_name] = 0;
            $category_weights[$cat_name] += $qty;

            $order_items_data[] = [
                'product_id' => $p_id,
                'name' => $prod_data['name'],
                'price' => $prod_data['price'],
                'buy_price' => $prod_data['buy_price'],
                'weight' => $qty,
                'subtotal' => $line_subtotal
            ];
        }

        if (empty($order_items_data))
            throw new Exception("Tidak ada item yang dipilih.");

        // 3. Calculate Discounts
        $system_discount = 0;
        $rules_res = $conn->query("SELECT * FROM wholesale_rules WHERE is_active = 1 ORDER BY min_weight_kg DESC");
        $rules = [];
        while ($r = $rules_res->fetch_assoc())
            $rules[$r['category_name']][] = $r;

        foreach ($category_weights as $cat => $weight) {
            if (isset($rules[$cat])) {
                foreach ($rules[$cat] as $rule) {
                    if ($weight >= $rule['min_weight_kg']) {
                        $system_discount += ($weight * $rule['discount_per_kg']);
                        break;
                    }
                }
            }
        }

        $manual_discount = floatval(preg_replace('/[^0-9]/', '', $_POST['manual_discount'] ?? '0'));
        $total_amount = $subtotal_gross - $system_discount - $manual_discount;

        // 4. Create Order
        $max_id = $conn->query("SELECT MAX(id) as m FROM orders")->fetch_assoc()['m'];
        $new_id = $max_id ? $max_id + 1 : 1;

        // Generate Professional Order Number
        $date_prefix = date('Ymd');
        $random_suffix = strtoupper(substr(md5(uniqid()), 0, 4));
        $order_number = "LB-$date_prefix-$random_suffix";

        $status = 'completed';
        $payment_method = 'cod';
        $notes = isset($_POST['order_notes']) ? mysqli_real_escape_string($conn, $_POST['order_notes']) : "Transaksi Manual (Admin)";
        $transaction_time = !empty($_POST['transaction_time']) ? $_POST['transaction_time'] : date('Y-m-d H:i:s');

        $stmt_order = $conn->prepare("INSERT INTO orders (id, order_number, customer_id, customer_name, customer_phone, customer_address, total_amount, manual_discount, status, payment_method, order_notes, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt_order->bind_param("isssisddssss", $new_id, $order_number, $customer_id, $customer_name, $customer_phone, $customer_address, $total_amount, $manual_discount, $status, $payment_method, $notes, $transaction_time);

        if (!$stmt_order->execute())
            throw new Exception("Gagal membuat pesanan: " . $conn->error);

        // 5. Insert Items & Updates Stock
        $stmt_item = $conn->prepare("INSERT INTO order_items (order_id, product_name, weight, price_per_kg, buy_price, subtotal) VALUES (?, ?, ?, ?, ?, ?)");

        foreach ($order_items_data as $item) {
            $stmt_item->bind_param("isdddd", $new_id, $item['name'], $item['weight'], $item['price'], $item['buy_price'], $item['subtotal']);
            if (!$stmt_item->execute())
                throw new Exception("Gagal menyimpan item.");

            $conn->query("UPDATE products SET stock = stock - {$item['weight']} WHERE id={$item['product_id']}");
        }

        $conn->commit();
        $success_msg = "Transaksi berhasil! ID Order: #$new_id";

    } catch (Exception $e) {
        $conn->rollback();
        $error_msg = "Error: " . $e->getMessage();
    }
}

// Fetch Customers
$customers = $conn->query("SELECT id, name, phone FROM customers ORDER BY name ASC");
// Fetch Products
$products = $conn->query("SELECT p.id, p.name, p.stock, p.price, p.short_code, c.name as category_name 
                          FROM products p 
                          LEFT JOIN categories c ON p.category_id = c.id 
                          WHERE p.stock > 0 ORDER BY p.name ASC");
$prod_arr = [];
while ($p = $products->fetch_assoc()) {
    $prod_arr[] = $p;
}

// Fetch Active Wholesale Rules for frontend calc
$rules_res = $conn->query("SELECT * FROM wholesale_rules WHERE is_active = 1 ORDER BY category_name ASC, min_weight_kg DESC");
$wholesale_rules = [];
while ($r = $rules_res->fetch_assoc()) {
    $wholesale_rules[$r['category_name']][] = [
        'min_weight' => floatval($r['min_weight_kg']),
        'discount_per_kg' => floatval($r['discount_per_kg'])
    ];
}

// Generate Auto Walk-in Name
$today = date('Y-m-d');
$walkin_count_q = $conn->query("SELECT COUNT(*) as count FROM orders WHERE customer_id IS NULL AND DATE(created_at) = '$today'");
$walkin_count = $walkin_count_q->fetch_assoc()['count'] + 1;
$auto_walkin_name = "Pelanggan" . str_pad($walkin_count, 3, '0', STR_PAD_LEFT);
?>
<script>
    const allProducts = <?php echo json_encode($prod_arr); ?>;
    const wholesaleRules = <?php echo json_encode($wholesale_rules); ?>;
</script>
<!DOCTYPE html>
<html class="light" lang="id">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Input Transaksi Manual - Lapak Bangsawan</title>
    <link rel="icon" href="<?= BASE_URL ?>assets/images/favicon-laba.png" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&amp;display=swap"
        rel="stylesheet" />
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet" />
    <!-- Tom Select CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <style>
        /* Tom Select Customization for Tailwind-ish look */
        .ts-control {
            border-radius: 0.5rem;
            border-color: #e2e8f0;
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
            transition: all 0.2s ease;
        }

        .ts-control:focus-within {
            border-color: #0d59f2;
            box-shadow: 0 0 0 3px rgba(13, 89, 242, 0.1);
        }

        .ts-dropdown {
            border-radius: 0.5rem;
            overflow: hidden;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            animation: slideDown 0.2s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .dark .ts-control {
            background-color: #0f172a;
            border-color: #334155;
            color: #cbd5e1;
        }

        .dark .ts-control:focus-within {
            border-color: #0d59f2;
            box-shadow: 0 0 0 3px rgba(13, 89, 242, 0.2);
        }

        .dark .ts-dropdown {
            background-color: #1e293b;
            border-color: #334155;
            color: #cbd5e1;
        }

        .dark .ts-dropdown .option.active {
            background-color: #334155;
        }

        .dark .ts-input {
            color: #cbd5e1;
        }
    </style>
    <script>
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
                    fontFamily: { "display": ["Inter", "sans-serif"] },
                },
            },
        }
    </script>
</head>

<body
    class="bg-background-light dark:bg-background-dark text-slate-600 dark:text-slate-300 font-display transition-colors duration-200 antialiased overflow-hidden h-screen flex">
    <?php include ROOT_PATH . "includes/admin/sidebar.php"; ?>
    <main class="flex-1 flex flex-col h-full relative overflow-hidden">
        <?php $page_title = "Input Transaksi Manual";
        include ROOT_PATH . "includes/admin/header.php"; ?>

        <div class="flex-1 overflow-y-auto p-4 md:p-8 scroll-smooth flex flex-col">
            <div class="max-w-full mx-auto flex flex-col gap-6 w-full flex-grow">

                <div class="flex items-center gap-4 mb-2 md:mb-4">
                    <a href="orders"
                        class="flex items-center gap-2 text-slate-500 hover:text-primary transition-colors">
                        <span class="material-icons-round">arrow_back</span> Kembali
                    </a>
                </div>

                <?php if ($success_msg): ?>
                    <div
                        class="bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-700 rounded-lg p-4 mb-4 flex items-start gap-3 shadow-sm auto-close-alert transition-opacity duration-500">
                        <span class="material-icons-round text-green-500">check_circle</span>
                        <div>
                            <h3 class="font-medium text-slate-900 dark:text-white">Berhasil</h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400"><?php echo $success_msg; ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($error_msg): ?>
                    <div
                        class="bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-700 rounded-lg p-4 mb-4 flex items-start gap-3 shadow-sm auto-close-alert transition-opacity duration-500">
                        <span class="material-icons-round text-red-500">error</span>
                        <div>
                            <h3 class="font-medium text-slate-900 dark:text-white">Gagal</h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400"><?php echo $error_msg; ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <form method="POST" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <input type="hidden" name="submit_transaction" value="1">

                    <!-- Left Column: Customer & Products -->
                    <div class="lg:col-span-2 flex flex-col gap-6">

                        <!-- Customer Section -->
                        <div
                            class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-6">
                            <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                                <span
                                    class="flex items-center justify-center size-6 rounded-full bg-primary/10 text-primary text-xs">1</span>
                                Data Pelanggan
                            </h2>

                            <div class="flex border-b border-slate-200 dark:border-slate-700 mb-6">
                                <button type="button" onclick="setCustomerType('walk-in')" id="tab-walk-in"
                                    class="customer-tab px-6 py-3 text-sm font-medium border-b-2 border-primary text-primary transition-all">
                                    Pelanggan Umum
                                </button>
                                <button type="button" onclick="setCustomerType('existing')" id="tab-existing"
                                    class="customer-tab px-6 py-3 text-sm font-medium border-b-2 border-transparent text-slate-500 hover:text-slate-700 transition-all">
                                    Member Terdaftar
                                </button>
                                <button type="button" onclick="setCustomerType('new')" id="tab-new"
                                    class="customer-tab px-6 py-3 text-sm font-medium border-b-2 border-transparent text-slate-500 hover:text-slate-700 transition-all">
                                    Pelanggan Baru
                                </button>
                                <input type="hidden" name="customer_type" id="customer_type_input" value="walk-in">
                            </div>

                            <!-- Walk-in Customer Form -->
                            <div id="walk-in_customer_form" class="customer-form">
                                <label class="block text-sm font-medium mb-2 text-slate-700 dark:text-slate-300">Nama di
                                    Nota (Otomatis)</label>
                                <input type="text" name="walkin_name" value="<?= $auto_walkin_name ?>" readonly
                                    class="w-full rounded-lg border-slate-200 bg-slate-100 dark:bg-slate-800 dark:border-slate-700 text-slate-500 cursor-not-allowed text-sm">
                                <p class="mt-2 text-xs text-slate-400 italic">* Nama dihasilkan otomatis.</p>
                            </div>

                            <!-- Existing Customer Form -->
                            <div id="existing_customer_form" class="customer-form hidden">
                                <label class="block text-sm font-medium mb-2 text-slate-700 dark:text-slate-300">Cari
                                    Pelanggan</label>
                                <select name="existing_customer_id" id="existing_customer_select"
                                    class="w-full rounded-lg border-slate-200 bg-slate-50 dark:bg-slate-800 dark:border-slate-700 focus:ring-primary focus:border-primary">
                                    <option value="">-- Pilih Pelanggan --</option>
                                    <?php
                                    $customers->data_seek(0);
                                    while ($c = $customers->fetch_assoc()):
                                        ?>
                                        <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['name']); ?>
                                            (<?php echo htmlspecialchars($c['phone']); ?>)</option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <!-- New Customer Form -->
                            <div id="new_customer_form"
                                class="customer-form hidden grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="col-span-2 md:col-span-1">
                                    <label
                                        class="block text-sm font-medium mb-2 text-slate-700 dark:text-slate-300">Nama
                                        Lengkap</label>
                                    <input type="text" name="new_customer_name"
                                        class="w-full rounded-lg border-slate-200 bg-slate-50 dark:bg-slate-800 dark:border-slate-700 focus:ring-primary focus:border-primary">
                                </div>
                                <div class="col-span-2 md:col-span-1">
                                    <label
                                        class="block text-sm font-medium mb-2 text-slate-700 dark:text-slate-300">Nomor
                                        Telepon</label>
                                    <input type="text" name="new_customer_phone"
                                        class="w-full rounded-lg border-slate-200 bg-slate-50 dark:bg-slate-800 dark:border-slate-700 focus:ring-primary focus:border-primary">
                                </div>
                                <div class="col-span-2">
                                    <label
                                        class="block text-sm font-medium mb-2 text-slate-700 dark:text-slate-300">Alamat
                                        Lengkap</label>
                                    <textarea name="new_customer_address" rows="2"
                                        class="w-full rounded-lg border-slate-200 bg-slate-50 dark:bg-slate-800 dark:border-slate-700 focus:ring-primary focus:border-primary"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Product Section (Responsive Grid) -->
                        <div
                            class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-4 md:p-6">
                            <div class="flex justify-between items-center mb-4">
                                <h2 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                    <span
                                        class="flex items-center justify-center size-6 rounded-full bg-primary/10 text-primary text-xs">2</span>
                                    Item Pesanan
                                </h2>
                                <button type="button" onclick="addItem()"
                                    class="text-sm bg-primary/10 text-primary hover:bg-primary hover:text-white px-4 py-2 rounded-lg font-bold transition-all flex items-center gap-2">
                                    <span class="material-icons-round text-base">add</span> Tambah Item
                                </button>
                            </div>

                            <!-- Standard HTML Table for Items -->
                            <div class="overflow-x-auto">
                                <table class="w-full border-separate border-spacing-y-2">
                                    <thead>
                                        <tr class="text-xs font-bold uppercase text-slate-500">
                                            <th class="text-left px-3 py-2 w-24">Kode</th>
                                            <th class="text-left px-3 py-2">Nama Produk</th>
                                            <th class="text-left px-3 py-2 w-32 text-right">Harga</th>
                                            <th class="text-center px-3 py-2 w-20">Qty</th>
                                            <th class="text-right px-3 py-2 w-32">Subtotal</th>
                                            <th class="w-10"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="items_container">
                                        <!-- Rows will be injected here -->
                                    </tbody>
                                </table>
                            </div>

                            <div id="empty_state" class="text-center py-8 text-slate-400 text-sm">
                                Belum ada item. Klik "Tambah Item".
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Summary & Notes -->
                    <div class="lg:col-span-1 flex flex-col gap-6">

                        <!-- Transaction Time Card -->
                        <div
                            class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-6">
                            <h2
                                class="text-sm font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2 uppercase tracking-wider">
                                <span class="material-icons-round text-primary text-sm">event</span>
                                Waktu Transaksi
                            </h2>
                            <input type="datetime-local" id="transaction_time_input" name="transaction_time"
                                class="w-full rounded-lg border-slate-200 bg-slate-50 dark:bg-slate-800 dark:border-slate-700 focus:ring-primary focus:border-primary text-sm transition-all text-slate-500">
                            <p class="text-[10px] text-slate-400 mt-2">Biarkan kosong untuk menggunakan waktu saat ini (Otomatis).</p>
                        </div>

                        <!-- Order Notes Card -->
                        <div
                            class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-6">
                            <h2
                                class="text-sm font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2 uppercase tracking-wider">
                                <span class="material-icons-round text-primary text-sm">notes</span>
                                Catatan Pesanan
                            </h2>
                            <textarea name="order_notes" rows="3"
                                placeholder="Tulis instruksi khusus (misal: potong dadu, kirim sore)..."
                                class="w-full rounded-lg border-slate-200 bg-slate-50 dark:bg-slate-800 dark:border-slate-700 focus:ring-primary focus:border-primary text-sm transition-all"></textarea>
                        </div>

                        <!-- Manual Discount Card -->
                        <div
                            class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-6">
                            <h2
                                class="text-sm font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2 uppercase tracking-wider">
                                <span class="material-icons-round text-primary text-sm">loyalty</span>
                                Diskon Manual
                            </h2>
                            <div class="space-y-2">
                                <label class="block text-xs font-bold text-slate-500 uppercase">Potongan Tambahan (Rp)</label>
                                <div class="relative flex items-center group">
                                    <span
                                        class="absolute left-3 text-sm font-bold text-slate-400 group-focus-within:text-primary transition-colors">Rp</span>
                                    <input type="text" name="manual_discount" id="manual_discount_input" placeholder="0"
                                        class="currency-input w-full pl-10 pr-4 py-2 rounded-lg border-slate-200 bg-slate-50 dark:bg-slate-800 dark:border-slate-700 text-sm font-bold focus:ring-primary focus:border-primary transition-all">
                                </div>
                            </div>
                        </div>

                        <!-- Summary Card -->
                        <div
                            class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-6 sticky top-6">
                            <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-6">Ringkasan</h2>

                            <div class="flex justify-between items-center mb-2 text-sm">
                                <span>Subtotal</span>
                                <span id="summary_subtotal" class="font-medium text-slate-700 dark:text-slate-300">Rp 0</span>
                            </div>

                            <div id="row_system_discount" class="flex justify-between items-center mb-2 text-sm text-green-600 hidden">
                                <span>Diskon Sistem (Grosir)</span>
                                <span id="summary_system_discount" class="font-medium">- Rp 0</span>
                            </div>

                            <div id="row_manual_discount" class="flex justify-between items-center mb-2 text-sm text-amber-600 hidden">
                                <span>Diskon Manual</span>
                                <span id="summary_manual_discount" class="font-medium">- Rp 0</span>
                            </div>

                            <div class="border-t border-slate-100 dark:border-slate-800 my-4"></div>

                            <div class="flex justify-between items-end mb-8">
                                <span class="text-slate-500 font-bold">Total Bayar</span>
                                <span id="summary_total" class="text-2xl font-black text-primary">Rp 0</span>
                            </div>

                            <button type="submit"
                                class="w-full bg-primary text-white py-3.5 rounded-xl font-bold hover:bg-blue-600 transition-all shadow-lg shadow-blue-500/30 hover:-translate-y-0.5">
                                Proses Pesanan
                            </button>
                        </div>
                    </div>
                </form>

            </div>
            <?php include ROOT_PATH . "includes/admin/footer.php"; ?>
        </div>
    </main>

    <!-- Template for Item Row -->
    <template id="item_template">
        <!-- Checkbox wrapper for Tom Select issue -->
        <tr class="item-row bg-slate-50/50 dark:bg-slate-800/20 group">
            <!-- Short Code -->
            <td class="px-2 py-2 align-middle">
                <input type="text" placeholder="Kode"
                    class="short-code-input w-full rounded-md border-slate-200 bg-slate-50 dark:bg-slate-900 text-sm py-2 px-2 font-mono uppercase focus:ring-primary focus:border-primary"
                    onkeydown="if(event.key === 'Enter'){ event.preventDefault(); lookupShortCode(this); }">
            </td>

            <!-- Product Select -->
            <td class="px-2 py-2 align-middle">
                <select name="product_id[]"
                    class="product-select w-full rounded-md border-slate-200 bg-slate-50 dark:bg-slate-900 text-sm py-2">
                    <option value="" data-price="0">-- Pilih Produk --</option>
                    <?php foreach ($prod_arr as $p): ?>
                        <option value="<?php echo $p['id']; ?>" data-price="<?php echo $p['price']; ?>"
                            data-stock="<?php echo $p['stock']; ?>"
                            data-category="<?php echo htmlspecialchars($p['category_name']); ?>"
                            data-code="<?php echo htmlspecialchars($p['short_code']); ?>">
                            <?php echo htmlspecialchars($p['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="hidden" name="items[]" value="1">
            </td>

            <!-- Price Display -->
            <td class="px-2 py-2 align-middle text-right">
                <span class="font-medium price-display text-sm text-slate-500">Rp 0</span>
            </td>

            <!-- Quantity Input -->
            <td class="px-2 py-2 align-middle text-center">
                <input type="number" name="qty[]" step="0.5" value="1" min="0.5"
                    class="w-20 inline-block rounded-md border-slate-200 bg-white dark:bg-slate-900 text-sm py-2 text-center focus:ring-primary focus:border-primary"
                    oninput="updateRow(this)">
            </td>

            <!-- Subtotal -->
            <td class="px-2 py-2 align-middle text-right">
                <span class="font-bold subtotal-display text-sm text-primary">Rp 0</span>
            </td>

            <!-- Delete Button -->
            <td class="px-2 py-2 align-middle text-center">
                <button type="button" onclick="removeRow(this)"
                    class="text-slate-400 hover:text-red-500 transition-colors p-1">
                    <span class="material-icons-round text-lg">cancel</span>
                </button>
            </td>
        </tr>
    </template>

    <script>
        let customerSelect;

        function initCustomerSelect() {
            if (document.getElementById('existing_customer_select')) {
                customerSelect = new TomSelect('#existing_customer_select', {
                    create: false,
                    dropdownParent: 'body',
                    sortField: {
                        field: "text",
                        direction: "asc"
                    },
                    placeholder: "Cari pelanggan...",
                    maxOptions: 50
                });
            }
        }

        function setCustomerType(type) {
            // Update UI Tabs
            document.querySelectorAll('.customer-tab').forEach(tab => {
                tab.classList.remove('border-primary', 'text-primary');
                tab.classList.add('border-transparent', 'text-slate-500');
            });
            const activeTab = document.getElementById('tab-' + type);
            activeTab.classList.remove('border-transparent', 'text-slate-500');
            activeTab.classList.add('border-primary', 'text-primary');

            // Show appropriate form
            document.querySelectorAll('.customer-form').forEach(form => form.classList.add('hidden'));
            document.getElementById(type + '_customer_form').classList.remove('hidden');

            // Update hidden input
            document.getElementById('customer_type_input').value = type;

            // Handle validation requirements
            if (type === 'existing') {
                document.querySelector('select[name="existing_customer_id"]').setAttribute('required', 'required');
                document.querySelector('input[name="new_customer_name"]').removeAttribute('required');
                document.querySelector('input[name="new_customer_phone"]').removeAttribute('required');
            } else if (type === 'new') {
                document.querySelector('select[name="existing_customer_id"]').removeAttribute('required');
                document.querySelector('input[name="new_customer_name"]').setAttribute('required', 'required');
                document.querySelector('input[name="new_customer_phone"]').setAttribute('required', 'required');
            } else {
                document.querySelector('select[name="existing_customer_id"]').removeAttribute('required');
                document.querySelector('input[name="new_customer_name"]').removeAttribute('required');
                document.querySelector('input[name="new_customer_phone"]').removeAttribute('required');
            }
        }

        function addItem() {
            const container = document.getElementById('items_container');
            const template = document.getElementById('item_template');

            const clone = template.content.cloneNode(true);
            const row = clone.querySelector('.item-row');

            container.appendChild(row);
            document.getElementById('empty_state').classList.add('hidden');

            // Init Tom Select
            const newSelect = row.querySelector('.product-select');
            const ts = new TomSelect(newSelect, {
                create: false,
                dropdownParent: 'body',
                sortField: {
                    field: "text",
                    direction: "asc"
                },
                placeholder: "Cari produk...",
                onChange: function (value) {
                    const option = newSelect.querySelector(`option[value="${value}"]`);
                    if (option) {
                        const code = option.getAttribute('data-code');
                        row.querySelector('.short-code-input').value = code || '';
                    }
                    updateRow(newSelect);
                }
            });
            newSelect.tomselect = ts;

            recalcTotal();
        }

        function lookupShortCode(input) {
            const code = input.value.trim().toUpperCase();
            if (!code) return;

            const row = input.closest('.item-row');
            const select = row.querySelector('.product-select');

            // Find option with this code
            const option = Array.from(select.options).find(opt => opt.getAttribute('data-code') === code);

            if (option) {
                select.tomselect.setValue(option.value);
            } else {
                alert('Kode produk tidak ditemukan');
                input.value = '';
            }
        }

        function removeRow(btn) {
            const row = btn.closest('.item-row');
            const select = row.querySelector('.product-select');
            if (select.tomselect) {
                select.tomselect.destroy();
            }
            row.remove();
            if (document.querySelectorAll('.item-row').length === 0) {
                document.getElementById('empty_state').classList.remove('hidden');
            }
            recalcTotal();
        }

        function updateRow(element) {
            let row = element.closest('.item-row');
            let select = row.querySelector('select');
            const qtyInput = row.querySelector('input[type="number"]');

            const selectedOpt = select.options[select.selectedIndex];
            if (!selectedOpt || !selectedOpt.value) return;

            const price = parseFloat(selectedOpt.getAttribute('data-price')) || 0;
            const qty = parseFloat(qtyInput.value) || 0;
            const category = selectedOpt.getAttribute('data-category');

            const subtotal = price * qty;

            const formatter = new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            });

            row.querySelector('.price-display').innerText = formatter.format(price);
            row.querySelector('.subtotal-display').innerText = formatter.format(subtotal);
            row.querySelector('.subtotal-display').setAttribute('data-val', subtotal);
            row.querySelector('.subtotal-display').setAttribute('data-weight', qty);
            row.querySelector('.subtotal-display').setAttribute('data-category', category);

            recalcTotal();
        }

        function formatRupiah(angka) {
            var number_string = angka.replace(/[^0-9]/g, "").toString(),
                sisa = number_string.length % 3,
                rupiah = number_string.substr(0, sisa),
                ribuan = number_string.substr(sisa).match(/\d{3}/gi);

            if (ribuan) {
                var separator = sisa ? "." : "";
                rupiah += separator + ribuan.join(".");
            }
            return rupiah;
        }

        document.querySelectorAll('.currency-input').forEach(input => {
            const update = () => {
                let raw = input.value.replace(/[^0-9]/g, '');
                if (raw) {
                    let cursorSource = input.selectionStart;
                    let oldLen = input.value.length;
                    input.value = formatRupiah(raw);
                    let newLen = input.value.length;
                    input.setSelectionRange(cursorSource + (newLen - oldLen), cursorSource + (newLen - oldLen));
                }
            };
            input.addEventListener('input', update);
        });

        function recalcTotal() {
            let subtotal = 0;
            let count = 0;
            let categoryWeights = {};

            document.querySelectorAll('.subtotal-display').forEach(el => {
                const val = parseFloat(el.getAttribute('data-val')) || 0;
                const weight = parseFloat(el.getAttribute('data-weight')) || 0;
                const cat = el.getAttribute('data-category');

                if (val > 0) {
                    subtotal += val;
                    count++;
                    if (cat) {
                        categoryWeights[cat] = (categoryWeights[cat] || 0) + weight;
                    }
                }
            });

            // Calculate System Wholesale Discount
            let systemDiscount = 0;
            for (let cat in categoryWeights) {
                if (wholesaleRules[cat]) {
                    const weight = categoryWeights[cat];
                    // Make sure rules are sorted by min_weight desc
                    const sortedRules = wholesaleRules[cat].sort((a, b) => b.min_weight - a.min_weight);
                    
                    for (let rule of sortedRules) {
                        if (weight >= rule.min_weight) {
                            systemDiscount += (weight * rule.discount_per_kg);
                            break; // Apply highest tier only
                        }
                    }
                }
            }

            const manualDiscountInput = document.getElementById('manual_discount_input');
            const manualDiscountVal = manualDiscountInput.value.replace(/[^0-9]/g, '');
            const manualDiscount = parseFloat(manualDiscountVal) || 0;
            
            const finalTotal = subtotal - systemDiscount - manualDiscount;

            const formatter = new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            });

            // Update UI
            document.getElementById('summary_subtotal').innerText = formatter.format(subtotal);

            // System Discount Row
            const rowSystem = document.getElementById('row_system_discount');
            if (systemDiscount > 0) {
                rowSystem.classList.remove('hidden');
                document.getElementById('summary_system_discount').innerText = "- " + formatter.format(systemDiscount);
            } else {
                rowSystem.classList.add('hidden');
            }

            // Manual Discount Row
            const rowManual = document.getElementById('row_manual_discount');
            if (manualDiscount > 0) {
                rowManual.classList.remove('hidden');
                document.getElementById('summary_manual_discount').innerText = "- " + formatter.format(manualDiscount);
            } else {
                rowManual.classList.add('hidden');
            }

            document.getElementById('summary_total').innerText = formatter.format(finalTotal < 0 ? 0 : finalTotal);
            
            // Debug/Extra info if needed, otherwise clear
            // document.getElementById('summary_display_text').innerText = ""; 
        }

        // Auto update time
        function updateTransactionTime() {
             const input = document.getElementById('transaction_time_input');
             if(!input.value) {
                const now = new Date();
                // Format to YYYY-MM-DDTHH:MM for datetime-local
                const year = now.getFullYear();
                const month = String(now.getMonth() + 1).padStart(2, '0');
                const day = String(now.getDate()).padStart(2, '0');
                const hours = String(now.getHours()).padStart(2, '0');
                const minutes = String(now.getMinutes()).padStart(2, '0');
                input.value = `${year}-${month}-${day}T${hours}:${minutes}`;
             }
        }
        
        // Run once on load
        window.addEventListener('load', () => {
            updateTransactionTime();
        });

        // Init
        setCustomerType('walk-in');
        initCustomerSelect();
        addItem(); 
        
        // Manual Discount Listener
        document.getElementById('manual_discount_input').addEventListener('input', recalcTotal); 
    </script>
</body>

</html>