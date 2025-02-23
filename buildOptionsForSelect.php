<?php
// buildOptionsForSelect.php
// This program creates the dropdown for previous net selection
// Written: 2017
// Updated: 2024-07-05
// Modified: 2024-09-24

function buildOptionsForSelect($db_found) {
    $output = '';

    if (!isset($db_found) || !$db_found) {
        error_log("Database connection failed in buildOptionsForSelect.php");
        return "Database connection failed";
    }

    // Read the time diff from UTC for the local time
    $tzdiff = isset($_COOKIE['tzdiff']) ? ($_COOKIE['tzdiff'] / -60) . ":00" : "-0:00";
    $sql = "SELECT ANY_VALUE(netID) AS netID, ANY_VALUE(status) AS status, ANY_VALUE(activity) AS activity,
                   ANY_VALUE(netcall) AS netcall, ANY_VALUE(frequency) AS frequency, MIN(status) AS minstat,
                   MIN(CONVERT_TZ(dttm, '+00:00', :tzdiff)) AS mindttm,
                   MIN(CONVERT_TZ(logdate, '+00:00', :tzdiff)) AS minlogdate,
                   MAX(CONVERT_TZ(timeout, '+00:00', :tzdiff)) AS timeout,
                   IF(MIN(CONVERT_TZ(logdate, '+00:00', :tzdiff)) IS NULL,
                      DATE(MIN(CONVERT_TZ(dttm, '+00:00', :tzdiff))),
                      DATE(MIN(CONVERT_TZ(logdate, '+00:00', :tzdiff)))) AS dteonly,
                   IF(MIN(CONVERT_TZ(logdate, '+00:00', :tzdiff)) IS NULL,
                      DAYNAME(MIN(CONVERT_TZ(dttm, '+00:00', :tzdiff))),
                      DAYNAME(MIN(CONVERT_TZ(logdate, '+00:00', :tzdiff)))) AS daynm,
                   POSITION('Meeting' IN ANY_VALUE(activity)) AS meetType,
                   POSITION('Event' IN ANY_VALUE(activity)) AS eventType,
                   SUM(IF(timeout IS NULL, 1, 0)) AS lo,
                   ANY_VALUE(pb) AS pb,
                   (CASE WHEN ANY_VALUE(pb) = '0' THEN '' WHEN ANY_VALUE(pb) = '1' THEN 'pbBlue' ELSE '' END) AS pbcolor
            FROM `NetLog`
            WHERE (CONVERT_TZ(dttm, '+00:00', :tzdiff) >= NOW() - INTERVAL 39 DAY AND pb = 1)
               OR (CONVERT_TZ(logdate, '+00:00', :tzdiff) >= NOW() - INTERVAL 10 DAY AND pb = 0)
            GROUP BY netID
            ORDER BY netID DESC";

    try {
        $stmt = $db_found->prepare($sql);
        $stmt->bindParam(':tzdiff', $tzdiff, PDO::PARAM_STR);
        $stmt->execute();
        $firstDate = true;
        $thisDate  = date('Y-m-d');
        while ($act = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $pbcolor = ($act['minstat'] < $act['lo']) ? ($act['pb'] > 0 ? 'pbBlue' : 'green') : '';
            $spcl = ($act['meetType'] > 0 || $act['eventType'] > 0) ? "spcl" : "";
            if ($act['pb'] == 0) {
                $activity = preg_replace('/\s\s+/', ' ', $act['activity']);
                $dteonly = $act['dteonly'];
                $logdate = "of $dteonly";
                $daynm = $act['daynm'];
            } else {
                $activity = preg_replace(['/\s\s+/', "/Event/"], [' ', ''], $act['activity']);
                $logdate = "For " . ($act['minlogdate'] ?? '');
                $daynm = "Pre-Built Net(s) For: " . $act['daynm'];
                $dteonly = $act['minlogdate'] ? date("Y/m/d", strtotime($act['minlogdate'])) : '';
            }
            if ($thisDate != $act['dteonly']) {
                $thisDate = $act['dteonly'];
                $output .= "<option disabled style='color:blue; font-weight:bold'>--------------- $daynm ---==========--- $dteonly ----------------------</option>\n";
            }
            if ($act['pb'] == 1 && $act['minlogdate'] && strtotime($act['minlogdate']) > time()) {
                $output .= "<option disabled style='color:blue; font-weight:bold'>--------- Future Event --------</option>\n";
            }
            $output .= "<option data-net-status=\"{$act['minstat']}\" value=\"{$act['netID']}\" class=\"$pbcolor $spcl\">{$act['netcall']}, Net #: {$act['netID']} --> $activity $logdate</option>\n";
        }
    } catch (PDOException $e) {
        error_log("Error in buildOptionsForSelect.php: " . $e->getMessage());
        $output = "<option disabled>Error loading net options. Please try again later.</option>";
    }
    //error_log("Ending buildOptionsForSelect.php");
    return $output;
}

// If this file is called directly, echo the output
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    if (!isset($db_found)) {
        require_once "dbConnectDtls.php";
    }
    echo buildOptionsForSelect($db_found);
}
?>