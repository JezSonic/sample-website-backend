<?php

namespace App\Utils\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use Stevebauman\Location\Facades\Location;

class IpLocationService {
    /**
     * Get location information from an IP address
     *
     * @param string|null $ip The IP address to lookup
     * @return string|null Location in the format "City, province, country" or null if not found
     */
    public static function getLocationFromIp(?string $ip): ?string {
        if (empty($ip) || $ip == '127.0.0.1' || $ip == 'localhost' || $ip == '::1') {
            return null;
        }

        try {
            // Using free IP-API service (no API key required for limited usage)
            $position = Location::get($ip);
            return "$position->cityName, $position->regionName, $position->countryName";
        } catch (Exception $e) {
            Log::error('Error getting location from IP: ' . $e->getMessage());
            return null;
        }
    }
}
