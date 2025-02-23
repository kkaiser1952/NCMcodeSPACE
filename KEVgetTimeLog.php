<?php
// This PHP only reads whats in the TimeLog table it does
// not write to it. TimeLog is written to by save.php, in
// this case when the comments column is modified.

require_once "dbConnectDtls.php";

$q = intval($_GET['netID']);
//$q = 5961;

try {  // Get the start time of the net
    $sql = "SELECT timestamp, ID, callsign, comment from TimeLog where netID = $q ORDER BY timestamp DESC";

    $stmt = $db_found->prepare($sql);
    $stmt->execute();

    $result = $stmt->fetchAll();
    $json = json_encode($result);
    echo $json;
} catch (PDOException $e) {
    echo $sql . "<br>" . $e->getMessage();
}
?>