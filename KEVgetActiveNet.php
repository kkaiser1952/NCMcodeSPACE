<?php
// getactivities.php    v2
// V2 Updated: 2024-04-26

error_reporting(0);
require_once "dbConnectDtls.php";

//echo ("@8 Top of getactivities");

$q = intval($_GET['netID']);
//$q = 11566;

//echo ("\n<br>@13 In getactivities q: $q\n<br>");

if (!isset($_COOKIE['tzdiff'])) {
    // Cookie not set
} else {
    $tzdiff = $_COOKIE['tzdiff'] / -60;
    $tzdiff = "$tzdiff:00";
}

//echo ("@22 tzdiff: $tzdiff");

function time_elapsed_A($secs)
{
    $bit = array(
        'y' => $secs / 31556926 % 12,
        'w' => $secs / 604800 % 52,
        'd' => $secs / 86400 % 7,
        'h' => $secs / 3600 % 24,
        'm' => $secs / 60 % 60,
        's' => $secs % 60
    );

    foreach ($bit as $k => $v)
        if ($v > 0) $ret[] = $v . $k;

    return join(' ', $ret);
} // End time_elapsed_A()

//echo "\n<br>@40 In getactivities q: $q";
if ($q !== 0) {
    try {
        $childCnt = 0;
        $children = "";
        //echo "\n<br>@45 In getactivities q: $q";
        $sql = "SELECT subNetOfID as parent, 
                    GROUP_CONCAT(DISTINCT netID SEPARATOR ', ') as child
                  FROM NetLog
                 WHERE subNetOfID = $q
                   AND subNetOfID <> 0
                 ORDER BY netID;";
        //echo("\n<br>@50 $sql");
        //echo "\n<br><script>console.log(" . json_encode($sql) . ");</script>";
        $stmt = $db_found->prepare($sql);
        //$stmt->bindParam(':q', $q, PDO::PARAM_INT);
        $stmt->execute();
        $children = $stmt->fetchColumn(1);

        $sql2 = "SELECT sec_to_time(sum(timeonduty)) as tottime,
                    activity,
                    pb
                FROM NetLog
                WHERE netID = :q
                GROUP BY netID;";

        $stmt2 = $db_found->prepare($sql2);
        $stmt2->bindParam(':q', $q, PDO::PARAM_INT);
        $stmt2->execute();
        $tottime = $stmt2->fetchColumn(0);
        //echo("\n<br>@70 $sql2");
        //echo "\n<br><script>console.log(" . json_encode($sql2) . ");</script>";

        $stmt2->execute();
        $activity = trim($stmt2->fetchColumn(1));

        $stmt2->execute();
        $prebuilt = $stmt2->fetchColumn(2);

        $sql = "SELECT netcall FROM NetLog WHERE netID = :q LIMIT 1;";

        $stmt = $db_found->prepare($sql);
        $stmt->bindParam(':q', $q, PDO::PARAM_INT);
        $stmt->execute();
        $netcall = $stmt->fetchColumn(0);
        //echo("\n<br>@85 $sql");
        //echo "\n<br><script>console.log(" . json_encode($sql) . ");</script>";

        $sql = "SELECT orgType, columnViews FROM NetKind WHERE `call` = :netcall LIMIT 0,1;";
        $stmt = $db_found->prepare($sql);
        $stmt->bindParam(':netcall', $netcall, PDO::PARAM_STR);
        $stmt->execute();
        $orgType = $stmt->fetchColumn(0);
        $theCookies = $stmt->fetchColumn(1);
        //echo("\n<br>$sql");
        //echo ("\n<br>@95 In getactivities q: $q");
    } catch (PDOException $e) {
        // Handle the PDO exception
        error_log("PDO Exception: " . $e->getMessage());
        error_log("File: " . $e->getFile());
        error_log("Line: " . $e->getLine());
        error_log("Trace: " . $e->getTraceAsString());
        //echo "\n<br>@102 A PDOException error occurred. Please try again.";
    } catch (Exception $e) {
        // Handle any other exceptions
        error_log("Exception: " . $e->getMessage());
        //echo "\n<br>@106 An Exception error occurred. Please try again later.";
    }
} // end if(q !== 0)
//echo ("\n<br>@109 In getactivities q: $q<br>");
$isopen = 0;

$stmt = $db_found->prepare("SELECT frequency, MIN(status) as minstat, MIN(logdate) as startTime, MAX(timeout) as endTime
                FROM `NetLog` 
                WHERE netID = :q
                  AND frequency <> ''
                  AND frequency NOT LIKE '%name%'
                 LIMIT 0,1");

$stmt->bindParam(':q', $q, PDO::PARAM_INT);
$stmt->execute();
$result = $stmt->fetch();
$freq = $result['frequency'];
$isopen = $result['minstat'];

//echo ("\n<br>@125 isopen 1 closed, 0 open: this $isopen\n<br>");
// 0: Net Open,  1: Net Closed
if ($isopen == 1) {
    $nowtime = strtotime($result['endTime']);
    $startTime = $result['startTime'];
    //echo "<span style='color:red; float:left;'>$netcall ==> This Net is Closed, not available for  edit</span>";
} else if ($isopen == 0) {
    $nowtime = time();
    $startTime = $result['startTime'];
}


$json = json_encode($result);
echo $json;


//echo ("\n<br>@136 tzdiff: $tzdiff");
if ($q <> 0) {
    $g_query = "
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
           TRIM(team) AS team,
           DATE_FORMAT(CONVERT_TZ(logdate, '+00:00', :tzdiff), '%H:%i') AS logdate,
           DATE_FORMAT(CONVERT_TZ(timeout, '+00:00', :tzdiff), '%H:%i') AS timeout
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
        ";

    //echo"\n<br>@216 $g_query \n<br>";
    //echo"\n<br>@217 In getactivities q: $q tzdiff: $tzdiff \n<br>";

    $stmt = $db_found->prepare($g_query);
    $stmt->bindParam(':q', $q, PDO::PARAM_INT);
    $stmt->bindParam(':tzdiff', $tzdiff, PDO::PARAM_STR);

    $db_found->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    try {
        $stmt->execute();
        //echo("\n<br>@227 $g_query \n<br>");
        //echo ("\n<br>@228 In getactivities q: $q \n<br>");
    } catch (PDOException $e) {
        echo "Error executing query: " . $e->getMessage();
    }

} else if ($q == 0) { // The very first netID was zero
    $g_query = "SELECT DISTINCT callsign, CONCAT(Fname,' ',Lname) as name, email, phone, creds, county, state, district, sum(timeonduty) as Vhours
                FROM NetLog
                WHERE id <> 0
                  AND netID <> 0
                GROUP BY id
                ORDER BY callsign";

    $stmt = $db_found->prepare($g_query);
    $stmt->execute();
    //echo("\n<br>@243 $g_query \n<br>");
}

// =========== KEVIN TESTING vvv
$stationsList = [];


foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
   array_push($stationsList, $row);
}

// =========== KEVIN TESTING ^^^
?>