<?php
$data = json_decode(file_get_contents('couriers.json'), true);
print_r(array_keys($data));
if (isset($data['data']))
    print_r(array_keys($data['data']));
?>