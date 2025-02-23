<?php
/* updateStationLocationWithW3W.php */
/* This program uses the W3W address to calculate lat/lon, grid, county, state etc. and update the stations table */
/* REQUIRED: a callsign and the What3Words address */
/* Written: 2021-10-15 */
/* Updated: 2024-10-24 */
/* .space Version */

ini_set('display_errors', 1);
error_reporting(E_ALL ^ E_NOTICE);

require_once "dbConnectDtls.php";
require_once "GridSquare.php";
require_once "geocode.php";
require_once "config.php";

// This is for what3words usage
require_once "Geocoder.php";
use What3words\Geocoder\Geocoder;
use What3words\Geocoder\AutoSuggestOption;
$api = new Geocoder("5WHIM4GD");

// Function to get county and state from FCC API
function getFCCLocationData($lat, $lng) {
    $url = "https://geo.fcc.gov/api/census/block/find?latitude=$lat&longitude=$lng&censusYear=2020&format=json";
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_HTTPHEADER => ['Accept: application/json']
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if ($response === false || $httpCode !== 200) {
        echo "FCC API Error: " . curl_error($ch) . " (HTTP Code: $httpCode)<br>";
        curl_close($ch);
        return null;
    }
    
    curl_close($ch);
    
    $data = json_decode($response, true);
    if (!$data || !isset($data['County']['name']) || !isset($data['State']['code'])) {
        echo "Failed to parse FCC API response<br>";
        return null;
    }
    
    return [
        'county' => str_replace(' County', '', $data['County']['name']),
        'state' => $data['State']['code']
    ];
}

// Fill the callsign and w3w based on address from FCC 
$callsign = 'WA0TJT';
$w3w = '///guiding.confusion.towards';

// from the fcc DB and en table with corrected database name

try {
$fccsql = $db_found->prepare("
    SELECT last,
           first,
           state,
           city,
           zip,
           CONCAT_WS(' ', address1, city, state, zip) AS address,
           fccid
    FROM netcontrolcp_fcc_amateur.en e1
    WHERE callsign = :callsign 
    AND e1.fccid = (
        SELECT MAX(e2.fccid) 
        FROM netcontrolcp_fcc_amateur.en e2 
        WHERE e2.callsign = :callsign
    )
    ORDER BY e1.fccid DESC 
    LIMIT 0,1");

    // Set a value for the callsign parameter
    //$callsign = '$callsign';
    $fccsql->bindParam(':callsign', $callsign, PDO::PARAM_STR);

    // Execute the statement
    $fccsql->execute();

    // Fetch the result
    $result = $fccsql->fetch(PDO::FETCH_ASSOC);
    
if ($result) {
        print_r($result); // Process the data as needed
    } else {
        echo "No record found for callsign: " . $callsign;
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Get coordinates from W3W
$w3wLL = $api->convertToCoordinates($w3w);
$lat = $w3wLL['coordinates']['lat'];
$lng = $w3wLL['coordinates']['lng'];
$country = $w3wLL['country'];

echo "What3Words Conversion Results:<br>";
echo "Latitude: $lat<br>";
echo "Longitude: $lng<br>";
echo "Country: $country<br><br>";

// Get the gridsquare from lat lng
$grid = gridsquare($lat, $lng);
echo "Grid Square: $grid<br><br>";

// Get county and state from FCC API
$locationInfo = getFCCLocationData($lat, $lng);
if ($locationInfo) {
    $county = $locationInfo['county'];
    $fccState = $locationInfo['state'];
    echo "FCC API Results:<br>";
    echo "County: $county<br>";
    echo "State: $fccState<br><br>";
} else {
    echo "Failed to get location data from FCC API<br><br>";
}

$sql = "
    UPDATE stations 
    SET latitude = $lat,
        longitude = $lng,
        grid = '$grid',
        county = '$county',
        city = '$city',
        state = '$state',
        home = '$lat,$lng,$grid,$county,$state,$city,$w3w',
        fccid = $fccid,
        active_call = 'y',
        country = '$country',
        dttm = NOW(),
        comment = 'via: updateStationLocationWithW3W',
        zip = '$zip'
    WHERE callsign = '$callsign'
";

echo "SQL that would be executed:<br><pre>$sql</pre>";
//$db_found->exec($sql);

?>