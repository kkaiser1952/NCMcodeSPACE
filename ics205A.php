<?php
// ics205A.php
// INCIDENT RADIO COMMUNICATIONS PLAN (ICS 205A)
// V2 Updated: 2024-06-03

/* https://training.fema.gov/emiweb/is/icsresource/assets/ics%20forms/ics%20form%20205,%20incident%20radio%20communications%20plan%20(v2).pdf 
*/

ini_set('display_errors', 1);
error_reporting(E_ALL ^ E_NOTICE);

require_once "dbConnectDtls.php";

$q = filter_input(INPUT_GET, "NetID", FILTER_VALIDATE_INT);

if ($q === false) {
    die("Invalid NetID parameter.");
}

$sql = "SELECT frequency,
               CASE
                   WHEN CONVERT(SUBSTRING(frequency, 1, 3), UNSIGNED INTEGER) > 400.0 THEN 'UHF'
                   WHEN CONVERT(SUBSTRING(frequency, 1, 3), UNSIGNED INTEGER) > 140.0 THEN 'VHF'
                   WHEN CONVERT(SUBSTRING(frequency, 1, 3), UNSIGNED INTEGER) < 140.0 THEN 'HF'
                   ELSE 'Unknown'
               END AS 'band'
        FROM NetLog
        WHERE netID = ?
        AND frequency <> ''";

$stmt = $db_found->prepare($sql);
$stmt->bind_param("i", $q);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$freq = $row['frequency'];
$band = $row['band'];

$sql1 = "SELECT min(a.logdate) AS minlog,
                 DATE(min(a.logdate)) AS indate,
                 TIME(min(a.logdate)) AS intime,
                 DATE(max(a.timeout)) AS outdate,
                 TIME(max(a.timeout)) AS outtime,
                 a.activity, a.fname, a.lname,
                 a.netcontrol,
                 a.callsign, a.netcall,
                 b.kindofnet, b.box4, b.box5
          FROM NetLog AS a
          INNER JOIN NetKind AS b ON a.netcall = b.call
          WHERE netID = ?
          AND logdate = (SELECT min(logdate)
                         FROM NetLog
                         WHERE netID = ?)";

$stmt = $db_found->prepare($sql1);
$stmt->bind_param("ii", $q, $q);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

$fname = $row['fname'];
$lname = $row['lname'];
$activity = $row['activity'];
$indate = $row['indate'];
$outdate = $row['outdate'];
$netcntl = $row['callsign'];
$intime = $row['intime'];
$outtime = $row['outtime'];
$name = $row['kindofnet'];

if ($row['netcontrol'] == "PRM") {
    $netcontrol = "Net Control Operator";
    $netopener = $row['callsign'];
}
?>
<!doctype html>

<html lang="en">
<head>
    <meta charset="utf-8"/>
    <title>ICS 205A</title>
    <meta name="author" content="Kaiser"/>
    <link rel="stylesheet" type="text/css" media="all" href="css/ics205.css">
    <link href='https://fonts.googleapis.com/css?family=Allerta' rel='stylesheet' type='text/css'>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <style>
        body table {
            width: 100%;
        }
    </style>
</head>
<body>
<div id="tableDiv">
    <table class="table1">
        <tr>
            <td class="box1">
                <p style="font-weight: bold;">ICS 205A Communications List</p>
                <br><br>
                <b>1. Incident Name:</b><br>Log:#<?php echo htmlspecialchars($q) . "/" . htmlspecialchars($row['activity']); ?>
            </td>
            <td class="box3">
                <b>2. Operational Period:</b><br><br>
                <b>Date From:</b> <?php echo htmlspecialchars($indate); ?> <b>To:</b> <?php echo htmlspecialchars($outdate); ?>
                <br>
                <b>Time From:</b> <?php echo htmlspecialchars($intime); ?> <b>To:</b> <?php echo htmlspecialchars($outtime); ?>
            </td>
        </tr>
    </table>
    <table class="table1">
        <tr>
            <td colspan="3">
                <b>3. Basic Local Communications Information:</b>
            </td>
        </tr>
        <tr>
            <th class="box3-1">Band and Incident Assigned Position</th>
            <th class="box3-2">Name (Alphabetized)</th>
            <th class="box3-3">Method(s) of Contact (phone, pager, cell, etc.)</th>
        </tr>

        <?php
        $sql1 = "SELECT band, fname, lname, callsign, Mode, email, tactical, netcontrol, phone,
                        (CASE
                            WHEN lname = '' THEN tactical
                            ELSE lname
                         END) AS lname,
                        (CASE
                            WHEN netcontrol = 'PRM' THEN 'Primary Net Control'
                            WHEN netcontrol = 'Log' THEN 'Primary Net Logger'
                            WHEN netcontrol = '2nd' THEN 'Secondary Net Control'
                            WHEN netcontrol = 'LSN' THEN 'Net Liaison'
                            WHEN netcontrol = 'EM' THEN 'Primary Net Logger'
                            WHEN netcontrol = 'PIO' THEN 'Public Information Officer'
                            ELSE 'Operator'
                         END) AS role
                 FROM NetLog
                 WHERE netID = ?
                 ORDER BY lname";

        $stmt = $db_found->prepare($sql1);
        $stmt->bind_param("i", $q);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $bonusRole = explode(">", $row['tactical'])[1] ?? '';

            echo "<tr>
                    <td style='width: 25%'>" . htmlspecialchars($row['band']) . " " . htmlspecialchars($row['role']) . " " . htmlspecialchars($bonusRole) . "</td>
                    <td>" . htmlspecialchars($row['lname']) . ", " . htmlspecialchars($row['fname']) . ", " . htmlspecialchars($row['callsign']) . "</td>
                    <td>" . htmlspecialchars($band) . " - " . htmlspecialchars($freq) . ", " . htmlspecialchars($row['Mode']) . " " . htmlspecialchars($row['email']) . " " . htmlspecialchars($row['phone']) . "</td>
                  </tr>";
        }
        ?>

        <tr>
            <td>4. Prepared by: <br><?php echo htmlspecialchars($fname) . " " . htmlspecialchars($lname); ?></td>
            <td>Position/Title: Net Control Operator</td>
            <td>Signature</td>
        </tr>
        <tr>
            <td>ICS 205A</td>
            <td>IAP Page</td>
            <td>Date/Time: <?php echo htmlspecialchars($outdate) . " " . htmlspecialchars($outtime); ?></td>
        </tr>
    </table>
</div>
<p>ics205A.php</p>
</body>
</html>