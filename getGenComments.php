<?php
// getGenComments.php
// Written: ??
// V2 Updated: 2024-07-01

error_reporting(0);
// date_default_timezone_set("America/Chicago");
require_once "dbConnectDtls.php";

//$q = intval($_GET['q']);
$q = isset($_GET['q']) ? $_GET['q'] : '';

// The if condition prevents the program from using the netID = 0 in the SQL
if ($q > 0) {
    // This SQL gets the genComm from the TimeLog
    $sql = "SELECT comment 
              FROM `TimeLog` 
             WHERE callsign = 'GENCOMM'
               AND id = 0 
               AND netID = :netID
             ORDER BY uniqueID DESC
             LIMIT 0,1";

    $stmt = $db_found->prepare($sql);
    $stmt->bindParam(':netID', $q, PDO::PARAM_INT);
    $stmt->execute();

    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result) {
        $comment = $result['comment'];
        // The htmlspecialchars(urldecode() stuff is so characters after the ampersand are still returned
        // see the example URL below
        echo htmlspecialchars(urldecode($comment));
    }
}

/* https://goto.webcasts.com/starthere.jsp?ei=1340938&tp_key=db6ffe8646 */
?>
