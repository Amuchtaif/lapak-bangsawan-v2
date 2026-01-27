<?php
header('Content-Type: application/json');
require_once dirname(__DIR__, 2) . "/config/init.php";
require_once ROOT_PATH . "helpers/BiteshipService.php";

$input = json_decode(file_get_contents('php://input'), true);
$area_id = $input['area_id'] ?? '';
$items = $input['items'] ?? [];

if (!$area_id || empty($items)) {
    echo json_encode(['success' => false, 'message' => 'Missing area_id or items']);
    exit;
}

// Calculate total weight and prepare items for Biteship
$totalWeight = getCartTotalWeight($items); // Use the helper function we created earlier
$biteshipItems = [];

foreach ($items as $item) {
    $biteshipItems[] = [
        'name' => $item['name'],
        'quantity' => (int) ($item['weight'] ?? 1),
        'value' => (int) $item['price'],
        'weight' => 1000 // Simplified: Biteship often expects weight per item in grams. 
        // Our getCartTotalWeight handles the logic, but for individual items we could be more specific.
    ];
}

$biteship = new BiteshipService();
$result = $biteship->checkRates($area_id, $totalWeight, $biteshipItems);

if ($result['success']) {
    echo json_encode([
        'success' => true,
        'rates' => $result['data']['couriers'],
        'total_weight' => $totalWeight
    ]);
} else {
    echo json_encode(['success' => false, 'message' => $result['message']]);
}
