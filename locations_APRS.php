<?php
// locations_APRS.php
// V3 Updated: 2024-06-06
// locations_APRS.php is designed to work much like its counter part locations_W3W.php but with APRS_call from the APRSIS as input. Done via right click on the field.
// It is called by the ajax() in NetManager-W3W-APRS.js

require_once "dbConnectDtls.php";
require_once "w3w_functions.php";
require_once "getCityStateFromLatLng.php";
require_once "config.php";

//ini_set('display_errors', 1);
//error_reporting(E_ALL);

// Check if the variables are set before sanitizing
$aprs_callsign = $_GET["aprs_call"] ?? '';
$aprs_callsign = strtoupper(filter_var($aprs_callsign, FILTER_SANITIZE_STRING));

$recordID = $_GET["recordID"] ?? 0;
$recordID = filter_var($recordID, FILTER_SANITIZE_NUMBER_INT);

$CurrentLat = $_GET["CurrentLat"] ?? 0.0;
$CurrentLat = filter_var($CurrentLat, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

$CurrentLng = $_GET["CurrentLng"] ?? 0.0;
$CurrentLng = filter_var($CurrentLng, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

$cs1 = $_GET["cs1"] ?? '';
$cs1 = filter_var($cs1, FILTER_SANITIZE_STRING);

$nid = $_GET["nid"] ?? 0;
$nid = filter_var($nid, FILTER_SANITIZE_NUMBER_INT);

$objName = $_GET["objName"] ?? '';
$objName = filter_var($objName, FILTER_SANITIZE_STRING);

$APRScomment = $_GET["comment"] ?? '';
$APRScomment = filter_var($APRScomment, FILTER_SANITIZE_STRING);

// passcodes
include('config2.php');
$aprs_fi_api_key = $config['aprs_fi']['api_key'];
$api_url = "http://api.aprs.fi/api/get?name={$aprs_callsign}&what=loc&apikey={$aprs_fi_api_key}&format=json";

// Fetch the data from the API
$json_data = file_get_contents($api_url);
$data = json_decode($json_data, true);

// Add debugging statement to check if $data contains the expected values
echo "<pre>";
print_r($data);
echo "</pre>";

// Extract the required data from the aprs.fi api 
$lat             = $data['entries'][0]['lat'] ?? 0.0;
$lng             = $data['entries'][0]['lng'] ?? 0.0;
$altitude_meters = $data['entries'][0]['altitude'] ?? 0;
$alt_feet        = $altitude_meters * 3.28084;
$altitude_feet   = number_format($alt_feet, 1);
$aprs_comment    = $data['entries'][0]['comment'] ?? '';

// $firsttime is the value of time in the returned array. It is the last time heard
// $thistime is the value of lasttime in the array. It is the most current time heard
$firsttime = gmdate('Y-m-d H:i:s', $data['entries'][0]['time'] ?? 0);
$thistime = gmdate('Y-m-d H:i:s', $data['entries'][0]['lasttime'] ?? 0);

// for including into the Time Line Log at end of the comment or object
$thislatlng = "$lat,$lng";

// Now get the crossroads data
include('getCrossRoads.php');
$crossroads = getCrossRoads($lat, $lng);

// Now get the gridsquare
include('GetGridSquare.php');
$grid = getgridsquare($lat, $lng);

// Now get the City, State, and Count
include('getCityStateFromLatLng.php');
[$state, $county, $city] = reverseGeocode($lat, $lng, $_GOOGLE_MAPS_API_KEY);

// Now lets add the what3words words from the W3W geocoder
$w3w_api_key = $config['geocoder']['api_key'];

// use What3words\Geocoder\Geocoder;
require_once('Geocoder.php');
$latx = (float) ($data['entries'][0]['lat'] ?? 0.0);
$lat = number_format($latx, 6);
$lngx = (float) ($data['entries'][0]['lng'] ?? 0.0);
$lng = number_format($lngx, 6);
echo ('@107 <br><br>lat '.$lat.', lng '.$lng.'<br>');

$api = new What3words\Geocoder\Geocoder($w3w_api_key);
   
// Get the what3words using lat lng
$result = $api->convertTo3wa($lat, $lng);
$what3words = $result['words'] ?? '';
$map = $result['map'] ?? '';

// This stuff is for printing only
$crossroads = html_entity_decode($crossroads);

$varsToKeep = [
    "aprs_callsign" => htmlspecialchars($aprs_callsign),
    "recordID"      => htmlspecialchars($recordID),
    "CurrentLat"    => htmlspecialchars($CurrentLat),
    "CurrentLng"    => htmlspecialchars($CurrentLng),
    "lat"           => htmlspecialchars($lat),
    "lng"           => htmlspecialchars($lng),
    "altitude_meters" => htmlspecialchars($altitude_meters),
    "altitude_feet" => htmlspecialchars($altitude_feet),
    "crossroads"    => htmlspecialchars($crossroads),
    "firsttime"     => htmlspecialchars($firsttime),
    "thistime"      => htmlspecialchars($thistime),
    "grid"          => htmlspecialchars($grid),
    "what3words"    => htmlspecialchars($what3words),
    "map"           => htmlspecialchars($map),
    "cs1"           => htmlspecialchars($cs1),
    "nid"           => htmlspecialchars($nid),
    "aprs_comment"  => htmlspecialchars($aprs_comment),
    "objName"       => htmlspecialchars($objName),
    "thislatlng"    => htmlspecialchars($thislatlng),
    "city"          => htmlspecialchars($city),
    "county"        => htmlspecialchars($county),
    "state"         => htmlspecialchars($state)
];

$json = json_encode($varsToKeep, JSON_PRETTY_PRINT);
echo "<br><br> $json";
echo "\n\n";

$deltax = 'LOC&#916:APRS '.$objName.' : '.$APRScomment.' : '.$what3words.' : '.$crossroads.' : ('.$thislatlng.')';

// This SQL updates the NetLog with all the information we just created.   
$sql = "UPDATE NetLog
           SET latitude     = :lat
              ,longitude    = :lng
              ,grid         = :grid
              ,w3w          = :w3w
              ,dttm         = NOW()
              ,comments     = :comments
              ,city         = :city
              ,county       = :county
              ,state        = :state
         WHERE recordID = :recordID     
";   
   
try { 
    $stmt = $db_found->prepare($sql);
    $stmt->bindValue(':lat', $lat);
    $stmt->bindValue(':lng', $lng);
    $stmt->bindValue(':grid', $grid);
    $w3wValue = $what3words . "<br>" . $crossroads;
    $stmt->bindValue(':w3w', $w3wValue);
    $commentsValue = $APRScomment . "--<br>Via APRS";
    $stmt->bindValue(':comments', $commentsValue);
    $stmt->bindValue(':city', $city);
    $stmt->bindValue(':county', $county);
    $stmt->bindValue(':state', $state);
    $stmt->bindValue(':recordID', $recordID);
    $stmt->execute();
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
   
// Update the TimeLog with the new information    
$sql2 = "INSERT INTO TimeLog 
        (timestamp, callsign, netID, comment)
        VALUES (NOW(), :callsign, :netID, :comment)
";
   
try { 
    $stmt = $db_found->prepare($sql2);
    // Bind parameters
    $stmt->bindValue(':callsign', $cs1);
    $stmt->bindValue(':netID', $nid);
    $stmt->bindValue(':comment', $deltax);

    if ($stmt->execute()) { 
        echo "sql2 executed successfully";
    } else {
        echo "sql2 execution failed";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>