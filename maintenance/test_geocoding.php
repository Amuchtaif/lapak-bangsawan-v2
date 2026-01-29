<?php
require_once 'config/init.php';
require_once 'helpers/BiteshipService.php';

$biteship = new BiteshipService();
// Try geocoding instead of areas search
$apiKey = BITESHIP_API_KEY;
$baseUrl = BITESHIP_BASE_URL;
$query = "Talun, Cirebon, Jawa Barat";
$url = $baseUrl . "/maps/geocoding?input=" . urlencode($query);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $apiKey]);
$res = curl_exec($ch);
curl_close($ch);

header('Content-Type: application/json');
echo $res;
?>