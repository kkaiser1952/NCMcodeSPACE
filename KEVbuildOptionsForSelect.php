<?php
// This program creates the dropdown for previous net selection
// 2020-10-14
// 2023-09-26 v2
// Updated: 2024-04-09

if (!$db_found) {
    require_once "dbConnectDtls.php";
    //die("Error: Database connection is not established. Check database credentials. <-- From buildOptionsForSelect_Proposed.php");
}

// uncomment the line below to run this code by itself without NCM, check the 'source' for output
require_once "dbConnectDtls.php";

// read the time diff from UTC for the local time
if (!isset($_COOKIE['tzdiff'])) {
    $tzdiff = "-0:00";   // make no adjustment to the various time values
} else {
    $tzdiff = $_COOKIE['tzdiff'] / -60;  // adjust the time values based on the time zone
    $tzdiff = "$tzdiff:00";    // echo("tzdiff= $tzdiff");
}

$sql = "SELECT ANY_VALUE(netID) AS netID
               ,ANY_VALUE(status) AS status
               ,ANY_VALUE(activity) AS activity
               ,ANY_VALUE(netcall) AS netcall
               ,ANY_VALUE(frequency) AS frequency
                /* 1 = close, 0 = open */  
               ,MIN(status) AS minstat  
               ,MIN(CONVERT_TZ(dttm, '+00:00', ?)) AS mindttm 
               ,MIN(CONVERT_TZ(logdate, '+00:00', ?)) AS minlogdate
               ,MAX(CONVERT_TZ(timeout, '+00:00', ?)) AS timeout
                                                       
               ,IF(MIN(CONVERT_TZ(logdate, '+00:00', ?) IS NULL) = 0,
                   DATE(MIN(CONVERT_TZ(logdate, '+00:00', ?))), 
                   DATE(MIN(CONVERT_TZ(dttm, '+00:00', ?)))) AS dteonly
               ,IF(MIN(CONVERT_TZ(logdate, '+00:00', ?) IS NULL) = 0,
                   DAYNAME(MIN(CONVERT_TZ(logdate, '+00:00', ?))), 
                   DAYNAME(MIN(CONVERT_TZ(dttm, '+00:00', ?)))) AS daynm
                                         
               ,POSITION('Meeting' IN ANY_VALUE(activity)) AS meetType
               ,POSITION('Event' IN ANY_VALUE(activity))   AS eventType
                                           
               ,SUM(IF(timeout IS NULL, 1, 0)) AS lo
               
               ,ANY_VALUE(pb) AS pb,
                  (CASE 
                     WHEN ANY_VALUE(pb) = '0' THEN ''
                     WHEN ANY_VALUE(pb) = '1' THEN 'pbBlue'
                       ELSE ''
                  END)  AS pbcolor   /* color for the open pre-built nets */
             FROM `NetLog` 
            WHERE (CONVERT_TZ(dttm, '+00:00', ?) >= NOW() - INTERVAL 39 DAY AND pb = 1)
               OR (CONVERT_TZ(logdate, '+00:00', ?) >= NOW() - INTERVAL 10 DAY AND pb = 0)
                                                   	
             GROUP BY netID
             ORDER BY netID DESC";

try {
    $stmt = $db_found->prepare($sql);
    $stmt->execute(array($tzdiff, $tzdiff, $tzdiff, $tzdiff, $tzdiff, $tzdiff, $tzdiff, $tzdiff, $tzdiff, $tzdiff, $tzdiff));

    $firstDate = true; // put date in list
    $thisDate = date('Y-m-d'); // switch for firstDate
    $spcl = "";

    $activeNets = [];

    while ($act = $stmt->fetch(PDO::FETCH_ASSOC)) {

        // Open nets are green, Open PB nets are blue, All else have no color
        $pbcolor = '';
        if ($act['minstat'] < $act['lo']) {
            $pbcolor = 'green';    // for standard nets
        }
        if (($act['minstat'] < $act['lo']) && ($act['pb'] > 0)) {
            $pbcolor = 'pbBlue';   // For PB nets
        }

        // We want to know if it's a meeting or event so we can set the 'spcl' as a class in the listing
        // This will give us the ability to eventually show the email and phone columns via cookieManagement.js
        if ($act['meetType'] > 0 || $act['eventType'] > 0) {
            $spcl = "spcl";
        } else {
            $spcl = "";
        }

        // Test to see if this is a pre-built net 
        switch ($act['pb']) {
            case 0:   // Not a pre-built
                $activity = preg_replace('/\s\s+/', ' ', $act['activity']);
                $dteonly = $act['dteonly'];
                $logdate = "of $dteonly";
                $daynm = $act['daynm'];
                break;
            case 1:   // This is a pre-built
                $activity = preg_replace('/\s\s+/', ' ', $act['activity']);
                $activity = preg_replace("/Event/", "", $activity);
                $logdate = "For " . $act['minlogdate'];
                $daynm = "Pre-Built Net(s) For: " . $act['daynm'];
                $dteonly = date("Y/m/d", strtotime($act['minlogdate']));
                break;
        } // End switch

        // firstDate and thisDate are used as tests allowing us to put the date between nets 
        if ($thisDate == $act['dteonly']) {
            $firstDate = false;
        } else {
            $firstDate = true;
        }

        array_push($activeNets, $act);

//        if ($firstDate) {   // Creates the day of the week and date separator
//            $thisDate = $act['dteonly']; // 2017-10-20
//
//            echo("<option disabled style='color:blue; font-weight:bold'>
//            --------------- $daynm ---==========--- $dteonly ----------------------</option>\n");
//        } // end if firstdate
//
//        // For Pre-Built, future events
//        if ($act['pb'] == 1 && strtotime($act['minlogdate']) > time()) {
//            echo("<option disabled style='color:blue; font-weight:bold'>--------- Future Event --------
//            </option>\n");
//        }
//
//        // This is the part that gets selected
//        echo("<option data-net-status=\"" . $act['minstat'] . "\" value=\"" . $act['netID'] . "\" class=\" " . $pbcolor . " " . $spcl . "\">
//            " . $act['netcall'] . ", Net #: " . $act['netID'] . " --> " . $activity . " " . $logdate . " </option>\n");
    } // End while

//var_dump($activeNets);
$json =    json_encode($activeNets);
//    var_dump($json);
    echo $json;


} catch (PDOException $e) {
    echo "xError: " . $e->getMessage();
} // end try
?>