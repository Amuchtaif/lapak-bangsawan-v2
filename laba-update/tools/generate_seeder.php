<?php
/**
 * Script untuk membuat Phinx Seeder dari data database eksisting.
 * Cara pakai: php tools/generate_seeder.php
 */

// 1. Load Config Database Project
require __DIR__ . '/../config/database.php';
// Variabel $host, $user, $pass, $db_name tersedia dari file di atas

// 2. Koneksi PDO
try {
    $dsn = "mysql:host=$host;dbname=$db_name;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    echo "Berhasil terkoneksi ke database: $db_name\n";
} catch (PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}

// 3. Target Tabel (Silahkan tambah tabel lain di sini)
// Jika ingin mengambil SEMUA tabel, bisa gunakan query 'SHOW TABLES'
$targetTables = [
    'users',
    'site_settings',
    'couriers',
    'categories',
    'products', // Menambahkan products sesuai konteks sebelumnya
    // 'orders',
    // 'order_details' 
];

// Header File Seeder
$className = 'ExistingDataSeeder';
$output = "<?php\n\n";
$output .= "use Phinx\Seed\AbstractSeed;\n\n";
$output .= "class $className extends AbstractSeed\n";
$output .= "{\n";
$output .= "    public function run(): void\n";
$output .= "    {\n";

// 4. Loop & Fetch Data
foreach ($targetTables as $table) {
    echo "Memproses tabel: $table... ";

    // Cek apakah tabel/data ada
    try {
        $stmt = $pdo->query("SELECT * FROM `$table`");
        $data = $stmt->fetchAll();

        if (empty($data)) {
            echo "KOSONG (dilewati)\n";
            continue;
        }

        echo "OK (" . count($data) . " baris)\n";

        // 5. Generate String
        // Kita gunakan var_export untuk mengubah array PHP menjadi string code
        $dataExport = var_export($data, true);

        // Membersihkan sedikit format agar lebih rapi (opsional)
        // Mengganti 'array (' menjadi '[' dan ')' menjadi ']' untuk syntax array pendek modern
        $dataExport = str_replace(['array (', ')'], ['[', ']'], $dataExport);

        // Tambahkan ke output script
        $output .= "        // Data untuk tabel: $table\n";
        $output .= "        \$data = $dataExport;\n";
        $output .= "        \$this->table('$table')->insert(\$data)->save();\n\n";

    } catch (Exception $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
    }
}

$output .= "    }\n";
$output .= "}\n";

// 6. Write File
$outputPath = __DIR__ . '/../db/seeds/' . $className . '.php';

// Pastikan folder seeds ada
if (!is_dir(dirname($outputPath))) {
    mkdir(dirname($outputPath), 0777, true);
}

if (file_put_contents($outputPath, $output)) {
    echo "\nSukses! File seeder telah dibuat di:\n$outputPath\n";
} else {
    echo "\nGagal menulis file seeder.\n";
}
