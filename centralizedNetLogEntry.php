<?php
// centralizedNetLogEntry.php
// This file is meant to do the input to NetLog 
// Written: 2024-08-25
// v2 Updated: 2024-08-25

/*
    This centralized approach offers several benefits:

Single Entry Point: All NetLog entries go through one function, ensuring consistent processing.
Modular Design: Each step of the process is in its own function, making it easier to maintain and debug.
Consistent Duplicate Checking: The callsignDupeCheck is performed before any insertion.
Transaction Management: The entire process is wrapped in a database transaction, ensuring data integrity.
Flexible Input Handling: It can handle both JSON and form-encoded input.
Comprehensive Error Handling: All exceptions are caught and logged.

To use this:

Replace your existing addToNet.php and checkForNewCall.php with this new file.
Update any JavaScript or other code that was calling those files to now call centralizedNetLogEntry.php.
Ensure that the input sent to this new file includes all necessary data (netID, callsign, netcall, etc.).

This approach should help eliminate the issues with premature insertions and inconsistent duplicate checking. It also provides a single point for adding any future enhancements or checks to the NetLog entry process.
*/

error_log("Entering file: " . __FILE__);

require_once "dbConnectDtls.php";
require_once "geocode.php";
require_once "GridSquare.php";
require_once "callsignDupeChecker.php";

function centralizedNetLogEntry($input) {
    global $db_found;
    
    $response = ['success' => false, 'message' => '', 'data' => null];
    
    try {
        if (!$db_found) {
            throw new Exception("Database connection failed");
        }

        $db_found->beginTransaction();

        // Extract and sanitize input
        $netID = $input['netID'] ?? 0;
        $callsign = strtoupper($input['callsign'] ?? '');
        $netcall = $input['netcall'] ?? '';
        $email = $input['email'] ?? '';
        $name = $input['name'] ?? '';
        $pb = isset($input['pb']) && $input['pb'] == 1 ? 1 : 0;

        // Perform duplicate check
        if (callsignDupeCheck($db_found, $netID, $callsign)) {
            throw new Exception("Callsign already exists in this net");
        }

        // Check if callsign exists in stations table
        $stationData = getStationData($db_found, $callsign);
        
        if (!$stationData) {
            // If not in stations table, get FCC data
            $fccData = getFCCData($db_found, $callsign);
            $stationData = createStationData($fccData, $callsign, $email, $name);
        }

        // Insert or update stations table
        upsertStationsTable($db_found, $stationData);

        // Insert into NetLog
        $netLogId = insertIntoNetLog($db_found, $netID, $stationData, $netcall, $pb);

        // Insert into TimeLog
        insertIntoTimeLog($db_found, $netLogId, $netID, $callsign);

        $db_found->commit();

        $response = [
            'success' => true,
            'message' => 'Entry added successfully',
            'data' => getNetLogRowData($db_found, $netLogId)
        ];

    } catch (Exception $e) {
        if ($db_found->inTransaction()) {
            $db_found->rollBack();
        }
        error_log("Error in centralizedNetLogEntry: " . $e->getMessage());
        $response = ['success' => false, 'message' => $e->getMessage()];
    } // End try-catch block

    return $response;
} // End centralizedNetLogEntry(

function getStationData($db, $callsign) {
    $stmt = $db->prepare("SELECT * FROM stations WHERE callsign = :callsign AND active_call = 'y'");
    $stmt->execute([':callsign' => $callsign]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getFCCData($db, $callsign) {
// replaces getFCCrecord.php
    try {
        $fccsql = $db->prepare("
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
        $fccsql->execute([$callsign]);
        
        if ($fccsql->rowCount() > 0) {
            $result = $fccsql->fetch(PDO::FETCH_ASSOC);
            
            // Process the result
            $fccData = [
                'fccid' => $result['fccid'],
                'Lname' => ucfirst(strtolower($result['last'])),
                'Fname' => ucfirst(strtolower($result['first'])),
                'state' => $result['state'],
                'city' => $result['city'],
                'zip' => $result['zip'],
                'address' => $result['address'],
            ];
            
            // Geocode the address
            $koords = geocode($fccData['address']);
            $fccData['latitude'] = $koords[0];
            $fccData['longitude'] = $koords[1];
            $fccData['county'] = $koords[2];
            $fccData['state'] = $koords[3] ?: $fccData['state']; // Use FCC state if geocode fails
            
            // Generate grid square
            $gridd = gridsquare($fccData['latitude'], $fccData['longitude']);
            $fccData['grid'] = implode('', $gridd);
            
            $fccData['home'] = "{$fccData['latitude']},{$fccData['longitude']},{$fccData['grid']},{$fccData['county']},{$fccData['state']}";
            $fccData['comments'] = "First Log In";
            
            return $fccData;
        } else {
            return ['comments' => "No FCC Record"];
        }
    } catch (PDOException $e) {
        error_log("Error in getFCCData: " . $e->getMessage());
        return ['comments' => "Error retrieving FCC record"];
    }
} // End getFCCData()

function createStationData($db, $callsign) {
    // First, check the stations table
    $stationData = getStationData($db, $callsign);
    
    if (!$stationData) {
        // If not in stations table, get FCC data
        $fccData = getFCCData($db, $callsign);
        
        if ($fccData && !isset($fccData['comments'])) {
            // Create new station entry from FCC data
            $stationData = [
                'callsign' => $callsign,
                'Fname' => $fccData['Fname'],
                'Lname' => $fccData['Lname'],
                'latitude' => $fccData['latitude'],
                'longitude' => $fccData['longitude'],
                'grid' => $fccData['grid'],
                'county' => $fccData['county'],
                'state' => $fccData['state'],
                'city' => $fccData['city'],
                'home' => $fccData['home'],
                // Add any other fields you store in the stations table
            ];
            
            // Insert new data into stations table
            insertIntoStations($db, $stationData);
        } else {
            // No FCC data found, create minimal entry
            $stationData = [
                'callsign' => $callsign,
                'comments' => "No Record Found",
                // Add any other default fields
            ];
            
            // You might want to insert this minimal data into stations table as well
            insertIntoStations($db, $stationData);
        }
    }
    
    return $stationData;
} // end createStationData()

function upsertStationsTable($db, $stationData) {
    // Logic to insert or update stations table
    // ...
} // End upsertStationsTable()

function insertIntoNetLog($db, $netID, $stationData, $netcall, $pb) {
    // Your NetLog insertion logic here
    // ...
} // End insertIntoNetLog()

function insertIntoTimeLog($db, $netLogId, $netID, $callsign) {
    // Your TimeLog insertion logic here
    // ...
} // End insertIntoTimeLog()

function getNetLogRowData($db, $netLogId) {
    // Fetch and return the full row data from NetLog
    // ...
} // End getNetLogRowData()

// Handle the incoming request
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST; // Fall back to $_POST if JSON parsing fails
}

$result = centralizedNetLogEntry($input);

header('Content-Type: application/json');
echo json_encode($result);