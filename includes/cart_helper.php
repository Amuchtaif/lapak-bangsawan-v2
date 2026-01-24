<?php

function calculateCartTotal($items)
{
    global $conn;

    $subtotal = 0;
    $total_discount = 0;
    $discounts_detail = [];
    $category_weights = [];

    // 1. Calculate Subtotal and Group Weights by Category
    foreach ($items as $item) {
        $subtotal += $item['price'] * $item['weight'];

        $cat = $item['category'] ?? 'Uncategorized';
        if (!isset($category_weights[$cat])) {
            $category_weights[$cat] = 0;
        }
        $category_weights[$cat] += $item['weight'];
    }

    // 2. Fetch Active Wholesale Rules (Sorted by weight DESC for Tiered Logic)
    $rules_res = $conn->query("SELECT * FROM wholesale_rules WHERE is_active = 1 ORDER BY category_name ASC, min_weight_kg DESC");
    $rules = [];
    while ($row = $rules_res->fetch_assoc()) {
        $rules[$row['category_name']][] = [
            'min_weight' => $row['min_weight_kg'],
            'discount_per_kg' => $row['discount_per_kg']
        ];
    }

    // 3. Apply Discounts based on Rules (First Match Wins)
    foreach ($category_weights as $cat => $total_weight) {
        if (isset($rules[$cat])) {
            foreach ($rules[$cat] as $rule) {
                if ($total_weight >= $rule['min_weight']) {
                    $discount_amount = $total_weight * $rule['discount_per_kg'];
                    $total_discount += $discount_amount;
                    $discounts_detail[] = [
                        'category' => $cat,
                        'amount' => $discount_amount,
                        'label' => "Potongan Grosir $cat"
                    ];
                    break; // First Tier Match Wins - Stop checking lower tiers
                }
            }
        }
    }

    $total = $subtotal - $total_discount;

    return [
        'subtotal' => $subtotal,
        'total_discount' => $total_discount,
        'discounts_detail' => $discounts_detail,
        'total' => $total
    ];
}
?>