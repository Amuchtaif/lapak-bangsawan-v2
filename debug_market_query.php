<?php
include 'config/init.php';
$where = "";
$order = "ORDER BY (products.stock > 0) DESC, products.id DESC";
$query = "SELECT products.*, categories.slug as category_slug, categories.name as category_name FROM products LEFT JOIN categories ON products.category_id = categories.id $where $order";
$res = $conn->query($query);
if (!$res) {
    echo "QUERY ERROR: " . $conn->error;
} else {
    echo "QUERY SUCCESS: " . $res->num_rows . " products found.";
}

$best_seller_query = "
    SELECT p.*, c.name as category_name, SUM(oi.weight) as total_sold 
    FROM order_items oi 
    JOIN orders o ON oi.order_id = o.id
    JOIN products p ON oi.product_name = p.name 
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE o.status IN ('completed', 'delivered')
    GROUP BY p.id 
    ORDER BY total_sold DESC 
    LIMIT 6";
$res2 = $conn->query($best_seller_query);
if (!$res2) {
    echo "\nBEST SELLER ERROR: " . $conn->error;
} else {
    echo "\nBEST SELLER SUCCESS: " . $res2->num_rows . " found.";
}
?>
