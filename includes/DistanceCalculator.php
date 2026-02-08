<?php

class DistanceCalculator
{
    /**
     * Calculates the great-circle distance between two points, with
     * the Haversine formula.
     *
     * @param float $lat1 Latitude of point 1 (in decimal degrees)
     * @param float $lon1 Longitude of point 1 (in decimal degrees)
     * @param float $lat2 Latitude of point 2 (in decimal degrees)
     * @param float $lon2 Longitude of point 2 (in decimal degrees)
     * @return float Distance in kilometers
     */
    public static function haversine($lat1, $lon1, $lat2, $lon2)
    {
        // Convert degrees to radians
        $lat1 = deg2rad($lat1);
        $lon1 = deg2rad($lon1);
        $lat2 = deg2rad($lat2);
        $lon2 = deg2rad($lon2);

        $deltaLat = $lat2 - $lat1;
        $deltaLon = $lon2 - $lon1;

        $a = sin($deltaLat / 2) * sin($deltaLat / 2) +
            cos($lat1) * cos($lat2) *
            sin($deltaLon / 2) * sin($deltaLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        $earthRadius = 6371; // Radius of the earth in km

        return $earthRadius * $c;
    }
}
