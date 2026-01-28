<?php
require_once 'config/init.php';
require_once 'helpers/BiteshipService.php';
$apiKey = BITESHIP_API_KEY;
$baseUrl = BITESHIP_BASE_URL;
$url = $baseUrl . "/maps/geocoding";
$data = ['input' => 'Talun, Cirebon'];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $apiKey,
    'Content-Type: application/json'
]);
$res = curl_exec($ch);
curl_close($ch);

header('Content-Type: application/json');
echo $res;
?>