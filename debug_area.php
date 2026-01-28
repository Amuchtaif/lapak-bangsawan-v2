<?php
require_once 'config/init.php';
require_once 'helpers/BiteshipService.php';

$biteship = new BiteshipService();
$q = "Pekalipan";
$res = $biteship->searchArea($q);

if ($res['success'] && !empty($res['data']['areas'])) {
    print_r(array_keys($res['data']['areas'][0]));
    print_r($res['data']['areas'][0]);
} else {
    echo "No areas found or error.";
}
?>