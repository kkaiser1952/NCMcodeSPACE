<?php
ob_start();
// processCheckIn.php
// V2 Updated: 2024-09-03

//error_log("Entering file: " . __FILE__);

require_once "dbFunctions.php";
require_once "CellRowHeaderDefinitions.php";
require_once "callsignDupeCheck.php";
require_once "database_logger.php";  // Make sure this path is correct

if (!function_exists('getRowDefinitions') || !function_exists('getHeaderDefinitions')) {
    error_log("CellRowHeaderDefinitions.php not loaded properly or missing required functions");
    die("Critical error: Required definitions not found. Please contact the administrator.");
}

function extractParams($q) {
    if (!is_string($q) || empty($q)) {
        return [
            'netID' => 0,
            'callsign' => '',
            'ispb' => 0,
            'netcall' => '',
            'mBand' => 0,
            'tfksrt' => '',
        ];
    }
    $parts = preg_split('/[:\s]+/', $q);

    return array(
        'netID' => isset($parts[0]) ? filter_var($parts[0], FILTER_VALIDATE_INT) : 0,
        'callsign' => isset($parts[1]) ? htmlspecialchars($parts[1], ENT_QUOTES, 'UTF-8') : '',
        'ispb' => isset($parts[2]) ? filter_var($parts[2], FILTER_VALIDATE_INT) : 0,
        'netcall' => isset($parts[3]) ? htmlspecialchars($parts[3], ENT_QUOTES, 'UTF-8') : '',
        'mBand' => isset($parts[4]) ? filter_var($parts[4], FILTER_VALIDATE_INT) : 0,
        'tfksrt' => isset($parts[5]) ? htmlspecialchars($parts[5], ENT_QUOTES, 'UTF-8') : '',
    );
}

