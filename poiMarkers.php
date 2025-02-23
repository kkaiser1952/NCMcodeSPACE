<!doctype html>
<?php

// poiMarkers.php
// This program selects from the DB/NetLog table all the data needed to create the marker points on a map;
// V2 Updated: 2024-06-18

ini_set('display_errors', 1);
error_reporting(E_ALL ^ E_NOTICE);

require_once "dbConnectDtls.php";

if (!$db_found) {
    die("Database connection failed: " . $db_found->errorInfo()[2]);
}

$dupCalls = "";

$sql = "SELECT tactical, latitude, COUNT(latitude)
        FROM poi
        GROUP BY latitude
        HAVING COUNT(latitude) > 1";

$stmt = $db_found->prepare($sql);
$stmt->execute();

while ($duperow = $stmt->fetch(PDO::FETCH_NUM)) {
    $dupCalls .= "$duperow[0],";
}

$POIMarkerList = "";
$listofMarkers = "";
$classNames = "";

$sql = "SELECT GROUP_CONCAT(DISTINCT CONCAT(class, 'L') SEPARATOR ',') AS class
        FROM poi
        GROUP BY class
        ORDER BY class";

$stmt = $db_found->prepare($sql);
$stmt->execute();

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $classNames .= $row['class'] . ",";
}

$classNames = rtrim($classNames, ',');

$sql = "SELECT 
        GROUP_CONCAT(REPLACE(tactical, '-', '') SEPARATOR ', ') as tackList,
        CONCAT('var ', class, 'List = L.layerGroup([', GROUP_CONCAT(REPLACE(tactical, '-', '') SEPARATOR ', '), '])') as MarkerList
        FROM poi
        GROUP BY class
        ORDER BY class";

$stmt = $db_found->prepare($sql);
$stmt->execute();

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $POIMarkerList .= $row['MarkerList'] . ";";
    $listofMarkers .= $row['tackList'] . ",";
}

$class = "";
$overlayListNames = "";

$sql = "SELECT class, 
        GROUP_CONCAT(REPLACE(tactical, '-', '') SEPARATOR ', ') as tackList                     
        FROM poi
        GROUP BY class
        ORDER BY class";

$stmt = $db_found->prepare($sql);
$stmt->execute();

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $class .= $row['class'] . " ";
    $overlayListNames .= '"' . $class . '": ' . $row['tackList'] . ",\r\n";
}

$H = 0;  // Hospitals
$E = 0;  // EOC
$R = 0;  // Repeaters
$P = 0;  // Police / Sheriff / CHP
$S = 0;  // SkyWarn
$F = 0;  // Firestations
$A = 0;  // Aviation
$G = 0;  // State / Federal / 
$T = 0;  // Town Hall
$K = 0;  // RF Holes 
$Y = 0;  // Tornado (siren)

$markNO = '';
$grid = '';
$rowno = 0;
$tactical = "";
$gs = "";
$poiBounds = "[";
$poiMarkers = "";

$sql = "SELECT id,  name,  Notes, w3w,
        LOWER(class) as class, 
        address, latitude, longitude, grid,
        CONCAT(latitude, ',', longitude) as koords,
        CONCAT(name, ' ', 
               address, ' ', 
               Notes, ' ',
               latitude, ', ', 
               longitude, ' ', 
               altitude, ' Ft.'
        ) as addr,
        REPLACE(REPLACE(REPLACE(tactical, '-', ''), '#', ''), '!', '') AS tactical,
        callsign,
        CONCAT(class, id) as altTactical,
        dttm
        FROM poi
        ORDER BY class";

$stmt = $db_found->prepare($sql);
$stmt->execute();

