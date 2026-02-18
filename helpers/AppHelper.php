<?php
// helpers/AppHelper.php

class AppHelper {
    private static $settings = null;

    // Fetch all settings once and cache them in static variable
    public static function loadSettings($conn) {
        if (self::$settings === null) {
            self::$settings = [];
            $result = $conn->query("SELECT setting_key, setting_value FROM site_settings");
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    self::$settings[$row['setting_key']] = $row['setting_value'];
                }
            }
        }
    }

    // Get specific setting
    public static function get_setting($key, $default = '') {
        // Assume database connection is globally available via $GLOBALS['conn'] 
        // OR initiated via loadSettings call early in init.php
        if (self::$settings === null) {
            global $conn;
            if ($conn) {
                self::loadSettings($conn);
            }
        }
        
        return isset(self::$settings[$key]) ? self::$settings[$key] : $default;
    }

    /**
     * Translate English duration (from Biteship) to Indonesian
     */
    public static function translateDuration($duration) {
        if (empty($duration)) return $duration;
        $search = ['hours', 'hour', 'days', 'day', 'mins', 'min'];
        $replace = ['Jam', 'Jam', 'Hari', 'Hari', 'Menit', 'Menit'];
        return str_ireplace($search, $replace, $duration);
    }
}

// Global convenience function
function get_setting($key, $default = '') {
    return AppHelper::get_setting($key, $default);
}
?>