if (!function_exists('processCheckIn')) {
    function processCheckIn($db_found, $params) {
        if (!is_array($params)) {
            return ['success' => false, 'message' => 'Invalid parameters'];
        }
        
        $netID = $params['netID'] ?? 0;
        $callsign = $params['callsign'] ?? '';
        $ispb = $params['ispb'] ?? 0;
        $netcall = $params['netcall'] ?? '';
        $mBand = $params['mBand'] ?? 0;
        $tfksrt = $params['tfksrt'] ?? '';

        $transaction_started = false;

        try {
            if (!$db_found) {
                throw new Exception("Database connection not found");
            }

            $db_found->beginTransaction();
            $transaction_started = true;

            // Log the query for duplicate check
            $start = microtime(true);
            $isDuplicate = callsignDupeCheck($db_found, $netID, $callsign);
            $duration = microtime(true) - $start;
            DatabaseLogger::log_query("Duplicate check query for netID: $netID, callsign: $callsign", 'ncm', $duration);

            if ($isDuplicate) {
                $db_found->rollBack();
                $transaction_started = false;
                return [
                    'success' => false,
                    'message' => 'Duplicate callsign detected',
                    'checkInRowData' => null
                ];
            }

            $ipaddress = getRealIpAddr();
            $cspart = preg_split("/[\/|\-]+/", $callsign);
            $csbase = $cspart[0];

            // Log the query for getting net information
            $start = microtime(true);
            $stmt1 = $db_found->prepare("
                SELECT netcall, pb, activity, subNetOfID, frequency, recordID
                FROM NetLog
                WHERE netID = ?
                AND logdate = (
                    SELECT MIN(logdate)
                    FROM NetLog
                    WHERE netID = ?
                )
            ");
            $stmt1->execute([$netID, $netID]);
            $duration = microtime(true) - $start;
            DatabaseLogger::log_query($stmt1->queryString, 'ncm', $duration);

            $row1 = $stmt1->fetch(PDO::FETCH_ASSOC);
            if (!$row1) {
                throw new Exception("Net not found for netID: $netID");
            }
            extract($row1);

            // Log the query for getting max row number
            $start = microtime(true);
            $stmt = $db_found->prepare("SELECT MAX(`row_number`) FROM NetLog WHERE netID = ?");
            $stmt->execute([$netID]);
            $duration = microtime(true) - $start;
            DatabaseLogger::log_query($stmt->queryString, 'ncm', $duration);

            $max_row_num = $stmt->fetchColumn() ?? 0;
            $stmt->closeCursor();

            // Log the query for checking callsign in stations table
            $start = microtime(true);
            $stmt2 = $db_found->prepare("
                SELECT id, Fname, Lname, creds, email, 
                       latitude, longitude,
                       grid, county, state, district, home, 
                       phone, tactical, country, city
                FROM stations 
                WHERE callsign = ?
                AND active_call = 'y'
                LIMIT 0, 1
            ");
            $stmt2->execute([$callsign]);
            $duration = microtime(true) - $start;
            DatabaseLogger::log_query($stmt2->queryString, 'ncm', $duration);

            $result = $stmt2->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                extract($result);
                $firstLogIn = 0;
            } else {
                $firstLogIn = 1;
                $fccData = require "getFCCrecord.php";
                extract($fccData);
                $id = null;
                $Fname = $Lname = $creds = $email = $grid = $tactical = $county = $state = $country = $district = $home = $phone = $city = '';
                $latitude = $longitude = 0;
            }

            if ($callsign == "NONHAM") {
                $tactical = "";
                $comments = "Not A Ham";
                $Lname = $Fname = "";
            } elseif ($callsign == "EMCOMM") {
                $tactical = "";
                $comments = "Emergency Mgnt. Not A Ham";
                $Lname = $Fname = "";
            } else {
                $comments = "";
            }

            $traffic = processTraffic($tfksrt);
            $currentTimestamp = date('Y-m-d H:i:s');
            $statusValue = ($pb == 1) ? 'OUT' : 'In';
            $timeLogIn = ($pb == 1) ? 0 : $currentTimestamp;
            $tactical = extractTactical($callsign);
            $tt = '00';
            $max_row_num++;

            // Log the query for inserting into NetLog
            $start = microtime(true);
            $sql = "INSERT INTO NetLog (ID, active, callsign, Fname, Lname, netID,
                grid, tactical, email, latitude, longitude,
                creds, activity, comments, logdate, netcall,
                subNetOfID, frequency, county, state, country,
                district, firstLogIn, pb, tt, home, phone, cat,
                section, traffic, `row_number`, city)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 
                            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 
                            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $db_found->prepare($sql);
            $stmt->execute([
                $id, $statusValue, $callsign, $Fname, $Lname, $netID,
                $grid, $tactical, $email, $latitude, $longitude,
                $creds, $activity, $comments, $timeLogIn, $netcall,
                $subNetOfID, $frequency, $county, $state, $country,
                $district, $firstLogIn, $pb, $tt, $home, '', '', '',
                $traffic, $max_row_num, $city
            ]);
            $duration = microtime(true) - $start;
            DatabaseLogger::log_query($sql, 'ncm', $duration);

            $recordID = $db_found->lastInsertId();

            // Log the query for inserting into TimeLog
            $start = microtime(true);
            $logtraffic = isset($logtraffic) ? $logtraffic : '';
            $sql = "INSERT INTO TimeLog (ID, netID, callsign, comment, timestamp, ipaddress, recordID)
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $db_found->prepare($sql);
            $stmt->execute([
                $id, $netID, $callsign, $comments . $logtraffic, $currentTimestamp, $ipaddress, $recordID
            ]);
            $duration = microtime(true) - $start;
            DatabaseLogger::log_query($sql, 'ncm', $duration);

            $db_found->commit();
            $transaction_started = false;

            $checkInRowData = [
                'recordID' => $recordID,
                'id' => $id,
                'active' => $statusValue,
                'callsign' => $callsign,
                'Fname' => $Fname,
                'Lname' => $Lname,
                'netID' => $netID,
                'grid' => $grid,
                'tactical' => $tactical,
                'email' => $email,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'creds' => $creds,
                'activity' => $activity,
                'comments' => $comments,
                'logdate' => $timeLogIn,
                'netcall' => $netcall,
                'subNetOfID' => $subNetOfID,
                'frequency' => $frequency,
                'county' => $county,
                'state' => $state,
                'country' => $country,
                'district' => $district,
                'firstLogIn' => $firstLogIn,
                'pb' => $pb,
                'tt' => $tt,
                'home' => $home,
                'phone' => '',
                'traffic' => $traffic,
                'row_number' => $max_row_num,
                'city' => $city,
            ];
            
            return [
                'success' => true,
                'message' => 'Callsign checked in successfully',
                'checkInRowData' => $checkInRowData
            ];

        } catch (Exception $e) {
            error_log("Error in processCheckIn: " . $e->getMessage());
            if ($transaction_started) {
                $db_found->rollBack();
            }
            return [
                'success' => false,
                'message' => 'An error occurred during check-in: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ];
        }
    }
}

function processTraffic($tfksrt) {
    switch (trim($tfksrt)) {
        case "R": return "Routine";
        case "W": return "Welfare";
        case "P": return "Priority";
        case "E": return "Emergency";
        case "Q": return "Question";
        case "A": return "Announcement";
        case "C": return "Comment";
        case "T": return "Traffic";
        default: return "";
    }
}

function extractTactical($callsign) {
    $firstNumPos = strcspn($callsign, '0123456789');
    return $firstNumPos < strlen($callsign) ? substr($callsign, $firstNumPos + 1) : $callsign;
}

header('Content-Type: application/json');

if (isset($_POST['q'])) {
    $params = extractParams($_POST['q']);
    $result = processCheckIn($db_found, $params);
    echo json_encode($result);
    exit;
} else {
    exit;
}
?>