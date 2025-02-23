<!doctype html>
<?php
    /* This program uses any changed email address, Fname, Lname, creds in the NetLog Table to update the stations table */
    /* Written: 2021-03-29 */
    /* V2 Updated: 2024-05-14 */
    
    ini_set('display_errors', 1); 
    error_reporting(E_ALL ^ E_NOTICE);

    require_once "dbConnectDtls.php";
    
    // Assuming you have already established the database connections
    // $db_found for the main database
    // $fcc_found for the FCC database

    $open = date('Y-m-d H:i:s');

    /* fccid from netcontrolcp_fcc_amateur.en */
    $sql = "
        UPDATE stations st
        INNER JOIN (
            SELECT callsign, fccid
            FROM netcontrolcp_fcc_amateur.en
            WHERE callsign IN (
                SELECT callsign
                FROM stations
                WHERE LEFT(callsign, 1) IN ('a', 'k', 'n', 'w')
            )
        ) fcc ON st.callsign = fcc.callsign
        SET st.fccID = fcc.fccid,
            st.dttm = ?
        WHERE fcc.fccid <> st.fccID
    ";

    $stmt = $db_found->prepare($sql);
    $stmt->bindValue(1, $open);
    $stmt->execute();

    /* The district is updated from the HPD table */
    $sql = "
        UPDATE stations st
        INNER JOIN HPD hp ON st.county = hp.county AND st.state = hp.state
        SET st.district = hp.district
        WHERE hp.state = st.state
        AND hp.county = st.county
        AND (st.district = '' OR st.district IS NULL)
        AND LEFT(st.callsign, 1) IN ('a', 'k', 'n', 'w')
    ";
    $db_found->exec($sql);

    $today = date("Y-m-d");

    /* List all the TE0ST netID's */
    $sql = "
        SELECT GROUP_CONCAT(DISTINCT netID) AS netids,
               COUNT(DISTINCT netID) AS theCount
        FROM NetLog
        WHERE netcall LIKE '%te0st'
    ";
    $stmt = $db_found->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetch();
    $testNets = $result['netids'];
    $theCount = $result['theCount'];
    echo "List of $theCount test nets:<br>$testNets<br><br>";

    /* List all the open nets */
    $sql = "
        SELECT GROUP_CONCAT(DISTINCT netID) AS netids,
               COUNT(DISTINCT netID) AS theCount
        FROM NetLog
        WHERE status = 0 /* 0 is open, 1 is closed */
        AND pb = 0 /* 0 is not a pre-built net, 1 is */
        ORDER BY netID DESC
    ";
    $stmt = $db_found->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetch();
    $openNets = $result['netids'];
    $theCount = $result['theCount'];
    echo "<p style='columns: 20px 2; column-gap: 10px;'>List of $theCount open nets:<br>$openNets<br><br></p>";

    /* List of bad callsigns in stations */
    $sql = "
        SELECT GROUP_CONCAT(callsign) AS badCalls
        FROM stations
        WHERE state = ''
        AND callsign NOT LIKE 'nonham%' AND ID < 38000
        AND callsign NOT LIKE 'emcomm%' AND ID < 38000
        AND callsign NOT LIKE ('AF%') AND callsign NOT LIKE ('AAA%')
        AND callsign NOT LIKE ('AAR%')
        AND (callsign LIKE 'a%' OR callsign LIKE 'k%' OR callsign LIKE 'n%' OR callsign LIKE 'w%')
    ";
    $stmt = $db_found->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetch();
    $badCalls = $result['badCalls'];
    echo "<p>List of Bad callsigns in the stations table:<br>$badCalls<br><br><br></p>";

    /* Report on number of updates */
    $sql = "
        SELECT COUNT(callsign) AS count
        FROM stations
        WHERE dttm >= DATE_SUB(NOW(), INTERVAL 0.5 HOUR)
    ";
    $stmt = $db_found->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetch();
    $count = $result['count'];
    echo "updateStationsWithEmail.php Program <br>Records Updated: $count";
?>