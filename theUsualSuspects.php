<!doctype html>
<?php
// theUsualSuspects.php
// V2 Updated: 2024-05-30
// This program produces a report of the callsign being called and opens as a modal or window

ini_set('display_errors', 1);
error_reporting(E_ALL ^ E_NOTICE);

require_once "dbConnectDtls.php";

$netcall = $_POST['netcall'] ?? '';
$numofmo = $_POST['numofmo'] ?? 6;
//echo ("netcall: $netcall <br>numofmo: $numofmo");

$and1 = '';
$netcall = strtoupper($netcall);
$state = '';

if ($state !== '') {
    $and1 = "AND state = '$state'";
}

// Function to convert time in seconds to days, hours, min, seconds
function secondsToDHMS($seconds) {
    $s = (int)$seconds;
    return sprintf('%d:%02d:%02d:%02d', $s/86400, $s/3600%24, $s/60%60, $s%60);
}

$sql = "
    SELECT callsign, Fname, Lname, CONCAT(state, ' ', county, ' ', district) as place,
           COUNT(callsign) as cnt_call,
           district
    FROM NetLog
    WHERE netcall = ?
      AND logdate > DATE_SUB(now(), INTERVAL ? MONTH)
    GROUP BY callsign
    ORDER BY `NetLog`.`district`, cnt_call DESC, callsign ASC
";

$stmt = $db_found->prepare($sql);
$stmt->execute([$netcall, $numofmo]);

$rowno = 0;
$liteItUp = '';
$lastDist = null;
$listing = '';

while ($row = $stmt->fetch()) {
    if ($lastDist !== $row['district']) {
        $liteItUp = "style=\"background-color:lightblue\"";
        $lastDist = $row['district'];
    } else {
        $liteItUp = "";
    }

    $rowno++;
    $Fname = ucfirst(strtolower($row['Fname']));
    $Lname = $row['Lname'];

    $listing .= "<tr $liteItUp>
        <td>$rowno</td>
        <td>$row[callsign]</td>
        <td>$Fname</td>
        <td>$Lname</td>
        <td>$row[place]</td>
        <td>$row[cnt_call]</td>
    </tr>";
}
?>

<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Stations Associated With This Net</title>
    <meta name="author" content="Kaiser" />
    <link rel="shortcut icon" type="image/x-icon" href="images/favicon-32x32.png">
    <link href='https://fonts.googleapis.com/css?family=Allerta' rel='stylesheet' type='text/css'>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <style>
        html {
            width: 100%;
        }
        h2 {
            column-span: all;
        }
        .prime {
            columns: 20px 2;
            column-gap: 10px;
        }
    </style>
</head>
<body>
    <h1>The Usual Suspects</h1>
    <h2>This is a list of the stations that have checked into the <?php echo $netcall ?> net in the past <?php echo $numofmo ?> months</h2>
    <div class="prime">
        <table>
            <tr>
                <th class="<?php echo $liteItUp ?>"></th>
                <th>CALL</th>
                <th>First</th>
                <th>Last</th>
                <th>St, CO, Dist</th>
                <th>Count</th>
            </tr>
            <?php echo $listing; ?>
        </table>
    </div>
    <div>
        <br><br>
        theUsualSuspects.php
    </div>
</body>
</html>