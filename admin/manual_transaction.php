<?php
require("auth_session.php");
require("../db_connect.php");

$success_msg = "";
$error_msg = "";

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_transaction'])) {
    $conn->begin_transaction();
    try {
        // 1. Handle Customer
        $customer_id = 0;
        $customer_name = "";
        $customer_phone = "";
        $customer_address = "";

        if ($_POST['customer_type'] === 'existing') {
            $customer_id = intval($_POST['existing_customer_id']);
            $cust_q = $conn->query("SELECT name, phone, address FROM customers WHERE id=$customer_id");
            if ($cust_q->num_rows == 0)
                throw new Exception("Pelanggan tidak ditemukan.");
            $cust_data = $cust_q->fetch_assoc();
            $customer_name = $cust_data['name'];
            $customer_phone = $cust_data['phone'];
            $customer_address = $cust_data['address'];
        } else {
            $customer_name = mysqli_real_escape_string($conn, $_POST['new_customer_name']);
            $customer_phone = mysqli_real_escape_string($conn, $_POST['new_customer_phone']);
            $customer_address = mysqli_real_escape_string($conn, $_POST['new_customer_address']);

            // Basic Validation
            if (empty($customer_name) || empty($customer_phone))
                throw new Exception("Nama dan Telepon pelanggan wajib diisi.");

            // Check if phone exists (optional, but good to avoid dupes)
            $check_phone = $conn->query("SELECT id FROM customers WHERE phone='$customer_phone'");
            if ($check_phone->num_rows > 0) {
                // Option: Use existing or throw error. Let's use existing to be smart.
                $customer_id = $check_phone->fetch_assoc()['id'];
            } else {
                $stmt_cust = $conn->prepare("INSERT INTO customers (name, phone, address, email) VALUES (?, ?, ?, '')");
                $stmt_cust->bind_param("sss", $customer_name, $customer_phone, $customer_address);
                if (!$stmt_cust->execute())
                    throw new Exception("Gagal membuat data pelanggan baru.");
                $customer_id = $conn->insert_id;
            }
        }

        // 2. Process Items & Calculate Total
        $items = $_POST['items']; // Array of pointers
        $total_amount = 0;
        $order_items_data = [];

        foreach ($items as $idx => $item_raw) {
            $p_id = intval($_POST["product_id"][$idx]);
            $qty = floatval($_POST["qty"][$idx]);

            if ($p_id == 0 || $qty <= 0)
                continue;

            $prod_q = $conn->query("SELECT name, price, stock FROM products WHERE id=$p_id FOR UPDATE");
            if ($prod_q->num_rows == 0)
                throw new Exception("Produk ID $p_id tidak ditemukan.");
            $prod_data = $prod_q->fetch_assoc();

            if ($prod_data['stock'] < $qty) {
                throw new Exception("Stok untuk {$prod_data['name']} tidak mencukupi (Sisa: {$prod_data['stock']}).");
            }

            $subtotal = $prod_data['price'] * $qty;
            $total_amount += $subtotal;

            $order_items_data[] = [
                'product_id' => $p_id,
                'name' => $prod_data['name'],
                'price' => $prod_data['price'],
                'weight' => $qty,
                'subtotal' => $subtotal
            ];
        }

        if (empty($order_items_data))
            throw new Exception("Tidak ada item yang dipilih.");

        // 3. Create Order
        $max_id = $conn->query("SELECT MAX(id) as m FROM orders")->fetch_assoc()['m'];
        $new_id = $max_id ? $max_id + 1 : 1;
        $status = 'completed';
        $payment_method = 'cod'; // Default for manual
        $notes = "Transaksi Manual (Admin)";

        $stmt_order = $conn->prepare("INSERT INTO orders (id, customer_name, customer_phone, customer_address, total_amount, status, payment_method, order_notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt_order->bind_param("isssdsss", $new_id, $customer_name, $customer_phone, $customer_address, $total_amount, $status, $payment_method, $notes);

        if (!$stmt_order->execute())
            throw new Exception("Gagal membuat pesanan: " . $conn->error);

        // 4. Insert Items & Updates Stock
        $stmt_item = $conn->prepare("INSERT INTO order_items (order_id, product_name, weight, price_per_kg, subtotal) VALUES (?, ?, ?, ?, ?)");

        foreach ($order_items_data as $item) {
            $stmt_item->bind_param("isddd", $new_id, $item['name'], $item['weight'], $item['price'], $item['subtotal']);
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
// Fetch Products (Include stock info in data attr)
$products = $conn->query("SELECT id, name, stock, price FROM products WHERE stock > 0 ORDER BY name ASC");
$prod_arr = [];
while ($p = $products->fetch_assoc()) {
    $prod_arr[] = $p;
}
?>
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
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
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
            <div class="max-w-7xl mx-auto flex flex-col gap-6 w-full flex-grow">

                <div class="flex items-center gap-4 mb-2 md:mb-4">
                    <a href="orders.php"
                        class="flex items-center gap-2 text-slate-500 hover:text-primary transition-colors">
                        <span class="material-icons-round">arrow_back</span> Kembali
                    </a>
                    <h1 class="text-xl md:text-2xl font-bold text-slate-900 dark:text-white">Transaksi Baru</h1>
                </div>

                <?php if ($success_msg): ?>
                    <div class="bg-green-100 text-green-700 p-4 rounded-lg flex items-center gap-2 mb-4 auto-close-alert transition-opacity duration-500">
                        <span class="material-icons-round">check_circle</span>
                        <?php echo $success_msg; ?>
                    </div>
                <?php endif; ?>

                <?php if ($error_msg): ?>
                    <div class="bg-red-100 text-red-700 p-4 rounded-lg flex items-center gap-2 mb-4 auto-close-alert transition-opacity duration-500">
                        <span class="material-icons-round">error</span>
                        <?php echo $error_msg; ?>
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

                            <div class="flex gap-4 mb-6 border-b border-slate-100 dark:border-slate-700 pb-4">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="customer_type" value="existing" checked
                                        onchange="toggleCustomerType()" class="text-primary focus:ring-primary">
                                    <span class="font-medium">Pelanggan Lama</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="customer_type" value="new" onchange="toggleCustomerType()"
                                        class="text-primary focus:ring-primary">
                                    <span class="font-medium">Pelanggan Baru</span>
                                </label>
                            </div>

                            <!-- Existing Customer Form -->
                            <div id="existing_customer_form">
                                <label class="block text-sm font-medium mb-2">Cari Pelanggan</label>
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
                            <div id="new_customer_form" class="hidden grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="col-span-2 md:col-span-1">
                                    <label class="block text-sm font-medium mb-2">Nama Lengkap</label>
                                    <input type="text" name="new_customer_name"
                                        class="w-full rounded-lg border-slate-200 bg-slate-50 dark:bg-slate-800 dark:border-slate-700 focus:ring-primary focus:border-primary">
                                </div>
                                <div class="col-span-2 md:col-span-1">
                                    <label class="block text-sm font-medium mb-2">Nomor Telepon</label>
                                    <input type="text" name="new_customer_phone"
                                        class="w-full rounded-lg border-slate-200 bg-slate-50 dark:bg-slate-800 dark:border-slate-700 focus:ring-primary focus:border-primary">
                                </div>
                                <div class="col-span-2">
                                    <label class="block text-sm font-medium mb-2">Alamat Lengkap</label>
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

                            <!-- Header Row (Desktop Only) -->
                            <div class="hidden md:grid md:grid-cols-12 gap-4 bg-slate-50 dark:bg-slate-800/50 p-3 rounded-lg text-xs font-bold uppercase text-slate-500 mb-2 sticky top-0 z-10">
                                <div class="col-span-5">Produk</div>
                                <div class="col-span-2">Harga/Kg</div>
                                <div class="col-span-2">Berat (Kg)</div>
                                <div class="col-span-2 text-right">Subtotal</div>
                                <div class="col-span-1 text-center"></div>
                            </div>

                            <div id="items_container" class="space-y-4 md:space-y-2 min-h-[100px]">
                                <!-- Rows will be injected here -->
                            </div>

                            <div id="empty_state" class="text-center py-8 text-slate-400 text-sm">
                                Belum ada item. Klik "Tambah Item".
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Summary -->
                    <div class="lg:col-span-1">
                        <div
                            class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-6 sticky top-6">
                            <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-6">Ringkasan</h2>

                            <div class="flex justify-between items-center mb-4 text-sm">
                                <span>Total Item</span>
                                <span id="summary_count" class="font-medium">0</span>
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
        <div class="item-row grid grid-cols-1 md:grid-cols-12 gap-4 items-start md:items-center bg-slate-50/50 dark:bg-slate-800/20 p-4 md:p-2 rounded-lg border border-slate-100 dark:border-slate-800/50">
            
            <!-- Product Select -->
            <div class="col-span-1 md:col-span-5 w-full">
                <label class="block md:hidden text-xs font-bold text-slate-500 uppercase mb-1">Produk</label>
                <div class="">
                    <select name="product_id[]"
                        class="product-select w-full rounded-md border-slate-200 bg-slate-50 dark:bg-slate-900 text-sm py-2"
                        onchange="updateRow(this)"> 
                        <option value="" data-price="0">-- Pilih Produk --</option>
                        <?php foreach ($prod_arr as $p): ?>
                            <option value="<?php echo $p['id']; ?>" data-price="<?php echo $p['price']; ?>"
                                data-stock="<?php echo $p['stock']; ?>">
                                <?php echo htmlspecialchars($p['name']); ?> (Stok: <?php echo $p['stock']; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <input type="hidden" name="items[]" value="1">
            </div>

            <!-- Price Display -->
             <div class="col-span-1 md:col-span-2 flex justify-between md:block items-center">
                <label class="block md:hidden text-xs font-bold text-slate-500 uppercase">Harga/Kg</label>
                <span class="font-medium price-display text-sm">Rp 0</span>
            </div>

            <!-- Quantity Input -->
            <div class="col-span-1 md:col-span-2 flex justify-between md:block items-center">
                 <label class="block md:hidden text-xs font-bold text-slate-500 uppercase">Berat (Kg)</label>
                <input type="number" name="qty[]" step="0.5" value="1" min="0.5"
                    class="w-full md:w-20 rounded-md border-slate-200 bg-slate-50 dark:bg-slate-900 text-sm py-2 text-center"
                    oninput="updateRow(this)">
            </div>

            <!-- Subtotal -->
             <div class="col-span-1 md:col-span-2 flex justify-between md:block items-center md:text-right">
                <label class="block md:hidden text-xs font-bold text-slate-500 uppercase">Subtotal</label>
                <span class="font-medium subtotal-display text-sm">Rp 0</span>
            </div>

            <!-- Delete Button -->
            <div class="col-span-1 md:col-span-1 text-right md:text-center mt-2 md:mt-0">
                <button type="button" onclick="removeRow(this)" class="w-full md:w-auto text-red-500 bg-red-50 dark:bg-red-900/30 hover:bg-red-100 p-2 rounded-lg md:rounded flex items-center justify-center gap-2">
                     <span class="md:hidden text-sm font-medium">Hapus Item</span>
                    <span class="material-icons-round text-base">close</span>
                </button>
            </div>
        </div>
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

        function toggleCustomerType() {
            const type = document.querySelector('input[name="customer_type"]:checked').value;
            const existingForm = document.getElementById('existing_customer_form');
            const newForm = document.getElementById('new_customer_form');

            if (type === 'existing') {
                existingForm.classList.remove('hidden');
                newForm.classList.add('hidden');
                document.querySelector('select[name="existing_customer_id"]').setAttribute('required', 'required');
                document.querySelector('input[name="new_customer_name"]').removeAttribute('required');
                document.querySelector('input[name="new_customer_phone"]').removeAttribute('required');
            } else {
                existingForm.classList.add('hidden');
                newForm.classList.remove('hidden');
                document.querySelector('select[name="existing_customer_id"]').removeAttribute('required');
                document.querySelector('input[name="new_customer_name"]').setAttribute('required', 'required');
                document.querySelector('input[name="new_customer_phone"]').setAttribute('required', 'required');
            }
        }

        function addItem() {
            const container = document.getElementById('items_container');
            const template = document.getElementById('item_template');

            // Clone
            const clone = template.content.cloneNode(true);
            const row = clone.querySelector('.item-row'); // Now it's a div, not a tr
            
            container.appendChild(row);
            document.getElementById('empty_state').classList.add('hidden');

            // Init Tom Select
            const newSelect = row.querySelector('.product-select');
            new TomSelect(newSelect, {
                create: false,
                dropdownParent: 'body',
                sortField: {
                    field: "text",
                    direction: "asc"
                },
                placeholder: "Cari produk...",
                onChange: function (value) {
                    updateRow(newSelect);
                }
            });

            recalcTotal();
        }

        function removeRow(btn) {
            const row = btn.closest('.item-row'); // Updated selector
            // Destroy Tom Select
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
            let row = element.closest('.item-row'); // Updated selector
            let select = row.querySelector('select');
            const qtyInput = row.querySelector('input[type="number"]');

            const selectedOpt = select.options[select.selectedIndex];

            if (!selectedOpt) return;

            const price = parseFloat(selectedOpt.getAttribute('data-price')) || 0;
            const qty = parseFloat(qtyInput.value) || 0;

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

            recalcTotal();
        }

        function recalcTotal() {
            let total = 0;
            let count = 0;

            document.querySelectorAll('.subtotal-display').forEach(el => {
                total += parseFloat(el.getAttribute('data-val')) || 0;
                count++;
            });

            const formatter = new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            });

            document.getElementById('summary_total').innerText = formatter.format(total);
            document.getElementById('summary_count').innerText = count;
        }

        // Init
        toggleCustomerType();
        initCustomerSelect();
        addItem(); 
    </script>
</body>

</html>