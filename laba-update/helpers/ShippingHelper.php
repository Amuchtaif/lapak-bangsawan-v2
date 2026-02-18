<?php
// helpers/ShippingHelper.php

class ShippingHelper {
    /**
     * Get shipping settings from database with defaults
     */
    public static function getLocalShippingSettings($conn) {
        $defaults = [
            'max_distance_local' => 10,  // km
            'price_per_km_local' => 1000 // rupiah
        ];

        try {
            $result = $conn->query("SELECT max_distance_local, price_per_km_local FROM settings_pengiriman LIMIT 1");
            if ($result && $result->num_rows > 0) {
                return $result->fetch_assoc();
            }
        } catch (Exception $e) {
            // Log error in production
        }

        return $defaults;
    }

    /**
     * Haversine formula to calculate distance between two points
     */
    public static function calculateDistance($lat1, $lon1, $lat2, $lon2) {
        $earthRadius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c;

        return round($distance, 2);
    }

    /**
     * Calculate local delivery cost based on settings
     */
    public static function calculateLocalCost($distance, $pricePerKm) {
        // User requested tiered pricing for easier change (kembalian):
        // - Under 1.5 km: 1000
        // - 1.5 - 1.9 km: 1500
        // - 2.0 km and above: increment by 500 per 0.5 km (assuming 1000/km base)
        
        if ($distance < 1.5) {
            return 1000;
        }

        // Logic: Increment by half of pricePerKm for every 0.5km step
        $halfKmPrice = $pricePerKm / 2;
        $steps = floor($distance / 0.5);
        $cost = $steps * $halfKmPrice;

        return (int)max(1000, $cost);
    }
}
?>
