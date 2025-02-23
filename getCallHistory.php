<?php
// getCallHistory.php
// This program produces a report of the callsign being called, it opens as a modal or window
// V2 Updated: 2024-06-06

ini_set('display_errors', 1);
error_reporting(E_ALL ^ E_NOTICE);

require_once "dbConnectDtls.php";
require_once "getCrossRoads.php";

$call = isset($_GET['call']) ? $_GET['call'] : '';
$call = strtoupper($call[0]);

$recordID = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Function to convert seconds to days, hours, min, seconds
function secondsToDHMS($seconds) {
    $s = (int)$seconds;
    return sprintf('%d:%02d:%02d:%02d', $s/86400, $s/3600%24, $s/60%60, $s%60);
}

$sql = "
    SELECT callsign, grid, creds, email, tactical, district, id, county, state, home,
           CONCAT(Fname, ' ', Lname) as name,
           latitude, longitude
    FROM stations
    WHERE callsign = :call
";

$stmt = $db_found->prepare($sql);
$stmt->execute([':call' => $call]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

$state      = $result['state'] ?? '';
$county     = $result['county'] ?? '';
$tactical   = $result['tactical'] ?? '';
$district   = $result['district'] ?? '';
$id         = $result['id'] ?? '';
$name       = $result['name'] ?? '';
$creds      = $result['creds'] ?? '';
$email      = $result['email'] ?? '';
$Ahome      = explode(',', $result['home'] ?? '');
$grid       = $Ahome[2] ?? '';
$koords     = $Ahome[0] ?? '' . ',' . $Ahome[1] ?? '';
$koords2    = "lat=" . $Ahome[0] ?? '' . "&lon=" . $Ahome[1] ?? '';

$crossroads = getCrossRoads($result['latitude'] ?? '', $result['longitude'] ?? '');

$sql2 = "
    SELECT c.fccid, c.callsign, c.full_name, c.address1, c.city, c.state, c.zip,
           b.class,
           CASE
               WHEN b.class = 'A' THEN 'Advanced'
               WHEN b.class = 'E' THEN 'Extra'
               WHEN b.class = 'T' THEN 'Technician'
               WHEN b.class = 'N' THEN 'Novice'
               WHEN b.class = 'P' THEN 'Extra'
               WHEN b.class = 'G' THEN 'General'
               ELSE 'Club'
           END AS hamclass
    FROM netcontrolcp_fcc_amateur.en c
    JOIN netcontrolcp_fcc_amateur.am b ON c.fccid = b.fccid AND c.callsign = b.callsign
    WHERE c.callsign = :call
    ORDER BY c.fccid DESC
    LIMIT 1
";

$stmt3 = $db_found->prepare($sql2);
$stmt3->execute([':call' => $call]);
$result = $stmt3->fetch(PDO::FETCH_ASSOC);

$hamclass = $result['hamclass'] ?? 'UNK';
$address  = $result['address1'] ?? '';
$city     = $result['city'] ?? '';
$state    = $result['state'] ?? '';
$zip      = $result['zip'] ?? '';
$fullname = $result['full_name'] ?? '';

if (empty($name)) {
    $name = trim($fullname);
}

$sql3 = "
    SELECT COUNT(a.callsign) AS logCount,
           MAX(a.tt) AS tt,
           MAX(a.recordID) AS recordID,
           MIN(a.logdate) AS firstLogDte,
           MAX(a.logdate) AS lastLogDte,
           MIN(a.netID) AS minID,
           MAX(a.netID) AS maxID,
           SUM(a.timeonduty) AS TOD,
           SUM(IF(YEAR(a.logdate) = '2016', 1, 0)) AS y2016,
           SUM(IF(YEAR(a.logdate) = '2017', 1, 0)) AS y2017,
           SUM(IF(YEAR(a.logdate) = '2018', 1, 0)) AS y2018,
           SUM(IF(YEAR(a.logdate) = '2019', 1, 0)) AS y2019,
           SUM(IF(YEAR(a.logdate) = '2020', 1, 0)) AS y2020,
           SUM(IF(YEAR(a.logdate) = '2021', 1, 0)) AS y2021,
           SUM(IF(YEAR(a.logdate) = '2022', 1, 0)) AS y2022,
           SUM(IF(YEAR(a.logdate) = '2023', 1, 0)) AS y2023,
           SUM(IF(YEAR(a.logdate) = '2024', 1, 0)) AS y2024,
           SUM(IF(YEAR(a.logdate) = '2016', a.timeonduty, 0)) AS h2016,
           SUM(IF(YEAR(a.logdate) = '2017', a.timeonduty, 0)) AS h2017,
           SUM(IF(YEAR(a.logdate) = '2018', a.timeonduty, 0)) AS h2018,
           SUM(IF(YEAR(a.logdate) = '2019', a.timeonduty, 0)) AS h2019,
           SUM(IF(YEAR(a.logdate) = '2020', a.timeonduty, 0)) AS h2020,
           SUM(IF(YEAR(a.logdate) = '2021', a.timeonduty, 0)) AS h2021,
           SUM(IF(YEAR(a.logdate) = '2022', a.timeonduty, 0)) AS h2022,
           SUM(IF(YEAR(a.logdate) = '2023', a.timeonduty, 0)) AS h2023,
           SUM(IF(YEAR(a.logdate) = '2024', a.timeonduty, 0)) AS h2024
    FROM ncm.NetLog a
    WHERE a.callsign = :call
      AND a.netID <> 0
      AND a.logdate <> 0
";

$stmt2 = $db_found->prepare($sql3);
$stmt2->execute([':call' => $call]);
$result = $stmt2->fetch(PDO::FETCH_ASSOC);

$logCount   = $result['logCount'] ?? 0;
$firstLogD  = $result['firstLogDte'] ?? '';
$lastLogDte = $result['lastLogDte'] ?? '';
$minID      = $result['minID'] ?? 0;
$maxID      = $result['maxID'] ?? 0;
$activity   = $result['activity'] ?? '';
$district   = $result['district'] ?? '';
$TOD        = $result['TOD'] ?? 0;
$tt         = $result['tt'] ?? 0;
$recordID   = $result['recordID'] ?? 0;

$startYear = 2016;
$currentYear = date('Y');

// Initialize variables for totals
$yearTotals = 0;
$yearHours = 0;

// Generate variables for each year dynamically
for ($year = $startYear; $year <= $currentYear; $year++) {
    ${"y$year"} = $result["y$year"] ?? 0;
    ${"h$year"} = $result["h$year"] ?? 0;
}

// Start what3word stuff
// ======================================
// This part deals with the what3words address
// https://docs.what3words.com/api/v3/

$curl = curl_init();

curl_setopt_array($curl, array(
    CURLOPT_URL => "https://api.what3words.com/v3/convert-to-3wa?key=5WHIM4GD&coordinates=" . urlencode($koords) . "&language=en&format=json",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "GET",
));

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
    throw new Exception("CURL Error #:" . $err);
}

