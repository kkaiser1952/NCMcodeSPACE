<?php
// getTimeLog.php
// This PHP only reads what's in the TimeLog table. It does not write to it.
// TimeLog is written to by save.php, in this case when the comments column is modified.

require_once "dbConnectDtls.php";

$q = intval($_GET['q']);

try {
    // Get the start time of the net 
    $sql = $db_found->prepare("SELECT MIN(logdate) as minTime, callsign FROM NetLog WHERE netID = :q LIMIT 1");
    $sql->execute([':q' => $q]);
    $result = $sql->fetch(PDO::FETCH_ASSOC);
    $ts = $result['minTime'];
    $startCallsign = $result['callsign'];

    // Get the count of entries in the TimeLog
    $sql = $db_found->prepare("SELECT COUNT(*) as maxCount FROM TimeLog WHERE netID = :q");
    $sql->execute([':q' => $q]);
    $maxCount = $sql->fetchColumn() + 1;

    $tbody = "<tbody style=\"counter-reset: sortabletablescope $maxCount;\">";

    $header = '<table class="sortable2" id="pretimeline" style="width:100%;">
        <thead> 
            <tr>
                <th>Date Time</th>
                <th>ID</th>
                <th>Callsign</th>
                <th style="width:1400px; max-width:1400px;">Comments Report</th>
            </tr>
        </thead>';

    $sql = $db_found->prepare("SELECT timestamp, ID, callsign, comment FROM TimeLog WHERE netID = :q ORDER BY timestamp DESC");
    $sql->execute([':q' => $q]);

    echo $header;  
    echo $tbody;

    while ($row = $sql->fetch(PDO::FETCH_ASSOC)) {
        $class = isset($row['comment']) && strlen($row['comment']) >= 300 ? 'scrollable' : 'nonscrollable';

        echo "<tr>";
        echo "<td nowrap>" . htmlspecialchars($row['timestamp']) . "</td>";
        echo "<td>" . htmlspecialchars($row['ID']) . "</td>";
        echo "<td>" . htmlspecialchars($row['callsign']) . "</td>";
        echo "<td><div class='$class'>" . htmlspecialchars($row['comment']) . "</div></td>";
        echo "</tr>";
    }

    // Add the start of net row
    echo "<tr>";
    echo "<td>" . htmlspecialchars($ts) . "</td>";
    echo "<td></td>";
    echo "<td>" . htmlspecialchars($startCallsign) . "</td>";
    echo "<td>Start of Net</td>";
    echo "</tr></tbody></table>";

} catch(PDOException $e) {
    error_log("Error in getTimeLog.php: " . $e->getMessage());
    echo "An error occurred. Please try again later.";
}
?>