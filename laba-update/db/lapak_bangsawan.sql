-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 23, 2026 at 09:57 AM
-- Server version: 10.4.27-MariaDB
-- PHP Version: 8.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `lapak_bangsawan`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `created_at`) VALUES
(1, 'Daging Ayam', 'daging-ayam', '2025-12-26 15:39:01'),
(2, 'Ikan Segar', 'ikan-segar', '2025-12-26 15:39:01'),
(3, 'Frozen Food', 'frozen-food', '2025-12-26 15:39:01'),
(4, 'Seafood', 'seafood', '2025-12-26 15:39:01'),
(14, 'Produk Jadi', 'produk-jadi', '2025-12-31 09:45:29');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) NOT NULL,
  `address` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `name`, `email`, `phone`, `address`, `created_at`) VALUES
(3, 'Rizky ahadillah', 'rzkyyyaap@gmail.com', '082216925947', 'Ambil Di tempat', '2026-01-03 01:29:40'),
(7, 'YOGAN MOHAMMAD AHAD ', 'arisyogan@gmail.com', '089512904218', 'Markas pondok as Sunnah ', '2026-01-12 14:31:25'),
(8, 'Iza Tumila', 'izatumila249@gmail.com', '087834273892', 'Pesona Kahuripan blok B no 2 Jl. Surapandan, Argasunya, Kec. Harjamukti, Kota Cirebon, Jawa Barat 14000, Indonesia', '2026-01-13 02:25:17'),
(9, 'Abdurrahman', '', '085814583225', 'Tpa anak sholeh', '2026-01-19 02:24:02'),
(10, 'Ita Ummu Umay', '', '089516914897', 'jl.wanagati no 10A RT 05 RW 03 (kontrakan dawet Ireng no 10A3) kelurahan karyamulya kecamatan kesambi kota Cirebon ', '2026-01-21 07:59:28'),
(11, 'Ummu Aliya', 'thegimmes@google.com', '081320142611', 'Gg istiqomah 2 ', '2026-01-21 12:46:07'),
(12, 'Yayat', '', '08994453450', 'Dawet ireng 10A4', '2026-01-21 12:58:54');

-- --------------------------------------------------------

--
-- Table structure for table `daily_sales_targets`
--

CREATE TABLE `daily_sales_targets` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `target_date` date NOT NULL,
  `target_qty_kg` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `daily_sales_targets`
--

INSERT INTO `daily_sales_targets` (`id`, `product_id`, `target_date`, `target_qty_kg`, `created_at`) VALUES
(1, 26, '2026-01-22', '20.00', '2026-01-22 12:55:54'),
(2, 27, '2026-01-22', '10.00', '2026-01-22 12:55:54'),
(3, 28, '2026-01-22', '10.00', '2026-01-22 12:55:54'),
(4, 29, '2026-01-22', '10.00', '2026-01-22 12:55:54'),
(5, 30, '2026-01-22', '9.00', '2026-01-22 12:55:54'),
(6, 31, '2026-01-22', '15.00', '2026-01-22 12:55:54'),
(7, 34, '2026-01-22', '8.00', '2026-01-22 12:55:54'),
(8, 24, '2026-01-22', '49.00', '2026-01-22 12:55:54'),
(9, 25, '2026-01-22', '0.00', '2026-01-22 12:55:54'),
(10, 33, '2026-01-22', '18.00', '2026-01-22 12:55:54'),
(11, 9, '2026-01-22', '10.00', '2026-01-22 12:55:54'),
(12, 10, '2026-01-22', '10.00', '2026-01-22 12:55:54'),
(13, 11, '2026-01-22', '10.00', '2026-01-22 12:55:54'),
(14, 12, '2026-01-22', '10.00', '2026-01-22 12:55:54'),
(15, 18, '2026-01-22', '10.00', '2026-01-22 12:55:54'),
(16, 37, '2026-01-22', '10.00', '2026-01-22 12:55:54'),
(17, 20, '2026-01-22', '10.00', '2026-01-22 12:55:54'),
(18, 21, '2026-01-22', '6.00', '2026-01-22 12:55:54'),
(19, 22, '2026-01-22', '0.00', '2026-01-22 12:55:54'),
(20, 23, '2026-01-22', '0.00', '2026-01-22 12:55:54'),
(21, 36, '2026-01-22', '0.00', '2026-01-22 12:55:54'),
(22, 19, '2026-01-22', '10.00', '2026-01-22 12:55:54');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `name`, `email`, `message`, `is_read`, `created_at`) VALUES
(2, 'Andi Muchtaif', 'amuchtaif@gmail.com', 'tingkatkan segi user experience website', 0, '2026-01-23 03:22:55');

