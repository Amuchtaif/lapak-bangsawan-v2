<?php
// Simulate a request to public/api/check_rates.php
$_SERVER['REQUEST_METHOD'] = 'POST';
$input = [
    'dest_lat' => -6.200000,
    'dest_lng' => 106.816666,
    'area_id' => 'IDNP9IDNC105IDND171IDZ45171', // Sample area ID
    'items' => [
        ['product_id' => 1, 'weight' => 1, 'unit' => 'kg', 'price' => 10000]
    ]
];

// Mock file_get_contents('php://input')
// This is tricky in CLI, but we can modify check_rates.php temporarily to use a variable or just use inclusion logic if it wasn't a standalone API.
// Alternatively, let's just inspect the debug info in the response if possible.

// But wait, I can just read the file and check if there's any obvious logic error.
// I recently added: 
/*
if (!empty($areaId)) {
    $biteship = new BiteshipService();
    try {
        // ...
*/
// Does $areaId come from $input?
// Yes: $areaId = $input['area_id'] ?? '';

// Let's check BiteshipService::checkRates in helpers/BiteshipService.php
?>
