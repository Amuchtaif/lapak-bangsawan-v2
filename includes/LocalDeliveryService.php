<?php

class LocalDeliveryService
{
    private $ratePerKm;
    private $maxDistance;

    public function __construct()
    {
        // Use global helper function if available, else fallback
        if (function_exists('get_setting')) {
            $this->ratePerKm = (int) get_setting('shipping_rate_per_km', 1000);
            $this->maxDistance = (float) get_setting('shipping_max_distance', 15.0);
        } else {
            $this->ratePerKm = 1000;
            $this->maxDistance = 15.0;
        }
    }

    /**
     * Calculate rate based on distance
     * Returns array or null if out of range
     */
    public function getRate($distance)
    {
        if ($distance > $this->maxDistance) {
            return null; // Too far for local delivery
        }

        // Logic: Free under 1km, then rate per km
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
            'courier_name' => 'Internal Toko',
            'courier_service_name' => $serviceName,
            'courier_service_code' => 'store_delivery',
            'type' => 'instant',
            'duration' => '1-3 Jam',
            'price' => $price,
            'description' => $description
        ];
    }
}
