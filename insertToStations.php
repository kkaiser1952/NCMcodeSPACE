<?php
// insertToStations.php
// V3 Updated: 2024-06-03

require_once "dbConnectDtls.php";

try {
    // Get the next ID 
    $sql = "SELECT MAX(ID) + 1 as nextid
            FROM stations 
            WHERE ID < 38000
            LIMIT 1";
    
    $stmt = $db_found->prepare($sql);
    $stmt->execute();
    $nextid = $stmt->fetchColumn();
    
    if ($nextid === false) {
        throw new Exception("Failed to retrieve the next ID.");
    }
    
    // Insert the new station record
    $sql = "INSERT INTO stations 
            (ID, callsign, Fname, Lname, grid, tactical,
             email, fccid, latitude, longitude, creds, county, 
             state, district, home, city, phone, zip, lastLogDT, 
             firstLogDT)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)";
    
    $stmt = $db_found->prepare($sql);
    
    if (!$stmt) {
        throw new Exception("Failed to prepare the SQL statement.");
    }
    
    $stmt->bindValue(1, $nextid, PDO::PARAM_INT);
    $stmt->bindValue(2, $csbase, PDO::PARAM_STR);
    $stmt->bindValue(3, $Fname, PDO::PARAM_STR);
    $stmt->bindValue(4, $Lname, PDO::PARAM_STR);
    $stmt->bindValue(5, $grid, PDO::PARAM_STR);
    $stmt->bindValue(6, $tactical, PDO::PARAM_STR);
    $stmt->bindValue(7, $email, PDO::PARAM_STR);
    $stmt->bindValue(8, $fccid, PDO::PARAM_STR);
    $stmt->bindValue(9, $latitude, PDO::PARAM_STR);
    $stmt->bindValue(10, $longitude, PDO::PARAM_STR);
    $stmt->bindValue(11, $creds, PDO::PARAM_STR);
    $stmt->bindValue(12, $county, PDO::PARAM_STR);
    $stmt->bindValue(13, $state, PDO::PARAM_STR);
    $stmt->bindValue(14, $district, PDO::PARAM_STR);
    $stmt->bindValue(15, $home, PDO::PARAM_STR);
    $stmt->bindValue(16, $city, PDO::PARAM_STR);
    $stmt->bindValue(17, ' ', PDO::PARAM_STR);
    $stmt->bindValue(18, $zip, PDO::PARAM_STR);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to execute the SQL statement.");
    }
    
    echo "New station record inserted successfully.";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    error_log("Error in insertToStations.php: " . $e->getMessage());
}
?>