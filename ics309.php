<?php
// ics309.php
// V2 Updated: 2024-06-05

ini_set('display_errors', 1);
error_reporting(E_ALL ^ E_NOTICE);

require_once "dbConnectDtls.php";

$q = filter_input(INPUT_GET, "NetID", FILTER_VALIDATE_INT);

if ($q === false) {
    die("Invalid NetID parameter.");
}

// The below SQL is used to report the parent and child nets
$sql = "SELECT subNetOfID, GROUP_CONCAT(DISTINCT netID SEPARATOR ', ') AS children
        FROM NetLog
        WHERE subNetOfID = ?
        ORDER BY netID";
$stmt = $db_found->prepare($sql);
$stmt->bindValue(1, $q, PDO::PARAM_INT);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$children = $row['children'];

$sql1 = "SELECT min(a.logdate) AS minlog,
                 DATE(min(a.logdate)) AS indate,
                 TIME(min(a.logdate)) AS intime,
                 DATE(max(a.timeout)) AS outdate,
                 TIME(max(a.timeout)) AS outtime,
                 a.activity, a.fname, a.lname,
                 a.netcontrol,
                 a.callsign,
                 a.netcall,
                 b.kindofnet, b.box4, b.box5, a.subNetOfID,
                 a.frequency
          FROM NetLog AS a
          INNER JOIN NetKind AS b ON a.netcall = b.call
          WHERE netID = ?
          AND logdate = (SELECT min(logdate)
                         FROM NetLog
                         WHERE netID = ?)";

$stmt = $db_found->prepare($sql1);
$stmt->bindValue(1, $q, PDO::PARAM_INT);
$stmt->bindValue(2, $q, PDO::PARAM_INT);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);

$fname = $row['fname'];
$lname = $row['lname'];
$activity = $row['activity'];
$indate = $row['indate'];
$outdate = $row['outdate'];
$netcntl = $row['callsign'];
$intime = $row['intime'];
$outtime = $row['outtime'];
$name = $row['activity'];
$parent = $row['subNetOfID'];
$freq = $row['frequency'];
$netCall = $row['netcall'];

if ($row['netcontrol'] == "PRM") {
    $netcontrol = "Net Control Operator";
    $netopener = $row['callsign'];
}
?>
<!doctype html>
<!-- This is the ICS-309 report -->
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <title>ICS 309</title>
    <meta name="author" content="Graham"/>
    <link rel="stylesheet" type="text/css" media="all" href="css/ics309.css">
    <link href='https://fonts.googleapis.com/css?family=Allerta' rel='stylesheet' type='text/css'>

    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>

    <style></style>
</head>
<body>
<div class="row1">
    <span class="r1box1">COMM Log<br>ICS 309<br><?php echo htmlspecialchars($netCall); ?></span>
    <span class="r1box2">1. Incident Name: Net#: <?php echo htmlspecialchars($q) . "<br>" . htmlspecialchars($activity); ?></span>
    <span class="r1box3">2. Operational Period (Date/Time)<br>
        From: <?php echo htmlspecialchars($indate) . " " . htmlspecialchars($intime) . "<br>
        To: " . htmlspecialchars($outdate) . " " . htmlspecialchars($outtime); ?>
    </span>
</div>

<div class="row2">
    <span class="r2box1">3. Radio Net Name or Position/Tactical Call</span>
    <span class="r2box2">4. Radio Operator (Name, Call Sign)<br><?php echo htmlspecialchars($netcntl) . " - " . htmlspecialchars($fname) . " " . htmlspecialchars($lname) . " Net Control"; ?></span>
</div>

<div class="row3">
    <span class="r3box1">COMMUNICATIONS LOG</span>
</div>

<div class="logtable">
    <table class="table1">
        <thead id="thead" style="text-align: center;">
        <tr>
            <th class="th1" colspan="1">Time<br>(UTC)</th>
            <th class="th2" colspan="2">FROM:<br>Call Sign/ID | Msg #</th>
            <th class="th3" colspan="2">TO:<br>Call Sign/ID | Msg #</th>
            <th class="th4" colspan="1">Message</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $sql = "SELECT time(TIMESTAMP) as timestamp,
                       ID, callsign, comment, uniqueID
                FROM TimeLog
                WHERE netID = ?
                  AND comment <> 'Initial  Log In'
                  AND comment NOT LIKE '%this id was deleted%'
                  AND comment <> 'The log was closed, ICS-214 Created'
                  AND callsign NOT IN('GENCOMM', 'weather')
                  AND comment <> 'The log was re-opened'
                  AND comment NOT LIKE '%Mode set to:%'
                  AND comment NOT LIKE '%Opened the  net from%'
                ORDER BY timestamp";

        $stmt = $db_found->prepare($sql);
        $stmt->bindValue(1, $q, PDO::PARAM_INT);
        $stmt->execute();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr style=\"height: 17pt\">
                      <td class=\"box4td1\" colspan=\"1\">" . htmlspecialchars($row['timestamp']) . "</td>
                      <td class=\"box4td2\" colspan=\"1\">" . htmlspecialchars($row['callsign']) . "</td>
                      <td class=\"box4td3\" colspan=\"1\">UNK</td>
                      <td class=\"box4td4\" colspan=\"1\">NCO</td>
                      <td class=\"box4td5\" colspan=\"1\">" . htmlspecialchars($row['uniqueID']) . "</td>
                      <td class=\"box4td6\" colspan=\"1\">" . htmlspecialchars($row['comment']) . "</td>
                  </tr>";
        }
        ?>
        </tbody>
    </table>
</div>

<div class="row5">
    <span class="r5box1">6. Prepared By (Name, Call sign)<br><?php echo htmlspecialchars($fname) . " " . htmlspecialchars($lname) . " -- " . htmlspecialchars($netcntl); ?></span>
    <span class="r5box2">7. Date &amp; Time Prepared<br><?php echo date('l jS \of F Y h:i:s A'); ?></span>
    <span class="r5box3">8.<br>Page 1 of</span>
</div>

<p>ics309.php</p>
</body>
</html>