<?php
// wx.php
// V2 Updated: 2024-07-03


require_once('config.php');

function getWeatherInfo($ip = false)
{
    $weatherData = getOpenWX($ip);
    if ($weatherData === false) {
        $weatherData = currentWX($ip);
    }
    return $weatherData;
}

function currentWX($ip = false)
{
    // ... (keep the existing function code) ...
    
    // Instead of returning a formatted string, return an array
    return [
        'location' => $loc,
        'description' => $obs['desc'],
        'temperature' => $obs['temp'],
        'windDirection' => $obs['windDirection'],
        'windSpeed' => $obs['windSpeed'],
        'humidity' => $obs['humidity']
    ];
}

function getOpenWX($ip = false)
{
    // ... (keep the existing function code) ...
    
    // Instead of returning a formatted string, return an array
    return [
        'location' => $loc,
        'description' => $obs['desc'],
        'temperature' => $obs['temp'],
        'windDirection' => $obs['windDirection'],
        'windSpeed' => $obs['windSpeed'],
        'humidity' => $obs['humidity']
    ];
}

// Keep other functions (doWeatherAPI, getGeoIP, distance, getDirection, getOpenWeatherAPI) as they are

// Don't output anything directly in this file

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

    if ($ip == '108.61.195.124') {
        return false;
    }

    $cache_file = "/home/netcontrolcp/var/www/wx_cache/geo_{$ip}.json";
    if (file_exists($cache_file) && time() - filemtime($cache_file) < 86400) {
        return json_decode(file_get_contents($cache_file));
    }

    $curl = curl_init("http://extreme-ip-lookup.com/json/{$ip}?key=" . $GLOBALS['_API_EXTREME_IP_KEY']);
    curl_setopt($curl, CURLOPT_TIMEOUT, 10);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_USERAGENT, 'net-control.us/1.0 kd0eav@clear-sky.net');
    $data = curl_exec($curl);
    if (strlen($data) > 0) {
        file_put_contents($cache_file, $data);
    }

    return json_decode($data);
}

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

function getOpenWX($ip = false)
{
    $geo = getGeoIP($ip);
    if ($geo === false || !isset($geo->countryCode) || $geo->countryCode != 'US') {
        return false;
    }

    $lat = $geo->lat;
    $lon = $geo->lon;

    $wx = getOpenWeatherAPI($lat, $lon);

    if ($wx === false || !isset($wx->name) || !isset($wx->main) || !isset($wx->main->temp) || !isset($wx->main->humidity) || !isset($wx->weather) || !isset($wx->wind) || !isset($wx->wind->speed)) {
        return false;
    }

    $loc = $wx->name;
    $obs['temp'] = round($wx->main->temp);
    $obs['humidity'] = round($wx->main->humidity);
    $obs['desc'] = $wx->weather[0]->main;
    $obs['icon'] = $wx->weather[0]->icon;
    $obs['windSpeed'] = round($wx->wind->speed);
    $obs['windDirection'] = isset($wx->wind->deg) ? getDirection($wx->wind->deg) : 'N/A';

    return "{$loc}: {$obs['desc']}, {$obs['temp']}F, wind: {$obs['windDirection']} @ {$obs['windSpeed']}, humidity: {$obs['humidity']}%";
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

?>