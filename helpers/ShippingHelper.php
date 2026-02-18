<?php
// helpers/ShippingHelper.php

class ShippingHelper {
    /**
     * Get shipping settings from database with defaults
     */
    public static function getLocalShippingSettings($conn) {
        $defaults = [
            'max_distance_local' => 10,    // km
            'price_per_km_local' => 1000,  // rupiah
            'free_distance_local' => 1.0   // km
        ];

        try {
            $result = $conn->query("SELECT max_distance_local, price_per_km_local, free_distance_local FROM settings_pengiriman LIMIT 1");
            if ($result && $result->num_rows > 0) {
                $settings = $result->fetch_assoc();
                // Ensure free_distance_local exists in the result, if not use default
                if (!isset($settings['free_distance_local'])) {
                    $settings['free_distance_local'] = 1.0;
                }
                return $settings;
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
    public static function calculateLocalCost($distance, $pricePerKm, $freeDistance = 1.0) {
        // Under free distance (e.g. 1 km): Free
        if ($distance < $freeDistance) {
            return 0;
        }

        // Tiered / Step logic for easier change (kembalian):
        // Increment by half of pricePerKm for every 0.5km step
        $halfKmPrice = $pricePerKm / 2;
        $steps = floor($distance / 0.5);
        $cost = $steps * $halfKmPrice;

        // Minimum cost if above free distance is at least the price per KM (or at least 1000)
        return (int)max($pricePerKm, $cost);
    }
}
?>
