<?php
// compress_assets.php
header('Content-Type: text/plain');
require_once __DIR__ . '/config/init.php';

if (!extension_loaded('gd')) {
    die("GD extension is not loaded. Compression cannot proceed.\n");
}

$dirs = [
    ROOT_PATH . 'assets/images/',
    ROOT_PATH . 'assets/uploads/',
    ROOT_PATH . 'assets/uploads/products/',
    ROOT_PATH . 'assets/uploads/receipts/'
];

$allowed_exts = ['jpg', 'jpeg', 'png'];
$total_saved = 0;
$count = 0;

echo "Starting asset compression...\n";

foreach ($dirs as $dir) {
    if (!is_dir($dir)) continue;

    echo "Processing directory: $dir\n";
    $files = scandir($dir);
    
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        
        $path = $dir . $file;
        if (is_dir($path)) continue;

        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if (in_array($ext, $allowed_exts)) {
            $old_size = filesize($path);
            
            // Only compress if larger than 100KB to avoid unnecessary processing
            if ($old_size > 102400) {
                echo "Compressing: $file (" . round($old_size / 1024) . " KB)... ";
                
                // Compress to a temporary file first
                $temp_file = $path . '.tmp';
                if (compress_image($path, $temp_file, 75)) {
                    $new_size = filesize($temp_file);
                    
                    if ($new_size < $old_size) {
                        rename($temp_file, $path);
                        $saved = $old_size - $new_size;
                        $total_saved += $saved;
                        echo "Done! Saved " . round($saved / 1024) . " KB (" . round(($saved / $old_size) * 100) . "%)\n";
                    } else {
                        unlink($temp_file);
                        echo "Skipped (already optimized)\n";
                    }
                } else {
                    echo "Failed\n";
                }
                $count++;
            }
        }
    }
}

echo "\nCompression complete!\n";
echo "Total files processed: $count\n";
echo "Total space saved: " . round($total_saved / 1024 / 1024, 2) . " MB\n";
?>
