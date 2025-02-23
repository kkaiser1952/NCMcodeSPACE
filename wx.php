<?php
// wx.php
// V2 Updated: 2024-07-03


require_once('config.php');

if (!function_exists('getOpenWX')) {
    // All the functions go inside this block
    
    function getWeatherInfo($ip = false) 
{
    $weatherData = getOpenWX($ip);
    if ($weatherData === false) {
        //error_log("getOpenWX failed, falling back to currentWX");
        $weatherData = currentWX($ip);
    }
    if ($weatherData === false) {
        //error_log("Both getOpenWX and currentWX failed to retrieve weather data");
    }
    return $weatherData;
}

    function currentWX($ip = false)
{
    $geo = getGeoIP($ip);
    if ($geo === false) {
        //error_log("Failed to get GeoIP data");
        return false;
    }

    if (!isset($geo->lat) || !isset($geo->lon)) {
        //error_log("GeoIP data missing latitude or longitude");
        return false;
    }

    $lat = $geo->lat;
    $lon = $geo->lon;

    if (isset($geo->city) && $geo->city != '') {
        $loc = $geo->city;
    } elseif (isset($geo->region) && $geo->region != '') {
        $loc = $geo->region;
    } elseif (isset($geo->country) && $geo->country != '') {
        $loc = $geo->country;
    } else {
        $loc = "Unknown";
    }

        /* original version of doWeatherAPI
        $points = doWeatherAPI("https://api.weather.gov/points/{$lat},{$lon}", 86400);
        if ($points === false) {
            return false;
        } */
         
        /* New version */
        $points = doWeatherAPI("https://api.weather.gov/points/{$lat},{$lon}", 86400);
        error_log("Weather points API response: " . json_encode($points));
        if ($points === false || !isset($points->properties) || !isset($points->properties->observationStations)) {
            //error_log("Failed to fetch weather points data or missing required properties");
            return false;
        } 

        $stations = doWeatherAPI($points->properties->observationStations, 86400);
        if ($stations === false) {
            return false;
        }

        $wx = doWeatherAPI($stations->features[0]->id . '/observations');
        if ($wx === false) {
            return false;
        }

        $current = $wx->features[0]->properties;
        $obs['station'] = $stations->features[0]->id;
        $obs['temp'] = round(($current->temperature->value * (9 / 5)) + 32, 1);
        $obs['humidity'] = round($current->relativeHumidity->value);
        $obs['desc'] = $current->textDescription;
        $obs['icon'] = $current->icon;
        $obs['windSpeed'] = round($current->windSpeed->value * 2.2369);

        $d = $current->windDirection->value;
        $obs['windDirection'] = getDirection($d);

        return [
            'location' => $loc,
            'description' => $obs['desc'],
            'temperature' => $obs['temp'],
            'windDirection' => $obs['windDirection'],
            'windSpeed' => $obs['windSpeed'],
            'humidity' => $obs['humidity']
        ];
    }

    function getOpenWX($ip = false) {
    //error_log("inside getOpenWX function");
    $geo = getGeoIP($ip);
    if ($geo === false) {
        //error_log("Failed to get GeoIP data in getOpenWX");
        return false;
    }

    if (!isset($geo->lat) || !isset($geo->lon)) {
        //error_log("GeoIP data missing latitude or longitude in getOpenWX");
        return false;
    }

    $lat = $geo->lat;
    $lon = $geo->lon;

        $wx = getOpenWeatherAPI($lat, $lon);

        if ($wx === false || !isset($wx->name) || !isset($wx->main) || !isset($wx->main->temp) || !isset($wx->main->humidity) || !isset($wx->weather) || !isset($wx->wind) || !isset($wx->wind->speed)) {
            return false;
        }

        return [
            'location' => $wx->name,
            'description' => $wx->weather[0]->main,
            'temperature' => round($wx->main->temp),
            'windDirection' => isset($wx->wind->deg) ? getDirection($wx->wind->deg) : 'N/A',
            'windSpeed' => round($wx->wind->speed),
            'humidity' => round($wx->main->humidity)
        ];
    }

function doWeatherAPI($url, $cache = 300)
{
    $cache_file = "/home/netcontrolcp/var/www/wx_cache/" . sha1($url) . ".json";

    if (file_exists($cache_file) && time() - filemtime($cache_file) < $cache) {
        return json_decode(file_get_contents($cache_file));
    }

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_PORT, 443);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: application/vnd.noaa.dwml+xml;version=1'));
    curl_setopt($curl, CURLOPT_USERAGENT, 'net-control.us/1.0 kd0eav@clear-sky.net');
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($curl, CURLOPT_TIMEOUT, 7);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $data = curl_exec($curl);

    if (!is_object(json_decode($data))) {
        file_put_contents("/home/netcontrolcp/var/www/wx_cache/error_" . time() . rand(), "$url\n$data");
        if (file_exists($cache_file)) {
            return json_decode(file_get_contents($cache_file));
        } else {
            return false;
        }
    }

    file_put_contents($cache_file, $data);

    return json_decode($data);
}