$w3w = json_decode($response, true);

if ($w3w === null && json_last_error() !== JSON_ERROR_NONE) {
    throw new Exception("Failed to parse JSON response");
}

$what3words = $w3w['words'] ?? '';
$themap     = $w3w['map'] ?? '';
$w3wmap     = "<a href='https://map.what3words.com/" . urlencode($what3words) . "?maptype=osm' target='_blank'>Map</a>";

// End what3word stuff
// ======================================
// Start fcc county stuff

// use: https://geo.fcc.gov/api/census/area?lat=39.202911&lon=-94.60288&format=json to get county

$curl = curl_init();

curl_setopt_array($curl, array(
    CURLOPT_URL => "https://geo.fcc.gov/api/census/area?" . $koords2 . "&format=json",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "GET",
));

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
    throw new Exception("CURL Error #:" . $err);
}

$fccdata = json_decode($response, true);

if ($fccdata === null && json_last_error() !== JSON_ERROR_NONE) {
    throw new Exception("Failed to parse JSON response");
}

if (!empty($county)) {
    $county = "$county County";
}
if (!empty($district)) {
    $district = "District: $district,";
}
if (!empty($creds)) {
    $creds = "Creds: $creds";
}

$fiAddr = "<a href='https://aprs.fi/#!addr=" . urlencode($koords) . "' target='_blank'>Map</a>";
$tod = secondsToDHMS($TOD);
$name = ucwords(strtolower($name));

