<?php
$data = json_decode(file_get_contents('couriers.json'), true);
$instant = [];
foreach ($data['couriers'] as $c) {
    if (in_array($c['courier_code'], ['gojek', 'grab', 'borzo', 'lalamove'])) {
        $instant[] = $c;
    }
}
echo json_encode($instant, JSON_PRETTY_PRINT);
?>