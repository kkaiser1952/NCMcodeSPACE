<?php
// closeLog.php
// V2 Updated: 2024-05-17

ini_set('display_errors', 1);
error_reporting(E_ALL);

date_default_timezone_set("America/Chicago");
require_once "dbConnectDtls.php";
require_once "WXdisplay.php";

$q = isset($_POST['q']) ? strip_tags(substr($_POST['q'], 0, 100)) : '';
echo $q;

$parts = explode(",", $q);
$netcall = strtoupper($parts[0]); // The netID number
$cs1 = strtoupper($parts[1]); // Call sign of the station that closed the net
$ipaddress = getRealIpAddr();
echo $ipaddress;

$close = now(CONST_USER_TIMEZONE, CONST_SERVER_TIMEZONE, CONST_SERVER_DATEFORMAT);

try {
    $db_found = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $db_found->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Insert the log closing time into the TimeLog table
    $comment = "The log was closed, ICS-214 Created";
    $sql1 = "INSERT INTO TimeLog (netID, callsign, comment, timestamp, ipaddress)
             VALUES (:netcall, :cs1, :comment, :close, :ipaddress)";
    $stmt1 = $db_found->prepare($sql1);
    $stmt1->bindParam(':netcall', $netcall);
    $stmt1->bindParam(':cs1', $cs1);
    $stmt1->bindParam(':comment', $comment);
    $stmt1->bindParam(':close', $close);
    $stmt1->bindParam(':ipaddress', $ipaddress);
    $stmt1->execute();

    // Update the NetLog table to set logclosedtime for all stations
    $sql2 = "UPDATE NetLog SET logclosedtime = :close WHERE netID = :netID";
    $stmt2 = $db_found->prepare($sql2);
    $stmt2->bindParam(':close', $close);
    $stmt2->bindParam(':netID', $q);
    $stmt2->execute();

    // Update the NetLog table to set timeout, status, ipaddress, and timeonduty
    $sql3 = "UPDATE NetLog
             SET timeout = :close,
                 status = '1',
                 ipaddress = :ipaddress,
                 timeonduty = (TIMESTAMPDIFF(SECOND, logdate, :close) + timeonduty)
             WHERE netID = :netID
               AND timeout IS NULL
               AND (status = 0 OR status IS NULL)";
    $stmt3 = $db_found->prepare($sql3);
    $stmt3->bindParam(':close', $close);
    $stmt3->bindParam(':ipaddress', $ipaddress);
    $stmt3->bindParam(':netID', $q);
    $stmt3->execute();

    // Calculate the total man hours for this net
    $sql4 = "SELECT SEC_TO_TIME(SUM(timeonduty)) AS tottime
             FROM NetLog
             WHERE netID = :netID
             GROUP BY netID";
    $stmt4 = $db_found->prepare($sql4);
    $stmt4->bindParam(':netID', $q);
    $stmt4->execute();
    $tottime = $stmt4->fetchColumn(0);

    // Generate the HTML table
    $wherestuff = $q != '0' ? "
       WHERE netID = :netID
       ORDER BY CASE
                    WHEN netcontrol IN ('PRM', 'CMD', 'TL') THEN 0
                    WHEN netcontrol IN ('2nd', '3rd', 'LSN', 'Log', 'PIO', 'EM') THEN 1
                    WHEN active = 'Out' THEN 4000
                    ELSE 2
                END,
                logdate DESC" : "GROUP BY (id) ORDER BY ID";

    $g_query = "SELECT recordID, netID, subNetOfID, id, callsign, tactical, Fname, grid, traffic, latitude, longitude,
                       netcontrol, activity, Lname, email, active, comments, frequency, creds,
                       DATE_FORMAT(logdate, '%H:%i') AS logdate, DATE_FORMAT(timeout, '%H:%i') AS timeout,
                       SEC_TO_TIME(timeonduty) AS time, netcall, status, Mode
                FROM NetLog
                $wherestuff";

    $stmt5 = $db_found->prepare($g_query);
    if ($q != '0') {
        $stmt5->bindParam(':netID', $q);
    }
    $stmt5->execute();

    $num_rows = 0;

    echo '<table class="sortable" id="thisNet">
            <thead id="thead" style="text-align: center">
                <tr>
                    <th>Role</th>
                    <th>Mode</th>
                    <th>Status</th>
                    <th>Traffic<br>YN</th>
                    <th width="5%">tt#</th>
                    <th>Callsign</th>
                    <th>First Name</th>
                    <th class="toggleLN">Last Name</th>
                    <th>Tactical</th>
                    <th class="toggleG">Grid</th>
                    <th class="toggleLAT">Latitude</th>
                    <th class="toggleLON">Longitude</th>
                    <th class="toggleE">eMail</th>
                    <th>Time In</th>
                    <th>Time Out</th>
                    <th>Time Line<br>Comments</th>
                    <th>Credentials</th>
                    <th class="toggleTOD">Time On Duty</th>
                </tr>
            </thead>
            <tbody id="netBody">';

    foreach ($stmt5 as $row) {
        $num_rows++;
        $bgcol = "";

        $netcall = isset($row['netcall']) && $row['netcall'] != "" ? $row['netcall'] : "All";

        // Set background row color based on some parameters
        $os = array("PRM", "2nd", "LSN", "Log", "PIO", "EM", "CMD", "TL", "======", 'Capt');

        if ($row['traffic'] == "Yes") {
            $bgcol = 'bgcolor="#ff9cc2"';
        } elseif (in_array($row['netcontrol'], $os)) {
            $bgcol = 'bgcolor="#bae5fc"';
        } elseif ($row['Mode'] == "Dig") {
            $bgcol = 'bgcolor="#fbc1ab"';
        } elseif ($row['netcontrol'] == null && $row['traffic'] != "Yes" && $num_rows % 2 == 0) {
            $bgcol = 'bgcolor="#EAF2D3"';
        }

        if ($row['active'] == "Out") {
            $bgcol = 'bgcolor="grey"';
        }

        $id = str_pad($row['id'], 2, '0', STR_PAD_LEFT);

        echo '<tr ' . $bgcol . ' id="' . $row['recordID'] . '">';

        if ($q != '0') {
            echo '<td class="editable_selectNC cent" id="netcontrol:' . $row['recordID'] . '">' . $row['netcontrol'] . '</td>';
            echo '<td class="editable_selectMode cent" id="Mode:' . $row['recordID'] . '">' . $row['Mode'] . '</td>';
            echo '<td class="editable_selectYNO cent" id="active:' . $row['recordID'] . '">' . $row['active'] . '</td>';
            echo '<td class="editable_selectTFC" id="traffic:' . $row['recordID'] . '">' . $row['traffic'] . '</td>';
            echo '<td class="cent">' . $row['tt'] . '</td>';
            echo '<td class="editCS1" id="callsign:' . $row['recordID'] . '" style="text-transform:uppercase">' . $row['callsign'] . '</td>';
            echo '<td class="editFnm" id="Fname:' . $row['recordID'] . '">' . $row['Fname'] . '</td>';
            echo '<td class="editLnm toggleLN" id="Lname:' . $row['recordID'] . '" style="text-transform:capitalize">' . $row['Lname'] . '</td>';
            echo '<td class="editTAC cent" id="tactical:' . $row['recordID'] . '" style="text-transform:uppercase">' . $row['tactical'] . '</td>';
            echo '<td class="editGRID toggleG" id="grid:' . $row['recordID'] . '">' . $row['grid'] . '</td>';
            echo '<td class="editLAT toggleLAT" id="latitude:' . $row['recordID'] . '">' . $row['latitude'] . '</td>';
            echo '<td class="editLON toggleLON" id="longitude:' . $row['recordID'] . '">' . $row['longitude'] . '</td>';
            echo '<td class="editEMAIL toggleE" id="email:' . $row['recordID'] . '">' . $row['email'] . '</td>';
            echo '<td class="cent" id="logdate:' . $row['recordID'] . '">' . $row['logdate'] . '</td>';
            echo '<td class="cent" id="timeout:' . $row['recordID'] . '">' . $row['timeout'] . '</td>';
            echo '<td class="editC" id="comments:' . $row['recordID'] . '" onClick="empty(\'comments:' . $row['recordID'] . '\');">' . $row['comments'] . '</td>';
            echo '<td class="editCREDS" id="creds:' . $row['recordID'] . '">' . $row['creds'] . '</td>';
            echo '<td class="toggleTOD cent" id="timeonduty:' . $row['recordID'] . '">' . $row['time'] . '</td>';
            echo '<td id="delete:' . $row['recordID'] . '"><a href="#" class="delete cent"><img alt="x" border="0" src="images/delete.png" /></a></td>';
        } else {
            echo '<td>' . $row['tt'] . '</td>';
            echo '<td class="editCS1" id="callsign:' . $row['recordID'] . '" style="text-transform:uppercase">' . $row['callsign'] . '</td>';
            echo '<td class="editFnm" id="Fname:' . $row['recordID'] . '">' . $row['Fname'] . '</td>';
            echo '<td class="editLnm" id="Lname:' . $row['recordID'] . '" style="text-transform:capitalize">' . $row['Lname'] . '</td>';
            echo '<td class="editTAC cent" id="tactical:' . $row['recordID'] . '" style="text-transform:uppercase">' . $row['tactical'] . '</td>';
            echo '<td class="editGRID" id="grid:' . $row['recordID'] . '">' . $row['grid'] . '</td>';
            echo '<td class="editLAT" id="latitude:' . $row['recordID'] . '">' . $row['latitude'] . '</td>';
            echo '<td class="editLON" id="longitude:' . $row['recordID'] . '">' . $row['longitude'] . '</td>';
            echo '<td class="editEMAIL" id="email:' . $row['recordID'] . '">' . $row['email'] . '</td>';
            echo '<td class="editCREDS" id="creds:' . $row['recordID'] . '">' . $row['creds'] . '</td>';
            echo '<td class="editTOD cent" id="timeonduty:' . $row['recordID'] . '">' . $row['time'] . '</td>';
        }

        echo '</tr>';
    }

    echo '</tbody>
          <tfoot>
            <tr>
              <td colspan="12" align="right">Total Man Hours = ' . $tottime . '</td>
            </tr>
          </tfoot>
        </table>';

    echo '<div hidden id="freq2">' . $row['frequency'] . '</div>';
    echo '<div hidden id="freq"></div>';
    echo '<div hidden id="type">Type of Net: ' . $row['activity'] . '</div>';
    echo '<div hidden id="idofnet">Net ID: ' . $row['netID'] . '</div>';
    echo '<div hidden id="activity">' . $row['activity'] . '</div>';
} catch (PDOException $e) {
    echo $g_query . "<br>" . $e->getMessage();
}
?>