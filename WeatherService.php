<?php
// WeatherService.php
// V2 Updated: 2024-06-18

class WeatherService
{
    private $blacklistedIPs = [
        '108.61.195.124',
        // '99.198.173.31',
    ];

    private $cacheExpiration = 900; // Cache expiration time in seconds (15 minutes)

    public function getRealIpAddress(): string
    {
        return $_SERVER['REMOTE_ADDR'] ?? '';
    }

    public function getWeatherDisplay(int $use, string $userIp): string
    {
        if (in_array($userIp, $this->blacklistedIPs)) {
            return '';
        }

        $cacheFile = "/home/netcontrolcp/var/www/wx_cache/{$userIp}.json";
        
        // Create the cache directory if it doesn't exist
        $cacheDir = dirname($cacheFile);
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $this->cacheExpiration) {
            $jsonString = file_get_contents($cacheFile);
        } else {
            $apiKey = 'aeaa0003367cc605';
            $apiUrl = "http://api.wunderground.com/api/{$apiKey}/geolookup/conditions/q/autoip.json?geo_ip={$userIp}";
            // Use cURL to fetch the URL contents
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10); // Set the connection timeout to 10 seconds
            curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Set the overall request timeout to 30 seconds
            $jsonString = curl_exec($ch);
            curl_close($ch);
    
            file_put_contents($cacheFile, $jsonString);
        }

        $parsedJson = json_decode($jsonString, true);

        if ($parsedJson && isset($parsedJson['location']) && isset($parsedJson['current_observation'])) {
            $location = $parsedJson['location']['city'];
            $tempF = $parsedJson['current_observation']['temp_f'];
            $wind = $parsedJson['current_observation']['wind_mph'];
            $windDir = $parsedJson['current_observation']['wind_dir'];
            $wx = $parsedJson['current_observation']['weather'];
            $humid = $parsedJson['current_observation']['relative_humidity'];

            if ($use === 1) {
                return "{$location}: {$wx}, {$tempF}, wind: {$windDir} @ {$wind}, humidity: {$humid}";
            } else {
                return "
                    <span class=\"weather-place\" oncontextmenu=\"defaultMode();return false;\">
                        <img src=\"images/wundergroundLogo_4c.png\" alt=\"wundergroundLogo_4c\" width=\"40\" />
                        <a href=\"https://www.wunderground.com/?apiref=a8092edcfa49acfb\" target=\"_blank\">
                            {$location}: {$wx}, {$tempF} F, wind: {$windDir} @ {$wind}mph, humidity: {$humid}
                        </a>
                    </span>";
            }
        }

        return '';
    }
}