$rowno = 0;
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $rowno++;
    $tactical = $row['tactical'];
    
    if ($row['tactical'] === "") {
        $tactical = $row['altTactical'];
    }   
        
    $icon = "";
    $poiBounds .= "[$row[koords]],";  
    
    switch ($row['class']) {
        case "hospital": $H++;  $iconName = "firstaidicon"; $markNO = "H$H";  
                         $markername = "images/markers/firstaid.png";  
                         $poimrkr = "hosmrkr";  break;
                         
        case "eoc":      $E++;  $iconName = "eocicon"; $markNO = "E$E";  
                         $markername = "images/markers/eoc.png";       
                         $poimrkr = "eocmrkr";  break;
                         
        case "repeater": $R++;  $iconName = "repeatericon"; $markNO = "R$R";  
                         $markername = "markers/repeater.png";  
                         $poimrkr = "rptmrkr";  break;
                         
        case "sheriff":  $X++;  $iconName = "policeicon"; $markNO = "X$X";  
                         $markername = "images/markers/police.png";    
                         $poimrkr = "polmrkr";  break;
                         
        case "skywarn":  $S++;  $iconName = "skywarnicon"; $markNO = "S$S";  
                         $markername = "images/markers/skywarn2.png";   
                         $poimrkr = "skymrkr";  break;
                         
        case "fire":     $F++;  $iconName = "fireicon"; $markNO = "F$F";  
                         $markername = "images/markers/fire.png";   
                         $poimrkr = "firemrkr";  break;
                  
        case "police":   $P++;  $iconName = "policeicon"; $markNO = "P$P";  
                         $markername = "images/markers/police.png";    
                         $poimrkr = "polmrkr";  break; 
                         
        case "chp":      $C++;  $iconName = "policeicon"; $markNO = "P$P";  
                         $markername = "images/markers/police.png";    
                         $poimrkr = "polmrkr";  break; 
                         
        case "state":    $G++;  $iconName = "govicon"; $markNO = "G$G";  
                         $markername = "images/markers/gov.png";    
                         $poimrkr = "govmrkr";  break; 
                         
        case "federal":  $G++;  $iconName = "govicon"; $markNO = "G$G";  
                         $markername = "images/markers/gov.png";    
                         $poimrkr = "govmrkr";  break; 
                    
        case "townhall": $T++;  $iconName = "govicon"; $markNO = "T$T";  
                         $markername = "images/markers/gov.png";    
                         $poimrkr = "govmrkr";  break;
        
        case "aviation": $A++;  $iconName = "govicon"; $markNO = "A$A";  
                         $markername = "images/markers/aviation.png";    
                         $poimrkr = "aviationmrkr";  break;     
                         
        case "rfhole":   $K++;  $iconName = "holeicon"; $markNO = "K$K";
                         $markername = "images/markers/hole.svg";    
                         $poimrkr = "aviationmrkr";  break;
                         
        case "siren":    $Y++;  $iconName = "tornadoicon"; $markNO = "K$K";
                         $markername = "images/markers/siren.png";    
                         $poimrkr = "sirenmrkr";  break;
                                                         
        default:         $D++;  $iconName = "default";  $markNO = "D$D";
                         $markername = "images/markers/blue_50_flag.png";
                         $poimrkr = "flagmrkr";
    }

    $dup = 0;
    if ($row['id'] == 144) {
        $dup = 50;
    }

    $poiMarkers .= "
    var $tactical = new L.marker(new L.LatLng({$row['latitude']}, {$row['longitude']}), { 
        icon: L.icon({iconUrl: '$markername', iconSize: [32, 34]}),
        title: " . json_encode("{$row['tactical']} {$row['name']}  {$row['Notes']} {$row['koords']}") . ",
    }).addTo(fg).bindPopup(" . json_encode("{$row['tactical']}<br><br>Name: {$row['name']}<br><br>W3W:<br><b style='color:red'> {$row['w3w']}</b><br>
        <br>Crossroads:<br> <b style='color:red'>{$row['address']}</b> <br><br> Grid: {$row[grid]}<br>{$row['Notes']}<br> {$row['koords']}<br><br>Created: {$row['dttm']}<br><br>poiMarkers.php
    ") . ");

    $('{$row['class_lower']}'._icon).addClass('$poimrkr');
    ";
}

$poiBounds = substr($poiBounds, 0, -1) . "]"; 

$POIMarkerList = substr($POIMarkerList, 0, -1) . "]);\n";        

$poiMarkers = substr($poiMarkers, 0, -1) . ";\n";                 

$listofMarkers = substr($listofMarkers, 0, -1) . "";              

$overlayListNames = substr($overlayListNames, 0, -1) . "";        

?>