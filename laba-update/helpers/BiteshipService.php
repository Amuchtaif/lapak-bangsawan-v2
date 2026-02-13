<?php
/**
 * BiteshipService Helper Class
 * Handles integration with Biteship API using cURL Native
 */

require_once dirname(__DIR__) . "/config/biteship.php";

class BiteshipService
{
    private $apiKey;
    private $baseUrl;

    public function __construct()
    {
        $this->apiKey = BITESHIP_API_KEY;
        $this->baseUrl = BITESHIP_BASE_URL;
    }

    /**
     * Search for area ID by query (Kelurahan/Kecamatan)
     * @param string $query
     * @return array
     */
    public function searchArea($query): array
    {
        $url = $this->baseUrl . "/maps/areas?countries=ID&input=" . urlencode($query) . "&type=single";
        return $this->request('GET', $url);
    }

    /**
     * Check Shipping Rates
     * @param string $destinationAreaId
     * @param int $weight
     * @param array $items
     * @param string $originAreaId
     * @param string $couriers
     * @param float|null $originLat
     * @param float|null $originLng
     * @param float|null $destLat
     * @param float|null $destLng
     * @param array $extraParams
     * @return array
     */
    public function checkRates($destinationAreaId, $weight, $items = [], $originAreaId = BITESHIP_ORIGIN_AREA_ID, $couriers = 'paxel,jne,jnt,sicepat,gojek,grab,lalamove', $originLat = null, $originLng = null, $destLat = null, $destLng = null, $extraParams = []): array
    {
        $url = $this->baseUrl . "/rates/couriers";
        $data = [
            'origin_area_id' => $originAreaId,
            'destination_area_id' => $destinationAreaId,
            'couriers' => $couriers,
            'items' => $items,
            'weight' => $weight // in grams
        ];

        // Add coordinates in both formats to ensure compatibility with different Biteship versions
        if ($originLat && $originLng) {
            $data['origin_latitude'] = (float) $originLat;
            $data['origin_longitude'] = (float) $originLng;
            $data['origin_coordinate'] = [
                'latitude' => (float) $originLat,
                'longitude' => (float) $originLng
            ];
        }

        if ($destLat && $destLng) {
            $data['destination_latitude'] = (float) $destLat;
            $data['destination_longitude'] = (float) $destLng;
            $data['destination_coordinate'] = [
                'latitude' => (float) $destLat,
                'longitude' => (float) $destLng
            ];
        }

        // Add extra params (contact info often required for instant)
        if (!empty($extraParams)) {
            $data = array_merge($data, $extraParams);
        }

        return $this->request('POST', $url, $data);
    }

    /**
     * Create/Book an Order/Pickup
     * @param array $orderData
     * @return array
     */
    public function createOrder($orderData): array
    {
        $url = $this->baseUrl . "/orders";
        return $this->request('POST', $url, $orderData);
    }

    /**
     * Cancel Order
     * @param string $orderId Biteship Order ID
     * @param string $reason Cancellation Reason
     * @return array
     */
    public function cancelOrder($orderId, $reason): array
    {
        // Endpoint: DELETE /v1/orders/{id}
        $url = $this->baseUrl . "/orders/" . $orderId;
        // Body: reason (required)
        return $this->request('DELETE', $url, ['reason' => $reason]);
    }

    /**
     * Retrieve Multiple Orders
     * @param int $limit
     * @return array
     */
    public function retrieveOrders($limit = 50): array
    {
        $url = $this->baseUrl . "/orders?limit=" . $limit;
        return $this->request('GET', $url);
    }

    /**
     * Get Single Order Details
     * @param string $orderId
     * @return array
     */
    public function getOrder($orderId): array
    {
        // Endpoint: GET /v1/orders/{id}
        $url = $this->baseUrl . "/orders/" . $orderId;
        return $this->request('GET', $url);
    }

    /**
     * Get Tracking status by Waybill ID and Courier Code
     * @param string $waybillId
     * @param string $courierCode
     * @return array
     */
    public function getTracking($waybillId, $courierCode): array
    {
        // Endpoint: /v1/trackings/{waybill_id}/couriers/{courier_code}
        $url = $this->baseUrl . "/trackings/" . $waybillId . "/couriers/" . $courierCode;
        return $this->request('GET', $url);
    }

    /**
     * Core Request Helper (cURL Native)
     * @param string $method
     * @param string $url
     * @param mixed $data
     * @return array
     */
    private function request($method, $url, $data = null): array
    {
        $ch = curl_init();

        $headers = [
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/json'
        ];

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Optional: if local issues with SSL

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        } elseif ($method === 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        } elseif ($method === 'GET') {
            // GET params usually in URL, but sometimes body? No, standard is URL.
            // Biteship might adhere to standard.
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);

