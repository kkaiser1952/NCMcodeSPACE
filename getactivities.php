<?php
// getactivities.php
// Written: 2015
// This script is responsible for fetching and formatting net activity data from the database.
// Key functionalities include:
// 1. Retrieving net details, status, and participant information based on the provided netID
// 2. Formatting the data into an HTML table structure
// 3. Applying custom sorting logic for different net types
// 4. Generating additional UI elements like buttons and hidden fields
// 5. Calculating and displaying net statistics (e.g., run time, total volunteer hours)
// 6. Handling different scenarios for various net types and configurations
// 7. Providing data for both active nets and historical summaries
// This script is crucial for populating the main net activity display in the NCM interface,
// working in conjunction with showActivities.js to present a dynamic and interactive net management tool.

// Updated: 2024-10-08

if (ob_get_level() == 0) ob_start();
//error_log("Output buffering started");
echo "<!-- Debug: Start of getactivities.php output -->\n";

ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

require_once "dbConnectDtls.php";
require_once "CellRowHeaderDefinitions.php";  // Add this line

if (!function_exists('getRowDefinitions') || !function_exists('getHeaderDefinitions')) {
    error_log("CellRowHeaderDefinitions.php not loaded properly or missing required functions");
    die("Critical error: Required definitions not found. Please contact the administrator.");
}

date_default_timezone_set('UTC');

$tableStructure = createTableStructure();

function errorHandler($errno, $errstr, $errfile, $errline) {
    //error_log("Error [$errno] $errstr on line $errline in file $errfile");
    //error_log("Stack trace: " . print_r(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS), true));
    return true;
}
set_error_handler("errorHandler");

function setContentType($type = 'html') {
    if ($type === 'json') {
        header('Content-Type: application/json');
    } else {
        header('Content-Type: text/html; charset=utf-8');
    }
}

// Helper function to calculate elapsed time
function time_elapsed_A($secs) {
    $secs = (int)$secs; // Ensure $secs is an integer
    $bit = array(
        'y' => $secs / 31556926,
        'w' => $secs % 31556926 / 604800,
        'd' => $secs % 604800 / 86400,
        'h' => $secs % 86400 / 3600,
        'm' => $secs % 3600 / 60,
        's' => $secs % 60
    );

    $ret = array();
    foreach ($bit as $k => $v) {
        if ($v > 0) {
            $ret[] = intval($v) . $k;
        }
    }

    return $ret ? join(' ', $ret) : '0s';
}

// Get netID from GET parameter
$q = isset($_GET['q']) ? $_GET['q'] : '';

// Get timezone difference from cookie
$tzdiff = isset($_COOKIE['tzdiff']) ? $_COOKIE['tzdiff'] : '0';
$tzdiff = intval($tzdiff);
$tzdiff = sprintf('%+03d:00', $tzdiff / -60);
$tzdiff = isset($_COOKIE['tzdiff']) ? $_COOKIE['tzdiff'] / -60 . ':00' : '+00:00';

