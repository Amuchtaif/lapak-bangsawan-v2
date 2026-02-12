<?php

class LocalDeliveryService
{
    private $baseRate = 10000; // Base rate in IDR
    private $ratePerKm = 2000; // Rate per km in IDR
    private $maxDistance = 100; // Max distance in km for local delivery (TEMP: set high for testing)

    /**
     * Calculate local delivery rates based on distance.
     *
     * @param float $distance Distance in kilometers
     * @return array|null Returns array of rate details or null if out of range
     */
    public function getRate($distance)
    {
        global $conn;
        require_once ROOT_PATH . "helpers/ShippingHelper.php";

        $settings = ShippingHelper::getLocalShippingSettings($conn);
        $max_distance = $settings['max_distance_local'];
        $price_per_km = $settings['price_per_km_local'];

        if ($distance > $max_distance) {
            return null; // Too far for local delivery
        }

        $cost = ShippingHelper::calculateLocalCost($distance, $price_per_km);

        return [
            'company' => 'local',
            'courier_name' => 'Kurir Internal',
            'courier_service_name' => 'Pengiriman Lokal',
            'courier_service_code' => 'store_delivery',
            'type' => 'instant',
            'duration' => '1-3 Jam',
            'price' => (int)$cost,
            'description' => 'Diantar langsung oleh kurir toko.'
        ];
    }
}
