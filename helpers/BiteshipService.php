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
     */
    public function searchArea($query)
    {
        $url = $this->baseUrl . "/maps/areas?countries=ID&input=" . urlencode($query) . "&type=single";
        return $this->request('GET', $url);
    }

    /**
     * Check Shipping Rates
     */
    public function checkRates($destinationAreaId, $weight, $items = [], $originAreaId = BITESHIP_ORIGIN_AREA_ID)
    {
        $url = $this->baseUrl . "/rates/couriers";
        $data = [
            'origin_area_id' => $originAreaId,
            'destination_area_id' => $destinationAreaId,
            'couriers' => 'jne,jnt,sicepat,gojek,grab,anteraja', // Common couriers
            'items' => $items,
            'weight' => $weight // in grams
        ];

        return $this->request('POST', $url, $data);
    }

    /**
     * Create/Book an Order/Pickup
     */
    public function createOrder($orderData)
    {
        $url = $this->baseUrl . "/orders";
        return $this->request('POST', $url, $orderData);
    }

    /**
     * Get Tracking status by Biteship Order ID
     */
    public function getTracking($biteshipOrderId)
    {
        $url = $this->baseUrl . "/orders/" . $biteshipOrderId;
        return $this->request('GET', $url);
    }

    /**
     * Core Request Helper (cURL Native)
     */
    private function request($method, $url, $data = null)
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

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
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

        if ($httpCode >= 200 && $httpCode < 300) {
            return [
                'success' => true,
                'data' => $decoded
            ];
        } else {
            return [
                'success' => false,
                'status_code' => $httpCode,
                'message' => $decoded['error'] ?? 'Unknown API Error',
                'raw' => $decoded
            ];
        }
    }
}
