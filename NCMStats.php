<?php
// NCMStats.php
// Used by help.php and index.php
// Updated: 2024-09-05

function convertSecToTime(int $sec): string
{
    $interval = new DateInterval('PT' . $sec . 'S');
    $parts = [
        'y' => 'years',
        'm' => 'months',
        'd' => 'days',
        'h' => 'hours'
    ];

    $formatted = [];
    foreach ($parts as $key => $label) {
        $value = $interval->$key;
        if ($value > 0) {
            $formatted[] = $value . ' ' . ($value === 1 ? rtrim($label, 's') : $label);
        }
    }

    if (empty($formatted)) {
        return '0 hours'; // If no time units found, default to "0 hours"
    }

    return implode(', ', array_slice($formatted, 0, -1)) . ' and ' . end($formatted);
}

try {
    if (!isset($db_found) || !$db_found) {
        require_once "dbConnectDtls.php";
    }

    if (!$db_found) {
        throw new Exception("Database connection failed");
    }

    $sql = "SELECT 
                COUNT(callsign) AS callsigns,
                COUNT(IF(comments LIKE '%first log in%', 1, NULL)) AS newb,
                COUNT(DISTINCT netID) AS netCnt,
                MAX(recordID) AS records,
                SUM(timeonduty) AS TOD,
                COUNT(DISTINCT LEFT(grid, 6)) AS gridCnt,
                COUNT(DISTINCT county) AS countyCnt,
                COUNT(DISTINCT state) AS stateCnt,
                COUNT(DISTINCT callsign) AS cscount,
                COUNT(DISTINCT netcall) AS netcall
            FROM NetLog
            WHERE netID <> 0 AND activity NOT LIKE '%TEST%'";

    $stmt = $db_found->query($sql);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    $callsigns = $result['callsigns'];
    $newb = $result['newb'];
    $netCnt = $result['netCnt'];
    $records = number_format($result['records'], 0);
    $tod = $result['TOD'];
    $gridCnt = $result['gridCnt'];
    $countyCnt = $result['countyCnt'];
    $stateCnt = $result['stateCnt'];
    $cscount = $result['cscount'];
    $netcall = $result['netcall'];
    $volHours = convertSecToTime($tod);

    $sql3 = "SELECT COUNT(DISTINCT org) AS orgCnt FROM NetKind";
    $orgCnt = $db_found->query($sql3)->fetchColumn();

    // Uncomment this to display results
    // echo "$cscount Stations, $netCnt Nets, $records Logins, $volHours of Volunteer Time";

} catch (PDOException $e) {
    error_log("Database query error: " . $e->getMessage());
    // Handle error appropriately (e.g., display a message to the user)
} catch (Exception $e) {
    error_log($e->getMessage());
    die("An error occurred: " . $e->getMessage());
}
