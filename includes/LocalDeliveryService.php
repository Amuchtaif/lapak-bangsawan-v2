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
        if ($distance > $this->maxDistance) {
            return null; // Too far for local delivery
        }

        return [
            'company' => 'local',
            'courier_name' => 'Kurir Toko',
            'courier_service_name' => 'Antar Langsung',
            'courier_service_code' => 'store_delivery',
            'type' => 'instant',
            'duration' => '1-3 Jam',
            'price' => 0,
            'description' => 'Gratis ongkir! Diantar langsung oleh kurir toko.'
        ];
    }
}
