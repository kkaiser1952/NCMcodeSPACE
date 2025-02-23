<?php
// addToNet.php
// V2 Updated: 2024-06-27

error_log("Entering file: " . __FILE__);

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "dbConnectDtls.php";

function logDebug($message) {
    error_log(date('Y-m-d H:i:s') . " - DEBUG: " . $message);
}

$input = json_decode(file_get_contents('php://input'), true);
logDebug("Received input in addToNet.php: " . print_r($input, true));

$response = ['success' => false, 'message' => '', 'data' => null];

if (isset($input['netID']) && isset($input['callsign']) && isset($input['netcall'])) {
    $netID = $input['netID'];
    $callsign = $input['callsign'];
    $netcall = $input['netcall'];
    $pb = isset($input['pb']) && $input['pb'] == 1 ? 1 : 0;
    
    try {
        if (!$db_found) {
            throw new Exception("Database connection failed");
        }

        $db_found->beginTransaction();

        // Check if the callsign already exists in this net
        $stmt = $db_found->prepare("SELECT COUNT(*) FROM NetLog WHERE netID = :netID AND callsign = :callsign");
        $stmt->execute([':netID' => $netID, ':callsign' => $callsign]);
        if ($stmt->fetchColumn() > 0) {
            throw new Exception("Callsign already exists in this net");
        }

        // Fetch station data
        $stmt = $db_found->prepare("SELECT * FROM stations WHERE callsign = :callsign AND active_call = 'y'");
        $stmt->execute([':callsign' => $callsign]);
        $stationData = $stmt->fetch(PDO::FETCH_ASSOC);

        logDebug("Station data retrieved: " . print_r($stationData, true));

        if (!$stationData) {
            // If not found in stations table, create a basic entry
            $stationData = [
                'ID' => uniqid(), // Generate a unique ID
                'callsign' => $callsign,
                'Fname' => '',
                'Lname' => 'No FCC Record',
                'grid' => '',
                'latitude' => '',
                'longitude' => '',
                'email' => '',
                'county' => '',
                'state' => '',
                'district' => '',
                'city' => ''
            ];
        } // End if no station found

        if (!isset($stationData['ID']) || empty($stationData['ID'])) {
            throw new Exception("ID is missing for station: " . $callsign);
        }

        // Prepare the SQL statement
$sql = "INSERT INTO NetLog (
    `row_number`, ID, netID, callsign, Fname, Lname, grid, 
    latitude, longitude, email, county, state, city, district, 
    netcall, pb, logdate, active
)
SELECT 
    COALESCE(MAX(`row_number`), 0) + 1,
    :ID, :netID, :callsign, :Fname, :Lname, :grid,
    :latitude, :longitude, :email, :county, :state, :city, 
    :district, :netcall, :pb, NOW(), 'In'
FROM NetLog 
WHERE netID = :netID";

logDebug("SQL Statement: " . $sql);

$stmt = $db_found->prepare($sql);

$params = [
    ':ID' => $stationData['ID'],
    ':netID' => $netID,
    ':callsign' => $callsign,
    ':Fname' => $stationData['Fname'] ?? '',
    ':Lname' => $stationData['Lname'] ?? '',
    ':grid' => $stationData['grid'] ?? '',
    ':latitude' => $stationData['latitude'] ?? '',
    ':longitude' => $stationData['longitude'] ?? '',
    ':email' => $stationData['email'] ?? '',
    ':county' => $stationData['county'] ?? '',
    ':state' => $stationData['state'] ?? '',
    ':city' => $stationData['city'] ?? '',
    ':district' => $stationData['district'] ?? '',
    ':netcall' => $netcall,
    ':pb' => $pb
];

logDebug("Parameters for INSERT: " . print_r($params, true));

if (!$stmt->execute($params)) {
    $errorInfo = $stmt->errorInfo();
    throw new Exception("Error executing INSERT: " . $errorInfo[2]);
}

        $recordID = $db_found->lastInsertId();
        logDebug("New record ID: " . $recordID);

        // Insert into TimeLog
        $stmt = $db_found->prepare("INSERT INTO TimeLog (ID, recordID, netID, callsign, comment, timestamp) VALUES (:ID, :recordID, :netID, :callsign, 'Checked in', NOW())");
        $stmt->execute([':ID' => $stationData['ID'], ':recordID' => $recordID, ':netID' => $netID, ':callsign' => $callsign]);

        // Fetch the full row data to return
        $stmt = $db_found->prepare("SELECT * FROM NetLog WHERE recordID = :recordID");
        $stmt->execute([':recordID' => $recordID]);
        $rowData = $stmt->fetch(PDO::FETCH_ASSOC);

        $db_found->commit();

        logDebug("Successfully added new entry. Returning data: " . print_r($rowData, true));
        
        $response = [
            'success' => true,
            'message' => 'Entry added successfully',
            'data' => $rowData  // Use $rowData instead of $newRowData
        ];
        
    } catch (Exception $e) {
        if (isset($db_found)) {
            $db_found->rollBack();
        }
        logDebug("Error in addToNet.php: " . $e->getMessage());
        $response = ['success' => false, 'message' => $e->getMessage()];
    }
} else {
    logDebug("Missing netID, callsign, or netcall in addToNet.php");
    $response = ['success' => false, 'message' => 'Missing netID, callsign, or netcall'];
}

// Added 2024-06-29 Phase 1
// Near the end of addToNet.php, replace the SSE code with:
if ($response['success']) {
    // Trigger SSE update via a separate API call
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://your-domain.com/trigger_sse.php");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'netID' => $netID,
        'additionalInfo' => $netcall . ", Net #: " . $netID . " --> " . ($rowData['activity'] ?? '')
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    curl_close($ch);
}

// Keep the JSON response at the end
header('Content-Type: application/json');
echo json_encode($response);