        if ($error) {
            return [
                'success' => false,
                'message' => 'cURL Error: ' . $error
            ];
        }

        $decoded = json_decode($response, true);

        // Biteship Success usually means 200/201
        // But the response body also usually contains 'success': true/false or 'options' etc
        if ($httpCode >= 200 && $httpCode < 300) {
            // Sometimes Biteship returns error inside 200 OK? Let's assume standard REST.
            // But check if decoded is valid
            if($decoded === null) {
                 return [
                    'success' => false,
                    'message' => 'Invalid JSON Response',
                    'raw' => $response
                ];
            }
            
            // If API returns { "success": false, "error": "..." } even on 200
            if (isset($decoded['success']) && $decoded['success'] === false) {
                 return [
                    'success' => false,
                    'message' => $decoded['error'] ?? 'API Logical Error',
                    'raw' => $decoded
                ];
            }

            return [
                'success' => true,
                'data' => $decoded
            ];
        } else {
            return [
                'success' => false,
                'status_code' => $httpCode,
                'message' => $decoded['error'] ?? $decoded['message'] ?? 'Unknown API Error',
                'raw' => $decoded
            ];
        }
    }

    /**
     * Get Coordinates from Area Name (Using Nominatim/OSM as fallback)
     * @param string $areaName
     * @return array|null
     */
    public function getCoordinatesFromArea($areaName): ?array
    {
        $url = "https://nominatim.openstreetmap.org/search?q=" . urlencode($areaName) . "&format=json&limit=1";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'LapakBangsawan/1.0 (contact@lapakbangsawan.com)'); // Required by OSM
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $res = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($res, true);
        if (!empty($data) && isset($data[0]['lat'], $data[0]['lon'])) {
            return [
                'latitude' => (float) $data[0]['lat'],
                'longitude' => (float) $data[0]['lon']
            ];
        }

        return null;
    }

    /**
     * Attempt to find Biteship Area ID from Coordinates (Reverse Geocoding)
     * @param float $lat
     * @param float $lng
     * @return string|null
     */
    public function retrieveAreaIdFromCoordinates($lat, $lng): ?string
    {
        // 1. Reverse Geocode via OSM
        // Use zoom=14 to get District/City level focus, or 18 for full address
        $url = "https://nominatim.openstreetmap.org/reverse?format=json&lat={$lat}&lon={$lng}&zoom=18&addressdetails=1";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'LapakBangsawan/1.0 (contact@lapakbangsawan.com)');
        curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Increase timeout
        $res = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($res, true);
        
        if (!$data || !isset($data['address'])) {
            // Debug: Log if OSM fails
            @file_put_contents('osm_debug.log', "OSM Failed for $lat,$lng: " . $res . PHP_EOL, FILE_APPEND);
            return null;
        }

        $addr = $data['address'];
        
        // Debug: Log OSM Result
        @file_put_contents('osm_debug.log', "OSM Result: " . print_r($addr, true) . PHP_EOL, FILE_APPEND);
        
        // Refined Mapping for Indonesia
        $kelurahan = $addr['village'] ?? $addr['suburb'] ?? $addr['hamlet'] ?? '';
        $kecamatan = $addr['city_district'] ?? $addr['district'] ?? '';
        $kota = $addr['city'] ?? $addr['town'] ?? $addr['municipality'] ?? $addr['regency'] ?? $addr['county'] ?? '';
        
        $country = $addr['country_code'] ?? 'id';
        if ($country !== 'id') return null; // Only ID supported

        // STRATEGY 1: Kecamatan + Kota (Most Reliable for Biteship)
        // Example: "Pasar Minggu, Jakarta Selatan"
        if ($kecamatan && $kota) {
             $query = "$kecamatan, $kota";
             $res = $this->searchArea($query);
             if ($res['success'] && !empty($res['data']['areas'])) {
                 return $res['data']['areas'][0]['id'];
             }
        }
        
        // STRATEGY 2: Kelurahan + Kota
        if ($kelurahan && $kota) {
             $query = "$kelurahan, $kota";
             $res = $this->searchArea($query);
             if ($res['success'] && !empty($res['data']['areas'])) {
                 return $res['data']['areas'][0]['id'];
             }
        }

        // STRATEGY 3: Kelurahan + Kecamatan
        if ($kelurahan && $kecamatan) {
             $query = "$kelurahan, $kecamatan";
             $res = $this->searchArea($query);
             if ($res['success'] && !empty($res['data']['areas'])) {
                 return $res['data']['areas'][0]['id'];
             }
        }
        
        // STRATEGY 4: Just Kecamatan (May return multiple, take first)
        if ($kecamatan) {
             $res = $this->searchArea($kecamatan);
             if ($res['success'] && !empty($res['data']['areas'])) {
                 return $res['data']['areas'][0]['id'];
             }
        }

        return null;
    }
}
