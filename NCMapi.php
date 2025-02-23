
<?php
// NCMapi.php
// V3 Updated: 2024-06-06
// https://code.tutsplus.com/tutorials/how-to-build-a-simple-rest-api-in-php--cms-37000

// This code is an API for use in Apple Shortcuts
// Based on callsign it gets the address, city, state from the fcc table
// Then in geocodes the above into latitude and longitude
// Also returning the county and state information
// Then GridSquare.php calculates the six character gridsquare
// Curl is then used to convert lat/lon into the what3words address

ini_set('display_errors', 1); 
error_reporting(E_ALL);

require_once "dbConnectDtls.php";  // Access to MySQL
require_once "geocode.php";
require_once "GridSquare.php";

// The callsign is requested when the shortcut is executed    
$cs1 = $_GET['cs1'] ?? '';
$cs1 = strtoupper(filter_var($cs1, FILTER_SANITIZE_STRING));

// Is this a U.S. callsign?
$csArray = ['A', 'K', 'N', 'W'];

// If this a U.S. callsign do this
if (in_array(substr($cs1, 0, 1), $csArray, true)) {
    // Use the callsign to find the address in the fcc table 
    $sql = "
        SELECT CONCAT(address1, ' ', city, ' ', state, ' ', zip) AS addr,
               fccid, zip
          FROM netcontrolcp_fcc_amateur.en 
         WHERE callsign = :cs1
         LIMIT 0, 1
    ";
    
    $stmt = $db_found->prepare($sql);
    $stmt->bindValue(':cs1', $cs1);
    $stmt->execute();
    $result      = $stmt->fetch();
    $fulladdress = $result['addr'] ?? '';
    $fccid       = $result['fccid'] ?? '';
    $zip         = $result['zip'] ?? '';

    // Geocode the address to get the latitude & longitude 
    // And the county & state
    $koords    = geocode($fulladdress);
    $latitude  = $koords[0] ?? '';
    $longitude = $koords[1] ?? '';
    $county    = $koords[2] ?? '';
    $state     = $koords[3] ?? '';
    
    if ($state === '') {
        $state = 'Unknown';
    }

    // Use gridsquare.php to get the gridsquare	
    $gridd = gridsquare($latitude, $longitude);
    $grid = implode('', $gridd);
} else {
    // If not in U.S. then get from hamcall
    $url = 'https://hamcall.net/call?username=wa0tjt&password=tjt0aw52&rawlookupCSV=1&callsign='.$cs1.'&program=ncm';
    $lines_string = file_get_contents($url);
    $str = explode(",", $lines_string); 

    $name      = $str[5] ?? '';     
    $pieces    = explode(' ', $name);
    $Lname     = array_pop($pieces);
    $string    = explode(' ', $name, 2);  
    $Fname     = $name[1] ?? '';
    $country   = $str[8] ?? '';
    $latitude  = $str[19] ?? '';
    $longitude = $str[20] ?? '';
    $grid      = $str[21] ?? '';
    $email     = $str[28] ?? '';

    $home = "$latitude,$longitude,$grid,,$country";
}

// To get the what3words address we'll ask W3W via curl, using lat/lon
$curl = curl_init();

curl_setopt_array($curl, [
    CURLOPT_URL => "https://api.what3words.com/v3/convert-to-3wa?key=5WHIM4GD&coordinates=$latitude,$longitude&language=en&format=json",
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "GET",
]);

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
    echo "cURL Error #:" . $err;
} else {
    $w3w = json_decode($response, true);
    $what3words = $w3w['words'] ?? '';
    $country = $w3w['country'] ?? '';
}

// Now we have everything we need to update the stations table for this callsign
$sql2 = "
    UPDATE stations 
       SET home      = :home,
           latitude  = :latitude,
           longitude = :longitude,
           grid      = :grid,
           county    = :county,
           state     = :state,
           fccid     = :fccid,
           comment   = 'Update Stations w/Callsign Shortcut',
           dttm      = NOW(),
           zip       = :zip,
           country   = :country
     WHERE callsign = :cs1
";

$stmt = $db_found->prepare($sql2);
$stmt->bindValue(':home', "$latitude,$longitude,$grid,$county,$state");
$stmt->bindValue(':latitude', $latitude);
$stmt->bindValue(':longitude', $longitude);
$stmt->bindValue(':grid', $grid);
$stmt->bindValue(':county', $county);
$stmt->bindValue(':state', $state);
$stmt->bindValue(':fccid', $fccid);
$stmt->bindValue(':zip', $zip);
$stmt->bindValue(':country', $country);
$stmt->bindValue(':cs1', $cs1);
$stmt->execute();
?>

<!doctype html>
<html>
    <head>
    </head>
    <body>
        <?php echo "
            <h2>Stations update for call: $cs1</h2>
            <div>Coordinates: $latitude, $longitude </div>
            <div>grid: $grid, county: $county, state: $state, zip: $zip, country: $country  </div>";
        ?>
    </body>
</html>