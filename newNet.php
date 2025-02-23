<?php
// newNet.php
// This PHP is called by NetManager-p2.js
// V2 Updated: 2024-08-06

header('Content-Type: application/json');

//error_log('Top of newNet.php');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'dbConnectDtls.php';
require_once 'getRealIpAddr.php';

if (!isset($db_found) || !$db_found) {
    error_log("newNet.php: Database connection failed");
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

try {
    $db_found->beginTransaction();

    //error_log("newNet.php: Starting new net creation process");

    // Get the JSON data from the request body
    $jsonData = file_get_contents('php://input');
    //error_log("In newNet.php Received JSON data: " . $jsonData);

    // Decode the JSON data into an associative array
    $data = json_decode($jsonData, true);
    //error_log("In newNet.php Decoded JSON data: " . print_r($data, true));

    // Check if the 'q' key exists in the decoded data
    if (!isset($data['q'])) {
        throw new Exception("Missing 'q' parameter in the request data");
    }

    $str = $data['q'];
    //error_log("In newNet.php Extracted 'q' parameter: " . $str);

    // Explode the 'q' parameter by colons
    $parts = explode(":", $str);
    //error_log("In newNet.php Exploded 'q' parameter: " . print_r($parts, true));

    if (count($parts) < 6) {
        throw new Exception("Invalid input: not enough parameters");
    }

    $cs1        = trim($parts[0] ?? '');
    $netcall    = trim($parts[1] ?? '');
    $newnetnm   = trim($parts[2] ?? '');
    $frequency  = trim($parts[3] ?? '');
    $subNetOfID = trim($parts[4] ?? '');
    $netKind    = trim($parts[5] ?? '');

    if (empty($cs1) || empty($netcall) || empty($newnetnm) || empty($frequency) || empty($netKind)) {
        throw new Exception("Invalid input: missing required parameters");
    }

    $cs1     = strtoupper($cs1);
    $netcall = strtoupper($netcall);
    
    $activity = $newnetnm . " " . $netKind;
    $pbspot   = isset($parts[6]) && $parts[6] == '1' ? 'PB' : '';
    
    //error_log("In newNet.php Activity: " . $activity);
    //error_log("In newNet.php PBspot: " . $pbspot);

    // Get the next netID from NetLog
    $stmt = $db_found->prepare("SELECT MAX(netID) + 1 FROM NetLog");
    $stmt->execute();
    $newNetID = $stmt->fetchColumn();

    // Retrieve station information
    $stmt2 = $db_found->prepare("
        SELECT MAX(recordID) AS maxID, MAX(id) as newid, id, Fname, Lname, creds, email, latitude, longitude,
               grid, county, state, district, home, phone, tactical, city
        FROM stations
        WHERE callsign = :cs1
        LIMIT 1
    ");
    $stmt2->bindParam(':cs1', $cs1);
    $stmt2->execute();
    $result = $stmt2->fetch(PDO::FETCH_ASSOC);

    // Extract the necessary information from $result
    $maxID     = $result['maxID'];
    $id        = $result['id'] ?? $result['newid'];
    $Fname     = ucwords(strtolower($result['Fname'] ?? ''));
    $Lname     = ucwords(strtolower($result['Lname'] ?? ''));
    $email     = $result['email'] ?? '';
    $latitude  = $result['latitude'] ?? '';
    $longitude = $result['longitude'] ?? '';
    $grid      = $result['grid'] ?? '';
    $county    = ucwords(strtolower($result['county'] ?? ''));
    $state     = $result['state'] ?? '';
    $district  = $result['district'] ?? '';
    $home      = $result['home'] ?? '';
    $phone     = $result['phone'] ?? '';
    $creds     = $result['creds'] ?? '';
    $city      = $result['city'] ?? '';

    $Lname  = str_replace("'", "\\'", $Lname);
    $firstLogIn = 0;

    if (!$maxID) {
        $id = $db_found->query("SELECT MAX(ID) + 1 FROM stations")->fetchColumn();
    }

    $open = date('Y-m-d H:i:s');
    $statusValue = $pbspot ? 'OUT' : 'In';
    $timeLogIn = $pbspot ? 0 : $open;
    $PBcomment = $pbspot ? 'Pre-Build Template Net for use at a later date' : null;

    // Insert into NetLog
    $stmt = $db_found->prepare("
        INSERT INTO NetLog (netcontrol, active, callsign, Fname, Lname, activity, tactical, id, netID, grid, 
            latitude, longitude, creds, email, comments, 
            frequency, subNetOfID, logdate, netcall, state, 
            county, city, district, pb, tt, 
            firstLogin, home, testnet, phone)
        VALUES ('PRM', :statusValue, :cs1, :Fname, :Lname, 
                :activity, 'Net', :id, :newNetID, :grid, 
                :latitude, :longitude, :creds, :email, 'Opened NCM', 
                :frequency, :subNetOfID, :logdate, :netcall, :state,
                :county, :city, :district, :pb, '00',
                :firstLogIn, :home, :testnet, :phone)
    ");
    $stmt->execute([
        ':statusValue' => $statusValue,
        ':cs1' => $cs1,
        ':Fname' => $Fname,
        ':Lname' => $Lname,
        ':activity' => $activity,
        ':id' => $id,
        ':newNetID' => $newNetID,
        ':grid' => $grid,
        ':latitude' => $latitude,
        ':longitude' => $longitude,
        ':creds' => $creds,
        ':email' => $email,
        ':frequency' => $frequency,
        ':subNetOfID' => $subNetOfID,
        ':logdate' => $open,
        ':netcall' => $netcall,
        ':state' => $state,
        ':county' => $county,
        ':city' => $city,
        ':district' => $district,
        ':pb' => $pbspot ? 1 : 0,
        ':firstLogIn' => $firstLogIn,
        ':home' => $home,
        ':testnet' => 'n',
        ':phone' => $phone
    ]);

    //error_log("newNet.php: Inserted new net into NetLog with netID: " . $newNetID);

    // Insert into TimeLog
    $ipaddress = getRealIpAddr();
    $comment = "$Fname $Lname Opened the $pbspot net from $ipaddress on $frequency";
    if ($subNetOfID > 0) {
        $comment .= ". Opened as a subnet of #$subNetOfID.";
    }

    $stmt1 = $db_found->prepare("
        INSERT INTO TimeLog (recordID, ID, netID, callsign, comment, ipaddress, latitude, longitude)
        VALUES (:recordID, :id, :netID, :callsign, 
                :comment, :ipaddress, :latitude, :longitude)
    ");
    $stmt1->execute([
        ':recordID' => $maxID,
        ':id' => $id,
        ':netID' => $newNetID,
        ':callsign' => $cs1,
        ':comment' => $comment,
        ':ipaddress' => $ipaddress,
        ':latitude' => $latitude,
        ':longitude' => $longitude
    ]);

    //error_log("newNet.php: Inserted new entry into TimeLog for netID: " . $newNetID);

    $db_found->commit();

    // Return the new netID, netcall, and activity
    $response = [
        'success' => true,
        'netID' => $newNetID,
        'netcall' => $netcall,
        'activity' => $activity
    ];
    //error_log("Sending response: " . json_encode($response));
    echo json_encode($response);

} catch (Exception $e) {
    if (isset($db_found)) {
        $db_found->rollBack();
    }
    //error_log("Error in newNet.php: " . $e->getMessage());
    http_response_code(500);
    //echo json_encode(['error' => 'Internal server error: ' . $e->getMessage()]);
}
?>