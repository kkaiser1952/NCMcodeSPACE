<?php
// buildThreeDropdowns.php
// Written: 2017
// Updated: 2024-09-24

require_once "dbConnectDtls.php";  // Access to MySQL
require_once "database_logger.php";  // Include the DatabaseLogger

class DropdownBuilder {
    private $db;
    private $logger;

    public function __construct(PDO $db, DatabaseLogger $logger) {
        $this->db = $db;
        $this->logger = $logger;
    }

    private function executeQuery($query) {
        $start = microtime(true);
        $result = $this->db->query($query);
        $duration = microtime(true) - $start;
        $this->logger->log_query($query, 'ncm', $duration);
        return $result;
    }

    public function buildGroupList() {
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
        $groupResult = $this->executeQuery($groupQuery);
        $groupList = '';
        foreach ($groupResult as $net) {
            if ($net['call'] != '') {
                $groupList .= "<a href='#{$net['id2']}' onclick='putInGroupInput(this);'>'{$net['call']}' ---> {$net['org']}</a>\n";
            }
        }
        return $groupList;
    }

    public function buildKindList() {
        $kindQuery = "
            SELECT CONCAT(t2.id, ';', t2.kindofnet) AS id3,
                kindofnet
            FROM NetKind t2
            WHERE t2.kindofnet != ''
            ORDER BY kindofnet
        ";
        $kindResult = $this->executeQuery($kindQuery);
        $kindList = '';
        foreach ($kindResult as $net) {
            $kindList .= "<a href='#{$net['id3']}' onclick='putInKindInput(this);'>{$net['kindofnet']}</a>\n";
        }
        return $kindList;
    }

    public function buildFreqList() {
        $freqQuery = "
            SELECT CONCAT(t1.id, ';', t1.`freq`) AS id4,
                freq
            FROM NetKind t1
            WHERE t1.freq != ''
            ORDER BY freq
        ";
        $freqResult = $this->executeQuery($freqQuery);
        $freqList = '';
        foreach ($freqResult as $net2) {
            $freqList .= "<a href='#{$net2['id4']}' onclick='putInFreqInput(this);'>{$net2['freq']}</a>\n";
        }
        return $freqList;
    }
}

// Usage See index.php
/*
$dropdownBuilder = new DropdownBuilder($db_found, new DatabaseLogger());
$groupList = $dropdownBuilder->buildGroupList();
$kindList = $dropdownBuilder->buildKindList();
$freqList = $dropdownBuilder->buildFreqList();
*/
?>