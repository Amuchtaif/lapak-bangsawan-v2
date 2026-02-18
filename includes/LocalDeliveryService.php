<?php

class LocalDeliveryService
{
    private $baseRate = 0; // Base rate in IDR
    private $ratePerKm; // Rate per km in IDR
    private $maxDistance; // Max distance in km for local delivery

    public function __construct()
    {
        $this->ratePerKm = (int) get_setting('shipping_rate_per_km', 2000);
        $this->maxDistance = (float) get_setting('shipping_max_distance', 15.0);
    }

    /**
     * Calculate local delivery rates based on distance.
     *
     * @param float $distance Distance in kilometers
     * @return array|null Returns array of rate details or null if out of range
     */
    public function getRate($distance)
    {
        if ($distance > $this->maxDistance) {
            return null; // Too far for local delivery
        }

        // Logic: Free under 1km, then 2000 per km
        $price = 0;
        $serviceName = 'Gratis Ongkir';
        $description = 'Gratis Ongkir! Jarak dekat (di bawah 1 km).';

        if ($distance >= 1.0) {
            $price = floor($distance) * $this->ratePerKm;
            $serviceName = 'Pengiriman Lokal';
            $description = 'Diantar langsung oleh kurir toko.';
        }

        return [
            'company' => 'local',
            'courier_name' => 'Kurir Internal',
            'courier_service_name' => $serviceName,
            'courier_service_code' => 'store_delivery',
            'type' => 'instant',
            'duration' => '1-3 Jam',
            'price' => $price,
            'description' => $description
        ];
    }
}
