<?php
/* save.php  */
/* V2 Updated: 2024-07-07 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

error_log("POST data: " . print_r($_POST, true));

require_once "dbConnectDtls.php";
require_once "GridSquare.php";
require_once "getRealIpAddr.php";

$rawdata = file_get_contents('php://input');
parse_str($rawdata, $data);

error_log("rawdata: " . $rawdata);
error_log("Received data from client:");
error_log("id: " . $data['id']);
error_log("value: " . $data['value']);

$idParts  = explode(':', $data['id']);
$column   = isset($idParts[0]) ? $idParts[0] : null;
$recordID = isset($idParts[1]) ? $idParts[1] : null;
$value    = isset($data['value']) ? $data['value'] : null;

$ipaddress = getRealIpAddr();

// Update the NetLog based on the column
if (isset($recordID) && isset($column) && isset($value)) {
    switch ($column) {
        case 'active':
            updateActive($recordID, $value, $ipaddress);
            break;
        case 'tactical':
            updateTactical($recordID, $value, $ipaddress);
            break;
        case 'mode':
            updateMode($recordID, $value, $ipaddress);
            break;
        case 'traffic':
            updateTraffic($recordID, $value, $ipaddress);
            break;
        case 'band':
            updateBand($recordID, $value, $ipaddress);
            break;
        case 'aprs_call':
            updateAprsCall($recordID, $value, $ipaddress);
            break;
        case 'team':
            updateTeam($recordID, $value, $ipaddress);
            break;
        case 'facility':
            updateFacility($recordID, $value, $ipaddress);
            break;
        case 'comments':
            updateComments($recordID, $value, $ipaddress);
            break;
        case in_array($column, ['county', 'city', 'state', 'grid', 'latitude', 'longitude', 'district', 'cat', 'section']):
            updateLocation($recordID, $column, $value, $ipaddress);
            break;
        case in_array($column, ['Fname', 'Lname', 'email', 'creds']):
            updateStationInfo($recordID, $column, $value);
            break;
        default:
            // Handle unknown column
            break;
    }

    // Update the NetLog with the new information
    $sql = "UPDATE NetLog SET $column = :value WHERE recordID = :recordID";
    $stmt = $db_found->prepare($sql);
    $stmt->bindValue(':value', $value, PDO::PARAM_STR);
    $stmt->bindValue(':recordID', $recordID, PDO::PARAM_STR);
    $stmt->execute();

    // Trigger SSE update for all changes
    triggerSSEUpdate($recordID, $column, $value);
} else {
    error_log("recordID: " . $recordID);
    error_log("column: " . $column);
    error_log("value: " . $value);
    // Handle missing required variables
    echo "Missing required variables";
    exit;
}

function updateActive($recordID, $value, $ipaddress) {
    global $db_found;

    $sql  = "SELECT ID, netID, callsign FROM NetLog WHERE recordID = :recordID";
    $stmt = $db_found->prepare($sql);
    $stmt->bindValue(':recordID', $recordID, PDO::PARAM_STR);
    $stmt->execute();
    $row  = $stmt->fetch(PDO::FETCH_ASSOC);

    $netID = $row['netID'];
    $ID    = $row['ID'];
    $cs1   = $row['callsign'];

    $sql  = "INSERT INTO TimeLog (recordID, ID, netID, callsign, comment, timestamp, ipaddress) 
            VALUES (:recordID, :ID, :netID, :cs1, :comment, NOW(), :ipaddress)";
    $stmt = $db_found->prepare($sql);
    $stmt->bindValue(':recordID', $recordID, PDO::PARAM_STR);
    $stmt->bindValue(':ID', $ID, PDO::PARAM_STR);
    $stmt->bindValue(':netID', $netID, PDO::PARAM_STR);
    $stmt->bindValue(':cs1', $cs1, PDO::PARAM_STR);
    $stmt->bindValue(':comment', "Status change: $value", PDO::PARAM_STR);
    $stmt->bindValue(':ipaddress', $ipaddress, PDO::PARAM_STR);
    $stmt->execute();

    if (in_array($value, ["OUT", "Out", "BRB", "QSY", "In-Out"])) {
        $sql  = "UPDATE NetLog 
                SET timeout = NOW(),
                    timeonduty = (timestampdiff(SECOND, logdate, NOW()) + timeonduty),
                    status = 1
                WHERE recordID = :recordID";
        $stmt = $db_found->prepare($sql);
        $stmt->bindValue(':recordID', $recordID, PDO::PARAM_STR);
        $stmt->execute();
    } elseif ($value == "In") {
        $sql  = "UPDATE NetLog 
                SET timeout = NULL,
                    logdate = CASE
                        WHEN pb = 1 AND logdate = 0 THEN NOW()
                        ELSE logdate
                    END,
                    status = 0,
                    logdate = NOW()
                WHERE recordID = :recordID";
        $stmt = $db_found->prepare($sql);
        $stmt->bindValue(':recordID', $recordID, PDO::PARAM_STR);
        $stmt->execute();
    }
    
    triggerSSEUpdate($recordID, 'active', $value);
}

function updateTactical($recordID, $value, $ipaddress) {
    global $db_found;

    if ($value != '' && $value != 'DELETE') {
        $sql  = "SELECT ID, netID, callsign, tactical FROM NetLog WHERE recordID = :recordID";
        $stmt = $db_found->prepare($sql);
        $stmt->bindValue(':recordID', $recordID, PDO::PARAM_STR);
        $stmt->execute();
        $row  = $stmt->fetch(PDO::FETCH_ASSOC);

        $netID = $row['netID'];
        $ID    = $row['ID'];
        $cs1   = $row['callsign'];

        $sql  = "INSERT INTO TimeLog (recordID, ID, netID, callsign, comment, timestamp, ipaddress) 
                VALUES (:recordID, :ID, :netID, :cs1, :comment, NOW(), :ipaddress)";
        $stmt = $db_found->prepare($sql);
        $stmt->bindValue(':recordID', $recordID, PDO::PARAM_STR);
        $stmt->bindValue(':ID', $ID, PDO::PARAM_STR);
        $stmt->bindValue(':netID', $netID, PDO::PARAM_STR);
        $stmt->bindValue(':cs1', $cs1, PDO::PARAM_STR);
        $stmt->bindValue(':comment', "Tactical set to: $value", PDO::PARAM_STR);
        $stmt->bindValue(':ipaddress', $ipaddress, PDO::PARAM_STR);
        $stmt->execute();
    } elseif ($value == 'DELETE') {
        $sql  = "SELECT netID, ID, callsign FROM NetLog WHERE recordID = :recordID";
        $stmt = $db_found->prepare($sql);
        $stmt->bindValue(':recordID', $recordID, PDO::PARAM_STR);
        $stmt->execute();
        $row  = $stmt->fetch(PDO::FETCH_ASSOC);

        $netID = $row['netID'];
        $ID    = $row['ID'];
        $cs1   = $row['callsign'];

        $sql  = "INSERT INTO TimeLog (recordID, timestamp, ID, netID, callsign, comment, ipaddress)
                VALUES (:recordID, NOW(), :ID, :netID, 'GENCOMM', :comment, :ipaddress)";
        $stmt = $db_found->prepare($sql);
        $stmt->bindValue(':recordID', $recordID, PDO::PARAM_STR);
        $stmt->bindValue(':ID', $ID, PDO::PARAM_STR);
        $stmt->bindValue(':netID', $netID, PDO::PARAM_STR);
        $stmt->bindValue(':comment', "The call $cs1 with this ID was deleted", PDO::PARAM_STR);
        $stmt->bindValue(':ipaddress', $ipaddress, PDO::PARAM_STR);
        $stmt->execute();

        $sql  = "DELETE FROM NetLog WHERE recordID = :recordID";
        $stmt = $db_found->prepare($sql);
        $stmt->bindValue(':recordID', $recordID, PDO::PARAM_STR);
        $stmt->execute();
    }
    
    triggerSSEUpdate($recordID, 'tactical', $value);
}

function updateMode($recordID, $value, $ipaddress) {
    global $db_found;

    $sql  = "SELECT ID, netID, callsign FROM NetLog WHERE recordID = :recordID";
    $stmt = $db_found->prepare($sql);
    $stmt->bindValue(':recordID', $recordID, PDO::PARAM_STR);
    $stmt->execute();
    $row  = $stmt->fetch(PDO::FETCH_ASSOC);

    $netID = $row['netID'];
    $ID    = $row['ID'];
    $cs1   = $row['callsign'];

    $sql  = "INSERT INTO TimeLog (recordID, ID, netID, callsign, comment, timestamp, ipaddress) 
            VALUES (:recordID, :ID, :netID, :cs1, :comment, NOW(), :ipaddress)";
    $stmt = $db_found->prepare($sql);
    $stmt->bindValue(':recordID', $recordID, PDO::PARAM_STR);
    $stmt->bindValue(':ID', $ID, PDO::PARAM_STR);
    $stmt->bindValue(':netID', $netID, PDO::PARAM_STR);
    $stmt->bindValue(':cs1', $cs1, PDO::PARAM_STR);
    $stmt->bindValue(':comment', "Mode set to: $value", PDO::PARAM_STR);
    $stmt->bindValue(':ipaddress', $ipaddress, PDO::PARAM_STR);
    $stmt->execute();
    
    triggerSSEUpdate($recordID, 'mode', $value);
}

function updateTraffic($recordID, $value, $ipaddress) {
    global $db_found;

    $sql  = "SELECT ID, netID, callsign FROM NetLog WHERE recordID = :recordID";
    $stmt = $db_found->prepare($sql);
    $stmt->bindValue(':recordID', $recordID, PDO::PARAM_STR);
    $stmt->execute();
    $row  = $stmt->fetch(PDO::FETCH_ASSOC);

    $netID = $row['netID'];
    $ID    = $row['ID'];
    $cs1   = $row['callsign'];

    $sql  = "INSERT INTO TimeLog (recordID, ID, netID, callsign, comment, timestamp, ipaddress) 
            VALUES (:recordID, :ID, :netID, :cs1, :comment, NOW(), :ipaddress)";
    $stmt = $db_found->prepare($sql);
    $stmt->bindValue(':recordID', $recordID, PDO::PARAM_STR);
    $stmt->bindValue(':ID', $ID, PDO::PARAM_STR);
    $stmt->bindValue(':netID', $netID, PDO::PARAM_STR);
    $stmt->bindValue(':cs1', $cs1, PDO::PARAM_STR);
    $stmt->bindValue(':comment', "Traffic set to: $value", PDO::PARAM_STR);
    $stmt->bindValue(':ipaddress', $ipaddress, PDO::PARAM_STR);
    $stmt->execute();
    
    triggerSSEUpdate($recordID, 'traffic', $value);
}

function updateBand($recordID, $value, $ipaddress) {
    global $db_found;

    $sql  = "SELECT ID, netID, callsign FROM NetLog WHERE recordID = :recordID";
    $stmt = $db_found->prepare($sql);
    $stmt->bindValue(':recordID', $recordID, PDO::PARAM_STR);
    $stmt->execute();
    $row  = $stmt->fetch(PDO::FETCH_ASSOC);

    $netID = $row['netID'];
    $ID    = $row['ID'];
    $cs1   = $row['callsign'];

    $sql  = "INSERT INTO TimeLog (recordID, ID, netID, callsign, comment, timestamp, ipaddress) 
            VALUES (:recordID, :ID, :netID, :cs1, :comment, NOW(), :ipaddress)";
    $stmt = $db_found->prepare($sql);
    $stmt->bindValue(':recordID', $recordID, PDO::PARAM_STR);
    $stmt->bindValue(':ID', $ID, PDO::PARAM_STR);
    $stmt->bindValue(':netID', $netID, PDO::PARAM_STR);
    $stmt->bindValue(':cs1', $cs1, PDO::PARAM_STR);
    $stmt->bindValue(':comment', "Band set to: $value", PDO::PARAM_STR);
    $stmt->bindValue(':ipaddress', $ipaddress, PDO::PARAM_STR);
    $stmt->execute();
    
    triggerSSEUpdate($recordID, 'band', $value);
}

function updateAprsCall($recordID, $value, $ipaddress) {
    global $db_found;

    $sql = "SELECT ID, netID, callsign, latitude, longitude FROM NetLog WHERE recordID = :recordID";
    $stmt = $db_found->prepare($sql);
    $stmt->bindValue(':recordID', $recordID, PDO::PARAM_STR);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    $netID = $row['netID'];
    $ID = $row['ID'];
    $cs1 = $row['callsign'];
    $lat = $row['latitude'];
    $lng = $row['longitude'];

    $sql = "INSERT INTO TimeLog (recordID, ID, netID, callsign, comment, timestamp, latitude, longitude, ipaddress) 
            VALUES (:recordID, :ID, :netID, :cs1, :comment, NOW(), :latitude, :longitude, :ipaddress)";
    $stmt = $db_found->prepare($sql);
    $stmt->bindValue(':recordID', $recordID, PDO::PARAM_STR);
    $stmt->bindValue(':ID', $ID, PDO::PARAM_STR);
    $stmt->bindValue(':netID', $netID, PDO::PARAM_STR);
    $stmt->bindValue(':cs1', $cs1, PDO::PARAM_STR);
    $stmt->bindValue(':comment', "APRS_CALL set to: $value", PDO::PARAM_STR);
    $stmt->bindValue(':latitude', $lat, PDO::PARAM_STR);
    $stmt->bindValue(':longitude', $lng, PDO::PARAM_STR);
    $stmt->bindValue(':ipaddress', $ipaddress, PDO::PARAM_STR);
    $stmt->execute();
    
    triggerSSEUpdate($recordID, 'aprs_call', $value);
}

function updateTeam($recordID, $value, $ipaddress) {
    global $db_found;

    $sql = "SELECT ID, netID, callsign FROM NetLog WHERE recordID = :recordID";
    $stmt = $db_found->prepare($sql);
    $stmt->bindValue(':recordID', $recordID, PDO::PARAM_STR);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    $netID = $row['netID'];
    $ID = $row['ID'];
    $cs1 = $row['callsign'];

    $sql = "INSERT INTO TimeLog (recordID, ID, netID, callsign, comment, timestamp, ipaddress) 
            VALUES (:recordID, :ID, :netID, :cs1, :comment, NOW(), :ipaddress)";
    $stmt = $db_found->prepare($sql);
    $stmt->bindValue(':recordID', $recordID, PDO::PARAM_STR);
    $stmt->bindValue(':ID', $ID, PDO::PARAM_STR);
    $stmt->bindValue(':netID', $netID, PDO::PARAM_STR);
    $stmt->bindValue(':cs1', $cs1, PDO::PARAM_STR);
    $stmt->bindValue(':comment', "Team set to: $value", PDO::PARAM_STR);
    $stmt->bindValue(':ipaddress', $ipaddress, PDO::PARAM_STR);
    $stmt->execute();
    
    triggerSSEUpdate($recordID, 'team', $value);
}

function updateFacility($recordID, $value, $ipaddress) {
    global $db_found;

    $sql = "SELECT ID, netID, callsign FROM NetLog WHERE recordID = :recordID";
    $stmt = $db_found->prepare($sql);
    $stmt->bindValue(':recordID', $recordID, PDO::PARAM_STR);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    $netID = $row['netID'];
    $ID = $row['ID'];
    $cs1 = $row['callsign'];

    $sql = "INSERT INTO TimeLog (recordID, ID, netID, callsign, comment, timestamp, ipaddress) 
            VALUES (:recordID, :ID, :netID, :cs1, :comment, NOW(), :ipaddress)";
    $stmt = $db_found->prepare($sql);
    $stmt->bindValue(':recordID', $recordID, PDO::PARAM_STR);
    $stmt->bindValue(':ID', $ID, PDO::PARAM_STR);
    $stmt->bindValue(':netID', $netID, PDO::PARAM_STR);
    $stmt->bindValue(':cs1', $cs1, PDO::PARAM_STR);
    $stmt->bindValue(':comment', "Facility set to: $value", PDO::PARAM_STR);
    $stmt->bindValue(':ipaddress', $ipaddress, PDO::PARAM_STR);
    $stmt->execute();
    
    triggerSSEUpdate($recordID, 'facility', $value);
}

function updateComments($recordID, $value, $ipaddress) {
    global $db_found;

    $value = str_replace("'", "''", $value);

    $sql = "SELECT ID, netID, callsign, home FROM NetLog WHERE recordID = :recordID";
    $stmt = $db_found->prepare($sql);
    $stmt->bindValue(':recordID', $recordID, PDO::PARAM_STR);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    $netID = $row['netID'];
    $ID = $row['ID'];
    $cs1 = $row['callsign'];
    $home = $row['home'];

    $deltaX = 'LOC&#916;';
    $Varray = array("@home", "@ home", "@  home", "at home", "gone home,", "headed home", "going home", "home");
    if (in_array(strtolower($value), $Varray)) {
        $latitude = explode(',', $home)[0];
        $longitude = explode(',', $home)[1];
        $grid = explode(',', $home)[2];
        $county = explode(',', $home)[3];
        $state = explode(',', $home)[4];
        $value2 = "$deltaX:COM:@home, reset to home coordinates ($home)";

        $sql = "UPDATE NetLog 
                SET latitude = :latitude, longitude = :longitude, 
                    grid = :grid, county = :county, state = :state, w3w = '',
                    delta = 'Y'
                WHERE recordID = :recordID";
        $stmt = $db_found->prepare($sql);
        $stmt->bindValue(':latitude', $latitude, PDO::PARAM_STR);
        $stmt->bindValue(':longitude', $longitude, PDO::PARAM_STR);
        $stmt->bindValue(':grid', $grid, PDO::PARAM_STR);
        $stmt->bindValue(':county', $county, PDO::PARAM_STR);
        $stmt->bindValue(':state', $state, PDO::PARAM_STR);
        $stmt->bindValue(':recordID', $recordID, PDO::PARAM_STR);
        $stmt->execute();
    } else {
        $value2 = $value;
    }

    $sql = "INSERT INTO TimeLog (recordID, ID, netID, callsign, comment, timestamp, ipaddress) 
            VALUES (:recordID, :ID, :netID, :cs1, :comment, NOW(), :ipaddress)";
    $stmt = $db_found->prepare($sql);
    $stmt->bindValue(':recordID', $recordID, PDO::PARAM_STR);
    $stmt->bindValue(':ID', $ID, PDO::PARAM_STR);
    $stmt->bindValue(':netID', $netID, PDO::PARAM_STR);
    $stmt->bindValue(':cs1', $cs1, PDO::PARAM_STR);
    $stmt->bindValue(':comment', $value2, PDO::PARAM_STR);
    $stmt->bindValue(':ipaddress', $ipaddress, PDO::PARAM_STR);
    $stmt->execute();
    
    triggerSSEUpdate($recordID, 'comments', $value);
}

function updateLocation($recordID, $column, $value, $ipaddress) {
    global $db_found;

    if ($column == "cat") {
        $column = "TRFK-FOR";
        $value = strtoupper($value);
    }

    $sql = "SELECT ID, netID, callsign FROM NetLog WHERE recordID = :recordID";
    $stmt = $db_found->prepare($sql);
    $stmt->bindValue(':recordID', $recordID, PDO::PARAM_STR);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    $netID = $row['netID'];
    $ID = $row['ID'];
    $cs1 = $row['callsign'];

    $deltaX = "$column Change";
    switch ($column) {
        case "grid":
            $deltaX = 'LOC&#916:Grid: ' . $value . ' This also changed LAT/LON values';
            break;
        case "state":
            $deltaX = 'LOC&#916:State: ' . $value;
            break;
        case "city":
            $deltaX = 'LOC&#916:City: ' . $value;
            break;
        case "county":
            $deltaX = 'LOC&#916:County: ' . $value;
            break;
        case "district":
            $deltaX = 'LOC&#916:District: ' . $value;
            break;
        case "latitude":
            $deltaX = 'LOC&#916:LAT: ' . $value . ' This also changed the grid value';
            break;
        case "longitude":
            $deltaX = 'LOC&#916:LON: ' . $value . ' This also changed the grid value';
            break;
    }

    $sql = "INSERT INTO TimeLog (recordID, ID, netID, callsign, comment, timestamp, ipaddress) 
            VALUES (:recordID, :ID, :netID, :cs1, :comment, NOW(), :ipaddress)";
    $stmt = $db_found->prepare($sql);
    $stmt->bindValue(':recordID', $recordID, PDO::PARAM_STR);
    $stmt->bindValue(':ID', $ID, PDO::PARAM_STR);
    $stmt->bindValue(':netID', $netID, PDO::PARAM_STR);
    $stmt->bindValue(':cs1', $cs1, PDO::PARAM_STR);
    $stmt->bindValue(':comment', $deltaX, PDO::PARAM_STR);
    $stmt->bindValue(':ipaddress', $ipaddress, PDO::PARAM_STR);
    $stmt->execute();

    if ($column == "TRFK-FOR") {
        $column = "cat";
    }
    
    triggerSSEUpdate($recordID, $column, $value);
}

function updateStationInfo($recordID, $column, $value) {
    global $db_found;
    
    if (empty($recordID) || empty($column) || empty($value)) {
        error_log("Error: Missing or empty parameters in updateStationInfo");
        error_log("recordID: " . var_export($recordID, true));
        error_log("column: " . var_export($column, true));
        error_log("value: " . var_export($value, true));
        return;
    }
    
    error_log("updateStationInfo called with parameters:");
    error_log("recordID: " . $recordID);
    error_log("column: " . $column);
    error_log("value: " . $value);
    
    triggerSSEUpdate($recordID, $column, $value);
}

function sendSSEEvent($event, $recordID, $column, $value) {
    ob_clean();
    header('Content-Type: text/event-stream');
    header('Cache-Control: no-cache');
    echo "event: $event\n";
    echo "data: " . json_encode(['recordID' => $recordID, 'column' => $column, 'value' => $value]) . "\n\n";
    flush();
}

function triggerSSEUpdate($recordID, $column, $value) {
    sendSSEEvent('update', $recordID, $column, $value);

    $sseUrl = "sse.php?recordID=" . urlencode($recordID) . "&column=" . urlencode($column) . "&value=" . urlencode($value);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $sseUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
}