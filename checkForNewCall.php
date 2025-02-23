<?php
// checkForNewCall.php
// V3 Updated: 2024-06-06

error_log("Entering file: " . __FILE__);

require_once "dbConnectDtls.php";
require_once "geocode.php";
require_once "GridSquare.php";

// Clean up the incoming data which should look like this  WA0TJT
// var q from BuildNewGroup is  thiscall+","+thisemail+","+thisname;
$q = $_POST["q"] ?? '';
$parts = explode(",", $q);
$thiscall = strtoupper($parts[0] ?? '');
$thisemail = $parts[1] ?? '';
$thisname = $parts[2] ?? '';
$name = explode(" ", $thisname);
$Fname = $name[0] ?? '';
$Lname = $name[1] ?? '';

// This first SQL checks to see if the callsign is already in the DB, if it is nothing happens, if its not it gets added.
$sql1 = "SELECT COUNT(*) FROM NetLog WHERE callsign = :thiscall";
$stmt1 = $db_found->prepare($sql1);
$stmt1->bindValue(':thiscall', $thiscall);
$stmt1->execute();
$count = $stmt1->fetchColumn();

if ($count > 0) {
    $sql2 = "SELECT ID, callsign FROM NetLog WHERE callsign = :thiscall LIMIT 0, 1";
    $stmt2 = $db_found->prepare($sql2);
    $stmt2->bindValue(':thiscall', $thiscall);
    $stmt2->execute();
    $row = $stmt2->fetch();
    // Do nothing with this call
} else {
    // This gets the next ID number for the new callsign
    $sql2 = "
        SELECT MIN(unused) AS unused
        FROM (
            SELECT MIN(t1.id) + 1 AS unused
            FROM NetLog AS t1
            WHERE NOT EXISTS (
                SELECT *
                FROM NetLog AS t2
                WHERE t2.id = t1.id + 1
            )
            UNION
            SELECT 1
            FROM DUAL
            WHERE NOT EXISTS (
                SELECT *
                FROM NetLog
                WHERE id = 1
            )
        ) AS subquery
    ";
    $stmt2 = $db_found->prepare($sql2);
    $stmt2->execute();
    $result = $stmt2->fetch();
    $id = $result['unused'] ?? 0;

    // Find the other variable values
    $fccsql = "
        SELECT first, last, full_name, state, CONCAT_WS(' ', address1, city, state, zip) AS fulladdress
        FROM netcontrolcp_fcc_amateur.en
        WHERE callsign = :thiscall
        AND fccid = (
            SELECT MAX(fccid)
            FROM netcontrolcp_fcc_amateur.en
            WHERE callsign = :thiscall
        )
        LIMIT 0, 1
    ";
    $stmt3 = $db_found->prepare($fccsql);
    $stmt3->bindValue(':thiscall', $thiscall);
    $stmt3->execute();
    $rows = $stmt3->rowCount();

    // Do this if something is returned
    if ($rows === 1) {
        $result = $stmt3->fetch();
        // Convert first & last name into proper case (first letter uppercase)
        $Fname = ucfirst(strtolower($result['first'] ?? ''));
        $Lname = ucfirst(strtolower($result['last'] ?? ''));
        $state2 = $result['state'] ?? '';
        $fulladdress = $result['fulladdress'] ?? '';
        $firstLogIn = 1;

        // This happens either way but really doesn't matter
        $koords = geocode($fulladdress);
        $latitude = $koords[0] ?? '';
        $longitude = $koords[1] ?? '';
        $county = $koords[2] ?? '';
        $state = $koords[3] ?? '';
        if ($state === '') {
            $state = $state2;
        }
        $gridd = gridsquare($latitude, $longitude);
        $grid = implode('', $gridd);

        echo "$Fname $Lname<br>full_name= {$result['full_name']}<br>state= $state2<br> $fulladdress<br> $latitude, $longitude<br>county= $county<br> $state <br>grid= $grid <br><br>";
    }
}

$sql3 = "
    INSERT INTO NetLog (netID, ID, callsign, Fname, Lname, email, latitude, longitude, grid, county, state, tt)
    VALUES ('0', :id, :thiscall, :Fname, :Lname, :thisemail, :latitude, :longitude, :grid, :county, :state, :id)
";
$stmt4 = $db_found->prepare($sql3);
$stmt4->bindValue(':id', $id);
$stmt4->bindValue(':thiscall', $thiscall);
$stmt4->bindValue(':Fname', $Fname);
$stmt4->bindValue(':Lname', $Lname);
$stmt4->bindValue(':thisemail', $thisemail);
$stmt4->bindValue(':latitude', $latitude);
$stmt4->bindValue(':longitude', $longitude);
$stmt4->bindValue(':grid', $grid);
$stmt4->bindValue(':county', $county);
$stmt4->bindValue(':state', $state);
$stmt4->execute();

echo "Updates Done!";
?>