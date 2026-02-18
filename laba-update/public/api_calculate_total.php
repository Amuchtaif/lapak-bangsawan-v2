<?php
header('Content-Type: application/json');
require_once dirname(__DIR__) . "/config/init.php";

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['items'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

// Enrich items with verified category from database
$enriched_items = [];
foreach ($input['items'] as $item) {
    $pid = intval($item['product_id'] ?? 0);
    $category = $item['category'] ?? 'Uncategorized';
    
    // Fetch real category from database if product_id exists
    if ($pid > 0) {
        $stmt = $conn->prepare("SELECT p.price, c.name as category_name 
                                FROM products p 
                                LEFT JOIN categories c ON p.category_id = c.id 
                                WHERE p.id = ?");
        $stmt->bind_param("i", $pid);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows > 0) {
            $row = $res->fetch_assoc();
            $category = $row['category_name'] ?? 'Uncategorized';
            // Optionally verify price from DB for security
            $item['price'] = floatval($row['price']);
        }
        $stmt->close();
    }
    
    $item['category'] = $category;
    $enriched_items[] = $item;
}

$calculation = calculateCartTotal($enriched_items);

// Format rupiah for JS display
$calculation['subtotal_formatted'] = 'Rp ' . number_format($calculation['subtotal'], 0, ',', '.');
$calculation['total_discount_formatted'] = 'Rp ' . number_format($calculation['total_discount'], 0, ',', '.');
$calculation['total_formatted'] = 'Rp ' . number_format($calculation['total'], 0, ',', '.');

foreach ($calculation['discounts_detail'] as &$detail) {
    $detail['amount_formatted'] = '-Rp ' . number_format($detail['amount'], 0, ',', '.');
}

echo json_encode($calculation);
?>