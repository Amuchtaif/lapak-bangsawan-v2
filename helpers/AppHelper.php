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
    public static function log_activity($conn, $user_id, $admin_name, $action, $details = '') {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, admin_name, action, details, ip_address) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $user_id, $admin_name, $action, $details, $ip);
        return $stmt->execute();
    }

    public static function format_phone($phone) {
        if (empty($phone)) return $phone;
        // Remove non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // If it starts with '8', it's likely an Indonesian mobile number missing '0' or '62'
        if (strpos($phone, '8') === 0 && (strlen($phone) >= 9 && strlen($phone) <= 13)) {
            $phone = '0' . $phone;
        } elseif (strpos($phone, '62') === 0) {
            // Replace '62' with '0' for local consistency if preferred, 
            // but usually we keep 08... for database and handle 62... for WA links
            $phone = '0' . substr($phone, 2);
        }
        
        return $phone;
    }

    public static function compress_image($source, $destination, $quality) {
        // 1. If GD is not loaded, we can only copy the file if it's not the same location
        if (!extension_loaded('gd')) {
            if ($source !== $destination) {
                return is_uploaded_file($source) ? move_uploaded_file($source, $destination) : copy($source, $destination);
            }
            return true;
        }

        $info = getimagesize($source);
        if (!$info) {
            return is_uploaded_file($source) ? move_uploaded_file($source, $destination) : copy($source, $destination);
        }

        $mime = $info['mime'];
        
        // 2. Load the image based on mime type
        switch ($mime) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($source);
                break;
            case 'image/gif':
                $image = imagecreatefromgif($source);
                break;
            case 'image/png':
                $image = imagecreatefrompng($source);
                // Handle transparency for PNG
                imagepalettetotruecolor($image);
                imagealphablending($image, true);
                imagesavealpha($image, true);
                break;
            default:
                return is_uploaded_file($source) ? move_uploaded_file($source, $destination) : copy($source, $destination);
        }

        if (!$image) {
            return is_uploaded_file($source) ? move_uploaded_file($source, $destination) : copy($source, $destination);
        }

        // 3. Save as JPEG (best compression for photos)
        // Note: Even if original was PNG, converting to JPEG 80% usually saves huge space
        $result = imagejpeg($image, $destination, $quality);
        
        // Free memory
        imagedestroy($image);

        // 4. If JPEG save failed for some reason, fallback to basic move
        if (!$result || !file_exists($destination)) {
            return move_uploaded_file($source_tmp, $destination);
        }

        return true;
    }

    public static function format_date_id($date, $include_day = true) {
        $timestamp = strtotime($date);
        $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $months = [
            1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];

        $day_name = $days[date('w', $timestamp)];
        $day = date('d', $timestamp);
        $month = $months[(int)date('m', $timestamp)];
        $year = date('Y', $timestamp);

        $formatted = "$day $month $year";
        if ($include_day) {
            $formatted = "$day_name, $formatted";
        }

        return $formatted;
    }
}

// Global convenience functions
function get_setting($key, $default = '') {
    return AppHelper::get_setting($key, $default);
}

function log_activity($action, $details = '') {
    global $conn;
    $uid = $_SESSION['user_id'] ?? 0;
    $uname = $_SESSION['username'] ?? 'System';
    return AppHelper::log_activity($conn, $uid, $uname, $action, $details);
}

function format_phone($phone) {
    return AppHelper::format_phone($phone);
}

function compress_image($source, $destination, $quality = 60) {
    return AppHelper::compress_image($source, $destination, $quality);
}

function format_date_id($date, $include_day = true) {
    return AppHelper::format_date_id($date, $include_day);
}
?>