-- --------------------------------------------------------

--
-- Table structure for table `operational_expenses`
--

CREATE TABLE `operational_expenses` (
  `id` int(11) NOT NULL,
  `expense_date` date NOT NULL,
  `category` enum('Pembelian Bahan Baku','Sewa & Utilitas','Gaji Karyawan','Marketing','Perlengkapan','Lainnya') NOT NULL,
  `description` text DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `proof_image` varchar(255) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `operational_expenses`
--

INSERT INTO `operational_expenses` (`id`, `expense_date`, `category`, `description`, `amount`, `proof_image`, `created_by`, `created_at`) VALUES
(1, '2026-01-01', 'Sewa & Utilitas', 'Bayar langganan wifi', '200000.00', '', 1, '2026-01-23 04:12:55');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `customer_phone` varchar(20) NOT NULL,
  `customer_address` text NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `order_notes` text DEFAULT NULL,
  `status` enum('pending','completed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `order_token` varchar(64) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `customer_name`, `customer_phone`, `customer_address`, `total_amount`, `payment_method`, `order_notes`, `status`, `created_at`, `order_token`) VALUES
(1, 'Atar', '082119242560', 'Desa ciperna, blok sikroya, kec talun, (rumah Pak Mansyur)', '122000.00', 'cod', NULL, 'cancelled', '2026-01-03 01:02:44', NULL),
(2, 'Muhamad Tohirudin', '081391916130', 'Jl. Abdullah No 91 Dusun Pahing Rt 010 Rw 003 Desa Cilimus Kecamatan Cilimus\nKab. Kuningan Jawa Barat, Indonesia', '28000.00', 'transfer', NULL, 'completed', '2026-01-03 01:22:34', NULL),
(3, 'Rizky ahadillah', '082216925947', 'Ambil Di tempat', '115500.00', 'cod', NULL, 'completed', '2026-01-03 01:29:40', NULL),
(4, 'YOGAN MOHAMMAD AHAD ', '089512904218', 'Markas pondok as Sunnah ', '75000.00', 'transfer', NULL, 'pending', '2026-01-12 14:31:25', 'f96dd97085d551e1c6450c4d835ab1c3'),
(5, 'YOGAN MOHAMMAD AHAD ', '089512904218', 'Kompleks pondok pesantren as, Sunnah Cirebon ', '140000.00', 'cod', NULL, 'pending', '2026-01-12 14:33:20', '9ea8484aff5f4ad9a728eecbe15ea40a'),
(6, 'Iza Tumila', '087834273892', 'Pesona Kahuripan blok B no 2 Jl. Surapandan, Argasunya, Kec. Harjamukti, Kota Cirebon, Jawa Barat 14000, Indonesia', '55000.00', 'transfer', NULL, 'pending', '2026-01-13 02:25:17', '9116d636f4ce4052df526bec995daccb'),
(7, 'Abdurrahman', '085814583225', 'Tpa anak sholeh', '60000.00', 'cod', NULL, 'pending', '2026-01-19 02:24:02', '61d9697882889533e59feb5d58aea320'),
(8, 'Ita Ummu Umay', '089516914897', 'jl.wanagati no 10A RT 05 RW 03 (kontrakan dawet Ireng no 10A3) kelurahan karyamulya kecamatan kesambi kota Cirebon ', '32000.00', 'cod', NULL, 'pending', '2026-01-21 07:59:28', 'c6e487ad11e4f74d930e8c90bd9ac859'),
(9, 'Ummu Aliya', '081320142611', 'Gg istiqomah 2 ', '96000.00', 'cod', NULL, 'pending', '2026-01-21 12:46:07', 'aa23382aecfd18771659383b284b6300'),
(10, 'Yayat', '08994453450', 'Dawet ireng 10A4', '96000.00', 'cod', NULL, 'pending', '2026-01-21 12:58:54', '481756dc63139eecf7340b445c650014'),
(11, 'Abdurrahman', '085814583225', 'Tpa anak sholeh', '94000.00', 'cod', 'Transaksi Manual (Admin)', 'completed', '2026-01-22 13:47:24', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `weight` decimal(10,2) NOT NULL,
  `price_per_kg` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_name`, `weight`, `price_per_kg`, `subtotal`) VALUES
(10, 1, 'Fillet Dada Kulit', '1.00', '44000.00', '44000.00'),
(11, 1, 'Daging Ayam Fresh', '1.00', '33000.00', '33000.00'),
(12, 1, 'Fillet Paha Kulit', '1.00', '45000.00', '45000.00'),
(13, 2, 'Fillet Dada Kulit', '1.00', '44000.00', '44000.00'),
(14, 2, 'Daging Ayam Fresh', '1.00', '33000.00', '33000.00'),
(15, 2, 'Fillet Paha Kulit', '1.00', '45000.00', '45000.00'),
(16, 3, 'Ikan Ekor Kuning', '1.00', '28000.00', '28000.00'),
(17, 4, 'Daging Ayam Fresh', '3.50', '33000.00', '115500.00'),
(18, 4, 'Fillet Paha Kulit', '1.00', '42000.00', '42000.00'),
(19, 4, 'Ikan Nila', '1.00', '33000.00', '33000.00'),
(20, 5, 'Fillet Paha Kulit', '1.00', '42000.00', '42000.00'),
(21, 5, 'Ikan Nila', '1.00', '33000.00', '33000.00'),
(22, 5, 'Fillet Dada Kulit', '1.00', '41000.00', '41000.00'),
(23, 5, 'Ekor', '1.00', '24000.00', '24000.00'),
(24, 6, 'Daging Ayam Fresh', '1.00', '30000.00', '30000.00'),
(25, 6, 'Ikan Lele', '1.00', '25000.00', '25000.00'),
(26, 7, 'Daging Ayam Fresh', '2.00', '30000.00', '60000.00'),
(27, 8, 'Daging Ayam Fresh', '1.00', '32000.00', '32000.00'),
(28, 9, 'Daging Ayam Fresh', '3.00', '32000.00', '96000.00'),
(29, 10, 'Daging Ayam Fresh', '3.00', '32000.00', '96000.00'),
(30, 11, 'Ikan Lele', '2.00', '25000.00', '50000.00'),
(31, 11, 'Chicken Eggroll', '2.00', '22000.00', '44000.00');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` decimal(10,2) NOT NULL DEFAULT 0.00,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `slug`, `description`, `price`, `stock`, `image`, `created_at`) VALUES
(9, 3, 'Dimsum Gyoza', 'dimsum-gyoza', 'GYOZA\r\nDIMSUM DAGING AYAM\r\nPRIMA', 23000.00, 10.00, 'assets/uploads/products/1766823743_product (gyoza).jpg', '2025-12-27 01:22:23'),
(10, 3, 'Siomay Ayam', 'siomay-ayam', 'Siomay Ayam dalam bentuk Frozen Food', 28000.00, 10.00, 'assets/uploads/products/1766826521_product (siomay-ayam).jpg', '2025-12-27 02:03:18'),
(11, 3, 'Siomay Ikan', 'siomay-ikan', 'Siomay ikan dalam bentuk frozen food', 25000.00, 10.00, 'assets/uploads/products/1766831390_product (siomay-ikan).jpg', '2025-12-27 03:29:50'),
(12, 3, 'Chicken Eggroll', 'chicken-eggroll', 'Eggroll dari chicken ayam dalam bentuk kemasan frozen', 22000.00, 6.00, 'assets/uploads/products/1766831452_WhatsApp Image 2025-12-19 at 14.16.00.jpeg', '2025-12-27 03:30:52'),
(18, 3, 'Tahu Bakso Ikan', 'tahu-bakso-ikan', 'tahu bakso ikan fresh', 28000.00, 10.00, 'assets/uploads/products/1767172811_WhatsApp Image 2025-12-29 at 13.24.02.jpeg', '2025-12-31 02:20:11'),
(19, 14, 'Krupuk Rajungan', 'krupuk-rajungan', 'krupuk rajungan asli', 15000.00, 10.00, 'assets/uploads/products/1767174378_Gemini_Generated_Image_nmnps0nmnps0nmnp.png', '2025-12-31 02:46:18'),
(20, 4, 'Udang', 'udang', 'udang segar ukuran sedang', 65000.00, 10.00, 'assets/uploads/products/1767174475_Gemini_Generated_Image_2svn0p2svn0p2svn.png', '2025-12-31 02:47:55'),
(21, 4, 'Ikan Tuna', 'ikan-tuna', 'ikan tuna segar', 35000.00, 6.00, 'assets/uploads/products/1767192438_(FILEminimizer) 2.png', '2025-12-31 07:47:18'),
(22, 4, 'Ikan Gayaman', 'ikan-gayaman', 'ikan gayaman segar', 28000.00, 0.00, 'assets/uploads/products/1767192703_(FILEminimizer) ikan gayemann.jpg', '2025-12-31 07:51:43'),
(23, 4, 'Ikan Teros', 'ikan-teros', 'ikan teros segar', 25000.00, 0.00, 'assets/uploads/products/1767192788_(FILEminimizer) ikan teros.jpg', '2025-12-31 07:53:08'),
(24, 2, 'Ikan Lele', 'ikan-lele', 'ikan lele segar', 25000.00, 45.00, 'assets/uploads/products/1767192853_(FILEminimizer) 3.png', '2025-12-31 07:54:13'),
(25, 2, 'Ikan Gurame', 'ikan-gurame', 'ikan gurame segar Frozen Fresh , Timbang hidup dengan proses pembersihan dari sisik dan kotoran serta sayatan samping badan untuk resapan bumbu, dikemas dengan plastik tebal/ ekor', 55000.00, 0.00, 'assets/uploads/products/1767415417_1767277115_Gurame.jpeg', '2025-12-31 07:55:01'),
(26, 1, 'Daging Ayam Fresh', 'daging-ayam-fresh', 'Daging ayam Segar pilihan yang diproses dengan standar kebersihan tinggi & penuh keberkahan, sehingga kualitas tetap terjaga sampai ke dapur Anda.\r\n\r\n? Proses Penanganan Standar LaBa:\r\n1?? Proses penyembelihan secara syar\'i InsyaAllah terjamin kehalalannya\r\n2?? Pembersihan bulu dan jeroan\r\n3?? Pemotongan masing-masing bagian daging ayam seperti paha, dada dan sayap\r\n4?? Pencucian menggunakan air mengalir\r\n5?? Packing plastik tebal food grade\r\n6?? Penyimpanan freezer untuk menjaga kualitas daging\r\n\r\n? Detail Produk:\r\n? Harga: Rp 35.000/kg\r\n?? Ukuran potongan : 8-12 Potong / kg\r\n? Kondisi: Fresh kemasan, siap olah', 32000.00, 20.00, 'assets/uploads/products/1767194439_(FILEminimizer) 1.png', '2025-12-31 08:20:39'),
(27, 1, 'Ati Ampela Ayam', 'ati-ampela-ayam', 'ati ampela ayam yg fresh', 24000.00, 10.00, 'assets/uploads/products/1767194480_(FILEminimizer) ati ampela ayam.jpg', '2025-12-31 08:21:20'),
(28, 1, 'Kepala Ayam', 'kepala-ayam', 'kepala ayam potongan', 14000.00, 10.00, 'assets/uploads/products/1767194572_(FILEminimizer) kepala ayam.jpg', '2025-12-31 08:22:52'),
(29, 1, 'Ceker', 'ceker', 'ceker ayam', 24000.00, 10.00, 'assets/uploads/products/1767194632_(FILEminimizer) ceker ayam.jpg', '2025-12-31 08:23:52'),
(30, 1, 'Ekor', 'ekor', 'ekor ayam', 24000.00, 9.00, 'assets/uploads/products/1767194671_(FILEminimizer) ekor ayam.jpg', '2025-12-31 08:24:31'),
(31, 1, 'Fillet Dada Kulit', 'fillet-dada-kulit', 'Dada ayam dengan potongan fillet', 43000.00, 15.00, 'assets/uploads/products/1767195400_(FILEminimizer) Gemini_Generated_Image_2sm7xu2sm7xu2sm7.jpg', '2025-12-31 08:36:40'),
(33, 2, 'Ikan Nila', 'ikan-nila', 'Ikan Nila Segar pilihan yang diproses dengan standar kebersihan tinggi & penuh keberkahan, sehingga kualitas tetap terjaga sampai ke dapur Anda.\r\n\r\nProses Penanganan Standar LaBa:\r\n1 Ditimbang terlebih dahulu sesuai pesanan\r\n2 Diawali dengan bacaan Basmalah ?\r\n3 Pembersihan kotoran & insang hingga bersih\r\n4 Penyayatan badan samping agar bumbu meresap\r\n5 Pencucian menggunakan air mengalir\r\n6 Packing plastik tebal food grade\r\n7 Penyimpanan freezer untuk menjaga kualitas\r\n\r\nDetail Produk:\r\nHarga: Rp 35.000/kg\r\nUkuran: 5ï¿½7 ekor / kg\r\nKondisi: Fresh kemasan, siap olah', 33000.00, 18.00, 'assets/uploads/products/1767195473_(FILEminimizer) Gemini_Generated_Image_optos6optos6opto.jpg', '2025-12-31 08:37:53'),
(34, 1, 'Fillet Paha Kulit', 'fillet-paha-kulit', 'paha kulit ayam dengan potongan fillet', 44000.00, 8.00, 'assets/uploads/products/1767415403_1767195439_(FILEminimizer) Gemini_Generated_Image_2sm7xu2sm7xu2sm7.jpg', '2025-12-31 08:38:33'),
(36, 4, 'Ikan Ekor Kuning', 'ikan-ekor-kuning', 'Ikan Ekor Kuning merupakan ikan seafood yang kami olah menjadi produk Frozen Fresh ', 28000.00, 0.00, 'assets/uploads/products/1767416183_1767269667_Ekor Kuning.jpeg', '2026-01-02 21:56:23'),
(37, 3, 'Otak-otak ikan', 'otak-otak-ikan', 'cemilan bergizi otak-otak dari bahan ikan fresh pilihan', 23000.00, 10.00, 'assets/uploads/products/1768170138_WhatsApp Image 2025-12-29 at 13.24.00.jpeg', '2026-01-11 22:22:18');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `full_name` varchar(100) DEFAULT 'Admin User',
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `full_name`, `password`, `email`, `created_at`) VALUES
(1, 'admin', 'Admin User', '$2y$10$ZEY.Svi7oSHgXTPAXbwknea05SAwb34I5K9YBjrZET1vmBnOF7zmK', 'admin@lapakbangsawan.com', '2025-12-26 15:07:11');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `daily_sales_targets`
--
ALTER TABLE `daily_sales_targets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_target` (`product_id`,`target_date`),
  ADD KEY `target_date` (`target_date`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_order_token` (`order_token`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `daily_sales_targets`
--
ALTER TABLE `daily_sales_targets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `daily_sales_targets`
--
ALTER TABLE `daily_sales_targets`
  ADD CONSTRAINT `fk_daily_target_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
