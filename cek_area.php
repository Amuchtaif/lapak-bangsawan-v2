<?php
// Masukkan API Key Anda dari Screenshot tadi
$apiKey = 'biteship_test.eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJuYW1lIjoibGFwYWstaW50ZWdyYXNpIiwidXNlcklkIjoiNjk3MzNmNGQzODI5ZDI2YmNiMzNlM2Q4IiwiaWF0IjoxNzY5NDg3OTUxfQ.7O5smgSFozNbLObYHe6qXA4kvNhK-7lI4vC2Tn4hxSA';

// Masukkan Nama Kecamatan / Kota Toko Anda
$query = 'Cirebon'; // Ganti dengan lokasi spesifik, misal: "Cilandak" atau "Lowokwaru"

$curl = curl_init();

curl_setopt_array($curl, array(
    CURLOPT_URL => "https://api.biteship.com/v1/maps/areas?countries=ID&input=" . urlencode($query) . "&type=single",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "GET",
    CURLOPT_HTTPHEADER => array(
        "Authorization: Bearer " . $apiKey
    ),
));

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
    echo "cURL Error #:" . $err;
} else {
    // Decode agar rapi
    $data = json_decode($response, true);
    echo "<pre>";
    print_r($data);
    echo "</pre>";
}
?>