<?php
require_once "config/init.php";
$items = [
    [
        'product_id' => 1,
        'name' => 'Trial Fish',
        'price' => 50000,
        'weight' => 6,
        'category' => 'Seafood',
        'unit' => 'kg'
    ]
];

$result = calculateCartTotal($items);
echo json_encode($result, JSON_PRETTY_PRINT);
?>