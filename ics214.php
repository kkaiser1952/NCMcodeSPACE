<?php
// ics214.php
// Written: A Long Time Ago
// Updated: 2024-09-16

// This report has the new ncm_reports.css, included by Claude

declare(strict_types=1);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once "dbFunctions.php";

// Ensure $db_found is available and is a PDO instance
if (!isset($db_found) || !($db_found instanceof PDO)) {
    die("Database connection error. Please contact the administrator.");
}

$pdo = $db_found; // Use $db_found as the PDO connection

$q = filter_input(INPUT_GET, "NetID", FILTER_VALIDATE_INT);
if ($q === false || $q === null || $q <= 0) {
    die("Invalid or missing NetID parameter.");
}

// Function to execute query and fetch a single row
function fetchSingleRow(PDO $pdo, string $sql, array $params = []): ?array {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

// Function to execute query and fetch all rows
function fetchAllRows(PDO $pdo, string $sql, array $params = []): array {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch children nets
$sql = "SELECT GROUP_CONCAT(DISTINCT netID SEPARATOR ', ') AS children
        FROM NetLog
        WHERE subNetOfID = :netId
        ORDER BY netID";
$children = fetchSingleRow($pdo, $sql, [':netId' => $q])['children'] ?? '';

// Fetch net details
$sql = "SELECT min(logdate) AS minlog,
               DATE(min(logdate)) AS indate,
               TIME(min(logdate)) AS intime,
               DATE(max(timeout)) AS outdate,
               TIME(max(timeout)) AS outtime,
               activity, netcall, subNetOfID,
               frequency
        FROM NetLog
        WHERE netID = :netId
        AND logdate <> 0";
$netDetails = fetchSingleRow($pdo, $sql, [':netId' => $q]);

// Fetch log preparer
$sql = "SELECT CONCAT(Fname, ' ', Lname) AS LogPrep
        FROM NetLog
        WHERE netID = :netId
        AND (netcontrol = 'PRM' OR netcontrol = 'LOG')
        ORDER BY CASE WHEN netcontrol = 'PRM' THEN 1 ELSE 2 END
        LIMIT 1";
$LogPrep = fetchSingleRow($pdo, $sql, [':netId' => $q])['LogPrep'] ?? '';

// Fetch net kind details
$sql = "SELECT box4, box5, kindofnet
        FROM NetKind
        WHERE `call` = :netcall";
$netKind = fetchSingleRow($pdo, $sql, [':netcall' => $netDetails['netcall'] ?? '']);

$box5 = $netKind['box5'] ?? 'Volunteer Amateur Radio Operators (Hams)';

// Fetch record counts
$recCount = fetchSingleRow($pdo, "SELECT COUNT(*) AS count FROM NetLog WHERE netID = :netId", [':netId' => $q])['count'] ?? 0;
$actCount = fetchSingleRow($pdo, "SELECT COUNT(*) AS count FROM TimeLog WHERE netID = :netId", [':netId' => $q])['count'] ?? 0;

$rowCount = $recCount + $actCount + 13;

// Fetch net log details
$sql = "SELECT ID, callsign, fname, lname, netcontrol,
               TRIM(tactical) AS tactical,
               TRIM(email) AS email,
               TRIM(creds) AS creds,
               TRIM(timeonduty) AS tmd,
               TRIM(sec_to_time(timeonduty)) AS tod,
               TRIM(CONCAT_WS('  ', city, state, county, ' Co., Dist.', district)) AS dist,
               TRIM(band) AS band,
               TRIM(team) AS team
        FROM NetLog
        WHERE netID = :netId
        AND (timeout <> 0 AND logdate <> 0 OR RIGHT(callsign, 2) = '-U')
        ORDER BY logdate";
$netLogDetails = fetchAllRows($pdo, $sql, [':netId' => $q]);

// Fetch time log details
$sql = "SELECT TIMESTAMP, ID, callsign, comment
        FROM TimeLog
        WHERE netID = :netId
        ORDER BY timestamp";
$timeLogDetails = fetchAllRows($pdo, $sql, [':netId' => $q]);

// Calculate man hours
$manHours = array_sum(array_column($netLogDetails, 'tmd'));
$hours = floor($manHours / 3600);
$mins = floor($manHours / 60 % 60);
$secs = floor($manHours % 60);
$timeFormat = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

// HTML generation starts here
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>ICS 214</title>
    <meta name="Keith Kaiser" content="Graham">
    <link rel="stylesheet" type="text/css" media="all" href="css/ncm_reports.css">
    <link href='https://fonts.googleapis.com/css?family=Allerta' rel='stylesheet' type='text/css'>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script>
        // Your JavaScript code here (showDTTM function, etc.)
    </script>
</head>
<body>
    <div class="report ics214">
        <table class="report-table">
            <!-- Table rows for ICS 214 form -->
            <?php
            // Generate table rows using the fetched data
            foreach ($netLogDetails as $row) {
                $nc = match ($row['netcontrol']) {
                    "PRM" => "Primary Net Control",
                    "2nd" => "Secondary Net Control",
                    "3rd" => "Tertiary Net Control",
                    "RELAY" => "Relay Station",
                    "PIO" => "Public Information Officer",
                    "Log" => "Net Logger",
                    "LSN" => "Liaison to ...",
                    "EM" => "Emergency Manager",
                    default => "Operator",
                };

                $creds = trim($row['creds']);
                if (!empty($creds)) {
                    $creds .= ',';
                }

                echo "<tr class=\"report-row\">
                          <td class=\"box-cell\">
                              {$row['callsign']} 
                              <span class=\"spread\">{$row['fname']} {$row['lname']}</span>
                              <span class=\"email\">{$row['email']}</span>
                          </td>
                          <td class=\"box-cell\">
                              {$row['band']} $nc 
                              <span class=\"spread\">{$row['tactical']}</span>
                          </td>
                          <td class=\"box-cell\">
                               <span class=\"tod\">{$row['tod']}</span>
                               <span class=\"creds\">$creds {$row['dist']}</span>
                               <span class=\"team spread\">{$row['team']}</span>
                          </td>
                      </tr>";
            }

            echo "<tr class=\"report-row summary\">
                  <td>{$recCount} Stations</td>
                  <td class=\"bold-text\">Total Volunteer Hours</td>
                  <td>{$timeFormat}</td>
                  </tr>";

            foreach ($timeLogDetails as $row) {
                echo "<tr class=\"report-row timeline\">
                          <td class=\"timestamp\">{$row['TIMESTAMP']}</td>
                          <td class=\"comment\" colspan=\"2\">{$row['callsign']}: {$row['comment']}</td>
                      </tr>";
            }
            ?>
        </table>
    </div>
    <footer class="report-footer">
        <p>net-control.space/ICS-214.php</p>
    </footer>
</body>
</html>