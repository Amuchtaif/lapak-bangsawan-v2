<?php

use Phinx\Seed\AbstractSeed;

class ExistingDataSeeder extends AbstractSeed
{
    public function run(): void
    {
        // Data untuk tabel: users
        $data = [
  0 => 
  [
    'id' => 1,
    'username' => 'admin',
    'full_name' => 'Admin User',
    'password' => '$2y$10$ZEY.Svi7oSHgXTPAXbwknea05SAwb34I5K9YBjrZET1vmBnOF7zmK',
    'email' => 'admin@lapakbangsawan.com',
    'created_at' => '2025-12-26 22:07:11',
  ],
];
        $this->table('users')->insert($data)->save();

        // Data untuk tabel: site_settings
        $data = [
  0 => 
  [
    'id' => 1,
    'setting_key' => 'hero_title',
    'setting_value' => 'Segar Hari ini, Dikirim Hari ini',
    'setting_type' => 'text',
  ],
  1 => 
  [
    'id' => 2,
    'setting_key' => 'hero_description',
    'setting_value' => 'Ayam, ikan, dan seafood premium langsung dari sumber terpercaya, siap melengkapi hidangan lezat keluarga Anda setiap hari.',
    'setting_type' => 'textarea',
  ],
  2 => 
  [
    'id' => 3,
    'setting_key' => 'hero_image',
    'setting_value' => 'assets/images/hero.jpg',
    'setting_type' => 'image',
  ],
  3 => 
  [
    'id' => 4,
    'setting_key' => 'contact_wa',
    'setting_value' => '62859110022099',
    'setting_type' => 'number',
  ],
  4 => 
  [
    'id' => 5,
    'setting_key' => 'contact_email',
    'setting_value' => 'lapakbangsawan@gmail.com',
    'setting_type' => 'text',
  ],
  5 => 
  [
    'id' => 6,
    'setting_key' => 'contact_address',
    'setting_value' => 'Jl. Wanagati, Karyamulya, Kesambi, Kota Cirebon, Jawa Barat',
    'setting_type' => 'textarea',
  ],
  6 => 
  [
    'id' => 7,
    'setting_key' => 'social_instagram',
    'setting_value' => 'https://instagram.com/lapakbangsawan',
    'setting_type' => 'text',
  ],
  7 => 
  [
    'id' => 8,
    'setting_key' => 'feature_title',
    'setting_value' => 'Mengapa Memilih Lapak Bangsawan?',
    'setting_type' => 'text',
  ],
  8 => 
  [
    'id' => 9,
    'setting_key' => 'feature_desc',
    'setting_value' => 'Kami memprioritaskan kebersihan dan kecepatan untuk memastikan Anda mendapatkan kualitas terbaik.',
    'setting_type' => 'textarea',
  ],
  9 => 
  [
    'id' => 10,
    'setting_key' => 'feature_1_title',
    'setting_value' => 'Pengiriman Hari Berikutnya',
    'setting_type' => 'text',
  ],
  10 => 
  [
    'id' => 11,
    'setting_key' => 'feature_1_desc',
    'setting_value' => 'Pesan sebelum pukul 20.00 dan terima produk segar Anda dalam waktu 24 jam, dijamin.',
    'setting_type' => 'textarea',
  ],
  11 => 
  [
    'id' => 12,
    'setting_key' => 'feature_1_icon',
    'setting_value' => 'local_shipping',
    'setting_type' => 'text',
  ],
  12 => 
  [
    'id' => 13,
    'setting_key' => 'feature_2_title',
    'setting_value' => 'Jaminan Dingin',
    'setting_type' => 'text',
  ],
  13 => 
  [
    'id' => 14,
    'setting_key' => 'feature_2_desc',
    'setting_value' => 'Pesanan Anda dijaga pada suhu dingin optimal dari gudang kami hingga ke pintu Anda.',
    'setting_type' => 'textarea',
  ],
  14 => 
  [
    'id' => 15,
    'setting_key' => 'feature_2_icon',
    'setting_value' => 'ac_unit',
    'setting_type' => 'text',
  ],
  15 => 
  [
    'id' => 16,
    'setting_key' => 'feature_3_title',
    'setting_value' => 'Tersertifikasi Halal',
    'setting_type' => 'text',
  ],
  16 => 
  [
    'id' => 17,
    'setting_key' => 'feature_3_desc',
    'setting_value' => 'Sumber dan pemrosesan tersertifikasi Halal 100% untuk ketenangan pikiran Anda.',
    'setting_type' => 'textarea',
  ],
  17 => 
  [
    'id' => 18,
    'setting_key' => 'feature_3_icon',
    'setting_value' => 'verified_user',
    'setting_type' => 'text',
  ],
  18 => 
  [
    'id' => 19,
    'setting_key' => 'cat_title',
    'setting_value' => 'Kategori Kami',
    'setting_type' => 'text',
  ],
  19 => 
  [
    'id' => 20,
    'setting_key' => 'cat_1_name',
    'setting_value' => 'Ayam',
    'setting_type' => 'text',
  ],
  20 => 
  [
    'id' => 21,
    'setting_key' => 'cat_1_img',
    'setting_value' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuBT7WB-3DzcAiZ0S54FmSH2rr8DFD-p_ccGKfRVR27S4M8RCSWTbsyLWm8fFVCSmHmE4GmMa2Dz4_9891pap4o3wWSzK0Gbu86_S7WMdQjyuGzLoDocPqEptH6i3GjYAB0s9H2Qy50xU14wRFIp7jMxTMCImgf7SSI9A296eAqRDYRnuLe91DMHroSswdzptypuq-SfcD_rYo1UWf_DN8B9ZukZvvIvW_udDIB_5GsdwQU6wLebT6EePNUmYG_BvQF8uOqQwslxHAg',
    'setting_type' => 'image',
  ],
  21 => 
  [
    'id' => 22,
    'setting_key' => 'cat_2_name',
    'setting_value' => 'Ikan Segar',
    'setting_type' => 'text',
  ],
  22 => 
  [
    'id' => 23,
    'setting_key' => 'cat_2_img',
    'setting_value' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuAAnUo5NQYt_ULyTdKlhOMf5E__z1f6Y2EXg5_YUGSL1-4aiYyV17sr2jtsmH3xu7D9IAg71TOgSaLNM9UjH2lBRPlFNZOa3uR7t8ySB0sM764FMWu4j9NAf4NWw4nF8LGxV699IzkqfpU3N-TCHEhge24Y4XOaYlJn4g_mZl8QxRjaV1bh4rVxWyZHw9hDl2be1cts6MnvKPz-veemNVTrsq-2jn5GRMjg19OwKr08rgZJbA90DpWKa-MYwcCHqAtYzA6eaCJWDGE',
    'setting_type' => 'image',
  ],
  23 => 
  [
    'id' => 24,
    'setting_key' => 'cat_3_name',
    'setting_value' => 'Makanan Beku',
    'setting_type' => 'text',
  ],
  24 => 
  [
    'id' => 25,
    'setting_key' => 'cat_3_img',
    'setting_value' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuBGI9Ig5_vO-qBUVf9ycfwmKJ61Q7lN5GT_uxpEH25-ohX3srnpTgGR7jeLpyVfUDElGIZ2fJ9fyZI-uJkOI-nWtRG58ShQnl6KY9RmVgwrQTRIJO-fLBxJi2sS2vhwqfMf4uObH_oCRhOPNLOt7VqzqCWasxYSZkMPtJfuLdAcroHuFKqA48qRkYUDs6cqt_nEVebqCbrGnnMeYbzqT6O8uSCQh-ihISEL6odtIDPAHJm_8g-AEJOVh-Wu-9C2jJ9dhsJSv3XM01k',
    'setting_type' => 'image',
  ],
  25 => 
  [
    'id' => 26,
    'setting_key' => 'cat_4_name',
    'setting_value' => 'Makanan Laut',
    'setting_type' => 'text',
  ],
  26 => 
  [
    'id' => 27,
    'setting_key' => 'cat_4_img',
    'setting_value' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuDmDUw7hqwFigqqquMPj9kID8_W09lt_EYvnEntQiV2QGWxJ0f11ZqcInyCxJY0d6-2JeB4OxEzc-afDczrCQm_NW449tU5IwErl3LS4LBMMwXdriWWg6RqQPyIIc_cqTv_RiuwXqPGATKWdjfHZlMdMJd_KsepTLZV2EjPP0okN4DIzbNZXquMAwTUw45E8HRrdI0Qo7LqXU4-14ERgpgZWV2Rmpd-7Lh6gPjOrTtcAArlW9qmAnMz80TjI-uoQgbkfunfCscl5l4',
    'setting_type' => 'image',
  ],
  27 => 
  [
    'id' => 28,
    'setting_key' => 'prod_title',
    'setting_value' => 'Produk Populer',
    'setting_type' => 'text',
  ],
  28 => 
  [
    'id' => 29,
    'setting_key' => 'prod_1_name',
    'setting_value' => 'Daging Ayam Segar',
    'setting_type' => 'text',
  ],
  29 => 
  [
    'id' => 30,
    'setting_key' => 'prod_1_desc',
    'setting_value' => 'Fresh Ayam',
    'setting_type' => 'text',
  ],
  30 => 
  [
    'id' => 31,
    'setting_key' => 'prod_1_img',
    'setting_value' => 'assets/images/daging ayam.jpg',
    'setting_type' => 'image',
  ],
  31 => 
  [
    'id' => 32,
    'setting_key' => 'prod_2_name',
    'setting_value' => 'Kepala Ayam',
    'setting_type' => 'text',
  ],
  32 => 
  [
    'id' => 33,
    'setting_key' => 'prod_2_desc',
    'setting_value' => 'Potongan Kepala Ayam',
    'setting_type' => 'text',
  ],
  33 => 
  [
    'id' => 34,
    'setting_key' => 'prod_2_img',
    'setting_value' => 'assets/images/pala-ayam.jpg',
    'setting_type' => 'image',
  ],
  34 => 
  [
    'id' => 35,
    'setting_key' => 'prod_3_name',
    'setting_value' => 'Ikan Tuna',
    'setting_type' => 'text',
  ],
  35 => 
  [
    'id' => 36,
    'setting_key' => 'prod_3_desc',
    'setting_value' => 'Fresh Tuna',
    'setting_type' => 'text',
  ],
  36 => 
  [
    'id' => 37,
    'setting_key' => 'prod_3_img',
    'setting_value' => 'assets/images/tuna.jpg',
    'setting_type' => 'image',
  ],
  37 => 
  [
    'id' => 38,
    'setting_key' => 'prod_4_name',
    'setting_value' => 'Ikan Seafood Premium',
    'setting_type' => 'text',
  ],
  38 => 
  [
    'id' => 39,
    'setting_key' => 'prod_4_desc',
    'setting_value' => 'Premium Kualitas Ikan',
    'setting_type' => 'text',
  ],
  39 => 
  [
    'id' => 40,
    'setting_key' => 'prod_4_img',
    'setting_value' => 'assets/images/seafood.jpg',
    'setting_type' => 'image',
  ],
];
        $this->table('site_settings')->insert($data)->save();

        // Data untuk tabel: categories
        $data = [
  0 => 
  [
    'id' => 1,
    'name' => 'Daging Ayam',
    'slug' => 'daging-ayam',
    'created_at' => '2025-12-26 22:39:01',
  ],
  1 => 
  [
    'id' => 2,
    'name' => 'Ikan Segar',
    'slug' => 'ikan-segar',
    'created_at' => '2025-12-26 22:39:01',
  ],
  2 => 
  [
    'id' => 3,
    'name' => 'Frozen Food',
    'slug' => 'frozen-food',
    'created_at' => '2025-12-26 22:39:01',
  ],
  3 => 
  [
    'id' => 4,
    'name' => 'Seafood',
    'slug' => 'seafood',
    'created_at' => '2025-12-26 22:39:01',
  ],
  4 => 
  [
    'id' => 14,
    'name' => 'Produk Jadi',
    'slug' => 'produk-jadi',
    'created_at' => '2025-12-31 16:45:29',
  ],
];
        $this->table('categories')->insert($data)->save();

        // Data untuk tabel: products
        $data = [
  0 => 
  [
    'id' => 9,
    'category_id' => 3,
    'short_code' => NULL,
    'name' => 'Dimsum Gyoza',
    'slug' => 'dimsum-gyoza',
    'description' => 'GYOZA
DIMSUM DAGING AYAM
PRIMA',
    'price' => '23000.00',
    'buy_price' => '0.00',
    'stock' => '10.00',
    'unit' => 'pcs',
    'image' => 'assets/uploads/products/1766823743_product (gyoza].jpg',
    'created_at' => '2025-12-27 01:22:23',
    'weight' => 1000,
  ],
  1 => 
  [
    'id' => 10,
    'category_id' => 3,
    'short_code' => NULL,
    'name' => 'Siomay Ayam',
    'slug' => 'siomay-ayam',
    'description' => 'Siomay Ayam dalam bentuk Frozen Food',
    'price' => '28000.00',
    'buy_price' => '0.00',
    'stock' => '10.00',
    'unit' => 'pcs',
    'image' => 'assets/uploads/products/1766826521_product (siomay-ayam].jpg',
    'created_at' => '2025-12-27 02:03:18',
    'weight' => 1000,
  ],
  2 => 
  [
    'id' => 11,
    'category_id' => 3,
    'short_code' => NULL,
    'name' => 'Siomay Ikan',
    'slug' => 'siomay-ikan',
    'description' => 'Siomay ikan dalam bentuk frozen food',
    'price' => '25000.00',
    'buy_price' => '0.00',
    'stock' => '10.00',
    'unit' => 'pcs',
    'image' => 'assets/uploads/products/1766831390_product (siomay-ikan].jpg',
    'created_at' => '2025-12-27 03:29:50',
    'weight' => 1000,
  ],
  3 => 
  [
    'id' => 12,
    'category_id' => 3,
    'short_code' => NULL,
    'name' => 'Chicken Eggroll',
    'slug' => 'chicken-eggroll',
    'description' => 'Eggroll dari chicken ayam dalam bentuk kemasan frozen',
    'price' => '22000.00',
    'buy_price' => '0.00',
    'stock' => '10.00',
    'unit' => 'pcs',
    'image' => 'assets/uploads/products/1766831452_WhatsApp Image 2025-12-19 at 14.16.00.jpeg',
    'created_at' => '2025-12-27 03:30:52',
    'weight' => 1000,
  ],
  4 => 
  [
    'id' => 18,
    'category_id' => 3,
    'short_code' => NULL,
    'name' => 'Tahu Bakso Ikan',
    'slug' => 'tahu-bakso-ikan',
    'description' => 'tahu bakso ikan fresh',
    'price' => '28000.00',
    'buy_price' => '0.00',
    'stock' => '10.00',
    'unit' => 'pcs',
    'image' => 'assets/uploads/products/1767172811_WhatsApp Image 2025-12-29 at 13.24.02.jpeg',
    'created_at' => '2025-12-31 02:20:11',
    'weight' => 1000,
  ],
  5 => 
  [
    'id' => 19,
    'category_id' => 14,
    'short_code' => NULL,
    'name' => 'Krupuk Rajungan',
    'slug' => 'krupuk-rajungan',
    'description' => 'krupuk rajungan asli',
    'price' => '15000.00',
    'buy_price' => '0.00',
    'stock' => '10.00',
    'unit' => 'pcs',
    'image' => 'assets/uploads/products/1767174378_Gemini_Generated_Image_nmnps0nmnps0nmnp.png',
    'created_at' => '2025-12-31 02:46:18',
    'weight' => 1000,
  ],
  6 => 
  [
    'id' => 20,
    'category_id' => 4,
    'short_code' => NULL,
    'name' => 'Udang',
    'slug' => 'udang',
    'description' => 'udang segar ukuran sedang',
    'price' => '65000.00',
    'buy_price' => '0.00',
    'stock' => '7.00',
    'unit' => 'kg',
    'image' => 'assets/uploads/products/1767174475_Gemini_Generated_Image_2svn0p2svn0p2svn.png',
    'created_at' => '2025-12-31 02:47:55',
    'weight' => 1000,
  ],
  7 => 
  [
    'id' => 21,
    'category_id' => 4,
    'short_code' => NULL,
    'name' => 'Ikan Tuna',
    'slug' => 'ikan-tuna',
    'description' => 'ikan tuna segar',
    'price' => '35000.00',
    'buy_price' => '0.00',
    'stock' => '4.00',
    'unit' => 'kg',
    'image' => 'assets/uploads/products/1767192438_(FILEminimizer] 2.png',
    'created_at' => '2025-12-31 07:47:18',
    'weight' => 1000,
  ],
  8 => 
  [
    'id' => 22,
    'category_id' => 4,
    'short_code' => NULL,
    'name' => 'Ikan Gayaman',
    'slug' => 'ikan-gayaman',
    'description' => 'ikan gayaman segar',
    'price' => '28000.00',
    'buy_price' => '0.00',
    'stock' => '0.00',
    'unit' => 'kg',
    'image' => 'assets/uploads/products/1767192703_(FILEminimizer] ikan gayemann.jpg',
    'created_at' => '2025-12-31 07:51:43',
    'weight' => 1000,
  ],
  9 => 
  [
    'id' => 23,
    'category_id' => 4,
    'short_code' => NULL,
    'name' => 'Ikan Teros',
    'slug' => 'ikan-teros',
    'description' => 'ikan teros segar',
    'price' => '25000.00',
    'buy_price' => '0.00',
    'stock' => '0.00',
    'unit' => 'kg',
    'image' => 'assets/uploads/products/1767192788_(FILEminimizer] ikan teros.jpg',
    'created_at' => '2025-12-31 07:53:08',
    'weight' => 1000,
  ],
  10 => 
  [
    'id' => 24,
    'category_id' => 2,
    'short_code' => '04',
    'name' => 'Ikan Lele',
    'slug' => 'ikan-lele',
    'description' => 'ikan lele segar',
    'price' => '25000.00',
    'buy_price' => '0.00',
    'stock' => '38.00',
    'unit' => 'kg',
    'image' => 'assets/uploads/products/1767192853_(FILEminimizer] 3.png',
    'created_at' => '2025-12-31 07:54:13',
    'weight' => 1000,
  ],
  11 => 
  [
    'id' => 25,
    'category_id' => 2,
    'short_code' => NULL,
    'name' => 'Ikan Gurame',
    'slug' => 'ikan-gurame',
    'description' => 'ikan gurame segar Frozen Fresh , Timbang hidup dengan proses pembersihan dari sisik dan kotoran serta sayatan samping badan untuk resapan bumbu, dikemas dengan plastik tebal/ ekor',
    'price' => '55000.00',
    'buy_price' => '0.00',
    'stock' => '0.00',
    'unit' => 'kg',
    'image' => 'assets/uploads/products/1767415417_1767277115_Gurame.jpeg',
    'created_at' => '2025-12-31 07:55:01',
    'weight' => 1000,
  ],
  12 => 
  [
    'id' => 26,
    'category_id' => 1,
    'short_code' => '03',
    'name' => 'Daging Ayam Dada',
    'slug' => 'daging-ayam-dada',
    'description' => 'Daging ayam Segar pilihan yang diproses dengan standar kebersihan tinggi & penuh keberkahan, sehingga kualitas tetap terjaga sampai ke dapur Anda.

? Proses Penanganan Standar LaBa:
1?? Proses penyembelihan secara syar\'i InsyaAllah terjamin kehalalannya
2?? Pembersihan bulu dan jeroan
3?? Pemotongan masing-masing bagian daging ayam seperti paha, dada dan sayap
4?? Pencucian menggunakan air mengalir
5?? Packing plastik tebal food grade
6?? Penyimpanan freezer untuk menjaga kualitas daging

? Detail Produk:
? Harga: Rp 35.000/kg
?? Ukuran potongan : 8-12 Potong / kg
? Kondisi: Fresh kemasan, siap olah',
    'price' => '32000.00',
    'buy_price' => '0.00',
    'stock' => '19.00',
    'unit' => 'kg',
    'image' => 'assets/uploads/products/1767194439_(FILEminimizer] 1.png',
    'created_at' => '2025-12-31 08:20:39',
    'weight' => 1000,
  ],
  13 => 
  [
    'id' => 27,
    'category_id' => 1,
    'short_code' => NULL,
    'name' => 'Ati Ampela Ayam',
    'slug' => 'ati-ampela-ayam',
    'description' => 'ati ampela ayam yg fresh',
    'price' => '24000.00',
    'buy_price' => '0.00',
    'stock' => '9.00',
    'unit' => 'kg',
    'image' => 'assets/uploads/products/1767194480_(FILEminimizer] ati ampela ayam.jpg',
    'created_at' => '2025-12-31 08:21:20',
    'weight' => 1000,
  ],
  14 => 
  [
    'id' => 28,
    'category_id' => 1,
    'short_code' => NULL,
    'name' => 'Kepala Ayam',
    'slug' => 'kepala-ayam',
    'description' => 'kepala ayam potongan',
    'price' => '14000.00',
    'buy_price' => '0.00',
    'stock' => '10.00',
    'unit' => 'kg',
    'image' => 'assets/uploads/products/1767194572_(FILEminimizer] kepala ayam.jpg',
    'created_at' => '2025-12-31 08:22:52',
    'weight' => 1000,
  ],
  15 => 
  [
    'id' => 29,
    'category_id' => 1,
    'short_code' => NULL,
    'name' => 'Ceker',
    'slug' => 'ceker',
    'description' => 'ceker ayam',
    'price' => '24000.00',
    'buy_price' => '0.00',
    'stock' => '10.00',
    'unit' => 'kg',
    'image' => 'assets/uploads/products/1767194632_(FILEminimizer] ceker ayam.jpg',
    'created_at' => '2025-12-31 08:23:52',
    'weight' => 1000,
  ],
  16 => 
  [
    'id' => 30,
    'category_id' => 1,
    'short_code' => NULL,
    'name' => 'Ekor',
    'slug' => 'ekor',
    'description' => 'ekor ayam',
    'price' => '24000.00',
    'buy_price' => '0.00',
    'stock' => '8.00',
    'unit' => 'kg',
    'image' => 'assets/uploads/products/1767194671_(FILEminimizer] ekor ayam.jpg',
    'created_at' => '2025-12-31 08:24:31',
    'weight' => 1000,
  ],
  17 => 
  [
    'id' => 31,
    'category_id' => 1,
    'short_code' => NULL,
    'name' => 'Fillet Dada Kulit',
    'slug' => 'fillet-dada-kulit',
    'description' => 'Dada ayam dengan potongan fillet',
    'price' => '43000.00',
    'buy_price' => '0.00',
    'stock' => '15.00',
    'unit' => 'kg',
    'image' => 'assets/uploads/products/1767195400_(FILEminimizer] Gemini_Generated_Image_2sm7xu2sm7xu2sm7.jpg',
    'created_at' => '2025-12-31 08:36:40',
    'weight' => 1000,
  ],
  18 => 
  [
    'id' => 33,
    'category_id' => 2,
    'short_code' => NULL,
    'name' => 'Ikan Nila',
    'slug' => 'ikan-nila',
    'description' => 'Ikan Nila Segar pilihan yang diproses dengan standar kebersihan tinggi & penuh keberkahan, sehingga kualitas tetap terjaga sampai ke dapur Anda.

Proses Penanganan Standar LaBa:
1 Ditimbang terlebih dahulu sesuai pesanan
2 Diawali dengan bacaan Basmalah ?
3 Pembersihan kotoran & insang hingga bersih
4 Penyayatan badan samping agar bumbu meresap
5 Pencucian menggunakan air mengalir
6 Packing plastik tebal food grade
7 Penyimpanan freezer untuk menjaga kualitas

Detail Produk:
Harga: Rp 35.000/kg
Ukuran: 5ï¿½7 ekor / kg
Kondisi: Fresh kemasan, siap olah',
    'price' => '33000.00',
    'buy_price' => '0.00',
    'stock' => '18.00',
    'unit' => 'kg',
    'image' => 'assets/uploads/products/1767195473_(FILEminimizer] Gemini_Generated_Image_optos6optos6opto.jpg',
    'created_at' => '2025-12-31 08:37:53',
    'weight' => 1000,
  ],
  19 => 
  [
    'id' => 34,
    'category_id' => 1,
    'short_code' => NULL,
    'name' => 'Fillet Paha Kulit',
    'slug' => 'fillet-paha-kulit',
    'description' => 'paha kulit ayam dengan potongan fillet',
    'price' => '44000.00',
    'buy_price' => '0.00',
    'stock' => '8.00',
    'unit' => 'kg',
    'image' => 'assets/uploads/products/1767415403_1767195439_(FILEminimizer] Gemini_Generated_Image_2sm7xu2sm7xu2sm7.jpg',
    'created_at' => '2025-12-31 08:38:33',
    'weight' => 1000,
  ],
  20 => 
  [
    'id' => 36,
    'category_id' => 4,
    'short_code' => NULL,
    'name' => 'Ikan Ekor Kuning',
    'slug' => 'ikan-ekor-kuning',
    'description' => 'Ikan Ekor Kuning merupakan ikan seafood yang kami olah menjadi produk Frozen Fresh ',
    'price' => '28000.00',
    'buy_price' => '0.00',
    'stock' => '0.00',
    'unit' => 'kg',
    'image' => 'assets/uploads/products/1767416183_1767269667_Ekor Kuning.jpeg',
    'created_at' => '2026-01-02 21:56:23',
    'weight' => 1000,
  ],
  21 => 
  [
    'id' => 37,
    'category_id' => 3,
    'short_code' => NULL,
    'name' => 'Otak-otak ikan',
    'slug' => 'otak-otak-ikan',
    'description' => 'cemilan bergizi otak-otak dari bahan ikan fresh pilihan',
    'price' => '23000.00',
    'buy_price' => '0.00',
    'stock' => '8.00',
    'unit' => 'pcs',
    'image' => 'assets/uploads/products/1769395477_1768170138_WhatsApp Image 2025-12-29 at 13.24.00.jpeg',
    'created_at' => '2026-01-11 22:22:18',
    'weight' => 1000,
  ],
  22 => 
  [
    'id' => 38,
    'category_id' => 1,
    'short_code' => '02',
    'name' => 'Daging Ayam Paha',
    'slug' => 'daging-ayam-paha',
    'description' => 'Daging Ayam Paha Fresh dan tersedia juga kemasan dingin',
    'price' => '32000.00',
    'buy_price' => '0.00',
    'stock' => '8.00',
    'unit' => 'kg',
    'image' => 'assets/uploads/products/1769395470_1769122818_IMG_20260121_062452.jpg',
    'created_at' => '2026-01-22 23:00:18',
    'weight' => 1000,
  ],
  23 => 
  [
    'id' => 40,
    'category_id' => 1,
    'short_code' => '01',
    'name' => 'Daging Ayam Sayap',
    'slug' => 'daging-ayam-sayap',
    'description' => 'Daging Ayam Bagian Sayap Fresh dan Kemasan Dingin.',
    'price' => '32000.00',
    'buy_price' => '0.00',
    'stock' => '9.00',
    'unit' => 'kg',
    'image' => 'assets/uploads/products/1769395276_1769123084_IMG_20251120_065204.jpg',
    'created_at' => '2026-01-22 23:04:44',
    'weight' => 1000,
  ],
];
        $this->table('products')->insert($data)->save();

    }
}