// Format hours for each year
for ($year = $startYear; $year <= $currentYear; $year++) {
    ${"h$year"} = secondsToDHMS(${"h$year"});
}

// Is there a headshot for this person?
$headshot = file_exists("headshots/$id.JPG") ? "$id.JPG" : (file_exists("headshots/$id.png") ? "$id.png" : 'gen.png');

?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel='stylesheet' type='text/css' href='https://fonts.googleapis.com/css?family=Share+Tech+Mono'>
    <link rel='stylesheet' type='text/css' href='https://fonts.googleapis.com/css?family=Allerta'>
    <style>
        /* CSS styles... */
    </style>
</head>
<body>
<div id='lb1'>
    <br>
    <div class='container'>
        <p class='item1' style='color:red'>
            <?= htmlspecialchars($name) ?>
            <br>
            <a href='mailto:<?= htmlspecialchars($email) ?>?Subject=NCM' target='_top'><?= htmlspecialchars($email) ?></a>
        </p>
        <p class='item2'>
            <img src='headshots/<?= htmlspecialchars($headshot) ?>' alt='Headshot'>
        </p>
        <p class='item3' style='color:red'>
            <?= htmlspecialchars($callsign) ?>
            Class: <?= htmlspecialchars($hamclass) ?>
            <br>
            <?= htmlspecialchars($creds) ?>
        </p>
    </div> <!-- End container -->

    <!-- Location Information -->
    <div class='container2'>
        <p class='b'><a href='https://www.qrz.com/db/<?= htmlspecialchars($call) ?>' target='_blank'><?= htmlspecialchars($call) ?></a></p>
        <span><?= htmlspecialchars($address) ?></span>
        <span><?= htmlspecialchars($city) ?> <?= htmlspecialchars($state) ?> <?= htmlspecialchars($zip) ?></span>
        <span><?= htmlspecialchars($county) ?></span>
        <span style='color:blue; font-weight:bold;'><?= htmlspecialchars($district) ?> Grid: <?= htmlspecialchars($grid) ?></span>
        <span><?= htmlspecialchars($koords) ?></span>
        <span style='color:blue; font-weight:bold;'>Crossroads</span>
        <span><?= htmlspecialchars($crossroads) ?></span>
        <span><br>aprs.fi Map: <?= $fiAddr ?></span>
        <span>what3words: //<?= htmlspecialchars($what3words) ?><br>W3W Map: <?= $w3wmap ?></span>
    </div> <!-- End container -->

    <h3 style='text-align: center; color: green;'>Log-in Activity</h3>
    <table id='reportTOD'>
        <thead>
            <tr>
                <th>Year</th>
                <th>Log Counts</th>
                <th>D:H:M:S on Duty</th>
            </tr>
        </thead>
        <tbody>
            <?php
            for ($year = $startYear; $year <= $currentYear; $year++) {
                $logCount = ${"y$year"};
                $hours = ${"h$year"};

                $yearTotals += $logCount;
                $yearHours += ${"h$year"};
                ?>
                <tr>
                    <td><?= htmlspecialchars($year) ?></td>
                    <td><?= htmlspecialchars($logCount) ?></td>
                    <td><?= htmlspecialchars($hours) ?></td>
                </tr>
                <?php
            }
            ?>
        </tbody>
        <tfoot>
            <tr>
                <td>Total</td>
                <td><?= htmlspecialchars($yearTotals) ?></td>
                <td><?= htmlspecialchars(secondsToDHMS($yearHours)) ?></td>
            </tr>
        </tfoot>
    </table>
    <div class='text-right'>
        <button type='button' onclick='window.close();'>Close</button>
    </div>

    <h5>
        <span class='equalspace'>First: Net # $minID on $firstLogD </span>
			  	  <span class='equalspace'>Last:  Net #$maxID on $lastLogDte </span>
			  </h5>
			   			  
			  $id 
			  <br><br>
			  getCallHistory.php
			  
			  
			  </body></html>
			  </div>
		   ");
?>