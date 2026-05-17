# Analisis Fitur Projek "Lapak Bangsawan"

Berdasarkan analisis struktur direktori dan file dalam projek ini, aplikasi "Lapak Bangsawan" merupakan sebuah platform e-commerce (toko online) lengkap yang mencakup sisi antarmuka pelanggan (Public/Front-end) dan panel manajemen lengkap untuk pengelola toko (Admin/Back-end). 

Berikut adalah rincian fitur-fitur yang ada di dalam projek ini:

## 1. Fitur Publik (Front-End / Customer Facing)
Berada di dalam direktori `public/`, fitur ini berinteraksi langsung dengan pelanggan atau pengunjung situs.

*   **Halaman Utama & Informasi:** 
    *   Halaman Landing Page (`home.php`) untuk menyambut pengunjung.
    *   Halaman Tentang Kami (`about.php`).
*   **Katalog & Detail Produk:** 
    *   Halaman Pasar/Katalog Produk (`market.php`) untuk melihat semua produk yang ditawarkan.
    *   Halaman Detail Produk (`product-detail.php`) untuk melihat spesifikasi produk.
*   **Sistem Keranjang & Checkout (Shopping Cart & Checkout):**
    *   Manajemen keranjang belanja (`cart.php`, `cart_helper.php`).
    *   Proses Checkout (`checkout.php`).
    *   API Kalkulasi Total Belanja (`api_calculate_total.php`).
*   **Simulasi & Kalkulasi Pengiriman (Shipping):**
    *   Simulasi ongkos kirim (`shipping_simulation.php`).
    *   Mendukung layanan pengiriman lokal berbasis jarak (`LocalDeliveryService.php`, `DistanceCalculator.php`).
*   **Pesanan & Pembayaran:**
    *   Pemrosesan pesanan (`save_order.php`).
    *   Halaman Pembayaran (`payment.php`).
    *   Status Pesanan Sukses (`order-success.php`, `success.php`, `thank_you.php`).
    *   Pelacakan Pesanan / Track Order (`track.php`).
*   **Komunikasi:**
    *   Pengunjung dapat mengirim pesan langsung (`save_message.php`).

---

## 2. Fitur Admin (Back-End / Control Panel)
Berada di dalam direktori `admin/`, fitur ini digunakan oleh pemilik toko atau staf untuk mengelola seluruh aspek bisnis toko online.

*   **Autentikasi & Keamanan:**
    *   Sistem Login dan Logout (`login.php`, `logout.php`).
    *   Manajemen sesi pengguna (`auth_session.php`).
    *   Catatan Aktivitas / Audit Trail (`activity_logs.php`) untuk memantau aktivitas admin.
*   **Dashboard & Pelaporan (Analytics & Reports):**
    *   Dashboard utama (`dashboard.php`) untuk ringkasan operasional.
    *   Fitur Ekspor Dashboard (`export_dashboard.php`).
    *   Laporan Harian (`daily_report.php`).
    *   Laporan Stok Produk (`report_stock.php`).
*   **Manajemen Pemesanan (Order Management):**
    *   Kelola semua pesanan pelanggan (`orders.php`).
    *   Transaksi Manual / Kasir (`manual_transaction.php`) untuk menangani pembelian offline.
    *   Cetak Nota / Struk Pembelian (`print_nota.php`).
*   **Manajemen Produk & Inventaris:**
    *   Kelola Produk (`products.php`).
    *   Kelola Kategori Produk (`categories.php`).
    *   Manajemen Inventaris secara menyeluruh (Direktori `inventory/`).
*   **Manajemen Keuangan / Arus Kas:**
    *   Pencatatan dan pengelolaan Pengeluaran (`expenses.php`).
*   **Manajemen Pelanggan & Mitra:**
    *   Data Pelanggan (`customers.php`).
    *   Data Mitra / Partner (`partners.php`).
    *   Aturan Harga Grosir / Wholesale (`wholesale_rules.php`) untuk pembelian partai besar atau kemitraan.
*   **Marketing & Target:**
    *   Input dan monitoring target penjualan / kinerja (`form_input_target.php`).
*   **Pengaturan Website (Settings):**
    *   Pengaturan Umum Aplikasi (`settings.php`).
    *   Pengaturan Landing Page (Direktori `landingpage/`).
    *   Pengaturan Pengiriman (`shipping_settings.php`) dan integrasi API Pengiriman (Direktori `shipping_api/`).
*   **Pesan Masuk (Inbox):**
    *   Membaca dan mengelola pesan dari pengunjung (`messages.php`).

---

## 3. Fitur Sistem & Developer (DevOps / Tools)
Fitur-fitur untuk mempermudah pengembangan dan pemeliharaan aplikasi.

*   **Database Migration & Seeding:** 
    *   Projek ini menggunakan **Phinx** (`phinx.php`, folder `db/migrations`, `MIGRATION_GUIDE.md`) untuk mengelola versi dan sinkronisasi struktur database antara environment pengembangan (contohnya Windows) dan server (Linux).
*   **Optimasi:**
    *   Terdapat skrip untuk kompresi aset/file (`compress_assets.php`).
*   **Modularitas Pengiriman Tambahan:**
    *   Penggunaan API biteship, dilihat dari file `add_biteship_order_id.sql`.

Ringkasnya, Lapak Bangsawan bukan hanya toko online biasa, tapi sudah mencakup **Point of Sale (POS) sederhana** (untuk transaksi manual), **Sistem Kemitraan (Grosir/Partner)**, manajemen operasional (Trek Pengeluaran & Audit Log), serta penghitungan ongkos kirim dinamis (termasuk kurir lokal).
