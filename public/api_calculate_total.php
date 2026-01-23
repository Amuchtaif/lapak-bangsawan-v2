<?php
header('Content-Type: application/json');
require_once dirname(__DIR__) . "/config/init.php";

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['items'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

$calculation = calculateCartTotal($input['items']);

// Format rupiah for JS display
$calculation['subtotal_formatted'] = 'Rp ' . number_format($calculation['subtotal'], 0, ',', '.');
$calculation['total_discount_formatted'] = 'Rp ' . number_format($calculation['total_discount'], 0, ',', '.');
$calculation['total_formatted'] = 'Rp ' . number_format($calculation['total'], 0, ',', '.');

foreach ($calculation['discounts_detail'] as &$detail) {
    $detail['amount_formatted'] = '-Rp ' . number_format($detail['amount'], 0, ',', '.');
}

echo json_encode($calculation);
?>