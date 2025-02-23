<?php
// getFCCrecord.php
// V3 Updated: 2024-08-29

error_log("Entering file: " . __FILE__);

require_once "dbFunctions.php";
require_once "geocode.php";
require_once "GridSquare.php";

// Initialize variables
$comments = "";
$firstLogIn = 0;
$Lname = $Fname = $state = $city = $zip = $address = $fccid = $county = $grid = $home = "";
$latitude = $longitude = 0;

// Check if callsign is set (it should be passed from processCheckIn.php)
if (!isset($callsign) || empty($callsign)) {
    error_log("Error in getFCCrecord.php: callsign variable is not set or empty");
    $comments = "Invalid callsign";
    return;
}

// Set $cs1 to $callsign if it's not already set
if (!isset($cs1) || empty($cs1)) {
    $cs1 = $callsign;
}

$csbase = $cs1;

try {
    if (!isset($db_found) || !($db_found instanceof PDO)) {
        throw new Exception("Database connection not found or invalid");
    }

    $fccsql = $db_found->prepare("
        SELECT last,
           first,
           state,
           CONCAT_WS(' ', address1, city, state, zip) AS address,
           fccid,
           city,
           zip
        FROM netcontrolcp_fcc_amateur.en
        WHERE callsign = ?
        ORDER BY fccid DESC
        LIMIT 1;
    ");
    $fccsql->bindValue(1, $csbase);
    $fccsql->execute();
    
    if ($fccsql->rowCount() > 0) {
        $result = $fccsql->fetch(PDO::FETCH_ASSOC);
        $fccid = $result['fccid'];
        $Lname = ucfirst(strtolower($result['last']));
        $Fname = ucfirst(strtolower($result['first']));
        $state2 = $result['state'];
        $city = $result['city'];
        $zip = $result['zip'];
        $address = $result['address'];
        $firstLogIn = 1;
        
        $koords = geocode($address);
        $latitude = $koords[0];
        $longitude = $koords[1];
        $county = $koords[2];
        $state = $koords[3];
        
        if (empty($state)) {
            $state = $state2;
        }
        
        $gridd = gridsquare($latitude, $longitude);
        $grid = "{$gridd[0]}{$gridd[1]}{$gridd[2]}{$gridd[3]}{$gridd[4]}{$gridd[5]}";
        $home = "$latitude,$longitude,$grid,$county,$state";
        $comments = "First Log In";
        
        include "insertToStations.php";
    } else {
        $comments = "No FCC Record";
        error_log("No FCC record found for callsign: $csbase");
    }
} catch (PDOException $e) {
    error_log("PDOException in getFCCrecord.php: " . $e->getMessage());
    $comments = "Error retrieving FCC record";
} catch (Exception $e) {
    error_log("Exception in getFCCrecord.php: " . $e->getMessage());
    $comments = "Error processing FCC record";
}

// Log the result
error_log("getFCCrecord.php result for $csbase: $comments");

// Return an array with all the gathered information
return [
    'Lname' => $Lname,
    'Fname' => $Fname,
    'state' => $state,
    'city' => $city,
    'zip' => $zip,
    'address' => $address,
    'fccid' => $fccid,
    'county' => $county,
    'grid' => $grid,
    'home' => $home,
    'latitude' => $latitude,
    'longitude' => $longitude,
    'firstLogIn' => $firstLogIn,
    'comments' => $comments
];
?>