function getGeoIP($ip = false)
{
    if ($ip === false) {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            if ($_SERVER['HTTP_X_FORWARDED_FOR'] != '') {
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } else {
                $ip = $_SERVER['REMOTE_ADDR'];
            }
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
    }

    //error_log("Attempting to get GeoIP for IP: " . $ip);

    if ($ip == '108.61.195.124') {
        error_log("IP is blocked: " . $ip);
        return false;
    }

    $cache_file = "/home/netcontrolcp/var/www/wx_cache/geo_{$ip}.json";
    if (file_exists($cache_file) && time() - filemtime($cache_file) < 86400) {
        $data = file_get_contents($cache_file);
        //error_log("Using cached GeoIP data: " . $data);
        return json_decode($data);
    }

    $url = "http://extreme-ip-lookup.com/json/{$ip}?key=" . $GLOBALS['_API_EXTREME_IP_KEY'];
    //error_log("Fetching GeoIP data from URL: " . $url);

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_TIMEOUT, 10);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_USERAGENT, 'net-control.us/1.0 kd0eav@clear-sky.net');
    $data = curl_exec($curl);

    if ($data === false) {
        //error_log("cURL error: " . curl_error($curl));
        return false;
    }

    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    if ($http_code != 200) {
        //error_log("HTTP error: " . $http_code);
        return false;
    }

    curl_close($curl);

    //error_log("Received GeoIP data: " . $data);

    if (strlen($data) > 0) {
        file_put_contents($cache_file, $data);
    } else {
        //error_log("Received empty response from GeoIP API");
        return false;
    }

    $decoded = json_decode($data);
    if ($decoded === null) {
        //error_log("Failed to decode JSON: " . json_last_error_msg());
        return false;
    }

    return $decoded;
} // END getGeoIp()

function distance($lat1, $lon1, $lat2, $lon2)
{
    $theta = $lon1 - $lon2;
    $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
    $dist = acos($dist);
    return rad2deg($dist) * 60;
}

function getDirection($d)
{
    if ($d > 348.75 || $d < 11.25) {
        return 'N';
    } elseif ($d > 326.25) {
        return 'NNW';
    } elseif ($d > 303.75) {
        return 'NW';
    } elseif ($d > 281.25) {
        return 'WNW';
    } elseif ($d > 258.75) {
        return 'W';
    } elseif ($d > 236.25) {
        return 'WSW';
    } elseif ($d > 213.75) {
        return 'SW';
    } elseif ($d > 191.25) {
        return 'SSW';
    } elseif ($d > 168.75) {
        return 'S';
    } elseif ($d > 146.25) {
        return 'SSE';
    } elseif ($d > 123.75) {
        return 'SE';
    } elseif ($d > 101.25) {
        return 'ESE';
    } elseif ($d > 78.75) {
        return 'E';
    } elseif ($d > 56.25) {
        return 'ENE';
    } elseif ($d > 33.75) {
        return 'NE';
    } else {
        return 'NNE';
    }
}

function getOpenWeatherAPI($lat, $lon)
{
    $url = "https://api.openweathermap.org/data/2.5/weather?units=imperial&lat=$lat&lon=$lon&appid=" . $GLOBALS['_API_OPEN_WXMAP_APPID'];

    $cache_file = "/home/netcontrolcp/var/www/wx_cache/owm_" . sha1($url) . ".json";

    if (file_exists($cache_file) && time() - filemtime($cache_file) < 300) {
        return json_decode(file_get_contents($cache_file));
    }

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_PORT, 443);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_USERAGENT, 'net-control.us/1.0 kd0eav@clear-sky.net');
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($curl, CURLOPT_TIMEOUT, 7);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $data = curl_exec($curl);

    if (!is_object(json_decode($data))) {
        file_put_contents("/home/netcontrolcp/var/www/wx_cache/error_" . time() . rand(), "$url\n$data");
        if (file_exists($cache_file)) {
            return json_decode(file_get_contents($cache_file));
        } else {
            return false;
        }
    }

    file_put_contents($cache_file, $data);

    return json_decode($data);
}
}
?>