//error_log("About to enter main try block");
try {
    global $db_found;
    if (!$db_found) {
        throw new Exception("Database connection failed");
    }

    //error_log("Database connection successful, about to process query");

if ($q !== 0) {
        //error_log("Processing for non-zero q: $q");
        
        // Fetch subnet information
        $stmt = $db_found->prepare("
            SELECT subNetOfID as parent, GROUP_CONCAT(DISTINCT netID SEPARATOR ', ') as child
            FROM NetLog
            WHERE subNetOfID = :q AND subNetOfID <> '0'
            ORDER BY netID
        ");
        $stmt->execute([':q' => $q]);
        $children = $stmt->fetchColumn(1);
        //error_log("Subnet information fetched");

        // Fetch net details
        $stmt = $db_found->prepare("
            SELECT sec_to_time(sum(timeonduty)) as tottime, activity, pb,
                   (SELECT netcall FROM NetLog WHERE netID = :q LIMIT 1) as netcall
            FROM NetLog
            WHERE netID = :q
            GROUP BY netID
        ");
        $stmt->execute([':q' => $q]);
        $netDetails = $stmt->fetch(PDO::FETCH_ASSOC);
        //error_log("Net details fetched");

        // Fetch organization type and column views
        $stmt = $db_found->prepare("
            SELECT orgType, columnViews 
            FROM NetKind 
            WHERE `call` = :netcall 
            LIMIT 1
        ");
        $stmt->execute([':netcall' => $netDetails['netcall']]);
        $netKind = $stmt->fetch(PDO::FETCH_ASSOC);
        //error_log("Organization type and column views fetched");

        // Fetch net status and frequency
        $stmt = $db_found->prepare("
            SELECT frequency, MIN(status) as minstat, MIN(logdate) as startTime, MAX(timeout) as endTime
            FROM NetLog 
            WHERE netID = :q AND frequency <> '' AND frequency NOT LIKE '%name%'
            LIMIT 1
        ");
        $stmt->execute([':q' => $q]);
        $netStatus = $stmt->fetch(PDO::FETCH_ASSOC);
        //error_log("Net status and frequency fetched");

        $isopen = $netStatus['minstat'];
        $nowtime = $isopen ? strtotime($netStatus['endTime']) : time();
        $startTime = $netStatus['startTime'];

        // Main query to fetch net log data
        echo ("at: SELECT FROM NETLOG IN getactivities.php");
        $stmt = $db_found->prepare("
            SELECT recordID, netID, subNetOfID, ID, callsign, tactical,
                   grid, traffic, latitude, longitude, netcontrol,
                   onSite, delta, `row_number`,
                   activity, email, active, comments, netcall, status,
                   Mode, band, w3w, aprs_call, home, ipaddress,
                   cat, section,
                   firstLogIn, phone, pb, tt,
                   logdate AS startdate,
                   TRIM(callsign) AS callsign,
                   TRIM(Fname) AS Fname,
                   TRIM(Lname) AS Lname,
                   TRIM(creds) AS creds,
                   sec_to_time(timeonduty) AS time,
                   TIMESTAMPDIFF(DAY, logdate, NOW()) AS daydiff,
                   TRIM(county) AS county,
                   TRIM(city) AS city,
                   TRIM(state) AS state,
                   TRIM(district) AS district,
                   TRIM(facility) AS facility,
                   TRIM(team) AS team
             --      ,DATE_FORMAT(CONVERT_TZ(logdate, '+00:00', :tzdiff), '%H:%i') AS logdate,
             --      DATE_FORMAT(CONVERT_TZ(timeout, '+00:00', :tzdiff), '%H:%i') AS timeout
            FROM NetLog
            WHERE netID = :q
            ORDER BY
                CASE
                    WHEN netcall IN ('KCHEART', 'ARHAB') THEN 0
                    WHEN netcontrol IN ('PRM', 'CMD', 'TL', 'EM') THEN 1
                    ELSE 2
                END,
                CASE
                    WHEN netcall IN ('KCHEART', 'ARHAB') THEN NULL
                    WHEN netcontrol IN ('Log', '2nd', 'LSN', 'PIO', 'SEC', 'RELAY', 'CMD') THEN 1
                    ELSE 4
                END,
                CASE
                    WHEN netcall IN ('KCHEART', 'ARHAB') THEN NULL
                    WHEN active = 'MISSING' THEN 3
                    ELSE 80
                END,
                CASE
                    WHEN netcall IN ('KCHEART', 'ARHAB') THEN NULL
                    WHEN active = 'BRB' THEN 5
                    ELSE 80
                END,
                CASE
                    WHEN netcall IN ('KCHEART', 'ARHAB') THEN NULL
                    WHEN active IN ('In-Out', 'Out', 'OUT') THEN 80
                    ELSE NULL
                END,
                CASE
                    WHEN netcall IN ('KCHEART', 'ARHAB') THEN NULL
                    WHEN facility = 'Checkins with no assignment' THEN 95
                    ELSE 6
                END,
                CASE
                    WHEN netcall = 'MESN' THEN district
                    ELSE NULL
                END,
                CASE
                    WHEN netcall IN ('KCHEART', 'ARHAB') THEN facility
                    ELSE NULL
                END,
                CASE
                    WHEN netcall LIKE '%sbbt202%' THEN team
                    ELSE NULL
                END,
                CASE
                    WHEN netcall IN ('KCHEART', 'ARHAB') AND (facility IN ('', 'Checkins with no assignment') AND active IN ('In-Out', 'Out', 'OUT', 'In', 'IN')) THEN 95
                    ELSE NULL
                END,
                facility,
                logdate
        ");
        $stmt->execute([':q' => $q, ':tzdiff' => $tzdiff]);
        $netLogData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        //error_log("Raw netLogData: " . print_r($netLogData, true));
        //error_log("Number of rows fetched: " . count($netLogData));
        //error_log("First row of data: " . print_r($netLogData[0] ?? 'No data', true));

    } else {
        //error_log("Processing for q = 0");
        // Query for when q is 0
        $stmt = $db_found->prepare("
            SELECT DISTINCT callsign, CONCAT(Fname,' ',Lname) as name, email, phone, creds, county, state, district, sum(timeonduty) as Vhours
            FROM NetLog
            WHERE id <> 0 AND netID <> 0
            GROUP BY id
            ORDER BY callsign
        ");
        $stmt->execute();
        $netLogData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        //error_log("Raw netLogData: " . print_r($netLogData, true));
        //error_log("Data fetched for q = 0");
    }

    // Process and output the data
    $num_rows = count($netLogData);
    $pbStat = 0;
    $subnetkey = 0;
    $editCS1 = "";
    $usedFacility = "";

    include "dropdowns.php";

    //error_log("About to output table body");
    //error_log("Data to be echoed: " . ob_get_contents());
    
    if (ob_get_length()) {
    error_log("Current buffer contents: " . ob_get_contents());
    //ob_clean();
    } else {
        error_log("No output buffer to clean");
    }

 echo "<!-- Debug: Start of table body -->\n";
 echo "<tbody id='netBody' class='sortable'>";
 //error_log("About to start processing rows");

    foreach ($netLogData as $row) {
        echo "<tr>";
        echo getRowDefinitions();  // This now outputs the full HTML for the row
        echo "</tr>";
    }

echo "</tbody>";

echo "<!-- Debug: End of table body -->\n";
//error_log("Finished processing all rows");
//error_log("Final table body HTML: " . ob_get_contents());

$runtime = time_elapsed_A($nowtime - strtotime($startTime));

//error_log("About to output footer");
    // Output footer
    echo "<tfoot><tr>";
    if ($netDetails['tottime'] != '00:00:00') {
        echo "<td></td><td class='runtime' colspan='5' align='left'>Run Time: $runtime </td><td class='tvh' colspan='8' align='right'>Total Volunteer Hours = {$netDetails['tottime']}</td>";
    }
    echo "</tr></tfoot></table>";
    
    //error_log("Table structure after generation: " . ob_get_contents());

    // Output hidden divs
    $hiddenDivs = [
        'freq2' => $netStatus['frequency'] ?? '',
        'freq' => '',
        'cookies' => $netKind['columnViews'] ?? '',
        'type' => "Type of Net: {$netDetails['activity']}",
        'idofnet' => $q,
        'activity' => $netDetails['activity'] ?? '',
        'domain' => $netDetails['netcall'] ?? '',
        'thenetcallsign' => $netDetails['netcall'] ?? '',
        'isopen' => $isopen,
        'ispb' => $netDetails['pb'] ?? '',
        'pbStat' => $pbStat
    ];

    foreach ($hiddenDivs as $id => $value) {
        echo "<div hidden id='$id'>$value</div>";
    }
    
    // Added 2024-07-17 to help find the netID value
    echo "<input type='hidden' id='currentNetID' value='" . htmlspecialchars($q) . "'>";

    // Output additional spans and buttons
    echo "<span id='add2pgtitle'>#$q/{$netDetails['netcall']}/{$netStatus['frequency']}&nbsp;&nbsp;";
    if ($subnetkey == 1) {
        echo "<button class='subnetkey' value='$subnetnum'>Sub Net of: $subnetnum</button>&nbsp;&nbsp;";
    }
    if ($children) {
        echo "<button class='subnetkey' value='$children'>Has Sub Net: $children</button>&nbsp;&nbsp;";
    }

    echo "<span STYLE='background-color: #befdfc'>Control</span>&nbsp;&nbsp;";

    echo "<span class='export2CSV' style='padding-left: 10pt;'><a href='#' onclick=\"window.open('netCSVdump.php?netID=$q')\">Export CSV</a></span>";
    echo "<span style='padding-left: 5pt;'><a href='#' id='geoDist' onclick='geoDistance()' title='geoDistance'><b style='color:green;'>geoDistance</b></a></span>";
    echo "<span style='padding-left: 5pt;'><a href='#' id='mapIDs' onclick='map2()' title='Map This Net'><b style='color:green;'>Map This Net</b></a></span>";
    //error_log("Final HTML output: " . ob_get_contents());
    echo "</span>";

    error_log("Data processing and output completed successfully");

} catch (PDOException $e) {
    error_log("PDO Exception in getactivities.php: " . $e->getMessage() . "\nStack trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo "An error occurred while fetching data. Please check the error log for more details.";
} catch (Exception $e) {
    //error_log("General Exception in getactivities.php: " . $e->getMessage() . "\nStack trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo "An unexpected error occurred. Please try again later.";
}
$finalOutput = ob_get_contents();
ob_end_clean();
echo $finalOutput;
?>