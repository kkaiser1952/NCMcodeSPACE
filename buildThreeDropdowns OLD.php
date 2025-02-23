<?php
// buildThreeDropdowns.php called by index.php
// V2 Updated: 2024-06-03

require_once "dbConnectDtls.php";  // Access to MySQL
require_once "database_logger.php";  // Include the DatabaseLogger

$groupList = '';
$kindList = '';
$freqList = '';

// Group query
$groupQuery = "
    SELECT t1.id,
        t1.`call`,
        t1.`orgType`,
        t1.`org`,
        t1.freq,
        t1.`kindofnet`,
        t2.`kindofnet` AS dfltKon,
        t3.freq AS dfltFreq,
        CHAR_LENGTH(t1.`orgType`) AS otl,
        CONCAT(t1.id, ';', t2.kindofnet, ';', t3.freq, ';', t1.`call`, ';', t1.`org`) AS id2,
        CONCAT(t1.id, ';', t2.kindofnet, ';', t3.freq, ';', t1.`kindofnet`) AS id3,
        REPLACE(CONCAT(t1.id, ';', t2.kindofnet, ';', t3.freq, ';', t1.`freq`), ' ', '') AS id4
    FROM NetKind t1
    LEFT JOIN NetKind t2 ON t1.dflt_kind = t2.id
    LEFT JOIN NetKind t3 ON t1.dflt_freq = t3.id
    ORDER BY `orgType`, `org`
";

$start = microtime(true);
$groupResult = $db_found->query($groupQuery);
$duration = microtime(true) - $start;
DatabaseLogger::log_query($groupQuery, 'ncm', $duration);

foreach ($groupResult as $net) {
    if ($net['call'] != '') {
        $groupList .= "<a href='#{$net['id2']}' onclick='putInGroupInput(this);'>'{$net['call']}' ---> {$net['org']}</a>\n";
    }
    $thisOrgType = $net['orgType'];
}

// Kind query
$kindQuery = "
    SELECT CONCAT(t2.id, ';', t2.kindofnet) AS id3,
        kindofnet
    FROM NetKind t2
    WHERE t2.kindofnet != ''
    ORDER BY kindofnet
";

$start = microtime(true);
$kindResult = $db_found->query($kindQuery);
$duration = microtime(true) - $start;
DatabaseLogger::log_query($kindQuery, 'ncm', $duration);

foreach ($kindResult as $net) {
    $kindList .= "<a href='#{$net['id3']}' onclick='putInKindInput(this);'>{$net['kindofnet']}</a>\n";
}

// Freq query
$freqQuery = "
    SELECT CONCAT(t1.id, ';', t1.`freq`) AS id4,
        freq
    FROM NetKind t1
    WHERE t1.freq != ''
    ORDER BY freq
";

$start = microtime(true);
$freqResult = $db_found->query($freqQuery);
$duration = microtime(true) - $start;
DatabaseLogger::log_query($freqQuery, 'ncm', $duration);

foreach ($freqResult as $net2) {
    $freqList .= "<a href='#{$net2['id4']}' onclick='putInFreqInput(this);'>{$net2['freq']}</a>\n";
}
?>