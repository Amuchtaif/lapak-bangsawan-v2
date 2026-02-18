<?php
// Simulate a REAL request to check_rates.php to see the full output including debug info
$_SERVER['REQUEST_METHOD'] = 'POST';
$input = [
    'dest_lat' => -6.200000,
    'dest_lng' => 106.816666,
    'area_id' => 'IDNP9IDNC105IDND171IDZ45118', 
    'postal_code' => '45118',
    'items' => [
        [
            'product_id' => 1,
            'name' => 'Beras Pandan Wangi',
            'price' => 15000,
            'weight' => 2,
            'unit' => 'kg'
        ]
    ]
];

// We need to mock the php://input for the script
// A simple way is to define a global and change check_rates.php to use it if set, 
// OR just use curl to hit it if we have a web server running.
// But I can't easily hit it via curl internally if I don't know the exact port/host.

// Let's just modify check_rates.php to accept data from a variable if defined.

ob_start();
include 'public/api/check_rates.php';
$output = ob_get_clean();

echo $output;
?>
