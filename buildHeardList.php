<?php
require_once "dbConnectDtls.php";

$netID = intval($_POST["q"]);

// Set the maximum length for GROUP_CONCAT
$sql0 = "SET GLOBAL group_concat_max_len=2048";
mysql_query($sql0);

$sql = $db_found->prepare("
    SELECT 
        GROUP_CONCAT(callsign, IF (netcontrol IS NULL, '', CONCAT(' ',netcontrol))
            ORDER BY logdate ASC SEPARATOR '<br> ') AS callList,
        GROUP_CONCAT(callsign ORDER BY logdate ASC SEPARATOR '<br> ') AS lowcase_callList,
        GROUP_CONCAT(callsign ORDER BY logdate ASC SEPARATOR ' ' ) AS callList2,
        GROUP_CONCAT(callsign,' (',county,'Co.)' ORDER BY logdate ASC SEPARATOR '<br> ') AS callCntyList,
        GROUP_CONCAT(callsign,' (',county,'Co., Dist: ',district,')' ORDER BY logdate ASC SEPARATOR '<br> ') AS callCntyDistList,
        GROUP_CONCAT(callsign ORDER BY callsign SEPARATOR '<br> ' ) AS callListAlpha,
        GROUP_CONCAT(Fname,', ',callsign ORDER BY callsign SEPARATOR '<br> ' ) AS nameAlpha
    FROM NetLog
    WHERE netID = :netID
    ORDER BY 
        CASE netcontrol
            WHEN 'PRM' THEN 0
            WHEN 'CMD' THEN 0
            WHEN 'TL' THEN 0
            WHEN '2nd' THEN 1
            WHEN 'Log' THEN 2
            WHEN 'LSN' THEN 3
            WHEN 'EM' THEN 4
            WHEN 'PIO' THEN 5
            WHEN 'SEC' THEN 6
            WHEN 'RELAY' THEN 7
            else 99
        END,
        dttm
");

$sql->bindValue(':netID', $netID, PDO::PARAM_INT);
$sql->execute();
$result = $sql->fetch(PDO::FETCH_ASSOC);

$netTitle = "NCS heard List for Net #$netID<br>";

$versions = [
    'Version1a: Lower Case Horizontal' => strtolower($netTitle . "Stations listed in net order: $result[lowcase_callList]"),
    'Version2: Horizontal' => $netTitle . "Stations listed in net order: $result[callList2]",
    'Version3: Vertical Combo' => $netTitle . "Stations listed in net order: $result[callCntyList]",
    'Version4: Vertical Combo with District' => $netTitle . "Stations listed in net order: $result[callCntyDistList]",
    'Version5: Vertical & Alphabetized' => $netTitle . "$result[callListAlpha]",
    'Version6: Vertical & With Name' => $netTitle . "$result[nameAlpha]"
];

echo $netTitle . "Stations listed in net order:<br>$result[callList]";

foreach ($versions as $title => $content) {
    echo "<div><br><br>$title<br><br>$content<div>";
}

echo "<div><input type=\"button\" onclick=\"javascript:window.close()\" value=\"Close\" style=\"float:right; padding-left: 20px;\"></div>";
echo "<div><p>Send me an example of anything else you might like. If the information is in the database I'll do my best to supply it.</p></div>";
echo "<p>buildHeardList.php</p>";
?>