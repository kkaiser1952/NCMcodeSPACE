<?php
// V2 UPDATED: 2024-06-18

class WeatherService
{
    private $blacklistedIPs = [
        '108.61.195.124',
        // '99.198.173.31',
    ];

    private $cacheExpiration = 900; // Cache expiration time in seconds (15 minutes)

    public function getRealIpAddress(): string
    {
        $ipSources = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'REMOTE_ADDR'
        ];

        foreach ($ipSources as $source) {
            if (!empty($_SERVER[$source])) {
                return $_SERVER[$source];
            }
        }

        return '';
    }

    public function getWeatherDisplay($use, $userIp): string
    {
        if (in_array($userIp, $this->blacklistedIPs)) {
            return '';
        }

        $cacheFile = "/var/www/wx_cache/{$userIp}.json";

        if (file_exists($cacheFile) && time() - filemtime($cacheFile) < $this->cacheExpiration) {
            $jsonString = file_get_contents($cacheFile);
        } else {
            $apiKey = 'aeaa0003367cc605';
            $apiUrl = "http://api.wunderground.com/api/{$apiKey}/geolookup/conditions/q/autoip.json?geo_ip={$userIp}";
            $jsonString = file_get_contents($apiUrl);
            file_put_contents($cacheFile, $jsonString);
        }

        $parsedJson = json_decode($jsonString);

        if ($parsedJson && isset($parsedJson->location) && isset($parsedJson->current_observation)) {
            $location = $parsedJson->location->city;
            $tempF = $parsedJson->current_observation->temp_f;
            $wind = $parsedJson->current_observation->wind_mph;
            $windDir = $parsedJson->current_observation->wind_dir;
            $wx = $parsedJson->current_observation->weather;
            $humid = $parsedJson->current_observation->relative_humidity;

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